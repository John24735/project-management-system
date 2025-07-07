<?php
// includes/auth.php
require_once __DIR__ . '/../config/db.php';
session_start();

// Register a new user
function register_user($username, $email, $password, $role_id)
{
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)');
    return $stmt->execute([$username, $email, $hash, $role_id]);
}

// Login user
function login_user($username, $password)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        return true;
    }
    return false;
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Get current user info
function current_user()
{
    global $pdo;
    if (!is_logged_in())
        return null;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check user role
function is_admin()
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}
function is_manager()
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2;
}
function is_member()
{
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3;
}

// Logout
function logout()
{
    session_unset();
    session_destroy();
}