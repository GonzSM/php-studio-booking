<?php

class User
{
    private $db;

    public function __construct($conn)
    {
        $this->db = $conn;
    }

    public function register(string $name, string $phone, string $email, string $password, string $type = 'client'): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, phone, email, password, type) VALUES (?, ?, ?, ?, ?)"
        );
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param('sssss', $name, $phone, $email, $hashed, $type);

        return $stmt->execute();
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return [];
    }

    public function getById(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return $row;
        }

        return [];
    }

    public function getAllClients(): array
    {
        $result = $this->db->query("SELECT * FROM users WHERE type = 'client' ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function searchClients(string $id, string $name, string $email, string $phone): array
    {
        $conditions = ["type = 'client'"];
        $params = [];
        $types = '';

        if ($id !== '') {
            $conditions[] = "id = ?";
            $params[] = (int) $id;
            $types .= 'i';
        }
        if ($name !== '') {
            $conditions[] = "name LIKE ?";
            $params[] = '%' . $name . '%';
            $types .= 's';
        }
        if ($email !== '') {
            $conditions[] = "email LIKE ?";
            $params[] = '%' . $email . '%';
            $types .= 's';
        }
        if ($phone !== '') {
            $conditions[] = "phone LIKE ?";
            $params[] = '%' . $phone . '%';
            $types .= 's';
        }

        $sql = "SELECT * FROM users WHERE " . implode(' AND ', $conditions) . " ORDER BY name";
        $stmt = $this->db->prepare($sql);

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getClientsWithActiveBooking(): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT u.* FROM users u
             INNER JOIN bookings b ON u.id = b.user_id
             WHERE u.type = 'client'
             AND b.booking_date = CURDATE()
             AND b.start_time <= CURTIME()
             AND ADDTIME(b.start_time, SEC_TO_TIME(b.duration * 3600)) > CURTIME()
             AND b.status = 'confirmed'"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
