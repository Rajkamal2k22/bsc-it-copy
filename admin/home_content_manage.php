<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$upload_dir_img = '../images/'; // For slider image uploads
$error_message = '';
$success_message = '';
$slider_to_edit = null;

// --- Helper function to update setting (from settings_manage.php) ---
function update_setting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

// --- Helper function for file upload (Reusable for slider images) ---
function handle_upload($file_key, $target_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp']) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_ext = strtolower(pathinfo(basename($_FILES[$file_key]['name']), PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (!in_array($file_ext, $allowed_extensions)) {
            return ['error' => "Invalid file type. Allowed: " . implode(', ', $allowed_extensions)];
        }
        
        if (!is_dir($target_dir)) {
             // 0775 permission for folder creation (check if it works on your environment)
             if (!mkdir($target_dir, 0775, true)) {
                 return ['error' => "Target directory could not be created or lacks permission."];
             }
        }
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            return ['success' => substr($target_file, 3)]; // Return relative path (e.g., 'images/...')
        } else {
            return ['error' => "File upload failed. Check permissions on " . $target_dir];
        }
    }
    return ['success' => null];
}


// --- PART 1: HOMEPAGE TEXT CONTENT LOGIC (Settings Table) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_text_content'])) {
    $settings_to_update = [
        'homepage_heading' => $_POST['homepage_heading'] ?? '',
        'homepage_para1' => $_POST['homepage_para1'] ?? '',
        'homepage_para2' => $_POST['homepage_para2'] ?? '',
        'homepage_para3' => $_POST['homepage_para3'] ?? '',
    ];
    $all_success = true;
    foreach ($settings_to_update as $key => $value) {
        // NL2BR is handled on the frontend display, store raw text
        if (!update_setting($key, $value)) {
            $all_success = false;
        }
    }
    
    // FINAL FIX: Redirect to show status message and reload cleanly
    if ($all_success) {
        header("Location: home_content_manage.php?status=text_updated");
        exit;
    } else {
        $error_message = "Error updating homepage text content. Check database.";
    }
}

// --- PART 2: SLIDER CRUD LOGIC (Sliders Table) ---

// DELETE Slider
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM sliders WHERE slider_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Slider deleted successfully!";
        header("Location: home_content_manage.php?status=slider_deleted");
        exit;
    } else {
        $error_message = "Error deleting slider record: " . $conn->error;
    }
}

// CREATE / UPDATE Slider
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_slider'])) {
    $id = $_POST['slider_id'] ?? null;
    $caption = trim($_POST['caption']);
    $sort_order = is_numeric($_POST['sort_order']) ? (int)$_POST['sort_order'] : 10;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $image_path = $_POST['existing_image'] ?? null;

    // Handle Image Upload
    $img_upload_result = handle_upload('image_url', $upload_dir_img);
    if (isset($img_upload_result['error'])) {
        $error_message = "Image Error: " . $img_upload_result['error'];
        goto end_content_crud;
    } elseif ($img_upload_result['success']) {
        $image_path = $img_upload_result['success'];
    }
    
    if (!$image_path) {
        $error_message = "Image is required for the slider.";
        goto end_content_crud;
    }

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE sliders SET image_url=?, caption=?, sort_order=?, is_active=? WHERE slider_id=?");
        $stmt->bind_param("ssiii", $image_path, $caption, $sort_order, $is_active, $id);
        $action = "updated";
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO sliders (image_url, caption, sort_order, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $image_path, $caption, $sort_order, $is_active);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: home_content_manage.php?status=slider_" . $action);
        exit;
    } else {
        $error_message = "Database Error: " . $stmt->error;
    }
}
end_content_crud:

// --- FETCH DATA FOR FORMS AND LISTS ---

// Fetch current homepage text content
$current_settings = [];
$settings_keys = ['homepage_heading', 'homepage_para1', 'homepage_para2', 'homepage_para3'];
foreach ($settings_keys as $key) {
    $current_settings[$key] = get_setting($key);
}

// Fetch all sliders
$sliders_result = $conn->query("SELECT * FROM sliders ORDER BY sort_order ASC");
$all_sliders = $sliders_result->fetch_all(MYSQLI_ASSOC);

// Fetch slider for edit form
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM sliders WHERE slider_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slider_to_edit = $result->fetch_assoc();
}

