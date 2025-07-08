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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 60px;
        }

        .navbar.fixed-top {
            z-index: 1050;
            box-shadow: 0 2px 8px rgba(80, 80, 180, 0.08);
            background: linear-gradient(90deg, #7c3aed 0%, #a78bfa 100%) !important;
        }

        .navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.3rem;
            color: #fff !important;
        }

        .navbar .navbar-brand img {
            height: 45px;
            width: auto;
            margin-right: 8px;
            object-fit: contain;
            background-color: whitesmoke;
            border-radius: 6px;
            padding: 2px;
        }

        .navbar .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar .nav-link.active,
        .navbar .nav-link:focus,
        .navbar .nav-link:hover {
            color: #ede9fe !important;
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            body {
                padding-top: 56px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand" href="<?php echo $base; ?>index.php">
                <img src="<?php echo $base; ?>assets/img/logo.png" alt="Logo">

            </a>
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
    <div class="container-fluid mt-4 px-0">