<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /pages/locations/list.php');
    exit;
}

header('Location: /pages/auth/login.php');
exit;
