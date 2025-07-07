<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
?>
<div class="text-center mt-5">
    <h1 class="display-4">Welcome, Admin!</h1>
    <p class="lead">Manage your organization's projects, tasks, and users from this central admin panel.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
        <a href="dashboard.php" class="btn btn-primary btn-lg">Dashboard</a>
        <a href="create_project.php" class="btn btn-success btn-lg">Create Project</a>
        <a href="assign_task.php" class="btn btn-warning btn-lg">Assign Task</a>
        <a href="reports.php" class="btn btn-info btn-lg">Reports</a>
        <a href="create_user.php" class="btn btn-secondary btn-lg">Create User</a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>