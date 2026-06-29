<?php
session_start();

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /pages/auth/login.php');
        exit;
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: /index.php');
        exit;
    }
}

function currentUserId(): int
{
    return (int) $_SESSION['user_id'];
}

function currentUserName(): string
{
    return $_SESSION['user_name'] ?? '';
}

function currentUserType(): string
{
    return $_SESSION['user_type'] ?? '';
}
