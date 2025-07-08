<?php
// Debug script for profile update
session_start();
require_once 'config/db.php';
require_once 'includes/settings.php';
require_once 'includes/auth.php';

echo "<h2>Profile Update Debug</h2>";

// Simulate a logged-in user (admin)
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;

$user = current_user();
echo "<p>Current user: " . ($user ? $user['username'] : 'None') . "</p>";

if ($user) {
    echo "<h3>Current Profile Data:</h3>";
    echo "<ul>";
    echo "<li>First name: " . ($user['first_name'] ?? 'NULL') . "</li>";
    echo "<li>Last name: " . ($user['last_name'] ?? 'NULL') . "</li>";
    echo "<li>Email: " . ($user['email'] ?? 'NULL') . "</li>";
    echo "<li>Profile picture: " . ($user['profile_picture'] ?? 'NULL') . "</li>";
    echo "</ul>";

    // Test the update function directly
    echo "<h3>Testing Profile Update:</h3>";

    $test_data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com'
    ];

    echo "<p>Updating with data: " . print_r($test_data, true) . "</p>";

    $result = update_user_profile($user['id'], $test_data);
    echo "<p>Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";

    // Check if data was actually saved
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $updated_user = $stmt->fetch();

    echo "<h3>After Update:</h3>";
    echo "<ul>";
    echo "<li>First name: " . ($updated_user['first_name'] ?? 'NULL') . "</li>";
    echo "<li>Last name: " . ($updated_user['last_name'] ?? 'NULL') . "</li>";
    echo "<li>Email: " . ($updated_user['email'] ?? 'NULL') . "</li>";
    echo "<li>Profile picture: " . ($updated_user['profile_picture'] ?? 'NULL') . "</li>";
    echo "</ul>";

    // Test the change detection logic
    echo "<h3>Testing Change Detection:</h3>";

    // Simulate POST data
    $_POST['first_name'] = 'Jane';
    $_POST['last_name'] = 'Smith';
    $_POST['email'] = 'jane.smith@example.com';

    $data = [];
    $has_changes = false;

    if (!empty($_POST['first_name']) && $_POST['first_name'] !== ($user['first_name'] ?? '')) {
        $data['first_name'] = trim($_POST['first_name']);
        $has_changes = true;
        echo "<p>First name change detected: " . $data['first_name'] . "</p>";
    }
    if (!empty($_POST['last_name']) && $_POST['last_name'] !== ($user['last_name'] ?? '')) {
        $data['last_name'] = trim($_POST['last_name']);
        $has_changes = true;
        echo "<p>Last name change detected: " . $data['last_name'] . "</p>";
    }
    if (!empty($_POST['email']) && $_POST['email'] !== ($user['email'] ?? '')) {
        $data['email'] = trim($_POST['email']);
        $has_changes = true;
        echo "<p>Email change detected: " . $data['email'] . "</p>";
    }

    echo "<p>Has changes: " . ($has_changes ? 'true' : 'false') . "</p>";
    echo "<p>Data to update: " . print_r($data, true) . "</p>";
}

echo "<h3>Database Connection Test:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p>Total users in database: " . $count . "</p>";
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>