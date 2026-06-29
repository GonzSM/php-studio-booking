<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Location.php';
requireLogin();

$locationObj = new Location($conn);
$filter = $_GET['filter'] ?? 'all';

switch ($filter) {
    case 'available':
        $locations = $locationObj->getWithAvailableStudios();
        $title = 'Locations with Available Studios';
        break;
    case 'fully_booked':
        requireAdmin();
        $locations = $locationObj->getFullyBooked();
        $title = 'Fully Booked Locations';
        break;
    default:
        $locations = $locationObj->getAll();
        $title = 'All Locations';
        break;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1><?= htmlspecialchars($title) ?></h1>

<div class="filter-bar">
    <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : '' ?>">All</a>
    <a href="?filter=available" class="btn <?= $filter === 'available' ? 'btn-primary' : '' ?>">Available</a>
    <?php if (isAdmin()): ?>
        <a href="?filter=fully_booked" class="btn <?= $filter === 'fully_booked' ? 'btn-primary' : '' ?>">Fully Booked</a>
    <?php endif; ?>
</div>

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
                <?php if ($filter === 'available'): ?>
                    <th>Available</th>
                <?php endif; ?>
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
                    <?php if ($filter === 'available'): ?>
                        <td><?= $loc['available_studios'] ?></td>
                    <?php endif; ?>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
