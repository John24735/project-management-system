<?php
// includes/header.php
require_once __DIR__ . '/auth.php';

// Determine base path for navigation links
$base = '';
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/admin/') !== false) {
    $base = '../';
} elseif (strpos($uri, '/manager/') !== false) {
    $base = '../';
} elseif (strpos($uri, '/member/') !== false) {
    $base = '../';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management System</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base; ?>index.php">PMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>admin/dashboard.php">Admin
                                    Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>admin/index.php">Admin Home</a>
                            </li>
                        <?php elseif (is_manager()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>manager/dashboard.php">Manager
                                    Dashboard</a></li>
                        <?php elseif (is_member()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>member/dashboard.php">Member
                                    Dashboard</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal"
                                data-bs-target="#loginModal">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal"
                                data-bs-target="#registerModal">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">