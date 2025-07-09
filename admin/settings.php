<?php
// Handle profile update redirect before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'])) {
    require_once '../config/db.php';
    require_once '../includes/auth.php';
    require_once '../includes/settings.php';

    $user = current_user();
    $data = [];
    $has_changes = false;

    // Check for changes
    if (!empty($_POST['first_name']) && $_POST['first_name'] !== ($user['first_name'] ?? '')) {
        $data['first_name'] = trim($_POST['first_name']);
        $has_changes = true;
    }
    if (!empty($_POST['last_name']) && $_POST['last_name'] !== ($user['last_name'] ?? '')) {
        $data['last_name'] = trim($_POST['last_name']);
        $has_changes = true;
    }
    if (!empty($_POST['email']) && $_POST['email'] !== ($user['email'] ?? '')) {
        $data['email'] = trim($_POST['email']);
        $has_changes = true;
    }
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
        $has_changes = true;
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_profile_picture($_FILES['profile_picture'], $user['id']);
        if ($upload['success']) {
            $data['profile_picture'] = $upload['path'];
            $has_changes = true;
        }
    }

    if ($has_changes) {
        if (update_user_profile($user['id'], $data)) {
            // Refresh user session data
            $user = current_user();
            $_SESSION['user_data'] = $user;
            // Set success message directly
            $msg = '<div class="alert alert-success p-2 my-2">Profile updated successfully.</div>';
        }
    }
}

require_once '../includes/header.php';
include '../includes/sidebar.php';
require_once '../includes/settings.php';

$tab = $_GET['tab'] ?? 'general';
$msg = '';

// Handle success message from redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $msg = '<div class="alert alert-success p-2 my-2">Profile updated successfully.</div>';
}

