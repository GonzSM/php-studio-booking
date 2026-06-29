<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Booking.php';
requireLogin();

$bookingObj = new Booking($conn);

if (isAdmin()) {
    $bookings = $bookingObj->getAllBookings();
    $title = 'All Bookings';
} else {
    $bookings = $bookingObj->getUpcomingByUser(currentUserId());
    $title = 'My Current & Future Bookings';
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1><?= $title ?></h1>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
    <div class="alert alert-success">Booking cancelled successfully.</div>
<?php endif; ?>

<?php if (!isAdmin()): ?>
    <div class="filter-bar">
        <a href="/pages/bookings/list.php" class="btn btn-primary">Upcoming</a>
        <a href="/pages/bookings/history.php" class="btn">History</a>
    </div>
<?php endif; ?>

<?php if (empty($bookings)): ?>
    <p>No bookings found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <?php if (isAdmin()): ?><th>Client</th><?php endif; ?>
                <th>Location</th>
                <th>Date</th>
                <th>Start</th>
                <th>Duration</th>
                <th>Cost</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <?php if (isAdmin()): ?><td><?= htmlspecialchars($b['client_name'] ?? '') ?></td><?php endif; ?>
                    <td><?= htmlspecialchars($b['location_name']) ?></td>
                    <td><?= $b['booking_date'] ?></td>
                    <td><?= substr($b['start_time'], 0, 5) ?></td>
                    <td><?= $b['duration'] ?>h</td>
                    <td>$<?= $b['cost_per_hour'] * $b['duration'] ?></td>
                    <td><?= ucfirst($b['status']) ?></td>
                    <td>
                        <?php if ($b['status'] === 'confirmed'): ?>
                            <a href="/pages/bookings/edit.php?id=<?= $b['id'] ?>" class="btn btn-small">Edit</a>
                            <a href="/pages/bookings/cancel.php?id=<?= $b['id'] ?>" class="btn btn-small btn-danger">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
