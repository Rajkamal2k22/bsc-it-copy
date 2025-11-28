<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$error_message = '';
$success_message = '';
$announcement_to_edit = null;

// --- 1. DELETE Logic ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE notice_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Announcement deleted successfully!";
        header("Location: announcements_manage.php?status=deleted");
        exit;
    } else {
        $error_message = "Error deleting record: " . $conn->error;
    }
}

// --- 2. CREATE / UPDATE Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_announcement'])) {
    $id = $_POST['notice_id'] ?? null;
    $short_text = trim($_POST['short_text']);
    $full_message = trim($_POST['full_message']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation check
    if (empty($short_text) || empty($full_message)) {
        $error_message = "Short Text and Full Message are required.";
        goto end_crud;
    }

    if ($id) {
        // UPDATE Operation
        $stmt = $conn->prepare("UPDATE announcements SET short_text=?, full_message=?, is_active=? WHERE notice_id=?");
        $stmt->bind_param("ssii", $short_text, $full_message, $is_active, $id);
        $action = "updated";
    } else {
        // INSERT Operation
        $stmt = $conn->prepare("INSERT INTO announcements (short_text, full_message, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $short_text, $full_message, $is_active);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: announcements_manage.php?status=" . $action);
        exit;
    } else {
        $error_message = "Database Error: " . $stmt->error;
    }
}
end_crud:

// --- 3. FETCH FOR EDIT FORM ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE notice_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $announcement_to_edit = $result->fetch_assoc();
}

// --- 4. FETCH ALL ANNOUNCEMENTS FOR LISTING ---
$announcements_result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$all_announcements = $announcements_result->fetch_all(MYSQLI_ASSOC);

// --- Status Message Handling ---
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $success_message = "Announcement added successfully!";
    } elseif ($_GET['status'] == 'updated') {
        $success_message = "Announcement updated successfully!";
    } elseif ($_GET['status'] == 'deleted') {
        $success_message = "Announcement deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements</title>
    <style>
        /* CSS styles (same as faculty_manage.php for consistent look) */
        body { font-family: Arial, sans-serif; background: #e9ebee; margin: 0; padding: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e5c9; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .card input[type="text"], .card textarea { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .card input[type="checkbox"] { margin-right: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f2f2f2; }
        .btn-edit, .btn-delete { background: #f0ad4e; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #d9534f; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Notices</h1>
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
            <h3><?php echo $announcement_to_edit ? 'Edit Announcement: ' . htmlspecialchars($announcement_to_edit['short_text']) : 'Add New Announcement'; ?></h3>
            
            <form method="POST">
                <input type="hidden" name="notice_id" value="<?php echo $announcement_to_edit['notice_id'] ?? ''; ?>">
                
                <label for="short_text">Short Text (Scrolling Bar):</label>
                <input type="text" id="short_text" name="short_text" value="<?php echo htmlspecialchars($announcement_to_edit['short_text'] ?? ''); ?>" required placeholder="e.g., 📢 1st Year Batch Started">
                
                <label for="full_message">Full Message (Details Pop-up):</label>
                <textarea id="full_message" name="full_message" rows="4" required><?php echo htmlspecialchars($announcement_to_edit['full_message'] ?? ''); ?></textarea>
                
                <label>
                    <input type="checkbox" name="is_active" <?php echo (isset($announcement_to_edit['is_active']) && $announcement_to_edit['is_active']) ? 'checked' : 'checked'; ?>>
                    Is Active (Show on Website)?
                </label>

                <button type="submit" name="submit_announcement">
                    <?php echo $announcement_to_edit ? 'Save Changes' : 'Add Announcement'; ?>
                </button>
            </form>
        </div>

        <h2>Current Announcements List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Text</th>
                    <th>Full Message</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_announcements as $a): ?>
                <tr>
                    <td><?php echo $a['notice_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($a['short_text']); ?></strong></td>
                    <td><?php echo substr(htmlspecialchars($a['full_message']), 0, 80) . '...'; ?></td>
                    <td><?php echo $a['is_active'] ? '✅ Active' : '❌ Inactive'; ?></td>
                    <td><?php echo date("Y-m-d H:i", strtotime($a['created_at'])); ?></td>
                    <td>
                        <a href="?edit_id=<?php echo $a['notice_id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $a['notice_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>