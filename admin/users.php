<?php
// Handle closing the reset password modal before any output
if (isset($_POST['close_reset_modal'])) {
    unset($_SESSION['reset_password_user'], $_SESSION['reset_password_value']);
    header('Location: users.php');
    exit;
}
// Handle create, edit, deactivate, reset password (POST) before any output
require_once '../config/db.php';
require_once '../includes/auth.php';
$action_msg = '';
// Fetch roles for later use
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
        } else {
            $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to deactivate user.</div>";
        }
    } elseif (isset($_POST['reactivate_user'])) {
        $id = $_POST['user_id'];
        $stmt = $pdo->prepare('UPDATE users SET active=1 WHERE id=?');
        if ($stmt->execute([$id])) {
            $action_msg = "<div class='alert alert-success p-2 my-2'>User reactivated.</div>";
        } else {
            $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to reactivate user.</div>";
        }
    } elseif (isset($_POST['reset_password'])) {
        $id = $_POST['user_id'];
        $newpass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
        if ($stmt->execute([$hash, $id])) {
            // Store the new password in session to show in modal
            $_SESSION['reset_password_user'] = $id;
            $_SESSION['reset_password_value'] = $newpass;
            // Redirect to show modal
            header('Location: users.php?show_reset_modal=1');
            exit;
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        if ($id != $_SESSION['user_id']) { // Prevent self-delete
            // Check for references in tasks
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE created_by = ? OR assigned_to = ?');
            $stmt->execute([$id, $id]);
            if ($stmt->fetchColumn() > 0) {
                $action_msg = "<div class='alert alert-warning p-2 my-2'>Cannot delete user: they are referenced in tasks. Reassign or delete their tasks first.</div>";
            } else {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
                if ($stmt->execute([$id])) {
                    $action_msg = "<div class='alert alert-success p-2 my-2'>User deleted.</div>";
                } else {
                    $action_msg = "<div class='alert alert-danger p-2 my-2'>Failed to delete user.</div>";
                }
            }
        } else {
            $action_msg = "<div class='alert alert-warning p-2 my-2'>You cannot delete your own account.</div>";
        }
    }
    // Refresh user list after action
    header('Location: users.php?msg=1');
    exit;
}
// Fetch users after any POST action
$users = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id $where_sql ORDER BY u.created_at DESC");
$users->execute($params);
$users = $users->fetchAll();
?>
<?php require_once '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>
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
                                data-bs-target="#editUserModal<?php echo $u['id']; ?>" title="Edit User"><i
                                    class="bi bi-pencil"></i></button>
                            <?php if ($isActive): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="deactivate_user" value="1"
                                        class="btn btn-outline-warning btn-sm py-0 px-1 ms-1"
                                        onclick="return confirm('Deactivate this user?')" title="Deactivate User"><i
                                            class="bi bi-person-x"></i></button>
                                </form>
                            <?php else: ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="reactivate_user" value="1"
                                        class="btn btn-outline-success btn-sm py-0 px-1 ms-1"
                                        onclick="return confirm('Reactivate this user?')" title="Reactivate User"><i
                                            class="bi bi-person-check"></i></button>
                                </form>
                            <?php endif; ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="reset_password" value="1"
                                    class="btn btn-outline-info btn-sm py-0 px-1 ms-1" title="Reset Password"><i
                                        class="bi bi-key"></i></button>
                            </form>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="delete_user" value="1"
                                    class="btn btn-outline-danger btn-sm py-0 px-1 ms-1"
                                    onclick="return confirm('Delete this user? This cannot be undone.')" title="Delete User"><i
                                        class="bi bi-trash"></i></button>
                            </form>
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
<?php if (isset($_GET['show_reset_modal']) && isset($_SESSION['reset_password_user']) && isset($_SESSION['reset_password_value'])): ?>
    <!-- Password Reset Modal -->
    <div class="modal fade show" id="resetPasswordModal" tabindex="-1" style="display:block; background:rgba(0,0,0,0.5);"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key"></i> Password Reset</h5>
                    <button type="button" class="btn-close" onclick="closeResetModal()"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">The new password for user ID
                        <b><?php echo htmlspecialchars($_SESSION['reset_password_user']); ?></b> is:
                    </p>
                    <div class="input-group mb-2" style="max-width:300px;margin:auto;">
                        <input type="text" class="form-control text-center fw-bold" id="resetPasswordValue"
                            value="<?php echo htmlspecialchars($_SESSION['reset_password_value']); ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('resetPasswordValue').value)"><i
                                class="bi bi-clipboard"></i></button>
                    </div>
                    <small class="text-muted">Copy and share this password with the user.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="closeResetModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <form id="closeResetModalForm" method="post" style="display:none;"><input type="hidden" name="close_reset_modal"
            value="1"></form>
    <script>
        // Focus and select the password for easy copying
        document.getElementById('resetPasswordValue').focus();
        document.getElementById('resetPasswordValue').select();
        function closeResetModal() {
            document.getElementById('resetPasswordModal').style.display = 'none';
            document.getElementById('closeResetModalForm').submit();
        }
    </script>
<?php endif; ?>
<?php require_once '../includes/footer.php'; ?>