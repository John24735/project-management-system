<?php
require_once '../includes/header.php';
if (!is_manager()) {
    header('Location: ../index.php');
    exit;
}
// Handle add member
$add_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?: substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    if ($username && $email) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 3)');
        if ($stmt->execute([$username, $email, $hash])) {
            $add_msg = "<div class='alert alert-success p-2 my-2'>Member added. Default password: <b>" . htmlspecialchars($password) . "</b></div>";
        } else {
            $add_msg = "<div class='alert alert-danger p-2 my-2'>Failed to add member. Username or email may exist.";
        }
    } else {
        $add_msg = "<div class='alert alert-danger p-2 my-2'>Please fill in all required fields.";
    }
}
// Fetch all non-admin users and their roles
$members = $pdo->query('SELECT u.id, u.username, u.first_name, u.last_name, u.role_id, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.role_id != 1')->fetchAll();
// Fetch workload and progress for each user
$workloads = [];
$completions = [];
$stmt = $pdo->query('SELECT assigned_to, COUNT(*) as workload, SUM(status="Completed") as completed FROM tasks GROUP BY assigned_to');
foreach ($stmt->fetchAll() as $row) {
    $workloads[$row['assigned_to']] = $row['workload'];
    $completions[$row['assigned_to']] = $row['completed'];
}
?>
<?php include '../includes/sidebar.php'; ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    :root {
        --primary-purple: #8d288f;
        --accent-gold: #bfa13a;
        --card-bg: #fcfbfe;
    }

    body,
    .main-content {
        font-family: 'Inter', system-ui, Arial, sans-serif;
    }

    .team-card-modern {
        border-radius: 18px;
        border: none;
        background: var(--card-bg);
        box-shadow: 0 2px 10px rgba(141, 40, 143, 0.07);
        overflow: hidden;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 0;
        transition: box-shadow 0.2s;
    }

    .team-card-modern:hover {
        box-shadow: 0 6px 24px rgba(141, 40, 143, 0.13);
    }

    .team-card-header {
        background: var(--primary-purple);
        color: #fff;
        padding: 0.6rem 1rem 0.4rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 3px solid var(--accent-gold);
    }

    .team-card-header .role {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--accent-gold);
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--accent-gold);
        padding-bottom: 2px;
    }

    .team-card-header .id {
        font-size: 0.85rem;
        font-weight: 500;
        opacity: 0.85;
    }

    .team-card-body {
        padding: 0.7rem 1rem 0.3rem 1rem;
        background: var(--card-bg);
        display: flex;
        flex-direction: row;
        gap: 1.1rem;
        align-items: center;
    }

    .team-card-details {
        flex: 1 1 60%;
        font-size: 0.93rem;
    }

    .team-card-details .label {
        font-weight: 600;
        color: var(--primary-purple);
        min-width: 70px;
        display: inline-block;
        font-size: 0.91rem;
    }

    .team-card-details .value {
        font-weight: 400;
        color: #222;
        font-size: 0.91rem;
    }

    .team-card-photo {
        flex: 0 0 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .team-avatar-modern {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--primary-purple);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-purple);
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(141, 40, 143, 0.08);
    }

    .team-card-footer {
        background: #fff;
        padding: 0.4rem 1rem 0.5rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        border-top: 1px solid #f0e6f6;
    }

    .team-card-footer .member-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary-purple);
        letter-spacing: 1px;
        margin-bottom: 0.1rem;
    }

    .team-card-footer .progress {
        width: 100%;
        height: 7px;
        border-radius: 4px;
        background: #f0e6f6;
        margin-bottom: 0.2rem;
    }

    .team-card-footer .progress-bar {
        font-size: 0.75rem;
        border-radius: 4px;
        background: var(--primary-purple) !important;
    }

    .team-card-footer .username {
        font-size: 0.87rem;
        color: #8d288f;
        margin-top: 0.1rem;
        font-weight: 500;
        opacity: 0.85;
    }
</style>
<div class="main-content">
    <div class="d-flex align-items-center mb-3 gap-2">
        <i class="bi bi-people fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.05rem;">Team</h2>
        <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addMemberModal"><i
                class="bi bi-person-plus"></i> Add Member</button>
    </div>
    <?php if ($add_msg)
        echo $add_msg; ?>
    <div class="row g-3">
        <?php foreach ($members as $member): ?>
            <?php $name = trim($member['first_name'] . ' ' . $member['last_name']); ?>
            <div class="col-md-6 col-lg-4">
                <div class="team-card-modern mb-2">
                    <div class="team-card-header">
                        <span class="role"><?php echo strtoupper(htmlspecialchars($member['role_name'])); ?></span>
                        <span class="id">ID: <?php echo str_pad($member['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="team-card-body">
                        <div class="team-card-details">
                            <div><span class="label">Email:</span> <span
                                    class="value"><?php echo htmlspecialchars($member['username']); ?>@mail.com</span></div>
                            <div><span class="label">Gender:</span> <span class="value">N/A</span></div>
                            <div><span class="label">Contact:</span> <span class="value">N/A</span></div>
                            <div><span class="label">Nationality:</span> <span class="value">N/A</span></div>
                        </div>
                        <div class="team-card-photo">
                            <?php
                            // Use profile_picture if available, else show a default placeholder
                            $profile_picture = !empty($member['profile_picture']) ? htmlspecialchars($member['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($name ?: $member['username']) . '&background=8d288f&color=fff&size=128';
                            ?>
                            <img src="<?php echo $profile_picture; ?>" alt="Profile" class="team-avatar-modern"
                                style="object-fit:cover; width:54px; height:54px; border-radius:50%; border:2px solid var(--primary-purple); background:#fff;">
                        </div>
                    </div>
                    <div class="team-card-footer">
                        <span
                            class="member-name"><?php echo htmlspecialchars($name) ?: htmlspecialchars($member['username']); ?></span>
                        <div class="progress">
                            <?php
                            $total = $workloads[$member['id']] ?? 0;
                            $done = $completions[$member['id']] ?? 0;
                            $progress = $total ? round(($done / $total) * 100) : 0;
                            ?>
                            <div class="progress-bar" role="progressbar" style="width:<?php echo $progress; ?>%;"
                                aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $progress; ?>%
                            </div>
                        </div>
                        <div class="username">@<?php echo htmlspecialchars($member['username']); ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="add_member" value="1">
                    <div class="mb-2">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password <small>(leave blank to auto-generate)</small></label>
                        <input type="text" name="password" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>