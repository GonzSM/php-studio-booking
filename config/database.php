<?php

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'myrecordingstudio';

define('STUDIO_OPEN_HOUR', 10);
define('STUDIO_CLOSE_HOUR', 22);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}
