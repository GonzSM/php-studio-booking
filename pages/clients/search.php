<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/User.php';
requireAdmin();

$clients = [];
$searchId = trim($_GET['id'] ?? '');
$searchName = trim($_GET['name'] ?? '');
$searchEmail = trim($_GET['email'] ?? '');
$searchPhone = trim($_GET['phone'] ?? '');
$searched = ($searchId !== '' || $searchName !== '' || $searchEmail !== '' || $searchPhone !== '');

if ($searched) {
    $userObj = new User($conn);
    $clients = $userObj->searchClients($searchId, $searchName, $searchEmail, $searchPhone);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Search Clients</h1>

<form method="GET" class="form">
    <div class="form-group">
        <label for="id">Client ID</label>
        <input type="text" id="id" name="id" placeholder="Exact ID..."
               value="<?= htmlspecialchars($searchId) ?>">
    </div>
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Part of name..."
               value="<?= htmlspecialchars($searchName) ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="Part of email..."
               value="<?= htmlspecialchars($searchEmail) ?>">
    </div>
    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" placeholder="Part of phone..."
               value="<?= htmlspecialchars($searchPhone) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<?php if ($searched): ?>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?= $client['id'] ?></td>
                        <td><?= htmlspecialchars($client['name']) ?></td>
                        <td><?= htmlspecialchars($client['phone']) ?></td>
                        <td><?= htmlspecialchars($client['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
