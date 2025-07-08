<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch manager's OWNED projects
$projects = $pdo->prepare('SELECT * FROM projects WHERE created_by = ?');
$projects->execute([$_SESSION['user_id']]);
$projects = $projects->fetchAll();
$project_ids = array_column($projects, 'id');
$task_counts = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0];
$upcoming_tasks = [];
$today = date('Y-m-d');
$week_later = date('Y-m-d', strtotime('+7 days'));
if ($project_ids) {
    $in = str_repeat('?,', count($project_ids) - 1) . '?';
    // Task distribution for tasks on projects OWNED by manager
    $stmt = $pdo->prepare("SELECT t.status, COUNT(*) as cnt FROM tasks t WHERE t.project_id IN ($in) GROUP BY t.status");
    $stmt->execute($project_ids);
    foreach ($stmt->fetchAll() as $row) {
        $task_counts[$row['status']] = $row['cnt'];
    }
    // Upcoming deadlines for tasks on projects OWNED by manager
    $stmt = $pdo->prepare("SELECT t.*, p.title as project_title FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.project_id IN ($in) AND t.deadline >= ? AND t.deadline <= ? AND t.status != 'Completed' ORDER BY t.deadline ASC LIMIT 5");
    $stmt->execute(array_merge($project_ids, [$today, $week_later]));
    $upcoming_tasks = $stmt->fetchAll();
}
// Also include tasks with no project (project_id IS NULL) created by the manager
$stmt = $pdo->prepare('SELECT t.*, NULL as project_title FROM tasks t WHERE t.project_id IS NULL AND t.created_by = ? AND t.deadline >= ? AND t.deadline <= ? AND t.status != "Completed" ORDER BY t.deadline ASC LIMIT 5');
$stmt->execute([$_SESSION['user_id'], $today, $week_later]);
$upcoming_tasks = array_merge($upcoming_tasks, $stmt->fetchAll());
function get_rag_status($project)
{
    $today = date('Y-m-d');
    if ($project['status'] === 'Completed')
        return 'green';
    if ($project['end_date'] < $today)
        return 'red';
    if ($project['end_date'] <= date('Y-m-d', strtotime('+7 days')))
        return 'amber';
    return 'green';
}
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-briefcase fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Manager Dashboard</h2>
    </div>
    <div class="row g-2 mb-2">
        <!-- Project Health (RAG) -->
        <div class="col-md-4">
            <div class="card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-activity"></i> Project Health</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($projects as $project):
                            $rag = get_rag_status($project); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-2"
                                style="font-size:0.92rem;">
                                <span><i class="bi bi-folder me-1"></i>
                                    <?php echo htmlspecialchars($project['title']); ?></span>
                                <?php if ($rag === 'red'): ?>
                                    <span class="badge bg-danger">Red</span>
                                <?php elseif ($rag === 'amber'): ?>
                                    <span class="badge bg-warning text-dark">Amber</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Green</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Task Distribution -->
        <div class="col-md-4">
            <div class="card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-list-task"></i> Task Distribution</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <span>Pending</span><span
                                class="badge bg-secondary"><?php echo $task_counts['Pending']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2"><span>In
                                Progress</span><span
                                class="badge bg-primary"><?php echo $task_counts['In Progress']; ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <span>Completed</span><span
                                class="badge bg-success"><?php echo $task_counts['Completed']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Upcoming Deadlines -->
        <div class="col-md-4">
            <div class="card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-calendar-event"></i> Upcoming Deadlines</h5>
                    <ul class="list-group list-group-flush">
                        <?php if ($upcoming_tasks):
                            foreach ($upcoming_tasks as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center p-2"
                                    style="font-size:0.92rem;">
                                    <span><i class="bi bi-list-task me-1"></i> <?php echo htmlspecialchars($task['title']); ?>
                                        <span
                                            class="text-muted">(<?php echo htmlspecialchars($task['project_title']); ?>)</span></span>
                                    <span
                                        class="badge bg-info text-dark"><?php echo htmlspecialchars($task['deadline']); ?></span>
                                </li>
                            <?php endforeach; else: ?>
                            <li class="list-group-item p-2 text-muted">No upcoming deadlines.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>