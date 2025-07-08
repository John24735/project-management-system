<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Real-time KPIs
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status IS NULL OR status = 'Active'")->fetchColumn();
$total_tasks = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$completed_tasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'")->fetchColumn();
$completion_rate = $total_tasks ? round(($completed_tasks / $total_tasks) * 100) : 0;
$overdue_tasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'Completed' AND deadline < CURDATE()") ? $pdo->query("SELECT COUNT(*) FROM tasks WHERE status != 'Completed' AND deadline < CURDATE()")->fetchColumn() : 0;

// Project completion rates
$projects = $pdo->query('SELECT p.id, p.title, COUNT(t.id) as total_tasks, SUM(t.status = "Completed") as completed_tasks FROM projects p LEFT JOIN tasks t ON p.id = t.project_id WHERE p.status IS NULL OR p.status = "Active" GROUP BY p.id')->fetchAll();

// User performance
$users = $pdo->query('SELECT u.username, COUNT(t.id) as total_assigned, SUM(t.status = "Completed") as completed, SUM(t.status != "Completed" AND t.deadline < CURDATE()) as overdue FROM users u LEFT JOIN tasks t ON u.id = t.assigned_to GROUP BY u.id ORDER BY completed DESC')->fetchAll();

// Task analytics by priority
$priority_stats = $pdo->query('SELECT priority, COUNT(*) as count, SUM(status = "Completed") as completed FROM tasks GROUP BY priority')->fetchAll();

// Task analytics by status
$status_stats = $pdo->query('SELECT status, COUNT(*) as count FROM tasks GROUP BY status')->fetchAll();

// Task completion trends (last 30 days)
$completion_trends = $pdo->query('SELECT DATE(updated_at) as date, COUNT(*) as completed FROM tasks WHERE status = "Completed" AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(updated_at) ORDER BY date DESC LIMIT 7')->fetchAll();

// Average task completion time by priority
$avg_completion_time = $pdo->query('SELECT priority, AVG(DATEDIFF(updated_at, created_at)) as avg_days FROM tasks WHERE status = "Completed" GROUP BY priority')->fetchAll();

// Recent activity (last 7 days)
$recent_activity = $pdo->query('SELECT t.title, t.status, u.username, p.title as project FROM tasks t JOIN users u ON t.assigned_to = u.id JOIN projects p ON t.project_id = p.id WHERE t.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY t.updated_at DESC LIMIT 10')->fetchAll();

