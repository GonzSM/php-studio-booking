<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/User.php';
requireAdmin();

$userObj = new User($conn);
$filter = $_GET['filter'] ?? 'all';

switch ($filter) {
    case 'active':
        $clients = $userObj->getClientsWithActiveBooking();
        $title = 'Clients Currently Using a Studio';
        break;
    default:
        $clients = $userObj->getAllClients();
        $title = 'All Registered Clients';
        break;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1><?= htmlspecialchars($title) ?></h1>

<div class="filter-bar">
    <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : '' ?>">All Clients</a>
    <a href="?filter=active" class="btn <?= $filter === 'active' ? 'btn-primary' : '' ?>">Active Now</a>
</div>

<?php if (empty($clients)): ?>
    <p>No clients found.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $client['id'] ?></td>
                    <td><?= htmlspecialchars($client['name']) ?></td>
                    <td><?= htmlspecialchars($client['phone']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td>
                        <a href="/pages/bookings/create.php?client_id=<?= $client['id'] ?>" class="btn btn-small">Book for Client</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
