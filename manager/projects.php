<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Handle project edit (inline/modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project'])) {
    $id = $_POST['project_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $status = $_POST['status'];
    $stmt = $pdo->prepare('UPDATE projects SET title=?, description=?, status=? WHERE id=? AND created_by=?');
    $stmt->execute([$title, $desc, $status, $id, $_SESSION['user_id']]);
    header('Location: projects.php?msg=1');
    exit;
}
// Fetch manager's projects
$stmt = $pdo->prepare('SELECT * FROM projects WHERE created_by = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();
$status_filter = $_GET['status'] ?? '';
if ($status_filter) {
    $projects = array_filter($projects, function ($p) use ($status_filter) {
        return $p['status'] === $status_filter; });
}
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-folder2-open fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">My Projects</h2>
        <form class="ms-auto" method="get">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Active" <?php if ($status_filter == 'Active')
                    echo ' selected'; ?>>Active</option>
                <option value="Completed" <?php if ($status_filter == 'Completed')
                    echo ' selected'; ?>>Completed</option>
                <option value="Archived" <?php if ($status_filter == 'Archived')
                    echo ' selected'; ?>>Archived</option>
            </select>
        </form>
    </div>
    <div class="row g-3">
        <?php foreach ($projects as $project): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2">
                            <i class="bi bi-folder"></i> <?php echo htmlspecialchars($project['title']); ?>
                        </h5>
                        <div class="mb-2 text-muted" style="font-size:0.95rem;">
                            <?php echo htmlspecialchars($project['description']); ?>
                        </div>
                        <div class="mb-2">
                            <span
                                class="badge bg-<?php echo $project['status'] == 'Active' ? 'success' : ($project['status'] == 'Completed' ? 'primary' : 'secondary'); ?>">Status:
                                <?php echo htmlspecialchars($project['status']); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Start:</span> <?php echo htmlspecialchars($project['start_date']); ?>
                            <span class="text-muted ms-2">End:</span> <?php echo htmlspecialchars($project['end_date']); ?>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editProjectModal<?php echo $project['id']; ?>"><i class="bi bi-pencil"></i>
                            Edit</button>
                    </div>
                </div>
            </div>
            <!-- Edit Modal -->
            <div class="modal fade" id="editProjectModal<?php echo $project['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Project</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control form-control-sm"
                                        value="<?php echo htmlspecialchars($project['title']); ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control form-control-sm"
                                        rows="2"><?php echo htmlspecialchars($project['description']); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Active" <?php if ($project['status'] == 'Active')
                                            echo ' selected'; ?>>
                                            Active</option>
                                        <option value="Completed" <?php if ($project['status'] == 'Completed')
                                            echo ' selected'; ?>>Completed</option>
                                        <option value="Archived" <?php if ($project['status'] == 'Archived')
                                            echo ' selected'; ?>>
                                            Archived</option>
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
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>