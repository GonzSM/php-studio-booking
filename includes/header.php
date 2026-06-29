<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyRecordingStudio</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="/index.php" class="navbar-brand">MyRecordingStudio</a>
    <div class="navbar-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/pages/locations/list.php">Locations</a>
            <a href="/pages/locations/search.php">Search Locations</a>

            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                <a href="/pages/locations/create.php">Add Location</a>
                <a href="/pages/clients/list.php">Clients</a>
                <a href="/pages/clients/search.php">Search Clients</a>
            <?php endif; ?>

            <a href="/pages/bookings/list.php">My Bookings</a>
            <a href="/pages/bookings/create.php">New Booking</a>

            <span class="navbar-user"><?= htmlspecialchars($_SESSION['user_name']) ?> (<?= $_SESSION['user_type'] ?>)</span>
            <a href="/pages/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/pages/auth/login.php">Login</a>
            <a href="/pages/auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
