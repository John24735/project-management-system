<?php
require_once '../includes/header.php';
if (!is_member()) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
// Fetch projects where the member has at least one assigned task
$stmt = $pdo->prepare('SELECT DISTINCT p.* FROM projects p JOIN tasks t ON p.id = t.project_id WHERE t.assigned_to = ?');
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
$project_id = $_GET['project_id'] ?? ($projects[0]['id'] ?? null);
$project = null;
$team = [];
$docs = [];
if ($project_id) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    // Team list
    $stmt = $pdo->prepare('SELECT u.username, u.first_name, u.last_name FROM users u JOIN project_members pm ON u.id = pm.user_id WHERE pm.project_id = ?');
    $stmt->execute([$project_id]);
    $team = $stmt->fetchAll();
    // Document links (placeholder)
    $docs = [
        ['title' => 'Project Plan', 'url' => '#'],
        ['title' => 'Requirements', 'url' => '#'],
        ['title' => 'Timeline', 'url' => '#']
    ];
}
function format_date($date)
{
    return date('d M Y', strtotime($date));
}
function due_date_indicator($deadline, $status)
{
    $today = date('Y-m-d');
    $d = strtotime($deadline);
    $t = strtotime($today);
    if ($status === 'Completed')
        return '';
    if ($d < $t)
        return '<span class="badge bg-danger ms-1">Overdue</span>';
    if ($d == $t)
        return '<span class="badge bg-warning text-dark ms-1">Due Today</span>';
    $days = round(($d - $t) / 86400);
    return '<span class="badge bg-info text-dark ms-1">Due in ' . $days . ' day' . ($days > 1 ? 's' : '') . '</span>';
}
$tasks = [];
if ($project_id) {
    // Only show tasks for this project assigned to the current member
    $stmt = $pdo->prepare('SELECT t.*, u.username, u.first_name, u.last_name FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE t.project_id = ? AND t.assigned_to = ? ORDER BY t.deadline ASC');
    $stmt->execute([$project_id, $user_id]);
    $tasks = $stmt->fetchAll();
    foreach ($tasks as &$task) {
        if (!isset($task['progress'])) {
            if (isset($task['status']) && $task['status'] === 'Completed') {
                $task['progress'] = 100;
            } elseif (isset($task['status']) && $task['status'] === 'In Progress') {
                $task['progress'] = 50;
            } else {
                $task['progress'] = 0;
            }
        }
    }
    unset($task);
}
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-folder2-open fs-6 text-primary"></i>
            <h2 class="fw-bold mb-0" style="font-size:1rem;">Project Overview</h2>
        </div>
        <?php if ($projects): ?>
            <div class="d-flex flex-nowrap overflow-auto gap-2 mb-2" style="padding-bottom:1px;">
                <?php foreach ($projects as $p):
                    $selected = ($project_id == $p['id']); ?>
                    <a href="?project_id=<?php echo $p['id']; ?>"
                        class="card project-switch-card text-decoration-none<?php if ($selected)
                            echo ' border-primary shadow-sm';
                        else
                            echo ' border-0'; ?>"
                        style="min-width:160px;max-width:200px;font-size:0.97rem;padding:0.2rem 0.5rem;<?php if ($selected)
                            echo 'background:#f6f9ff;'; ?>transition:box-shadow 0.15s;"
                        onmouseover="this.style.boxShadow='0 2px 8px rgba(80,80,180,0.10)';"
                        onmouseout="this.style.boxShadow='<?php echo $selected ? '0 1px 6px rgba(80,80,180,0.06)' : 'none'; ?>';">
                        <div class="card-body py-1 px-2">
                            <div class="fw-semibold mb-1" style="font-size:0.98rem;"><i class="bi bi-folder2-open me-1"></i>
                                <?php echo htmlspecialchars($p['title']); ?></div>
                            <div class="small text-muted mb-1"><i class="bi bi-calendar-event me-1"></i>
                                <?php echo format_date($p['end_date']); ?>
                                <?php echo due_date_indicator($p['end_date'], $p['status']); ?></div>
                            <span class="badge bg-light text-secondary border border-1 border-secondary"
                                style="font-size:0.85em;"><i class="bi bi-activity"></i>
                                <?php echo htmlspecialchars($p['status']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($project): ?>
        <div class="card p-2 mb-2">
            <h5 class="fw-semibold mb-2" style="font-size:0.98rem;">Description</h5>
            <div class="mb-2 text-muted"><?php echo htmlspecialchars($project['description']); ?></div>
            <div class="mb-2"><b>Timeline:</b> <?php echo format_date($project['start_date']); ?> to
                <?php echo format_date($project['end_date']); ?>
                <?php echo due_date_indicator($project['end_date'], $project['status']); ?>
            </div>
            <div class="mb-2"><b>Status:</b> <?php echo htmlspecialchars($project['status']); ?></div>
        </div>
        <div class="card p-2 mb-2">
            <h5 class="fw-semibold mb-2" style="font-size:0.98rem;">Team</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($team as $member): ?>
                    <li class="list-group-item p-2">
                        <?php echo htmlspecialchars(trim($member['first_name'] . ' ' . $member['last_name'])) ?: htmlspecialchars($member['username']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card p-2 mb-2">
            <h5 class="fw-semibold mb-2" style="font-size:0.98rem;">Documents</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($docs as $doc): ?>
                    <li class="list-group-item p-2"><a href="<?php echo $doc['url']; ?>"
                            target="_blank"><?php echo htmlspecialchars($doc['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card p-2 mb-2">
            <h5 class="fw-semibold mb-2" style="font-size:0.98rem;">Project Tasks</h5>
            <?php if ($tasks): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" style="font-size:0.91rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Assignee</th>
                                <th>Deadline</th>
                                <th>Due</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['status']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($task['first_name'] . ' ' . $task['last_name'])) ?: htmlspecialchars($task['username']); ?>
                                    </td>
                                    <td><?php echo format_date($task['deadline']); ?></td>
                                    <td><?php echo due_date_indicator($task['deadline'], $task['status']); ?></td>
                                    <td>
                                        <div class="progress" style="height: 14px; min-width: 80px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo (int) $task['progress']; ?>%;"
                                                aria-valuenow="<?php echo (int) $task['progress']; ?>" aria-valuemin="0"
                                                aria-valuemax="100"><?php echo (int) $task['progress']; ?>%</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No tasks for this project.</div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No project selected or assigned.</div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>