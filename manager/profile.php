<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
// Fetch user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $profile_picture_path = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
        $target = dirname(__DIR__) . '/uploads/' . basename($filename);
        if (!is_dir(dirname($target)))
            mkdir(dirname($target), 0777, true);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
            // Delete old image if not default
            if (!empty($user['profile_picture']) && strpos($user['profile_picture'], 'uploads/profile_') !== false && file_exists(dirname(__DIR__) . '/' . $user['profile_picture'])) {
                @unlink(dirname(__DIR__) . '/' . $user['profile_picture']);
            }
            $profile_picture_path = 'uploads/' . basename($filename);
        }
    }
    $stmt = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, email=?, profile_picture=? WHERE id=?');
    if ($stmt->execute([$first_name, $last_name, $email, $profile_picture_path, $user_id])) {
        $msg = '<div class="alert alert-success p-2 my-2">Profile updated.</div>';
    } else {
        $msg = '<div class="alert alert-danger p-2 my-2">Failed to update profile.</div>';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $newpass = $_POST['new_password'];
    if ($newpass) {
        $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
        $stmt->execute([password_hash($newpass, PASSWORD_DEFAULT), $user_id]);
        $msg = '<div class="alert alert-success p-2 my-2">Password changed.</div>';
    }
}
?>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-person fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">Profile</h2>
    </div>
    <?php if ($msg)
        echo $msg; ?>
    <form method="post" class="row g-2" enctype="multipart/form-data">
        <div class="col-md-4">
            <div class="mb-2">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($user['first_name']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($user['last_name']); ?>">
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary btn-sm mt-2">Save Changes</button>
        </div>
        <div class="col-md-4">
            <div class="mb-2">
                <label class="form-label">Profile Picture</label><br>
                <img src="<?php
                if (!empty($user['profile_picture'])) {
                    echo (strpos($user['profile_picture'], 'http') === 0 ? htmlspecialchars($user['profile_picture']) : '../' . htmlspecialchars($user['profile_picture']));
                } else {
                    echo 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . ' ' . $user['last_name']) . '&background=8d288f&color=fff&size=128';
                }
                ?>" alt="Profile" style="width:72px;height:72px;border-radius:50%;object-fit:cover;">
                <input type="file" name="profile_picture" class="form-control form-control-sm mt-2">
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                data-bs-target="#changePasswordModal">Change Password</button>
        </div>
    </form>
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="change_password" class="btn btn-primary btn-sm">Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>