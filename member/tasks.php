<?php
require_once '../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'kanban'; // Default to kanban
$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$where = ['t.assigned_to = ?'];
$params = [$user_id];
if ($search) {
    $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($priority) {
    $where[] = 't.priority = ?';
    $params[] = $priority;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
// Fetch tasks with project and progress
$stmt = $pdo->prepare("SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id $where_sql ORDER BY t.deadline ASC");
$stmt->execute($params);
$tasks = $stmt->fetchAll();
// Kanban grouping
$kanban = ['Pending' => [], 'In Progress' => [], 'Completed' => []];
foreach ($tasks as &$task) {
    if (!isset($task['progress'])) {
        // Calculate progress as percent of completed status, or set to 0 if not available
        if (isset($task['status']) && $task['status'] === 'Completed') {
            $task['progress'] = 100;
        } elseif (isset($task['status']) && $task['status'] === 'In Progress') {
            $task['progress'] = 50;
        } else {
            $task['progress'] = 0;
        }
    }
    $kanban[$task['status']][] = $task;
}
unset($task);
function format_date($date)
{
    return date('d M Y', strtotime($date));
}
function due_date_indicator($deadline, $status)
{
    $today = date('Y-m-d');
    $d = strtotime($deadline);
    $t = strtotime($today);
    if ($status === 'Completed')
        return '';
    if ($d < $t)
        return '<span class="badge bg-danger ms-1">Overdue</span>';
    if ($d == $t)
        return '<span class="badge bg-warning text-dark ms-1">Due Today</span>';
    $days = round(($d - $t) / 86400);
    return '<span class="badge bg-info text-dark ms-1">Due in ' . $days . ' day' . ($days > 1 ? 's' : '') . '</span>';
}
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-list-task fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">My Tasks</h2>
        <form class="ms-auto d-flex gap-2" method="get">
            <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..."
                value="<?php echo htmlspecialchars($search); ?>">
            <select name="priority" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <option value="Low" <?php if ($priority == 'Low')
                    echo ' selected'; ?>>Low</option>
                <option value="Medium" <?php if ($priority == 'Medium')
                    echo ' selected'; ?>>Medium</option>
                <option value="High" <?php if ($priority == 'High')
                    echo ' selected'; ?>>High</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        </form>
        <div class="ms-2">
            <a href="?view=table"
                class="btn btn-sm <?php echo $view == 'table' ? 'btn-primary' : 'btn-outline-primary'; ?>">Table</a>
            <a href="?view=kanban"
                class="btn btn-sm <?php echo $view == 'kanban' ? 'btn-primary' : 'btn-outline-primary'; ?>">Kanban</a>
        </div>
    </div>
    <?php if ($view == 'table'): ?>
        <table class="table table-sm table-bordered align-middle" style="font-size:0.93rem;">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th>Progress</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td><?php echo htmlspecialchars($task['project_title']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><?php echo format_date($task['deadline']); ?>
                            <?php echo due_date_indicator($task['deadline'], $task['status']); ?></td>
                        <td>
                            <div class="progress" style="height: 16px; min-width: 80px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?php echo (int) $task['progress']; ?>%;"
                                    aria-valuenow="<?php echo (int) $task['progress']; ?>" aria-valuemin="0"
                                    aria-valuemax="100">
                                    <?php echo (int) $task['progress']; ?>%
                                </div>
                            </div>
                        </td>
                        <td><button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#updateTaskModal" data-task='<?php echo json_encode($task); ?>'>Update</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="row kanban-board g-2">
            <?php foreach ($kanban as $status => $tasks): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light fw-bold text-center"><?php echo $status; ?></div>
                        <div class="kanban-column p-2" data-status="<?php echo $status; ?>" style="min-height:120px;">
                            <?php foreach ($tasks as $task): ?>
                                <div class="card mb-2" style="min-height: 160px; max-width: 100%;">
                                    <div class="card-body p-2 d-flex flex-column justify-content-between" style="height: 100%;">
                                        <div>
                                            <div class="fw-bold mb-1"><?php echo htmlspecialchars($task['title']); ?></div>
                                            <div class="text-muted mb-1" style="font-size:0.92rem;">
                                                <i class="bi bi-folder2-open me-1"></i>
                                                <?php echo htmlspecialchars($task['project_title']); ?>
                                            </div>
                                            <div class="mb-1" style="font-size:0.92rem;">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?php echo format_date($task['deadline']); ?>
                                                <?php echo due_date_indicator($task['deadline'], $task['status']); ?>
                                            </div>
                                            <div class="mb-1" style="font-size:0.92rem;">
                                                <i class="bi bi-bar-chart"></i> Progress:
                                                <div class="progress"
                                                    style="height: 12px; min-width: 80px; display:inline-block; width:60%; vertical-align:middle;">
                                                    <div class="progress-bar" role="progressbar"
                                                        style="width: <?php echo (int) $task['progress']; ?>%;"
                                                        aria-valuenow="<?php echo (int) $task['progress']; ?>" aria-valuemin="0"
                                                        aria-valuemax="100"><?php echo (int) $task['progress']; ?>%</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#updateTaskModal"
                                                data-task='<?php echo json_encode($task); ?>'>Update</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<!-- Update Task Modal -->
<div class="modal fade" id="updateTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateTaskForm" method="post" action="update_task.php">
                <div class="modal-header">
                    <h5 class="modal-title">Update Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="modalTaskId">
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select name="status" id="modalStatus" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Progress (%)</label>
                        <input type="number" name="progress" id="modalProgress" class="form-control" min="0" max="100">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Comment</label>
                        <textarea name="comment" id="modalComment" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var updateTaskModal = document.getElementById('updateTaskModal');
        updateTaskModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var task = JSON.parse(button.getAttribute('data-task'));
            document.getElementById('modalTaskId').value = task.id;
            document.getElementById('modalStatus').value = task.status;
            document.getElementById('modalProgress').value = task.progress;
            document.getElementById('modalComment').value = '';
        });
    });
</script>
<?php require_once '../includes/footer.php'; ?>