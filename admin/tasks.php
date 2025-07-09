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

function human_friendly_date($date)
{
    $timestamp = strtotime($date);
    $now = strtotime(date('Y-m-d'));
    $diff = ($timestamp - $now) / 86400;
    if ($diff === 0)
        return 'Today';
    if ($diff === 1)
        return 'Tomorrow';
    if ($diff === -1)
        return 'Yesterday';
    if ($diff > 1 && $diff < 7)
        return 'In ' . intval($diff) . ' days';
    if ($diff < -1 && $diff > -7)
        return abs(intval($diff)) . ' days ago';
    return date('M j, Y', $timestamp);
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
        <div class="row kanban-board g-2">
            <?php foreach ($kanban as $status => $tasks): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light fw-bold text-center"><?php echo htmlspecialchars($status); ?></div>
                        <div class="kanban-column p-2" data-status="<?php echo htmlspecialchars($status); ?>"
                            style="min-height:200px;">
                            <?php foreach ($tasks as $task): ?>
                                <div class="card mb-2 kanban-task" draggable="true" data-task-id="<?php echo $task['id']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#taskModal<?php echo $task['id']; ?>"
                                    style="cursor:pointer;">
                                    <div class="card-body p-2">
                                        <div class="fw-bold mb-1"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="text-muted mb-1" style="font-size:0.92rem;"><i
                                                class="bi bi-folder2-open me-1"></i>
                                            <?php echo htmlspecialchars($task['project_title']); ?></div>
                                        <div class="text-muted mb-1" style="font-size:0.92rem;"><i class="bi bi-person me-1"></i>
                                            <?php echo htmlspecialchars($task['assigned_to_name']); ?></div>
                                        <div class="mb-1" style="font-size:0.92rem;"><i class="bi bi-calendar-event me-1"></i>
                                            <?php echo human_friendly_date($task['deadline']); ?></div>
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
                                                <div class="mb-2"><strong>Project:</strong>
                                                    <?php echo htmlspecialchars($task['project_title']); ?></div>
                                                <div class="mb-2"><strong>Assigned To:</strong>
                                                    <?php echo htmlspecialchars($task['assigned_to_name']); ?></div>
                                                <div class="mb-2"><strong>Status:</strong>
                                                    <?php echo htmlspecialchars($task['status']); ?></div>
                                                <div class="mb-2"><strong>Priority:</strong>
                                                    <?php echo htmlspecialchars($task['priority']); ?></div>
                                                <div class="mb-2"><strong>Due Date:</strong>
                                                    <?php echo human_friendly_date($task['deadline']); ?>
                                                    (<?php echo htmlspecialchars(date('M j, Y', strtotime($task['deadline']))); ?>)
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
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
        <script>
            // Minimal drag-and-drop for Kanban (copied from manager/tasks.php)
            let draggedTask = null;
            document.querySelectorAll('.kanban-task').forEach(el => {
                el.addEventListener('dragstart', e => { draggedTask = el; });
            });
            document.querySelectorAll('.kanban-column').forEach(col => {
                col.addEventListener('dragover', e => { e.preventDefault(); });
                col.addEventListener('drop', e => {
                    if (draggedTask) {
                        const taskId = draggedTask.getAttribute('data-task-id');
                        const newStatus = col.getAttribute('data-status');
                        // Submit status change via POST
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `<input type=\"hidden\" name=\"task_id\" value=\"${taskId}\"><input type=\"hidden\" name=\"status\" value=\"${newStatus}\">`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        </script>
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