<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
// Fetch member's tasks
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.assigned_to = ?');
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();
// Task stats
$total = count($tasks);
$completed = 0;
$in_progress = 0;
$pending = 0;
foreach ($tasks as $task) {
    if ($task['status'] === 'Completed')
        $completed++;
    elseif ($task['status'] === 'In Progress')
        $in_progress++;
    else
        $pending++;
}
// Recent comments
$comments = $pdo->prepare('SELECT c.comment, c.created_at, t.title as task_title FROM task_comments c JOIN tasks t ON c.task_id = t.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5');
$comments->execute([$_SESSION['user_id']]);
$comments = $comments->fetchAll();
?>
<h2>Member Dashboard</h2>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Tasks</h5>
                <p class="card-text display-6"><?php echo $total; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Completed</h5>
                <p class="card-text display-6"><?php echo $completed; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">In Progress</h5>
                <p class="card-text display-6"><?php echo $in_progress; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">My Tasks</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                                <td><?php echo htmlspecialchars($task['status']); ?></td>
                                <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                                <td><a href="update_task.php?task_id=<?php echo $task['id']; ?>"
                                        class="btn btn-sm btn-outline-primary">Update</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Recent Comments</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($comments as $c): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($c['task_title']); ?>:</strong><br>
                            <?php echo htmlspecialchars($c['comment']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($c['created_at']); ?></small>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                        <li class="list-group-item text-muted">No recent comments.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>