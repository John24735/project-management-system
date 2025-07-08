<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
<?php
// Fetch roles
$roles = $pdo->query('SELECT * FROM roles')->fetchAll();
// Handle filters/search
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';
$where = [];
$params = [];
if ($search) {
    $where[] = '(username LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role_filter) {
    $where[] = 'role_id = ?';
    $params[] = $role_filter;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$users = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id $where_sql ORDER BY u.created_at DESC");
$users->execute($params);
$users = $users->fetchAll();
// Handle create, edit, deactivate, reset password (POST)
$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role_id = $_POST['role_id'];
        $password = $_POST['password'] ?: substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        if ($username && $email && $role_id) {
            if (register_user($username, $email, $password, $role_id)) {
                $action_msg = "<div class='alert alert-success p-2 my-2'>User created. Default password: <b>" . htmlspecialchars($password) . "</b></div>";
            } else {
                $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to create user. Username or email may exist.</div>";
            }
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role_id = $_POST['role_id'];
        $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, role_id=? WHERE id=?');
        if ($stmt->execute([$username, $email, $role_id, $id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>User updated.</div>";
        } else {
            $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to update user.</div>";
        }
    } elseif (isset($_POST['deactivate_user'])) {
        $id = $_POST['user_id'];
        $stmt = $pdo->prepare('UPDATE users SET active=0 WHERE id=?');
        if ($stmt->execute([$id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>User deactivated.</div>";
        }
    } elseif (isset($_POST['reset_password'])) {
        $id = $_POST['user_id'];
        $newpass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
        if ($stmt->execute([$hash, $id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>Password reset to: <b>" . htmlspecialchars($newpass) . "</b></div>";
        }
    }
    // Refresh user list after action
    header('Location: users.php?msg=1');
    exit;
}
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-people fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">Users</h2>
        <button class="btn btn-sm btn-primary ms-auto d-flex align-items-center gap-1" data-bs-toggle="modal"
            data-bs-target="#createUserModal"><i class="bi bi-plus-circle"></i> New User</button>
    </div>
    <?php if ($action_msg)
        echo $action_msg; ?>
    <form class="row g-2 mb-2" method="get">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search username/email"
                value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto">
            <select name="role" class="form-select form-select-sm">
                <option value="">All Roles</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?php echo $r['id']; ?>" <?php if ($role_filter == $r['id'])
                           echo ' selected'; ?>>
                        <?php echo htmlspecialchars($r['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <form method="post">
        <table class="table table-sm table-bordered align-middle" style="font-size:0.92rem;">
            <thead class="table-light">
                <tr>
                    <th><input type="checkbox"
                            onclick="document.querySelectorAll('.user-check').forEach(cb=>cb.checked=this.checked)">
                    </th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><input type="checkbox" class="user-check" name="bulk[]" value="<?php echo $u['id']; ?>"></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['role_name']); ?></td>
                        <td>
                            <?php
                            $isActive = isset($u['active']) ? $u['active'] : 1;
                            echo $isActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        <td>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" data-bs-toggle="modal"
                                data-bs-target="#editUserModal<?php echo $u['id']; ?>"><i class="bi bi-pencil"></i></button>
                            <button type="submit" name="deactivate_user" value="1"
                                class="btn btn-outline-warning btn-sm py-0 px-1 ms-1"
                                onclick="return confirm('Deactivate this user?')"><i class="bi bi-person-x"></i><input
                                    type="hidden" name="user_id" value="<?php echo $u['id']; ?>"></button>
                            <button type="submit" name="reset_password" value="1"
                                class="btn btn-outline-info btn-sm py-0 px-1 ms-1"><i class="bi bi-key"></i><input
                                    type="hidden" name="user_id" value="<?php echo $u['id']; ?>"></button>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <div class="mb-2">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($u['username']); ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control form-control-sm"
                                                value="<?php echo htmlspecialchars($u['email']); ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Role</label>
                                            <select name="role_id" class="form-select form-select-sm">
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?php echo $r['id']; ?>" <?php if ($u['role_id'] == $r['id'])
                                                           echo ' selected'; ?>><?php echo htmlspecialchars($r['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit_user" class="btn btn-primary btn-sm"><i
                                                class="bi bi-save"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Bulk actions (future) -->
    </form>
</div>
<!-- Create Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select form-select-sm">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password <small>(leave blank to auto-generate)</small></label>
                        <input type="text" name="password" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create_user" class="btn btn-primary btn-sm"><i
                            class="bi bi-plus-circle"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>