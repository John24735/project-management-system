<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
// Fetch stats
$total_projects = $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$total_tasks = $pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();
$total_users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$recent_projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recent_tasks = $pdo->query('SELECT t.*, u.username FROM tasks t JOIN users u ON t.assigned_to = u.id ORDER BY t.created_at DESC LIMIT 5')->fetchAll();
?>
<h2>Admin Dashboard</h2>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Projects</h5>
                <p class="card-text display-6"><?php echo $total_projects; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Tasks</h5>
                <p class="card-text display-6"><?php echo $total_tasks; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text display-6"><?php echo $total_users; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Recent Projects</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_projects as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($p['end_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Recent Tasks</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_tasks as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['title']); ?></td>
                                <td><?php echo htmlspecialchars($t['username']); ?></td>
                                <td><?php echo htmlspecialchars($t['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body text-center">
                <a href="create_project.php" class="btn btn-primary m-2">Create Project</a>
                <a href="assign_task.php" class="btn btn-success m-2">Assign Task</a>
                <a href="reports.php" class="btn btn-info m-2">View Reports</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>