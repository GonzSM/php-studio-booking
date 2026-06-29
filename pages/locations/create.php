<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Location.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $numStudios = (int) ($_POST['num_studios'] ?? 0);
    $costPerHour = (float) ($_POST['cost_per_hour'] ?? 0);

    if (empty($description) || $numStudios <= 0 || $costPerHour <= 0) {
        $error = 'All fields are required and must have valid values.';
    } else {
        $locationObj = new Location($conn);
        if ($locationObj->create($description, $numStudios, $costPerHour)) {
            $success = 'Location created successfully!';
        } else {
            $error = 'Failed to create location.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Create Location</h1>

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
               value="<?= htmlspecialchars($_POST['description'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="num_studios">Number of Studios</label>
        <input type="number" id="num_studios" name="num_studios" min="1" required
               value="<?= htmlspecialchars($_POST['num_studios'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="cost_per_hour">Cost per Hour ($)</label>
        <input type="number" id="cost_per_hour" name="cost_per_hour" min="0.01" step="0.01" required
               value="<?= htmlspecialchars($_POST['cost_per_hour'] ?? '') ?>">
    </div>
    <button type="submit" class="btn btn-primary">Create Location</button>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
