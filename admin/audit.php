<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Filters
$date = $_GET['date'] ?? '';
$user = $_GET['user'] ?? '';
$where = [];
$params = [];
if ($date) {
    $where[] = 'DATE(l.timestamp) = ?';
    $params[] = $date;
}
if ($user) {
    $where[] = 'u.username = ?';
    $params[] = $user;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
// Fetch logs from audit_logs table
$stmt = $pdo->prepare("SELECT l.*, u.username FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id $where_sql ORDER BY l.timestamp DESC LIMIT 100");
$stmt->execute($params);
$logs = $stmt->fetchAll();
// Get all users for filter
$all_users = $pdo->query('SELECT DISTINCT u.username FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id')->fetchAll();
// Export CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_logs.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Timestamp', 'User', 'Action', 'Details']);
    foreach ($logs as $l)
        fputcsv($out, [$l['timestamp'], $l['username'], $l['action'], $l['details']]);
    fclose($out);
    exit;
}
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-clipboard-data fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Audit Logs</h2>
        <form method="post" class="ms-auto">
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" name="export_csv"><i
                    class="bi bi-download"></i> Export CSV</button>
        </form>
    </div>
    <form class="row g-2 mb-2" method="get">
        <div class="col-auto">
            <input type="date" name="date" class="form-control form-control-sm"
                value="<?php echo htmlspecialchars($date); ?>">
        </div>
        <div class="col-auto">
            <select name="user" class="form-select form-select-sm">
                <option value="">All Users</option>
                <?php foreach ($all_users as $u): ?>
                    <option value="<?php echo htmlspecialchars($u['username']); ?>" <?php if ($user == $u['username'])
                           echo ' selected'; ?>><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <table class="table table-sm table-bordered align-middle" style="font-size:0.92rem;">
        <thead class="table-light">
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $i => $l): ?>
                <tr>
                    <td><?php echo htmlspecialchars($l['timestamp']); ?></td>
                    <td><?php echo htmlspecialchars($l['username']); ?></td>
                    <td><?php echo htmlspecialchars($l['action']); ?></td>
                    <td>
                        <button type="button" class="btn btn-outline-info btn-sm py-0 px-1" data-bs-toggle="modal"
                            data-bs-target="#logModal<?php echo $i; ?>"><i class="bi bi-info-circle"></i></button>
                        <!-- Modal -->
                        <div class="modal fade" id="logModal<?php echo $i; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bi bi-info-circle"></i> Log Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <pre
                                            class="small bg-light p-2 rounded"><?php echo htmlspecialchars(json_encode(json_decode($l['details']), JSON_PRETTY_PRINT)); ?></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>