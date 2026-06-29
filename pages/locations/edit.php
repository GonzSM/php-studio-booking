<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Location.php';
requireAdmin();

$locationObj = new Location($conn);
$id = (int) ($_GET['id'] ?? 0);
$location = $locationObj->getById($id);

if (empty($location)) {
    header('Location: /pages/locations/list.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $numStudios = (int) ($_POST['num_studios'] ?? 0);
    $costPerHour = (float) ($_POST['cost_per_hour'] ?? 0);

    if (empty($description) || $numStudios <= 0 || $costPerHour <= 0) {
        $error = 'All fields are required and must have valid values.';
    } else {
        if ($locationObj->update($id, $description, $numStudios, $costPerHour)) {
            $success = 'Location updated successfully!';
            $location = $locationObj->getById($id);
        } else {
            $error = 'Failed to update location.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Edit Location</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="form">
    <div class="form-group">
        <label for="description">Description</label>
        <input type="text" id="description" name="description" required
               value="<?= htmlspecialchars($location['description']) ?>">
    </div>
    <div class="form-group">
        <label for="num_studios">Number of Studios</label>
        <input type="number" id="num_studios" name="num_studios" min="1" required
               value="<?= $location['num_studios'] ?>">
    </div>
    <div class="form-group">
        <label for="cost_per_hour">Cost per Hour ($)</label>
        <input type="number" id="cost_per_hour" name="cost_per_hour" min="0.01" step="0.01" required
               value="<?= $location['cost_per_hour'] ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Location</button>
    <a href="/pages/locations/list.php" class="btn">Cancel</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
