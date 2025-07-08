<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch manager's projects
$projects = $pdo->prepare('SELECT p.* FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?');
$projects->execute([$_SESSION['user_id']]);
$projects = $projects->fetchAll();
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-briefcase fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Manager Dashboard</h2>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="card summary-card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-folder2-open"></i> My Projects</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($projects as $project): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-2"
                                style="font-size:0.92rem;">
                                <span><i class="bi bi-folder me-1"></i>
                                    <?php echo htmlspecialchars($project['title']); ?></span>
                                <span class="badge bg-primary rounded-pill"><i class="bi bi-activity me-1"></i>
                                    <?php echo htmlspecialchars($project['status'] ?? 'Active'); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card summary-card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-list-task"></i> Tasks</h5>
                    <a href="tasks.php" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                        style="font-size:0.92rem;"><i class="bi bi-list-check"></i> View Tasks</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>