$tab = $_GET['tab'] ?? 'overview';
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-bar-chart fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Reports</h2>
    </div>

    <!-- KPI Summary Cards -->
    <div class="summary-row mb-3">
        <div class="summary-card">
            <div class="icon"><i class="bi bi-folder2-open"></i></div>
            <div class="fw-bold"><?php echo $total_projects; ?></div>
            <div class="label"><i class="bi bi-folder"></i> Active Projects</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-list-task"></i></div>
            <div class="fw-bold"><?php echo $completion_rate; ?>%</div>
            <div class="label"><i class="bi bi-check-circle"></i> Completion Rate</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-exclamation-octagon"></i></div>
            <div class="fw-bold"><?php echo $overdue_tasks; ?></div>
            <div class="label"><i class="bi bi-clock-history"></i> Overdue Tasks</div>
        </div>
        <div class="summary-card">
            <div class="icon"><i class="bi bi-people"></i></div>
            <div class="fw-bold"><?php echo count($users); ?></div>
            <div class="label"><i class="bi bi-person"></i> Active Users</div>
        </div>
    </div>

    <!-- Report Tabs -->
    <ul class="nav nav-tabs mb-3" style="font-size:0.98rem;">
        <li class="nav-item"><a class="nav-link<?php if ($tab == 'overview')
            echo ' active'; ?>" href="?tab=overview"><i class="bi bi-graph-up"></i> Overview</a></li>
        <li class="nav-item"><a class="nav-link<?php if ($tab == 'projects')
            echo ' active'; ?>" href="?tab=projects"><i class="bi bi-folder2-open"></i> Projects</a></li>
        <li class="nav-item"><a class="nav-link<?php if ($tab == 'users')
            echo ' active'; ?>" href="?tab=users"><i class="bi bi-people"></i> Users</a></li>
        <li class="nav-item"><a class="nav-link<?php if ($tab == 'tasks')
            echo ' active'; ?>" href="?tab=tasks"><i class="bi bi-list-task"></i> Tasks</a></li>
        <li class="nav-item"><a class="nav-link<?php if ($tab == 'activity')
            echo ' active'; ?>" href="?tab=activity"><i class="bi bi-activity"></i> Activity</a></li>
    </ul>

    <!-- Report Content -->
    <div class="card summary-card p-3">
        <?php if ($tab == 'overview'): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="d-flex align-items-center gap-2 mb-0"><i class="bi bi-graph-up"></i> System Overview</h5>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export</button>
            </div>

            <!-- Quick Stats Row -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card summary-card p-2 text-center">
                        <div class="fw-bold text-primary"><?php echo $total_projects; ?></div>
                        <div class="small text-muted">Active Projects</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card p-2 text-center">
                        <div class="fw-bold text-success"><?php echo $completion_rate; ?>%</div>
                        <div class="small text-muted">Completion Rate</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card p-2 text-center">
                        <div class="fw-bold text-warning"><?php echo $overdue_tasks; ?></div>
                        <div class="small text-muted">Overdue Tasks</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card p-2 text-center">
                        <div class="fw-bold text-info"><?php echo count($users); ?></div>
                        <div class="small text-muted">Active Users</div>
                    </div>
                </div>
            </div>

            <h6 class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-folder2-open"></i> Project Completion</h6>
            <table class="table table-sm table-bordered" style="font-size:0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>Progress</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                        <?php $percent = $p['total_tasks'] ? round(($p['completed_tasks'] / $p['total_tasks']) * 100) : 0; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo $p['total_tasks']; ?></td>
                            <td><?php echo $p['completed_tasks']; ?></td>
                            <td>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" style="width:<?php echo $percent; ?>%"></div>
                                </div>
                            </td>
                            <td><?php echo $percent; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($tab == 'projects'): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="d-flex align-items-center gap-2 mb-0"><i class="bi bi-folder2-open"></i> Project Completion %
                </h5>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export</button>
            </div>
            <table class="table table-sm table-bordered" style="font-size:0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>Progress</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                        <?php $percent = $p['total_tasks'] ? round(($p['completed_tasks'] / $p['total_tasks']) * 100) : 0; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo $p['total_tasks']; ?></td>
                            <td><?php echo $p['completed_tasks']; ?></td>
                            <td>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" style="width:<?php echo $percent; ?>%"></div>
                                </div>
                            </td>
                            <td><?php echo $percent; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($tab == 'users'): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="d-flex align-items-center gap-2 mb-0"><i class="bi bi-people"></i> User Performance</h5>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export</button>
            </div>
            <table class="table table-sm table-bordered" style="font-size:0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Overdue</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php $rate = $u['total_assigned'] ? round(($u['completed'] / $u['total_assigned']) * 100) : 0; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo $u['total_assigned']; ?></td>
                            <td><span class="badge bg-success"><?php echo $u['completed']; ?></span></td>
                            <td><span class="badge bg-danger"><?php echo $u['overdue']; ?></span></td>
                            <td><?php echo $rate; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($tab == 'tasks'): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="d-flex align-items-center gap-2 mb-0"><i class="bi bi-list-task"></i> Task Analytics</h5>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export</button>
            </div>

            <h6 class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-flag"></i> Task Priority Distribution</h6>
            <table class="table table-sm table-bordered" style="font-size:0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th>Priority</th>
                        <th>Total</th>
                        <th>Completed</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($priority_stats as $p): ?>
                        <?php $rate = $p['count'] ? round(($p['completed'] / $p['count']) * 100) : 0; ?>
                        <tr>
                            <td><span
                                    class="badge badge-<?php echo strtolower($p['priority']); ?>"><?php echo htmlspecialchars($p['priority']); ?></span>
                            </td>
                            <td><?php echo $p['count']; ?></td>
                            <td><?php echo $p['completed']; ?></td>
                            <td><?php echo $rate; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($tab == 'activity'): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="d-flex align-items-center gap-2 mb-0"><i class="bi bi-activity"></i> Recent Activity (Last 7
                    Days)</h5>
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Export</button>
            </div>
            <table class="table table-sm table-bordered" style="font-size:0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th>Task</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Project</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($a['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($a['username']); ?></td>
                            <td><?php echo htmlspecialchars($a['project']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>