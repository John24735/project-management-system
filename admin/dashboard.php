<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
// Real-time stats
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status IS NULL OR status = 'Active'")->fetchColumn();
$total_tasks = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$open_tasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'Completed'")->fetchColumn();
$overdue_tasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'Completed' AND deadline < CURDATE()") ? $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'Completed' AND deadline < CURDATE()")->fetchColumn() : 0;
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$recent_projects = $pdo->query("SELECT * FROM projects WHERE status IS NULL OR status = 'Active' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_tasks = $pdo->query('SELECT t.*, u.username as assigned_to_name, p.title as project_title, a.username as assigned_by_name FROM tasks t JOIN users u ON t.assigned_to = u.id LEFT JOIN projects p ON t.project_id = p.id JOIN users a ON t.created_by = a.id ORDER BY t.created_at DESC LIMIT 5')->fetchAll();
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-shield-lock fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Admin Overview</h2>
    </div>
    <div class="summary-row mb-3">
        <div class="summary-card">
            <div class="icon"><i class="bi bi-folder2-open"></i></div>
            <div class="fw-bold"><?php echo $total_projects; ?></div>
            <div class="label"><i class="bi bi-folder"></i> Active Projects</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-list-task"></i></div>
            <div class="fw-bold"><?php echo $open_tasks; ?></div>
            <div class="label"><i class="bi bi-list-check"></i> Open Tasks</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="fw-bold"><?php echo $overdue_tasks; ?></div>
            <div class="label"><i class="bi bi-clock-history"></i> Overdue Tasks</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-people"></i></div>
            <div class="fw-bold"><?php echo $total_users; ?></div>
            <div class="label"><i class="bi bi-person"></i> Users</div>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="card summary-card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-folder2-open"></i> Recent Projects</h5>
                    <table class="table table-sm table-bordered mb-0 w-100" style="font-size:0.92rem; width:100%;">
                        <thead>
                            <tr>
                                <th><i class="bi bi-folder"></i> Title</th>
                                <th><i class="bi bi-calendar-event"></i> Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (
                                $recent_projects as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card summary-card p-2 mb-2">
                <div class="card-body p-2">
                    <h5 class="card-title mb-2 d-flex align-items-center gap-2" style="font-size:1rem;"><i
                            class="bi bi-list-task"></i> Recent Tasks</h5>
                    <table class="table table-sm table-bordered mb-0 w-100" style="font-size:0.92rem; width:100%;">
                        <thead>
                            <tr>
                                <th><i class="bi bi-list-check"></i> Task</th>
                                <th><i class="bi bi-folder"></i> Project</th>
                                <th><i class="bi bi-person"></i> Assigned</th>
                                <th><i class="bi bi-person-badge"></i> Assigned By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_tasks as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                                    <td><?php echo htmlspecialchars($t['project_title'] ?? 'No Project'); ?></td>
                                    <td><?php echo htmlspecialchars($t['assigned_to_name']); ?></td>
                                    <td><?php echo htmlspecialchars($t['assigned_by_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>