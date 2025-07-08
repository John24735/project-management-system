<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Fetch all tasks with project and user info
$stmt = $pdo->query('SELECT t.*, p.title as project_title, u.username as assigned_to_name FROM tasks t JOIN projects p ON t.project_id = p.id JOIN users u ON t.assigned_to = u.id ORDER BY t.deadline ASC');
$tasks = $stmt->fetchAll();
// Group tasks by status for Kanban
$kanban = ['Pending' => [], 'In Progress' => [], 'Completed' => []];
foreach ($tasks as $task) {
    $kanban[$task['status']][] = $task;
}
$view = $_GET['view'] ?? 'kanban';
// Handle status change (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE tasks SET status=? WHERE id=?');
    $stmt->execute([$_POST['status'], $_POST['task_id']]);
    header('Location: tasks.php?view=' . urlencode($view));
    exit;
}
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-list-task fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Tasks</h2>
        <div class="ms-auto">
            <a href="?view=kanban" class="btn btn-outline-primary btn-sm<?php if ($view == 'kanban')
                echo ' active'; ?> d-inline-flex align-items-center gap-1"><i class="bi bi-columns-gap"></i>
                Kanban</a>
            <a href="?view=table" class="btn btn-outline-secondary btn-sm<?php if ($view == 'table')
                echo ' active'; ?> d-inline-flex align-items-center gap-1"><i class="bi bi-table"></i> Table</a>
        </div>
    </div>
    <?php if ($view == 'kanban'): ?>
        <div class="row g-2">
            <?php foreach ($kanban as $status => $list): ?>
                <div class="col-md-4">
                    <div class="card summary-card p-2 mb-2">
                        <div class="card-body p-2">
                            <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                                    class="bi bi-kanban"></i> <?php echo htmlspecialchars($status); ?></h5>
                            <?php foreach ($list as $task): ?>
                                <?php
                                $priority = strtolower($task['priority']);
                                $border = 'border-start border-3 ';
                                if ($priority == 'high')
                                    $border .= 'border-danger-subtle';
                                elseif ($priority == 'medium')
                                    $border .= 'border-warning-subtle';
                                elseif ($priority == 'low')
                                    $border .= 'border-success-subtle';
                                else
                                    $border .= 'border-secondary-subtle';
                                $deadline = date('F j, Y', strtotime($task['deadline']));
                                ?>
                                <div class="task-card mb-2 p-2 <?php echo $border; ?>"
                                    style="background:#fff; border-radius:12px; transition:box-shadow 0.2s; box-shadow:0 1px 6px rgba(80,80,180,0.06); cursor:pointer;"
                                    data-bs-toggle="modal" data-bs-target="#taskModal<?php echo $task['id']; ?>">
                                    <div class="fw-bold d-flex align-items-center gap-2 mb-1" style="font-size:0.97rem;">
                                        <i class="bi bi-list-task"></i> <?php echo htmlspecialchars($task['title']); ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-light text-secondary border border-1 border-secondary"><i
                                                class="bi bi-kanban"></i>
                                            <?php echo htmlspecialchars($task['status']); ?></span>
                                        <span class="badge badge-<?php echo $priority; ?>"><i class="bi bi-flag"></i>
                                            <?php echo htmlspecialchars($task['priority']); ?></span>
                                    </div>
                                    <div class="small text-muted mb-1 d-flex align-items-center gap-2">
                                        <i class="bi bi-folder"></i> <?php echo htmlspecialchars($task['project_title']); ?>
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($task['assigned_to_name']); ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark" style="font-size:0.85em;"><i
                                                class="bi bi-calendar-event"></i> <?php echo $deadline; ?></span>
                                    </div>
                                </div>
                                <!-- Task Details Modal -->
                                <div class="modal fade" id="taskModal<?php echo $task['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title d-flex align-items-center gap-2"><i
                                                        class="bi bi-list-task"></i> <?php echo htmlspecialchars($task['title']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-6 small"><i class="bi bi-kanban me-1 text-secondary"></i>
                                                        <strong>Status:</strong><br>
                                                        <span
                                                            class="badge bg-light text-secondary border border-1 border-secondary"><?php echo htmlspecialchars($task['status']); ?></span>
                                                    </div>
                                                    <div class="col-6 small"><i class="bi bi-flag me-1 text-warning"></i>
                                                        <strong>Priority:</strong><br>
                                                        <span
                                                            class="badge badge-<?php echo $priority; ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                                                    </div>
                                                    <div class="col-6 small"><i class="bi bi-folder me-1 text-primary"></i>
                                                        <strong>Project:</strong><br>
                                                        <?php echo htmlspecialchars($task['project_title']); ?>
                                                    </div>
                                                    <div class="col-6 small"><i class="bi bi-person me-1 text-info"></i>
                                                        <strong>Assigned To:</strong><br>
                                                        <?php echo htmlspecialchars($task['assigned_to_name']); ?>
                                                    </div>
                                                    <div class="col-12 small"><i class="bi bi-calendar-event me-1 text-success"></i>
                                                        <strong>Deadline:</strong> <?php echo $deadline; ?></div>
                                                </div>
                                                <hr class="my-2">
                                                <div class="bg-light rounded p-2 small">
                                                    <i class="bi bi-info-circle me-1 text-muted"></i>
                                                    <strong>Description:</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($task['description'] ?? 'No description.')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <table class="table table-sm table-bordered align-middle" style="font-size:0.92rem;">
            <thead class="table-light">
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
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($task['status']); ?></span></td>
                        <td><span
                                class="badge badge-<?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>