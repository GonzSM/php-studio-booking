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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingDate = $_POST['booking_date'] ?? '';
    $startTime = ($_POST['start_time'] ?? '') . ':00';
    $duration = (int) ($_POST['duration'] ?? 0);

    if (empty($bookingDate) || empty($startTime) || $duration <= 0) {
        $error = 'All fields are required.';
    } else {
        $result = $bookingObj->update($id, $bookingDate, $startTime, $duration);
        if ($result['success']) {
            $success = $result['message'];
            $booking = $bookingObj->getById($id);
        } else {
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Edit Booking #<?= $booking['id'] ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<p><strong>Location:</strong> <?= htmlspecialchars($booking['location_name']) ?></p>
<p><strong>Client:</strong> <?= htmlspecialchars($booking['client_name']) ?></p>

<form method="POST" class="form">
    <div class="form-group">
        <label for="booking_date">Booking Date</label>
        <input type="date" id="booking_date" name="booking_date" required
               min="<?= date('Y-m-d') ?>"
               value="<?= $booking['booking_date'] ?>">
    </div>
    <div class="form-group">
        <label for="start_time">Start Time (10:00 - 21:00)</label>
        <input type="time" id="start_time" name="start_time" required
               min="10:00" max="21:00"
               value="<?= substr($booking['start_time'], 0, 5) ?>">
    </div>
    <div class="form-group">
        <label for="duration">Duration (hours)</label>
        <input type="number" id="duration" name="duration" min="1" max="12" required
               value="<?= $booking['duration'] ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Booking</button>
    <a href="/pages/bookings/list.php" class="btn">Cancel</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
