<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
// Project completion rates
$projects = $pdo->query('SELECT p.id, p.title, COUNT(t.id) as total_tasks, SUM(t.status = "Completed") as completed_tasks FROM projects p LEFT JOIN tasks t ON p.id = t.project_id GROUP BY p.id')->fetchAll();
// User contributions
$users = $pdo->query('SELECT u.username, COUNT(t.id) as tasks_completed FROM users u LEFT JOIN tasks t ON u.id = t.assigned_to AND t.status = "Completed" GROUP BY u.id')->fetchAll();
?>
<h2>Reports</h2>
<h4>Project Completion Rates</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Project</th>
            <th>Total Tasks</th>
            <th>Completed Tasks</th>
            <th>Completion %</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['title']); ?></td>
                <td><?php echo $p['total_tasks']; ?></td>
                <td><?php echo $p['completed_tasks']; ?></td>
                <td>
                    <?php $percent = $p['total_tasks'] ? round(($p['completed_tasks'] / $p['total_tasks']) * 100) : 0; ?>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%;"
                            aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $percent; ?>%
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h4>User Contributions</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Tasks Completed</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo $u['tasks_completed']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>