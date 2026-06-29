<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Booking.php';
requireLogin();

$bookingObj = new Booking($conn);
$id = (int) ($_GET['id'] ?? 0);
$booking = $bookingObj->getById($id);

if (empty($booking)) {
    header('Location: /pages/bookings/list.php');
    exit;
}

if (!isAdmin() && $booking['user_id'] !== currentUserId()) {
    header('Location: /pages/bookings/list.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $bookingObj->cancel($id);
    if ($result['success']) {
        header('Location: /pages/bookings/list.php?msg=cancelled');
        exit;
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Cancel Booking #<?= $booking['id'] ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="alert alert-warning">
    <p>Are you sure you want to cancel this booking?</p>
    <p><strong>Location:</strong> <?= htmlspecialchars($booking['location_name']) ?></p>
    <p><strong>Date:</strong> <?= $booking['booking_date'] ?></p>
    <p><strong>Time:</strong> <?= substr($booking['start_time'], 0, 5) ?></p>
    <p><strong>Duration:</strong> <?= $booking['duration'] ?> hour(s)</p>
</div>

<form method="POST">
    <button type="submit" class="btn btn-danger">Yes, Cancel Booking</button>
    <a href="/pages/bookings/list.php" class="btn">No, Go Back</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
