<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Booking.php';
require_once __DIR__ . '/../../classes/Location.php';
require_once __DIR__ . '/../../classes/User.php';
requireLogin();

$locationObj = new Location($conn);
$locations = $locationObj->getAll();

$error = '';
$confirmation = null;

$clientId = currentUserId();
$clients = [];
if (isAdmin()) {
    $userObj = new User($conn);
    $clients = $userObj->getAllClients();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationId = (int) ($_POST['location_id'] ?? 0);
    $bookingDate = $_POST['booking_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $duration = (int) ($_POST['duration'] ?? 0);

    if (isAdmin() && !empty($_POST['client_id'])) {
        $clientId = (int) $_POST['client_id'];
    }

    if ($locationId <= 0 || empty($bookingDate) || empty($startTime) || $duration <= 0) {
        $error = 'All fields are required.';
    } elseif ($bookingDate < date('Y-m-d')) {
        $error = 'Booking date cannot be in the past.';
    } else {
        $startTime = $startTime . ':00';
        $bookingObj = new Booking($conn);
        $result = $bookingObj->create($clientId, $locationId, $bookingDate, $startTime, $duration);

        if ($result['success']) {
            $booking = $bookingObj->getById($result['booking_id']);
            $totalCost = $booking['cost_per_hour'] * $booking['duration'];
            $confirmation = [
                'booking' => $booking,
                'total_cost' => $totalCost
            ];
        } else {
            $error = $result['message'];
        }
    }
}

$preselectedLocation = (int) ($_GET['location_id'] ?? 0);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>New Booking</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($confirmation): ?>
    <div class="alert alert-success">
        <h2>Booking Confirmed!</h2>
        <p><strong>Booking ID:</strong> <?= $confirmation['booking']['id'] ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($confirmation['booking']['location_name']) ?></p>
        <p><strong>Client:</strong> <?= htmlspecialchars($confirmation['booking']['client_name']) ?></p>
        <p><strong>Date:</strong> <?= $confirmation['booking']['booking_date'] ?></p>
        <p><strong>Start Time:</strong> <?= substr($confirmation['booking']['start_time'], 0, 5) ?></p>
        <p><strong>Duration:</strong> <?= $confirmation['booking']['duration'] ?> hour(s)</p>
        <p><strong>Total Cost:</strong> $<?= $confirmation['total_cost'] ?></p>
    </div>
    <a href="/pages/bookings/list.php" class="btn btn-primary">View My Bookings</a>
<?php else: ?>
    <form method="POST" class="form">
        <?php if (isAdmin()): ?>
            <div class="form-group">
                <label for="client_id">Client</label>
                <select id="client_id" name="client_id" required>
                    <option value="">-- Select Client --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?> (<?= htmlspecialchars($client['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="location_id">Location</label>
            <select id="location_id" name="location_id" required>
                <option value="">-- Select Location --</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>"
                        <?= (int) $loc['id'] === $preselectedLocation ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['description']) ?> - $<?= $loc['cost_per_hour'] ?>/hr
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="booking_date">Booking Date</label>
            <input type="date" id="booking_date" name="booking_date" required
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="start_time">Start Time (10:00 - 21:00)</label>
            <input type="time" id="start_time" name="start_time" required
                   min="10:00" max="21:00"
                   value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="duration">Duration (hours)</label>
            <input type="number" id="duration" name="duration" min="1" max="12" required
                   value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Book Now</button>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
