<?php
// Handle POST actions before any output
require_once '../config/db.php';
require_once '../includes/auth.php';
$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_project'])) {
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
<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Fetch users for owner dropdown
$owners = $pdo->query('SELECT id, username FROM users')->fetchAll();
// Handle filters/search
$search = trim($_GET['search'] ?? '');
$view = $_GET['view'] ?? 'grid'; // Default to grid view
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
?>
<style>
    .project-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(80, 80, 180, 0.08);
        padding: 1.2rem;
        margin-bottom: 1rem;
        border-left: 4px solid #e9ecef;
        transition: box-shadow 0.2s, transform 0.2s;
        cursor: pointer;
    }

    .project-card:hover {
        box-shadow: 0 4px 20px rgba(80, 80, 180, 0.12);
        transform: translateY(-2px);
    }

    .project-card .project-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d2d4d;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .project-card .project-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.8rem;
        line-height: 1.4;
    }

    .project-card .project-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.8rem;
        font-size: 0.85rem;
        color: #888;
    }

    .project-card .project-dates {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }

    .project-card .date-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .project-card .date-label {
        font-weight: 500;
        color: #555;
    }

    .project-card .date-value {
        color: #2d2d4d;
    }

    .project-card .project-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .project-card.border-success {
        border-left-color: #28a745 !important;
    }

    .project-card.border-warning {
        border-left-color: #ffc107 !important;
    }

    .project-card.border-info {
        border-left-color: #17a2b8 !important;
    }

    .project-card.border-secondary {
        border-left-color: #6c757d !important;
    }
</style>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-folder2-open fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Projects</h2>
        <div class="ms-auto d-flex gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <a href="?view=grid<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="btn btn-outline-primary<?php echo $view == 'grid' ? ' active' : ''; ?>">
                    <i class="bi bi-grid-3x3-gap"></i> Grid
                </a>
                <a href="?view=table<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="btn btn-outline-secondary<?php echo $view == 'table' ? ' active' : ''; ?>">
                    <i class="bi bi-table"></i> Table
                </a>
            </div>
            <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal"
                data-bs-target="#createProjectModal"><i class="bi bi-plus-circle"></i> New Project</button>
        </div>
    </div>
    <?php if ($action_msg)
        echo $action_msg; ?>
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title/owner"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <input type="hidden" name="view" value="<?php echo $view; ?>">
    </form>

    <?php if ($view == 'grid'): ?>
        <!-- Grid View -->
        <div class="row g-3">
            <?php foreach ($projects as $p): ?>
                <?php
                $status = isset($p['status']) ? $p['status'] : 'Active';
                $border_class = 'border-success';
                if ($status == 'Archived')
                    $border_class = 'border-secondary';
                elseif ($status == 'Completed')
                    $border_class = 'border-info';
                elseif ($status == 'Pending')
                    $border_class = 'border-warning';

                $start_date = new DateTime($p['start_date']);
                $end_date = new DateTime($p['end_date']);
                $created_date = new DateTime($p['created_at']);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="project-card <?php echo $border_class; ?>">
                        <div class="project-title">
                            <i class="bi bi-folder2"></i>
                            <?php echo htmlspecialchars($p['title']); ?>
                        </div>
                        <div class="project-description">
                            <?php echo htmlspecialchars(substr($p['description'] ?? 'No description', 0, 100)) . (strlen($p['description'] ?? '') > 100 ? '...' : ''); ?>
                        </div>
                        <div class="project-meta">
                            <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($p['owner']); ?></span>
                            <span
                                class="badge <?php echo $status == 'Active' ? 'bg-success' : ($status == 'Archived' ? 'bg-secondary' : ($status == 'Completed' ? 'bg-info' : 'bg-warning')); ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>
                        <div class="project-dates">
                            <div class="date-item">
                                <span class="date-label">Start:</span>
                                <span class="date-value"><?php echo $start_date->format('M j, Y'); ?></span>
                            </div>
                            <div class="date-item">
                                <span class="date-label">End:</span>
                                <span class="date-value"><?php echo $end_date->format('M j, Y'); ?></span>
                            </div>
                        </div>
                        <div class="project-actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editProjectModal<?php echo $p['id']; ?>" title="Edit Project">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="archive_project" class="btn btn-outline-warning btn-sm"
                                    onclick="return confirm('Archive this project?')" title="Archive Project">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Table View -->
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
                    <?php
                    $status = isset($p['status']) ? $p['status'] : 'Active';
                    $chip = 'bg-success';
                    if ($status == 'Archived')
                        $chip = 'bg-secondary';
                    elseif ($status == 'Completed')
                        $chip = 'bg-info';
                    elseif ($status == 'Pending')
                        $chip = 'bg-warning';

                    $start_date = new DateTime($p['start_date']);
                    $end_date = new DateTime($p['end_date']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                        <td><?php echo htmlspecialchars($p['owner']); ?></td>
                        <td>
                            <span class='badge <?php echo $chip; ?>'><?php echo htmlspecialchars($status); ?></span>
                        </td>
                        <td><?php echo $start_date->format('M j, Y'); ?></td>
                        <td><?php echo $end_date->format('M j, Y'); ?></td>
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
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Edit Modals -->
<?php foreach ($projects as $p): ?>
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
                        <button type="submit" name="edit_project" class="btn btn-primary btn-sm"><i class="bi bi-save"></i>
                            Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

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