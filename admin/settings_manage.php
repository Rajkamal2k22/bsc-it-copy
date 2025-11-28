<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$error_message = '';
$success_message = '';

// --- Helper function to update setting ---
function update_setting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
    $stmt->bind_param("ss", $value, $key);
    // Execute and return result, logging error if unsuccessful
    if (!$stmt->execute()) {
        error_log("Settings Update Error for $key: " . $stmt->error);
        return false;
    }
    return true;
}

// --- 1. UPDATE Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_settings'])) {
    
    // --- Update Contact Settings ---
    $settings_to_update = [
        'contact_address' => trim($_POST['contact_address'] ?? ''),
        'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        'contact_email_main' => trim($_POST['contact_email_main'] ?? ''),
        'contact_email_feedback' => trim($_POST['contact_email_feedback'] ?? ''),
        'footer_copyright_year' => trim($_POST['footer_copyright_year'] ?? date('Y')),
        'admin_username' => trim($_POST['admin_username'] ?? 'admin'),
    ];

    $all_success = true;

    foreach ($settings_to_update as $key => $value) {
        // Only update non-password fields if the key exists (assuming keys are set in setup.sql)
        if ($key != 'admin_password') {
            if (!update_setting($key, $value)) {
                $all_success = false;
            }
        }
    }
    
    // --- Update Password (Special Handling) ---
    $new_password = $_POST['new_password'] ?? '';
    if (!empty($new_password)) {
        // Hash the new password before storing
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        if (update_setting('admin_password', $hashed_password)) {
            $success_message .= "Admin password updated successfully. ";
        } else {
            $all_success = false;
        }
    }

    if ($all_success) {
        $success_message = "All general settings saved successfully! " . $success_message;
    } else {
        $error_message = "An error occurred while saving some settings. Please check the server logs.";
    }
}

// --- 2. FETCH ALL SETTINGS FOR FORM ---
$settings_data = [];
$settings_result = $conn->query("SELECT key_name, value FROM settings");

if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings_data[$row['key_name']] = $row['value'];
    }
} else {
    $error_message = "Error fetching settings from the database. Please ensure 'settings' table exists.";
}

// Helper to get value safely in HTML
function get_setting_value_safe($key, $data) {
    return htmlspecialchars($data[$key] ?? 'N/A');
}

// Determine the current username (set in setup.sql or updated)
$current_username = get_setting_value_safe('admin_username', $settings_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage General Settings</title>
    <style>
        /* CSS styles (same as other admin pages for consistency) */
        body { font-family: Arial, sans-serif; background: #e9ebee; margin: 0; padding: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; max-width: 900px; margin: 0 auto; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e5c9; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .card input[type="text"], .card input[type="email"], .card input[type="password"], .card input[type="number"], .card textarea { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .setting-group { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage General Settings</h1>
        <div>
            Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | 
            <a href="index.php">Dashboard</a>
            <a href="?logout=true">Logout</a>
        </div>
    </div>
    <div class="container">
        
        <?php if ($success_message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Website Contact Information & Footer</h3>
            <form method="POST">
                
                <div class="setting-group">
                    <label for="contact_address">Primary Address (Footer/Contact Page):</label>
                    <input type="text" id="contact_address" name="contact_address" value="<?php echo get_setting_value_safe('contact_address', $settings_data); ?>">
                    
                    <label for="contact_phone">Main Contact Phone Number:</label>
                    <input type="text" id="contact_phone" name="contact_phone" value="<?php echo get_setting_value_safe('contact_phone', $settings_data); ?>">
                    
                    <label for="contact_email_main">Main Department Email (Public):</label>
                    <input type="email" id="contact_email_main" name="contact_email_main" value="<?php echo get_setting_value_safe('contact_email_main', $settings_data); ?>">

                    <label for="contact_email_feedback">Feedback/Unofficial Email (Contact Page):</label>
                    <input type="email" id="contact_email_feedback" name="contact_email_feedback" value="<?php echo get_setting_value_safe('contact_email_feedback', $settings_data); ?>">
                    
                    <label for="footer_copyright_year">Footer Copyright Year (e.g., 2025):</label>
                    <input type="number" id="footer_copyright_year" name="footer_copyright_year" value="<?php echo get_setting_value_safe('footer_copyright_year', $settings_data); ?>">
                </div>

                <h3>Admin Panel Login Settings</h3>

                <div class="setting-group">
                    <label for="admin_username">Admin Username:</label>
                    <input type="text" id="admin_username" name="admin_username" value="<?php echo $current_username; ?>" placeholder="Enter new username">

                    <label for="new_password">New Password (Leave blank to keep current password):</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password to change">
                </div>

                <button type="submit" name="submit_settings">Save All Settings</button>
            </form>
        </div>
    </div>
</body>
</html>