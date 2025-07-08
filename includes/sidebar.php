<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';
// includes/sidebar.php
$user = current_user();
$role = '';
if (is_admin())
    $role = 'Admin';
elseif (is_manager())
    $role = 'Manager';
elseif (is_member())
    $role = 'Member';

// Get user display name and profile picture
$display_name = get_user_display_name($user);
$profile_picture_url = get_profile_picture_url($user);
?>
<aside class="sidebar d-flex flex-column">
    <div class="d-flex flex-column align-items-center mb-4 position-relative">
        <div class="rounded-circle bg-light mb-2 position-relative" style="width:72px;height:72px;overflow:hidden;">
            <img src="<?php echo $profile_picture_url; ?>" alt="Profile Picture"
                style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div class="fw-bold d-flex align-items-center gap-2" style="font-size:1.15rem;"><i
                class="bi bi-person-fill"></i> <?php echo htmlspecialchars($display_name); ?></div>
        <div class="text-muted d-flex align-items-center gap-2" style="font-size:0.98rem;"><i class="bi bi-award"></i>
            <?php echo $role; ?></div>
    </div>
    <?php if ($role === 'Admin'): ?>
        <!-- <div class="text-uppercase d-flex align-items-center gap-2 mb-1"><i class="bi bi-list-ul"></i> Menu</div> -->
        <nav class="nav flex-column mb-2">
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php')
                echo ' active'; ?>" href="../admin/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'users.php')
                echo ' active'; ?>" href="../admin/users.php"><i class="bi bi-people"></i> Users</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'projects.php')
                echo ' active'; ?>" href="../admin/projects.php"><i class="bi bi-folder2-open"></i> Projects</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'tasks.php')
                echo ' active'; ?>" href="../admin/tasks.php"><i class="bi bi-list-task"></i> Tasks</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'reports.php')
                echo ' active'; ?>" href="../admin/reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'settings.php')
                echo ' active'; ?>" href="../admin/settings.php"><i class="bi bi-gear"></i> Settings</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'audit.php')
                echo ' active'; ?>" href="../admin/audit.php"><i class="bi bi-clipboard-data"></i> Audit Logs</a>
        </nav>
    <?php elseif ($role === 'Manager'): ?>
        <nav class="nav flex-column mb-2">
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php')
                echo ' active'; ?>" href="../manager/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'projects.php')
                echo ' active'; ?>" href="../manager/projects.php"><i class="bi bi-folder2-open"></i> My Projects</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'tasks.php')
                echo ' active'; ?>" href="../manager/tasks.php"><i class="bi bi-list-task"></i> Tasks</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'team.php')
                echo ' active'; ?>" href="../manager/team.php"><i class="bi bi-people"></i> Team</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'reports.php')
                echo ' active'; ?>" href="../manager/reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'notifications.php')
                echo ' active'; ?>" href="../manager/notifications.php"><i class="bi bi-bell"></i> Notifications</a>
        </nav>
    <?php elseif ($role === 'Member'): ?>
        <nav class="nav flex-column mb-2">
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php')
                echo ' active'; ?>"
                href="../member/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'tasks.php')
                echo ' active'; ?>"
                href="../member/tasks.php"><i class="bi bi-list-task"></i> My Tasks</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'project_overview.php')
                echo ' active'; ?>"
                href="../member/project_overview.php"><i class="bi bi-folder2-open"></i> Project Overview</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'notifications.php')
                echo ' active'; ?>"
                href="../member/notifications.php"><i class="bi bi-bell"></i> Notifications</a>
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'profile.php')
                echo ' active'; ?>"
                href="../member/profile.php"><i class="bi bi-person"></i> Profile</a>
        </nav>
    <?php else: ?>
        <div class="text-uppercase d-flex align-items-center gap-2 mb-1"><i class="bi bi-list-ul"></i> Menu</div>
        <nav class="nav flex-column mb-2">
            <a class="nav-link<?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php')
                echo ' active'; ?>" href="../member/dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            <a class="nav-link" href="../member/tasks.php"><i class="bi bi-list-check"></i> My Tasks</a>
            <a class="nav-link" href="../member/calendar.php"><i class="bi bi-calendar-event"></i> Calendar</a>
            <a class="nav-link" href="../member/reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
            <a class="nav-link" href="../member/settings.php"><i class="bi bi-gear"></i> Settings</a>
        </nav>
    <?php endif; ?>
    <div class="mt-auto">
        <a class="nav-link d-flex align-items-center gap-2" href="../logout.php"><i class="bi bi-box-arrow-right"></i>
            Logout</a>
    </div>
</aside>