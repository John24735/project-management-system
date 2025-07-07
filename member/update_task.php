<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    echo '<div class="alert alert-danger">Task not found.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
// Fetch task
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND assigned_to = ?');
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();
if (!$task) {
    echo '<div class="alert alert-danger">Task not found or not assigned to you.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? $task['status'];
    $comment = trim($_POST['comment'] ?? '');
    $stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?');
    if ($stmt->execute([$status, $task_id])) {
        if ($comment) {
            $stmt2 = $pdo->prepare('INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)');
            $stmt2->execute([$task_id, $_SESSION['user_id'], $comment]);
        }
        $success = 'Task updated successfully!';
    } else {
        $error = 'Failed to update task.';
    }
}
?>
<h2>Update Task</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="Pending" <?php if ($task['status'] === 'Pending')
                echo 'selected'; ?>>Pending</option>
            <option value="In Progress" <?php if ($task['status'] === 'In Progress')
                echo 'selected'; ?>>In Progress
            </option>
            <option value="Completed" <?php if ($task['status'] === 'Completed')
                echo 'selected'; ?>>Completed</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Comment</label>
        <textarea name="comment" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Task</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>