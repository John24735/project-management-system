<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Fetch users for owner dropdown
$owners = $pdo->query('SELECT id, username FROM users')->fetchAll();
// Handle filters/search
$search = trim($_GET['search'] ?? '');
$where = [];
$params = [];
if ($search) {
    $where[] = '(p.title LIKE ? OR u.username LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$projects = $pdo->prepare("SELECT p.*, u.username as owner FROM projects p JOIN users u ON p.created_by = u.id $where_sql ORDER BY p.created_at DESC");
$projects->execute($params);
$projects = $projects->fetchAll();
// Handle create, edit, archive (POST)
$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_project'])) {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        $owner = $_POST['owner_id'];
        if ($title && $start && $end && $owner) {
            $stmt = $pdo->prepare('INSERT INTO projects (title, description, start_date, end_date, created_by, status) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt->execute([$title, $desc, $start, $end, $owner, 'Active'])) {
                $action_msg = "<div class='alert alert-success p-2 my-2'>Project created.</div>";
            } else {
                $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to create project.</div>";
            }
        }
    } elseif (isset($_POST['edit_project'])) {
        $id = $_POST['project_id'];
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        $owner = $_POST['owner_id'];
        $stmt = $pdo->prepare('UPDATE projects SET title=?, description=?, start_date=?, end_date=?, created_by=? WHERE id=?');
        if ($stmt->execute([$title, $desc, $start, $end, $owner, $id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>Project updated.</div>";
        } else {
            $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to update project.</div>";
        }
    } elseif (isset($_POST['archive_project'])) {
        $id = $_POST['project_id'];
        $stmt = $pdo->prepare("UPDATE projects SET status='Archived' WHERE id=?");
        if ($stmt->execute([$id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>Project archived.</div>";
        }
    }
    // Refresh project list after action
    header('Location: projects.php?msg=1');
    exit;
}
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-folder2-open fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Projects</h2>
        <button class="btn btn-sm btn-primary ms-auto d-flex align-items-center gap-1" data-bs-toggle="modal"
            data-bs-target="#createProjectModal"><i class="bi bi-plus-circle"></i> New Project</button>
    </div>
    <?php if ($action_msg)
        echo $action_msg; ?>
    <form class="row g-2 mb-2" method="get">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title/owner"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <table class="table table-sm table-bordered align-middle" style="font-size:0.92rem;">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Start</th>
                <th>End</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><?php echo htmlspecialchars($p['owner']); ?></td>
                    <td>
                        <?php
                        $status = isset($p['status']) ? $p['status'] : 'Active';
                        $chip = 'bg-success';
                        if ($status == 'Archived')
                            $chip = 'bg-secondary';
                        elseif ($status == 'Completed')
                            $chip = 'bg-info';
                        elseif ($status == 'Pending')
                            $chip = 'bg-warning';
                        echo "<span class='badge $chip'>" . htmlspecialchars($status) . "</span>";
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($p['end_date']); ?></td>
                    <td>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" data-bs-toggle="modal"
                            data-bs-target="#editProjectModal<?php echo $p['id']; ?>"><i class="bi bi-pencil"></i></button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" name="archive_project"
                                class="btn btn-outline-warning btn-sm py-0 px-1 ms-1"
                                onclick="return confirm('Archive this project?')"><i class="bi bi-archive"></i></button>
                        </form>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editProjectModal<?php echo $p['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Project</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                    <div class="mb-2">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control form-control-sm"
                                            value="<?php echo htmlspecialchars($p['title']); ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control form-control-sm"
                                            rows="2"><?php echo htmlspecialchars($p['description']); ?></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm"
                                            value="<?php echo htmlspecialchars($p['start_date']); ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm"
                                            value="<?php echo htmlspecialchars($p['end_date']); ?>" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Owner</label>
                                        <select name="owner_id" class="form-select form-select-sm">
                                            <?php foreach ($owners as $o): ?>
                                                <option value="<?php echo $o['id']; ?>" <?php if ($p['created_by'] == $o['id'])
                                                       echo ' selected'; ?>><?php echo htmlspecialchars($o['username']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="edit_project" class="btn btn-primary btn-sm"><i
                                            class="bi bi-save"></i> Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Create Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-folder-plus"></i> Create Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Owner</label>
                        <select name="owner_id" class="form-select form-select-sm">
                            <?php foreach ($owners as $o): ?>
                                <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create_project" class="btn btn-primary btn-sm"><i
                            class="bi bi-plus-circle"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>