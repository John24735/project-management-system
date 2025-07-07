<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
$error = '';
$success = '';
$members = [];
// Fetch all members
$stmt = $pdo->query("SELECT id, username FROM users WHERE role_id = 3");
$members = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $assigned_members = $_POST['members'] ?? [];
    if ($title && $start_date && $end_date) {
        $stmt = $pdo->prepare('INSERT INTO projects (title, description, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)');
        if ($stmt->execute([$title, $description, $start_date, $end_date, $_SESSION['user_id']])) {
            $project_id = $pdo->lastInsertId();
            foreach ($assigned_members as $user_id) {
                $stmt2 = $pdo->prepare('INSERT INTO project_members (project_id, user_id) VALUES (?, ?)');
                $stmt2->execute([$project_id, $user_id]);
            }
            $success = 'Project created successfully!';
        } else {
            $error = 'Failed to create project.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<h2>Create Project</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Assign Members</label>
        <select name="members[]" class="form-select" multiple>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['username']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Create Project</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>