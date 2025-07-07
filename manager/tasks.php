<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch tasks for manager's projects
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title, u.username as assigned_to_name FROM tasks t JOIN projects p ON t.project_id = p.id JOIN users u ON t.assigned_to = u.id WHERE t.project_id IN (SELECT project_id FROM project_members WHERE user_id = ?)');
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();
?>
<h2>Project Tasks</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Project</th>
            <th>Task</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Deadline</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                <td><?php echo htmlspecialchars($task['title']); ?></td>
                <td><?php echo htmlspecialchars($task['assigned_to_name']); ?></td>
                <td><?php echo htmlspecialchars($task['status']); ?></td>
                <td><?php echo htmlspecialchars($task['priority']); ?></td>
                <td><?php echo htmlspecialchars($task['deadline']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>