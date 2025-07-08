<?php
require_once '../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-bell fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">Notifications</h2>
    </div>
    <ul class="list-group">
        <?php if ($notifications):
            foreach ($notifications as $n): ?>
                <li
                    class="list-group-item d-flex justify-content-between align-items-center <?php if (!$n['is_read'])
                        echo 'list-group-item-info'; ?>">
                    <span><i class="bi bi-bell me-2"></i> <?php echo htmlspecialchars($n['message']); ?></span>
                    <span class="text-muted" style="font-size:0.92rem;"><?php echo htmlspecialchars($n['created_at']); ?></span>
                </li>
            <?php endforeach; else: ?>
            <li class="list-group-item text-muted">No notifications.</li>
        <?php endif; ?>
    </ul>
</div>
<?php require_once '../includes/footer.php'; ?>