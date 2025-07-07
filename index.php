<?php
require_once __DIR__ . '/includes/header.php';

// Handle login and registration logic
$login_error = '';
$register_error = '';
$register_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_submit'])) {
        $username = trim($_POST['login_username'] ?? '');
        $password = $_POST['login_password'] ?? '';
        if ($username && $password) {
            if (login_user($username, $password)) {
                if (is_admin()) header('Location: admin/dashboard.php');
                elseif (is_manager()) header('Location: manager/dashboard.php');
                else header('Location: member/dashboard.php');
                exit;
            } else {
                $login_error = 'Invalid username or password.';
            }
        } else {
            $login_error = 'Please fill in all fields.';
        }
    } elseif (isset($_POST['register_submit'])) {
        $username = trim($_POST['register_username'] ?? '');
        $email = trim($_POST['register_email'] ?? '');
        $password = $_POST['register_password'] ?? '';
        $role_id = 3; // Default to member
        if ($username && $email && $password) {
            if (register_user($username, $email, $password, $role_id)) {
                $register_success = 'Registration successful! You can now log in.';
            } else {
                $register_error = 'Registration failed. Username or email may already exist.';
            }
        } else {
            $register_error = 'Please fill in all fields.';
        }
    }
}
?>
<div class="full-vh" style="overflow: hidden;">
    <div class="landing-center">
        <h1 class="display-4">Welcome to the Project Management System</h1>
        <p class="lead">Manage your projects, tasks, and teams efficiently in one place.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <?php if (!is_logged_in()): ?>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</button>
                <button class="btn btn-outline-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
            <?php else: ?>
                <a href="logout.php" class="btn btn-danger btn-lg">Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($login_error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
                        <div class="mb-3">
                            <label for="login_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="login_username" name="login_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="login_password" name="login_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="login_submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Register</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($register_error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($register_error); ?></div><?php endif; ?>
                        <?php if ($register_success): ?><div class="alert alert-success"><?php echo htmlspecialchars($register_success); ?></div><?php endif; ?>
                        <div class="mb-3">
                            <label for="register_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="register_username" name="register_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="register_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="register_email" name="register_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="register_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="register_password" name="register_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="register_submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>
<script>
// Open modal if there was an error or success (after POST)
<?php if ($login_error): ?>
    var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
<?php endif; ?>
<?php if ($register_error || $register_success): ?>
    var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
<?php endif; ?>
</script>