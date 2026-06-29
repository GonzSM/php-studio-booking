<?php

class Booking
{
    private $db;

    public function __construct($conn)
    {
        $this->db = $conn;
    }

    public function create(int $userId, int $locationId, string $bookingDate, string $startTime, int $duration): array
    {
        if ($bookingDate < date('Y-m-d')) {
            return ['success' => false, 'message' => 'Booking date cannot be in the past.'];
        }

        $startHour = (int) substr($startTime, 0, 2);
        $endHour = $startHour + $duration;

        if ($startHour < STUDIO_OPEN_HOUR || $endHour > STUDIO_CLOSE_HOUR) {
            return ['success' => false, 'message' => 'Session must be between 10:00 AM and 10:00 PM.'];
        }

        if ($duration < 1 || $duration > 12) {
            return ['success' => false, 'message' => 'Duration must be between 1 and 12 hours.'];
        }

        if (!$this->isStudioAvailable($locationId, $bookingDate, $startTime, $duration)) {
            return ['success' => false, 'message' => 'No studios available at this location for the selected time.'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO bookings (user_id, location_id, booking_date, start_time, duration, status)
             VALUES (?, ?, ?, ?, ?, 'confirmed')"
        );
        $stmt->bind_param('iissi', $userId, $locationId, $bookingDate, $startTime, $duration);

        if ($stmt->execute()) {
            $bookingId = $this->db->insert_id;
            return [
                'success' => true,
                'booking_id' => $bookingId,
                'message' => 'Booking confirmed successfully.'
            ];
        }

        return ['success' => false, 'message' => 'Failed to create booking.'];
    }

    public function update(int $bookingId, string $bookingDate, string $startTime, int $duration): array
    {
        if (!$this->canModify($bookingId)) {
            return ['success' => false, 'message' => 'Cannot modify a session that has already started.'];
        }

        if ($bookingDate < date('Y-m-d')) {
            return ['success' => false, 'message' => 'Booking date cannot be in the past.'];
        }

        if ($duration < 1 || $duration > 12) {
            return ['success' => false, 'message' => 'Duration must be between 1 and 12 hours.'];
        }

        $startHour = (int) substr($startTime, 0, 2);
        $endHour = $startHour + $duration;

        if ($startHour < STUDIO_OPEN_HOUR || $endHour > STUDIO_CLOSE_HOUR) {
            return ['success' => false, 'message' => 'Session must be between 10:00 AM and 10:00 PM.'];
        }

        $booking = $this->getById($bookingId);
        if (!$this->isStudioAvailable($booking['location_id'], $bookingDate, $startTime, $duration, $bookingId)) {
            return ['success' => false, 'message' => 'No studios available at this location for the selected time.'];
        }

        $stmt = $this->db->prepare(
            "UPDATE bookings SET booking_date = ?, start_time = ?, duration = ? WHERE id = ?"
        );
        $stmt->bind_param('ssii', $bookingDate, $startTime, $duration, $bookingId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Booking updated successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to update booking.'];
    }

    public function cancel(int $bookingId): array
    {
        if (!$this->canModify($bookingId)) {
            return ['success' => false, 'message' => 'Cannot cancel a session that has already started.'];
        }

        $stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $bookingId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Booking cancelled successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to cancel booking.'];
    }

    public function getById(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, l.description AS location_name, l.cost_per_hour, u.name AS client_name
             FROM bookings b
             INNER JOIN locations l ON b.location_id = l.id
             INNER JOIN users u ON b.user_id = u.id
             WHERE b.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return $row;
        }

        return [];
    }

    public function getUpcomingByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, l.description AS location_name, l.cost_per_hour
             FROM bookings b
             INNER JOIN locations l ON b.location_id = l.id
             WHERE b.user_id = ?
             AND b.status = 'confirmed'
             AND (b.booking_date > CURDATE()
                  OR (b.booking_date = CURDATE()
                      AND ADDTIME(b.start_time, SEC_TO_TIME(b.duration * 3600)) > CURTIME()))
             ORDER BY b.booking_date, b.start_time"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getCompletedByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, l.description AS location_name, l.cost_per_hour
             FROM bookings b
             INNER JOIN locations l ON b.location_id = l.id
             WHERE b.user_id = ?
             AND (b.status = 'cancelled'
                  OR (b.booking_date < CURDATE()
                      OR (b.booking_date = CURDATE()
                          AND ADDTIME(b.start_time, SEC_TO_TIME(b.duration * 3600)) <= CURTIME())))
             ORDER BY b.booking_date DESC, b.start_time DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllBookings(): array
    {
        $result = $this->db->query(
            "SELECT b.*, l.description AS location_name, l.cost_per_hour, u.name AS client_name
             FROM bookings b
             INNER JOIN locations l ON b.location_id = l.id
             INNER JOIN users u ON b.user_id = u.id
             ORDER BY b.booking_date DESC, b.start_time DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function canModify(int $bookingId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM bookings WHERE id = ? AND status = 'confirmed'
             AND (booking_date > CURDATE()
                  OR (booking_date = CURDATE() AND start_time > CURTIME()))"
        );
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function isStudioAvailable(int $locationId, string $date, string $startTime, int $duration, int $excludeBookingId = 0): bool
    {
        $endHour = (int) substr($startTime, 0, 2) + $duration;
        $endTime = $endHour . ':00:00';

        if ($excludeBookingId > 0) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS booked
                 FROM bookings
                 WHERE location_id = ? AND booking_date = ? AND status = 'confirmed'
                 AND id != ?
                 AND start_time < ? AND ADDTIME(start_time, SEC_TO_TIME(duration * 3600)) > ?"
            );
            $stmt->bind_param('isiss', $locationId, $date, $excludeBookingId, $endTime, $startTime);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS booked
                 FROM bookings
                 WHERE location_id = ? AND booking_date = ? AND status = 'confirmed'
                 AND start_time < ? AND ADDTIME(start_time, SEC_TO_TIME(duration * 3600)) > ?"
            );
            $stmt->bind_param('isss', $locationId, $date, $endTime, $startTime);
        }

        $stmt->execute();
        $booked = $stmt->get_result()->fetch_assoc()['booked'];

        $stmt2 = $this->db->prepare("SELECT num_studios FROM locations WHERE id = ?");
        $stmt2->bind_param('i', $locationId);
        $stmt2->execute();
        $location = $stmt2->get_result()->fetch_assoc();

        return $booked < $location['num_studios'];
    }
}
