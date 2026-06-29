<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Booking.php';
requireLogin();

$bookingObj = new Booking($conn);
$bookings = $bookingObj->getCompletedByUser(currentUserId());

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Completed Sessions</h1>

<div class="filter-bar">
    <a href="/pages/bookings/list.php" class="btn">Upcoming</a>
    <a href="/pages/bookings/history.php" class="btn btn-primary">History</a>
</div>

<?php if (empty($bookings)): ?>
    <p>No completed sessions found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Date</th>
                <th>Start</th>
                <th>Duration</th>
                <th>Cost</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['location_name']) ?></td>
                    <td><?= $b['booking_date'] ?></td>
                    <td><?= substr($b['start_time'], 0, 5) ?></td>
                    <td><?= $b['duration'] ?>h</td>
                    <td>$<?= $b['cost_per_hour'] * $b['duration'] ?></td>
                    <td><?= ucfirst($b['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
