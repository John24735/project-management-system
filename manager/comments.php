<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    echo '<div class="alert alert-danger">No task selected.</div>';
    exit;
}
// Check if task belongs to manager's projects
$stmt = $pdo->prepare('SELECT t.*, p.title as project_title FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ? AND p.created_by = ?');
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();
if (!$task) {
    echo '<div class="alert alert-danger">Task not found or not allowed.</div>';
    exit;
}
// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $stmt = $pdo->prepare('INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)');
    $stmt->execute([$task_id, $_SESSION['user_id'], trim($_POST['comment'])]);
    header('Location: comments.php?task_id=' . urlencode($task_id));
    exit;
}
// Fetch comments
$stmt = $pdo->prepare('SELECT c.*, u.username FROM task_comments c JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC');
$stmt->execute([$task_id]);
$comments = $stmt->fetchAll();
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-chat-dots fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Comments for: <?php echo htmlspecialchars($task['title']); ?>
        </h2>
    </div>
    <div class="mb-3">
        <form method="post">
            <div class="input-group">
                <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Post</button>
            </div>
        </form>
    </div>
    <ul class="list-group mb-3">
        <?php foreach ($comments as $c): ?>
            <li class="list-group-item">
                <b><?php echo htmlspecialchars($c['username']); ?>:</b> <?php echo htmlspecialchars($c['comment']); ?>
                <span class="text-muted float-end"
                    style="font-size:0.92rem;"><?php echo htmlspecialchars($c['created_at']); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <!-- File uploads (if any) could be shown here -->
</div>
<?php require_once '../includes/footer.php'; ?>