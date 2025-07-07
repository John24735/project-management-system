<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
$error = '';
$success = '';
$projects = $pdo->query('SELECT id, title FROM projects')->fetchAll();
$members = $pdo->query('SELECT id, username FROM users WHERE role_id = 3')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $assigned_to = $_POST['assigned_to'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';
    $deadline = $_POST['deadline'] ?? '';
    if ($project_id && $title && $assigned_to && $deadline) {
        $stmt = $pdo->prepare('INSERT INTO tasks (project_id, title, description, assigned_to, priority, deadline, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$project_id, $title, $description, $assigned_to, $priority, $deadline, $_SESSION['user_id']])) {
            $success = 'Task assigned successfully!';
        } else {
            $error = 'Failed to assign task.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<h2>Assign Task</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Project</label>
        <select name="project_id" class="form-select" required>
            <option value="">Select Project</option>
            <?php foreach ($projects as $project): ?>
                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['title']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Task Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Assign To</label>
        <select name="assigned_to" class="form-select" required>
            <option value="">Select Member</option>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Deadline</label>
        <input type="date" name="deadline" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Assign Task</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>