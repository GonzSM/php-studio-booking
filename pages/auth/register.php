<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/User.php';

if (isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $type = $_POST['type'] ?? 'client';

    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($type !== 'client' && $type !== 'admin') {
        $error = 'Invalid user type.';
    } else {
        $userObj = new User($conn);

        if ($userObj->emailExists($email)) {
            $error = 'Email is already registered.';
        } else {
            if ($userObj->register($name, $phone, $email, $password, $type)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Register</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="form">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" required
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">
    </div>
    <div class="form-group">
        <label for="type">Account Type</label>
        <select id="type" name="type">
            <option value="client">Client</option>
            <option value="admin">Administrator</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
    <p>Already have an account? <a href="/pages/auth/login.php">Login here</a></p>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
