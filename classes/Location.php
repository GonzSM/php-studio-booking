<?php

class Location
{
    private $db;

    public function __construct($conn)
    {
        $this->db = $conn;
    }

    public function create(string $description, int $numStudios, float $costPerHour): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO locations (description, num_studios, cost_per_hour) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sid', $description, $numStudios, $costPerHour);
        return $stmt->execute();
    }

    public function update(int $id, string $description, int $numStudios, float $costPerHour): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE locations SET description = ?, num_studios = ?, cost_per_hour = ? WHERE id = ?"
        );
        $stmt->bind_param('sidi', $description, $numStudios, $costPerHour, $id);
        return $stmt->execute();
    }

    public function getById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM locations WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return $row;
        }

        return [];
    }

    public function getAll(): array
    {
        $result = $this->db->query("SELECT * FROM locations ORDER BY description");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function search(string $id, string $description): array
    {
        $conditions = [];
        $params = [];
        $types = '';

        if ($id !== '') {
            $conditions[] = "id = ?";
            $params[] = (int) $id;
            $types .= 'i';
        }
        if ($description !== '') {
            $conditions[] = "description LIKE ?";
            $params[] = '%' . $description . '%';
            $types .= 's';
        }

        if (empty($conditions)) {
            return [];
        }

        $sql = "SELECT * FROM locations WHERE " . implode(' AND ', $conditions) . " ORDER BY description";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getWithAvailableStudios(): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*,
                    l.num_studios - COALESCE(booked.count, 0) AS available_studios
             FROM locations l
             LEFT JOIN (
                 SELECT location_id, COUNT(*) AS count
                 FROM bookings
                 WHERE booking_date = CURDATE()
                 AND start_time <= CURTIME()
                 AND ADDTIME(start_time, SEC_TO_TIME(duration * 3600)) > CURTIME()
                 AND status = 'confirmed'
                 GROUP BY location_id
             ) booked ON l.id = booked.location_id
             HAVING available_studios > 0
             ORDER BY l.description"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFullyBooked(): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*
             FROM locations l
             INNER JOIN (
                 SELECT location_id, COUNT(*) AS count
                 FROM bookings
                 WHERE booking_date = CURDATE()
                 AND start_time <= CURTIME()
                 AND ADDTIME(start_time, SEC_TO_TIME(duration * 3600)) > CURTIME()
                 AND status = 'confirmed'
                 GROUP BY location_id
             ) booked ON l.id = booked.location_id AND booked.count >= l.num_studios
             ORDER BY l.description"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
