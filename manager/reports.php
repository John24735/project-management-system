<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Fetch manager's OWNED projects
$stmt = $pdo->prepare('SELECT id, title FROM projects WHERE created_by = ?');
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();
$selected_project = $_GET['project_id'] ?? ($projects[0]['id'] ?? null);
$completion_rate = 0;
$earned_value = 0;
$workload = [];
if ($selected_project) {
    // Completion rate
    $stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(status="Completed") as completed FROM tasks WHERE project_id = ?');
    $stmt->execute([$selected_project]);
    $row = $stmt->fetch();
    $completion_rate = $row['total'] ? round(($row['completed'] / $row['total']) * 100) : 0;
    // Earned value (for demo: completed tasks * 100)
    $earned_value = $row['completed'] * 100;
    // Workload per member
    $stmt = $pdo->prepare('SELECT u.username, COUNT(t.id) as tasks FROM users u JOIN tasks t ON t.assigned_to = u.id WHERE t.project_id = ? GROUP BY u.id');
    $stmt->execute([$selected_project]);
    $workload = $stmt->fetchAll();
}
// CSV export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="project_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Username', 'Tasks Assigned']);
    foreach ($workload as $w) {
        fputcsv($out, [$w['username'], $w['tasks']]);
    }
    fclose($out);
    exit;
}
?>
<?php include '../includes/sidebar.php'; ?>
<style>
    .reports-card {
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(141,40,143,0.07);
        border: none;
        background: #fff;
        min-height: 160px;
    }
    .reports-card .card-body {
        padding: 0.7rem 0.8rem 0.7rem 0.8rem;
    }
    .reports-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .display-6 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    #completionChart, #workloadChart {
        max-height: 110px;
    }
</style>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-bar-chart fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">Reports</h2>
        <form class="ms-auto" method="get">
            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($projects as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php if ($selected_project == $p['id'])
                           echo ' selected'; ?>>
                        <?php echo htmlspecialchars($p['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <form method="post" class="ms-2">
            <button type="submit" name="export_csv" class="btn btn-outline-secondary btn-sm"><i
                    class="bi bi-download"></i> Export CSV</button>
        </form>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card reports-card p-2">
                <div class="card-body">
                    <h5 class="card-title">Completion Rate</h5>
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card reports-card p-2">
                <div class="card-body">
                    <h5 class="card-title">Earned Value</h5>
                    <div class="display-6 fw-bold text-success">$<?php echo $earned_value; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card reports-card p-2">
                <div class="card-body">
                    <h5 class="card-title">Member Workload</h5>
                    <canvas id="workloadChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const completionData = {
        labels: ['Completed', 'Remaining'],
        datasets: [{
            data: [<?php echo $completion_rate; ?>, <?php echo 100 - $completion_rate; ?>],
            backgroundColor: ['#8d288f', '#e9ecef']
        }]
    };
    new Chart(document.getElementById('completionChart'), { type: 'doughnut', data: completionData, options: { cutout: '70%' } });
    const workloadData = {
        labels: [<?php foreach ($workload as $w) {
            echo "'" . addslashes($w['username']) . "',";
        } ?>],
        datasets: [{
            label: 'Tasks',
            data: [<?php foreach ($workload as $w) {
                echo $w['tasks'] . ",";
            } ?>],
            backgroundColor: '#8d288f'
        }]
    };
    new Chart(document.getElementById('workloadChart'), { type: 'bar', data: workloadData });
</script>
<?php require_once '../includes/footer.php'; ?>