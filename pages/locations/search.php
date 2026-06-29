<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Location.php';
requireLogin();

$locations = [];
$searchId = trim($_GET['id'] ?? '');
$searchDescription = trim($_GET['description'] ?? '');
$searched = ($searchId !== '' || $searchDescription !== '');

if ($searched) {
    $locationObj = new Location($conn);
    $locations = $locationObj->search($searchId, $searchDescription);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Search Locations</h1>

<form method="GET" class="form">
    <div class="form-group">
        <label for="id">Location ID</label>
        <input type="text" id="id" name="id" placeholder="Exact ID..."
               value="<?= htmlspecialchars($searchId) ?>">
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <input type="text" id="description" name="description" placeholder="Part of description..."
               value="<?= htmlspecialchars($searchDescription) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<?php if ($searched): ?>
    <?php if (empty($locations)): ?>
        <p>No locations found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                    <th>Studios</th>
                    <th>Cost/Hour</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($locations as $loc): ?>
                    <tr>
                        <td><?= $loc['id'] ?></td>
                        <td><?= htmlspecialchars($loc['description']) ?></td>
                        <td><?= $loc['num_studios'] ?></td>
                        <td>$<?= $loc['cost_per_hour'] ?></td>
                        <td>
                            <a href="/pages/bookings/create.php?location_id=<?= $loc['id'] ?>" class="btn btn-small">Book</a>
                            <?php if (isAdmin()): ?>
                                <a href="/pages/locations/edit.php?id=<?= $loc['id'] ?>" class="btn btn-small">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
