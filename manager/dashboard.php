<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch manager's projects
$projects = $pdo->prepare('SELECT p.* FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?');
$projects->execute([$_SESSION['user_id']]);
$projects = $projects->fetchAll();
?>
<h2>Manager Dashboard</h2>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">My Projects</h5>
                <ul class="list-group">
                    <?php foreach ($projects as $project): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($project['title']); ?>
                            <span
                                class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($project['status'] ?? 'Active'); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Tasks</h5>
                <a href="tasks.php" class="btn btn-primary">View Tasks</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>