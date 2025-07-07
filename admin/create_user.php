<?php
require_once __DIR__ . '/../includes/header.php';
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
$error = '';
$success = '';
$roles = $pdo->query('SELECT * FROM roles')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role_id = $_POST['role_id'] ?? 3;
    $password = $_POST['password'] ?? '';
    if (!$password) {
        $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }
    if ($username && $email && $role_id) {
        if (register_user($username, $email, $password, $role_id)) {
            $success = "User created successfully! Default password: <strong>" . htmlspecialchars($password) . "</strong>";
        } else {
            $error = 'Failed to create user. Username or email may already exist.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
$recent_users = $pdo->query('SELECT u.username, u.email, r.name as role, u.created_at FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC LIMIT 5')->fetchAll();
?>
<h2>Create User</h2>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<form method="post" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Default Password <small>(leave blank to auto-generate)</small></label>
        <input type="text" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Create User</button>
</form>
<h4>Recently Created Users</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recent_users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>