// Status message handling
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'slider_added') {
        $success_message = "New slider image added successfully! Please Hard Refresh the main page to see changes.";
    } elseif ($_GET['status'] == 'slider_updated') {
        $success_message = "Slider updated successfully! Please Hard Refresh the main page to see changes.";
    } elseif ($_GET['status'] == 'slider_deleted') {
        $success_message = "Slider deleted successfully! Please Hard Refresh the main page to see changes.";
    } elseif ($_GET['status'] == 'text_updated') {
        $success_message = "Homepage text content saved successfully! Please Hard Refresh the main page to see changes.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Homepage Content</title>
    <style>
        /* CSS styles (Reusable admin styles) */
        body { font-family: Arial, sans-serif; background: #e9ebee; margin: 0; padding: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; max-width: 900px; margin: 0 auto; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e5c9; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .card input[type="text"], .card textarea, .card input[type="number"], .card select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f2f2f2; }
        .btn-edit, .btn-delete { background: #f0ad4e; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #d9534f; }
        .img-preview { max-width: 80px; max-height: 80px; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Homepage Content</h1>
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
            <h3>Homepage Text Content</h3>
            <p>This controls the main Heading and paragraphs on the homepage (index.php).</p>
            <form method="POST">
                <label for="homepage_heading">Main Heading (H1):</label>
                <input type="text" id="homepage_heading" name="homepage_heading" value="<?php echo htmlspecialchars($current_settings['homepage_heading'] ?? ''); ?>" required>
                
                <label for="homepage_para1">Paragraph 1 (The Department of B.Sc. (IT)...):</label>
                <textarea id="homepage_para1" name="homepage_para1" rows="4" required><?php echo htmlspecialchars($current_settings['homepage_para1'] ?? ''); ?></textarea>
                
                <label for="homepage_para2">Paragraph 2 (With a strong focus on practical learning...):</label>
                <textarea id="homepage_para2" name="homepage_para2" rows="4" required><?php echo htmlspecialchars($current_settings['homepage_para2'] ?? ''); ?></textarea>
                
                <label for="homepage_para3">Paragraph 3 (Our goal is to empower students...):</label>
                <textarea id="homepage_para3" name="homepage_para3" rows="4" required><?php echo htmlspecialchars($current_settings['homepage_para3'] ?? ''); ?></textarea>

                <button type="submit" name="submit_text_content">Save Text Content</button>
            </form>
        </div>

        <a name="sliders"></a>
        <div class="card">
            <h3><?php echo $slider_to_edit ? 'Edit Slider Image' : 'Add New Slider Image'; ?></h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="slider_id" value="<?php echo $slider_to_edit['slider_id'] ?? ''; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($slider_to_edit['image_url'] ?? ''); ?>">

                <label for="image_url">Image File (New upload overrides existing):</label>
                <?php if ($slider_to_edit && $slider_to_edit['image_url']): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($slider_to_edit['image_url']); ?>" class="img-preview"> (<?php echo basename($slider_to_edit['image_url']); ?>)</p>
                <?php endif; ?>
                <input type="file" id="image_url" name="image_url" accept="image/*" <?php echo $slider_to_edit ? '' : 'required'; ?>>

                <label for="caption">Caption (Alt Text):</label>
                <input type="text" id="caption" name="caption" value="<?php echo htmlspecialchars($slider_to_edit['caption'] ?? ''); ?>">
                
                <label for="sort_order">Sort Order:</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($slider_to_edit['sort_order'] ?? '10'); ?>">

                <label>
                    <input type="checkbox" name="is_active" <?php echo (isset($slider_to_edit['is_active']) && $slider_to_edit['is_active']) ? 'checked' : 'checked'; ?>>
                    Is Active?
                </label>
                
                <button type="submit" name="submit_slider">
                    <?php echo $slider_to_edit ? 'Save Slider Changes' : 'Add New Slider'; ?>
                </button>
            </form>
        </div>

        <h2>Current Slider Images</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Caption (Alt Text)</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_sliders as $s): ?>
                <tr>
                    <td><?php echo $s['slider_id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($s['image_url']); ?>" class="img-preview" alt="Slider Image">
                    </td>
                    <td><?php echo htmlspecialchars($s['caption'] ?? 'N/A'); ?></td>
                    <td><?php echo $s['sort_order']; ?></td>
                    <td><?php echo $s['is_active'] ? '✅ Active' : '❌ Inactive'; ?></td>
                    <td>
                        <a href="?edit_id=<?php echo $s['slider_id']; ?>#sliders" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $s['slider_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this slider?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>