// Handle POST (save settings)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;

    if ($tab == 'general') {
        $platform_name = trim($_POST['platform_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $timezone = $_POST['timezone'] ?? 'UTC';
        $date_format = $_POST['date_format'] ?? 'Y-m-d';
        $time_format = $_POST['time_format'] ?? 'H:i';

        if (empty($platform_name)) {
            $msg = '<div class="alert alert-danger p-2 my-2">Platform name is required.</div>';
            $success = false;
        }

        if ($success) {
            // Save to database
            set_setting('platform_name', $platform_name);
            set_setting('company_name', $company_name);
            set_setting('timezone', $timezone);
            set_setting('date_format', $date_format);
            set_setting('time_format', $time_format);
            $msg = '<div class="alert alert-success p-2 my-2">General settings saved successfully.</div>';
        }
    } elseif ($tab == 'email') {
        $smtp_host = trim($_POST['smtp_host'] ?? '');
        $smtp_port = trim($_POST['smtp_port'] ?? '587');
        $smtp_user = trim($_POST['smtp_user'] ?? '');
        $smtp_pass = $_POST['smtp_pass'] ?? '';
        $from_email = trim($_POST['from_email'] ?? '');
        $from_name = trim($_POST['from_name'] ?? '');

        if (empty($smtp_host) || empty($smtp_user) || empty($from_email)) {
            $msg = '<div class="alert alert-danger p-2 my-2">SMTP host, username, and from email are required.</div>';
            $success = false;
        }

        if ($success) {
            // Save to database
            set_setting('smtp_host', $smtp_host);
            set_setting('smtp_port', $smtp_port, 'integer');
            set_setting('smtp_user', $smtp_user);
            if (!empty($smtp_pass)) {
                set_setting('smtp_pass', $smtp_pass);
            }
            set_setting('from_email', $from_email);
            set_setting('from_name', $from_name);
            $msg = '<div class="alert alert-success p-2 my-2">Email settings saved successfully.</div>';
        }
    } elseif ($tab == 'security') {
        $session_timeout = (int) ($_POST['session_timeout'] ?? 30);
        $max_login_attempts = (int) ($_POST['max_login_attempts'] ?? 5);
        $password_min_length = (int) ($_POST['password_min_length'] ?? 8);
        $require_2fa = isset($_POST['require_2fa']) ? 1 : 0;
        $force_password_change = isset($_POST['force_password_change']) ? 1 : 0;

        if ($session_timeout < 5 || $session_timeout > 1440) {
            $msg = '<div class="alert alert-danger p-2 my-2">Session timeout must be between 5 and 1440 minutes.</div>';
            $success = false;
        }

        if ($success) {
            // Save to database
            set_setting('session_timeout', $session_timeout, 'integer');
            set_setting('max_login_attempts', $max_login_attempts, 'integer');
            set_setting('password_min_length', $password_min_length, 'integer');
            set_setting('require_2fa', $require_2fa, 'boolean');
            set_setting('force_password_change', $force_password_change, 'boolean');
            $msg = '<div class="alert alert-success p-2 my-2">Security settings saved successfully.</div>';
        }
    } elseif ($tab == 'notifications') {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $task_assignments = isset($_POST['task_assignments']) ? 1 : 0;
        $project_updates = isset($_POST['project_updates']) ? 1 : 0;
        $deadline_reminders = isset($_POST['deadline_reminders']) ? 1 : 0;
        $reminder_days = (int) ($_POST['reminder_days'] ?? 1);

        if ($success) {
            // Save to database
            set_setting('email_notifications', $email_notifications, 'boolean');
            set_setting('task_assignments', $task_assignments, 'boolean');
            set_setting('project_updates', $project_updates, 'boolean');
            set_setting('deadline_reminders', $deadline_reminders, 'boolean');
            set_setting('reminder_days', $reminder_days, 'integer');
            $msg = '<div class="alert alert-success p-2 my-2">Notification settings saved successfully.</div>';
        }
    }
}

// Get current settings from database
$current_settings = [
    'platform_name' => get_setting('platform_name', 'Project Management System'),
    'company_name' => get_setting('company_name', 'Your Company'),
    'timezone' => get_setting('timezone', 'UTC'),
    'date_format' => get_setting('date_format', 'Y-m-d'),
    'time_format' => get_setting('time_format', 'H:i'),
    'smtp_host' => get_setting('smtp_host', 'smtp.gmail.com'),
    'smtp_port' => get_setting('smtp_port', 587),
    'smtp_user' => get_setting('smtp_user', 'admin@company.com'),
    'from_email' => get_setting('from_email', 'noreply@company.com'),
    'from_name' => get_setting('from_name', 'Project Management System'),
    'session_timeout' => get_setting('session_timeout', 30),
    'max_login_attempts' => get_setting('max_login_attempts', 5),
    'password_min_length' => get_setting('password_min_length', 8),
    'require_2fa' => get_setting('require_2fa', false),
    'force_password_change' => get_setting('force_password_change', false),
    'email_notifications' => get_setting('email_notifications', true),
    'task_assignments' => get_setting('task_assignments', true),
    'project_updates' => get_setting('project_updates', true),
    'deadline_reminders' => get_setting('deadline_reminders', true),
    'reminder_days' => get_setting('reminder_days', 1)
];
?>
<div class="main-content">
    <div class="d-flex align-items-center mb-2 gap-2">
        <i class="bi bi-gear fs-5 text-primary"></i>
        <h2 class="fw-bold mb-0" style="font-size:1.1rem;">System Settings</h2>
    </div>

    <?php if (!empty($msg))
        echo $msg; ?>

    <ul class="nav nav-tabs mb-3" style="font-size:0.98rem;">
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'general')
                echo ' active'; ?>" href="?tab=general">
                <i class="bi bi-sliders"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'email')
                echo ' active'; ?>" href="?tab=email">
                <i class="bi bi-envelope"></i> Email
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'security')
                echo ' active'; ?>" href="?tab=security">
                <i class="bi bi-shield-lock"></i> Security
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'notifications')
                echo ' active'; ?>" href="?tab=notifications">
                <i class="bi bi-bell"></i> Notifications
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php if ($tab == 'profile')
                echo ' active'; ?>" href="?tab=profile">
                <i class="bi bi-person"></i> Profile
            </a>
        </li>
    </ul>

    <div class="card summary-card p-3">
        <?php if ($tab == 'general'): ?>
            <form method="post">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-sliders"></i> General Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Platform Name</label>
                        <input type="text" class="form-control form-control-sm" name="platform_name"
                            value="<?php echo htmlspecialchars($current_settings['platform_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control form-control-sm" name="company_name"
                            value="<?php echo htmlspecialchars($current_settings['company_name']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Timezone</label>
                        <select class="form-select form-select-sm" name="timezone">
                            <option value="UTC" <?php if ($current_settings['timezone'] == 'UTC')
                                echo 'selected'; ?>>UTC
                            </option>
                            <option value="America/New_York" <?php if ($current_settings['timezone'] == 'America/New_York')
                                echo 'selected'; ?>>Eastern Time</option>
                            <option value="America/Chicago" <?php if ($current_settings['timezone'] == 'America/Chicago')
                                echo 'selected'; ?>>Central Time</option>
                            <option value="America/Denver" <?php if ($current_settings['timezone'] == 'America/Denver')
                                echo 'selected'; ?>>Mountain Time</option>
                            <option value="America/Los_Angeles" <?php if ($current_settings['timezone'] == 'America/Los_Angeles')
                                echo 'selected'; ?>>Pacific Time
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Format</label>
                        <select class="form-select form-select-sm" name="date_format">
                            <option value="Y-m-d" <?php if ($current_settings['date_format'] == 'Y-m-d')
                                echo 'selected'; ?>>
                                YYYY-MM-DD</option>
                            <option value="m/d/Y" <?php if ($current_settings['date_format'] == 'm/d/Y')
                                echo 'selected'; ?>>
                                MM/DD/YYYY</option>
                            <option value="d/m/Y" <?php if ($current_settings['date_format'] == 'd/m/Y')
                                echo 'selected'; ?>>
                                DD/MM/YYYY</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time Format</label>
                        <select class="form-select form-select-sm" name="time_format">
                            <option value="H:i" <?php if ($current_settings['time_format'] == 'H:i')
                                echo 'selected'; ?>>
                                24-hour</option>
                            <option value="h:i A" <?php if ($current_settings['time_format'] == 'h:i A')
                                echo 'selected'; ?>>
                                12-hour</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> Save General Settings
                    </button>
                </div>
            </form>

        <?php elseif ($tab == 'email'): ?>
            <form method="post">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-envelope"></i> Email Configuration</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control form-control-sm" name="smtp_host"
                            value="<?php echo htmlspecialchars($current_settings['smtp_host']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" class="form-control form-control-sm" name="smtp_port"
                            value="<?php echo htmlspecialchars($current_settings['smtp_port']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input type="email" class="form-control form-control-sm" name="smtp_user"
                            value="<?php echo htmlspecialchars($current_settings['smtp_user']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" class="form-control form-control-sm" name="smtp_pass"
                            placeholder="Enter password to update">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" class="form-control form-control-sm" name="from_email"
                            value="<?php echo htmlspecialchars($current_settings['from_email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" class="form-control form-control-sm" name="from_name"
                            value="<?php echo htmlspecialchars($current_settings['from_name']); ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> Save Email Settings
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="bi bi-envelope-check"></i> Test Connection
                    </button>
                </div>
            </form>

        <?php elseif ($tab == 'security'): ?>
            <form method="post">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-shield-lock"></i> Security Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Session Timeout (minutes)</label>
                        <input type="number" class="form-control form-control-sm" name="session_timeout"
                            value="<?php echo $current_settings['session_timeout']; ?>" min="5" max="1440">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Login Attempts</label>
                        <input type="number" class="form-control form-control-sm" name="max_login_attempts"
                            value="<?php echo $current_settings['max_login_attempts']; ?>" min="3" max="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Minimum Password Length</label>
                        <input type="number" class="form-control form-control-sm" name="password_min_length"
                            value="<?php echo $current_settings['password_min_length']; ?>" min="6" max="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Force Password Change (days)</label>
                        <input type="number" class="form-control form-control-sm" name="force_password_change"
                            value="<?php echo $current_settings['force_password_change']; ?>" min="0" max="365">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="require_2fa" id="require_2fa" <?php if ($current_settings['require_2fa'])
                                echo 'checked'; ?>>
                            <label class="form-check-label" for="require_2fa">
                                Require Two-Factor Authentication for all users
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> Save Security Settings
                    </button>
                </div>
            </form>

        <?php elseif ($tab == 'notifications'): ?>
            <form method="post">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-bell"></i> Notification Preferences</h6>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="email_notifications"
                                id="email_notifications" <?php if ($current_settings['email_notifications'])
                                    echo 'checked'; ?>>
                            <label class="form-check-label" for="email_notifications">
                                Enable email notifications
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="task_assignments" id="task_assignments"
                                <?php if ($current_settings['task_assignments'])
                                    echo 'checked'; ?>>
                            <label class="form-check-label" for="task_assignments">
                                Notify users when tasks are assigned to them
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="project_updates" id="project_updates"
                                <?php if ($current_settings['project_updates'])
                                    echo 'checked'; ?>>
                            <label class="form-check-label" for="project_updates">
                                Notify project members of project updates
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="deadline_reminders"
                                id="deadline_reminders" <?php if ($current_settings['deadline_reminders'])
                                    echo 'checked'; ?>>
                            <label class="form-check-label" for="deadline_reminders">
                                Send deadline reminders
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reminder Days Before Deadline</label>
                        <input type="number" class="form-control form-control-sm" name="reminder_days"
                            value="<?php echo $current_settings['reminder_days']; ?>" min="1" max="7">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> Save Notification Settings
                    </button>
                </div>
            </form>

        <?php elseif ($tab == 'profile'): ?>
            <?php
            require_once '../includes/auth.php';
            $user = current_user();
            ?>
            <form method="post" enctype="multipart/form-data" action="?tab=profile">
                <h6 class="d-flex align-items-center gap-2 mb-3"><i class="bi bi-person"></i> Update Profile</h6>
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <img src="<?php echo get_profile_picture_url($user); ?>" class="rounded-circle mb-2"
                            style="width:80px;height:80px;object-fit:cover;">
                        <div>
                            <input type="file" name="profile_picture" accept="image/*"
                                class="form-control form-control-sm mt-2">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control form-control-sm" name="first_name"
                                    value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control form-control-sm" name="last_name"
                                    value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control form-control-sm" name="email"
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control form-control-sm" name="password"
                                    placeholder="Leave blank to keep current password">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Save Profile</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>