<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch manager's projects for dropdown (owned or assigned)
$stmt = $pdo->prepare('SELECT DISTINCT p.* FROM projects p LEFT JOIN project_members pm ON p.id = pm.project_id WHERE p.created_by = ? OR pm.user_id = ?');
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$projects = $stmt->fetchAll();
$project_ids = array_column($projects, 'id');
$selected_project = $_GET['project_id'] ?? ($projects[0]['id'] ?? null);
// Handle status change (drag-and-drop)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE tasks SET status=? WHERE id=? AND project_id IN (' . str_repeat('?,', count($project_ids) - 1) . '?' . ')');
    $stmt->execute(array_merge([$_POST['status'], $_POST['task_id']], $project_ids));
    header('Location: tasks.php?project_id=' . urlencode($selected_project));
    exit;
}
// Handle create task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $assigned_to = $_POST['assigned_to'] ?: null;
    $project_id = $_POST['project_id'] ?: null;
    $priority = $_POST['priority'] ?? 'Medium';
    $deadline = $_POST['deadline'] ?: null;
    if ($title && $desc) { // Require description
        $stmt = $pdo->prepare('INSERT INTO tasks (project_id, title, description, assigned_to, status, priority, deadline, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$project_id, $title, $desc, $assigned_to, 'Pending', $priority, $deadline, $_SESSION['user_id']]);
        header('Location: tasks.php' . ($selected_project ? '?project_id=' . urlencode($selected_project) : ''));
        exit;
    }
}
// Fetch tasks for selected project
$kanban = ['Pending' => [], 'In Progress' => [], 'Completed' => []];
if ($selected_project) {
    $stmt = $pdo->prepare('SELECT t.*, u.username as assigned_to_name FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE t.project_id = ?');
    $stmt->execute([$selected_project]);
    foreach ($stmt->fetchAll() as $task) {
        $kanban[$task['status']][] = $task;
    }
}
// Fetch all users for assignment (including admin)
$members = $pdo->query('SELECT id, username FROM users')->fetchAll();
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-list-task fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Tasks</h2>
        <form class="ms-auto" method="get">
            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">No Project</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php if ($selected_project == $p['id'])
                           echo ' selected'; ?>>
                        <?php echo htmlspecialchars($p['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <!-- Create Task Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="post" class="row g-2 align-items-end">
                <input type="hidden" name="create_task" value="1">
                <div class="col-md-3">
                    <label class="form-label mb-0">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Assign To</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">Unassigned</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Project</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">No Project</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">Deadline</label>
                    <input type="date" name="deadline" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100" type="submit"><i class="bi bi-plus-circle"></i>
                        Add</button>
                </div>
                <div class="col-12">
                    <label class="form-label mb-0">Description</label>
                    <textarea name="description" class="form-control form-control-sm" rows="1" required></textarea>
                </div>
            </form>
        </div>
    </div>
    <div class="row kanban-board g-2">
        <?php foreach ($kanban as $status => $tasks): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light fw-bold text-center"><?php echo $status; ?></div>
                    <div class="kanban-column p-2" data-status="<?php echo $status; ?>" style="min-height:200px;">
                        <?php foreach ($tasks as $task): ?>
                            <div class="card mb-2 kanban-task" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                                <div class="card-body p-2">
                                    <div class="fw-bold"><?php echo htmlspecialchars($task['title']); ?></div>
                                    <div class="text-muted" style="font-size:0.92rem;">Assigned:
                                        <?php echo htmlspecialchars($task['assigned_to_name']); ?>
                                    </div>
                                    <div class="text-muted" style="font-size:0.92rem;">Deadline:
                                        <?php echo htmlspecialchars($task['deadline']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
    // Minimal drag-and-drop for Kanban
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
                form.innerHTML = `<input type="hidden" name="task_id" value="${taskId}"><input type="hidden" name="status" value="${newStatus}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
<?php require_once '../includes/footer.php'; ?>