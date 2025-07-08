<?php require_once '../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
// Task counts by priority
$stmt = $pdo->prepare('SELECT priority, COUNT(*) as cnt FROM tasks WHERE assigned_to = ? GROUP BY priority');
$stmt->execute([$user_id]);
$priority_counts = ['Low' => 0, 'Medium' => 0, 'High' => 0];
foreach ($stmt->fetchAll() as $row) {
    $priority_counts[$row['priority']] = $row['cnt'];
}
$total_tasks = array_sum($priority_counts);
// Today's tasks
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.assigned_to = ? AND t.deadline = ? ORDER BY t.deadline ASC');
$stmt->execute([$user_id, $today]);
$todays_tasks = $stmt->fetchAll();
// Overdue tasks
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.assigned_to = ? AND t.status != "Completed" AND t.deadline < ? ORDER BY t.deadline ASC');
$stmt->execute([$user_id, $today]);
$overdue_tasks = $stmt->fetchAll();
// Upcoming tasks (next 7 days)
$week_later = date('Y-m-d', strtotime('+7 days'));
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.assigned_to = ? AND t.deadline > ? AND t.deadline <= ? AND t.status != "Completed" ORDER BY t.deadline ASC');
$stmt->execute([$user_id, $today, $week_later]);
$upcoming_tasks = $stmt->fetchAll();
?>
<!-- Make sure Bootstrap Icons CDN is loaded in header.php: <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> -->
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-house-door fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">Dashboard</h2>
        <a href="#" class="btn btn-outline-secondary btn-sm ms-auto"><i class="bi bi-calendar-event"></i> Calendar
            Sync</a>
    </div>
    <div class="summary-row mb-3">
        <div class="summary-card total">
            <div class="icon"><i class="bi bi-list-task"></i></div>
            <div class="fw-bold"><?php echo $total_tasks; ?></div>
            <div class="label"><i class="bi bi-collection me-1"></i> Total Tasks</div>
        </div>
        <div class="summary-card low">
            <div class="icon"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="fw-bold"><?php echo $priority_counts['Low']; ?></div>
            <div class="label"><i class="bi bi-arrow-down-left-circle me-1"></i> Low</div>
        </div>
        <div class="summary-card medium">
            <div class="icon"><i class="bi bi-exclamation-circle"></i></div>
            <div class="fw-bold"><?php echo $priority_counts['Medium']; ?></div>
            <div class="label"><i class="bi bi-exclamation-diamond me-1"></i> Medium</div>
        </div>
        <div class="summary-card high">
            <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="fw-bold"><?php echo $priority_counts['High']; ?></div>
            <div class="label"><i class="bi bi-exclamation-octagon me-1"></i> High</div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-2 mb-2">
                <div class="card-body">
                    <h5 class="card-title mb-2" style="font-size:1rem;">Today's Tasks</h5>
                    <ul class="list-group list-group-flush">
                        <?php if ($todays_tasks):
                            foreach ($todays_tasks as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <span><i class="bi bi-list-task me-1"></i> <?php echo htmlspecialchars($task['title']); ?>
                                        <span
                                            class="text-muted">(<?php echo htmlspecialchars($task['project_title']); ?>)</span></span>
                                    <a href="update_task.php?task_id=<?php echo $task['id']; ?>"
                                        class="btn btn-outline-primary btn-sm">Update</a>
                                </li>
                            <?php endforeach; else: ?>
                            <li class="list-group-item p-2 text-muted">No tasks for today.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-2 mb-2">
                <div class="card-body">
                    <h5 class="card-title mb-2" style="font-size:1rem;">Overdue Tasks</h5>
                    <ul class="list-group list-group-flush">
                        <?php if ($overdue_tasks):
                            foreach ($overdue_tasks as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <span><i class="bi bi-exclamation-triangle me-1 text-danger"></i>
                                        <?php echo htmlspecialchars($task['title']); ?>
                                        <span
                                            class="text-muted">(<?php echo htmlspecialchars($task['project_title']); ?>)</span></span>
                                    <a href="update_task.php?task_id=<?php echo $task['id']; ?>"
                                        class="btn btn-outline-danger btn-sm">Update</a>
                                </li>
                            <?php endforeach; else: ?>
                            <li class="list-group-item p-2 text-muted">No overdue tasks.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="card p-2 mb-2">
        <div class="card-body">
            <h5 class="card-title mb-2" style="font-size:1rem;">Upcoming Tasks (Next 7 Days)</h5>
            <ul class="list-group list-group-flush">
                <?php if ($upcoming_tasks):
                    foreach ($upcoming_tasks as $task): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <span><i class="bi bi-calendar-event me-1"></i> <?php echo htmlspecialchars($task['title']); ?>
                                <span class="text-muted">(<?php echo htmlspecialchars($task['project_title']); ?>)</span></span>
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($task['deadline']); ?></span>
                        </li>
                    <?php endforeach; else: ?>
                    <li class="list-group-item p-2 text-muted">No upcoming tasks.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>