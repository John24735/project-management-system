<?php
// includes/settings.php
require_once __DIR__ . '/../config/db.php';

// Get a system setting
function get_setting($key, $default = null)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $setting = $stmt->fetch();

        if ($setting) {
            switch ($setting['setting_type']) {
                case 'integer':
                    return (int) $setting['setting_value'];
                case 'boolean':
                    return (bool) $setting['setting_value'];
                case 'json':
                    return json_decode($setting['setting_value'], true);
                default:
                    return $setting['setting_value'];
            }
        }
        return $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Set a system setting
function set_setting($key, $value, $type = 'string')
{
    global $pdo;
    try {
        // Convert value based on type
        switch ($type) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'boolean':
                $value = $value ? '1' : '0';
                break;
            case 'json':
                $value = json_encode($value);
                break;
        }

        $stmt = $pdo->prepare('INSERT INTO system_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP');
        return $stmt->execute([$key, $value, $type, $value]);
    } catch (Exception $e) {
        return false;
    }
}

// Get all settings as array
function get_all_settings()
{
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT setting_key, setting_value, setting_type FROM system_settings');
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = get_setting($row['setting_key']);
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

// Update user profile
function update_user_profile($user_id, $data)
{
    global $pdo;
    try {
        $updates = [];
        $params = [];

        if (isset($data['first_name'])) {
            $updates[] = 'first_name = ?';
            $params[] = trim($data['first_name']);
        }

        if (isset($data['last_name'])) {
            $updates[] = 'last_name = ?';
            $params[] = trim($data['last_name']);
        }

        if (isset($data['email'])) {
            $updates[] = 'email = ?';
            $params[] = trim($data['email']);
        }

        if (isset($data['profile_picture'])) {
            $updates[] = 'profile_picture = ?';
            $params[] = $data['profile_picture'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $user_id;
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        return $result;
    } catch (Exception $e) {
        error_log("Profile update exception: " . $e->getMessage());
        return false;
    }
}

// Upload profile picture
function upload_profile_picture($file, $user_id)
{
    global $pdo;
    $upload_dir = __DIR__ . '/../uploads/profiles/';

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Delete old profile picture if it exists
    try {
        $stmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $old_picture = $stmt->fetchColumn();

        if ($old_picture && !empty($old_picture)) {
            $old_filepath = __DIR__ . '/../' . $old_picture;
            if (file_exists($old_filepath) && is_file($old_filepath)) {
                unlink($old_filepath);
            }
        }
    } catch (Exception $e) {
        // Log error but continue with upload
        error_log("Error deleting old profile picture: " . $e->getMessage());
    }

    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => 'uploads/profiles/' . $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

// Get user profile picture URL
function get_profile_picture_url($user)
{
    if (!empty($user['profile_picture'])) {
        // Check if we're in admin, manager, or member directory
        $current_path = $_SERVER['REQUEST_URI'] ?? '';
        if (
            strpos($current_path, '/admin/') !== false ||
            strpos($current_path, '/manager/') !== false ||
            strpos($current_path, '/member/') !== false
        ) {
            return '../' . $user['profile_picture'];
        } else {
            return $user['profile_picture'];
        }
    }

    // Default avatar - use a simple SVG data URI
    return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 72 72"><circle cx="36" cy="36" r="36" fill="#e9ecef"/><circle cx="36" cy="28" r="12" fill="#6c757d"/><path d="M36 44c-8.8 0-16 7.2-16 16v4h32v-4c0-8.8-7.2-16-16-16z" fill="#6c757d"/></svg>');
}

// Get user display name
function get_user_display_name($user)
{
    if (!empty($user['first_name']) && !empty($user['last_name'])) {
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    return $user['username'];
}
?>