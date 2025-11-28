<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$upload_dir_img = '../images/'; 
$error_message = '';
$success_message = '';
$item_to_edit = null;

// Categories used for filtering in gallery.php
$categories = ['faculty', 'classroom', 'lab&library', 'events', 'workshops', 'celebrations'];

// --- Helper function for file upload (Reusable) ---
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
             if (!mkdir($target_dir, 0775, true)) {
                 return ['error' => "Target directory could not be created or lacks permission."];
             }
        }
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            return ['success' => substr($target_file, 3)]; // Return relative path
        } else {
            return ['error' => "File upload failed. Check permissions on " . $target_dir];
        }
    }
    return ['success' => null];
}

// --- 1. DELETE Logic ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM gallery_items WHERE item_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Gallery item deleted successfully!";
        header("Location: gallery_manage.php?status=deleted");
        exit;
    } else {
        $error_message = "Error deleting record: " . $conn->error;
    }
}

// --- 2. CREATE / UPDATE Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_item'])) {
    $id = $_POST['item_id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $event_date = trim($_POST['event_date']);
    $sort_order = is_numeric($_POST['sort_order']) ? (int)$_POST['sort_order'] : 99;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image_path = $_POST['existing_image'] ?? null;

    // Handle Image Upload
    $img_upload_result = handle_upload('image_url', $upload_dir_img);
    if (isset($img_upload_result['error'])) {
        $error_message = "Image Upload Error: " . $img_upload_result['error'];
        goto end_crud;
    } elseif ($img_upload_result['success']) {
        $image_path = $img_upload_result['success'];
    }
    
    if (!$image_path) {
        $error_message = "Image file is required.";
        goto end_crud;
    }

    if ($id) {
        // UPDATE Operation
        $stmt = $conn->prepare("UPDATE gallery_items SET image_url=?, title=?, description=?, category=?, event_date=?, sort_order=?, is_active=? WHERE item_id=?");
        $stmt->bind_param("sssssiii", $image_path, $title, $description, $category, $event_date, $sort_order, $is_active, $id);
        $action = "updated";
    } else {
        // INSERT Operation
        $stmt = $conn->prepare("INSERT INTO gallery_items (image_url, title, description, category, event_date, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $image_path, $title, $description, $category, $event_date, $sort_order, $is_active);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: gallery_manage.php?status=" . $action);
        exit;
    } else {
        $error_message = "Database Error: " . $stmt->error;
    }
}
end_crud:

// --- 3. FETCH FOR EDIT FORM ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM gallery_items WHERE item_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item_to_edit = $result->fetch_assoc();
}

// --- 4. FETCH ALL ITEMS FOR LISTING ---
$items_result = $conn->query("SELECT * FROM gallery_items ORDER BY event_date DESC, sort_order ASC");
$all_items = $items_result->fetch_all(MYSQLI_ASSOC);

// --- Status Message Handling ---
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $success_message = "New gallery item added successfully!";
    } elseif ($_GET['status'] == 'updated') {
        $success_message = "Gallery item updated successfully!";
    } elseif ($_GET['status'] == 'deleted') {
        $success_message = "Gallery item deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Gallery</title>
    <style>
        /* CSS styles (Reusable admin styles) */
        body { font-family: Arial, sans-serif; background: #e9ebee; margin: 0; padding: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e5c9; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .card input[type="text"], .card input[type="file"], .card input[type="date"], .card input[type="number"], .card textarea, .card select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f2f2f2; }
        .btn-edit, .btn-delete { background: #f0ad4e; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #d9534f; }
        .img-preview { max-width: 100px; max-height: 80px; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Gallery Items</h1>
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
            <h3><?php echo $item_to_edit ? 'Edit Gallery Item: ' . htmlspecialchars($item_to_edit['title']) : 'Add New Gallery Item'; ?></h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo $item_to_edit['item_id'] ?? ''; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($item_to_edit['image_url'] ?? ''); ?>">
                
                <label for="title">Title/Caption:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($item_to_edit['title'] ?? ''); ?>" required placeholder="e.g., Computer Lab Session or Final Year Farewell">

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3" required placeholder="A short description of the image/event."><?php echo htmlspecialchars($item_to_edit['description'] ?? ''); ?></textarea>
                
                <label for="category">Category (For Filtering):</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo (isset($item_to_edit['category']) && $item_to_edit['category'] == $cat) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="event_date">Event Date:</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($item_to_edit['event_date'] ?? date('Y-m-d')); ?>">

                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label for="sort_order">Sort Order:</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($item_to_edit['sort_order'] ?? '99'); ?>">
                    </div>
                    <div style="flex: 1; padding-top: 30px;">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo (isset($item_to_edit['is_active']) && $item_to_edit['is_active']) ? 'checked' : 'checked'; ?>>
                            Is Active?
                        </label>
                    </div>
                </div>

                <label for="image_url">Image File (Required - New upload overrides existing):</label>
                <?php if ($item_to_edit && $item_to_edit['image_url']): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($item_to_edit['image_url']); ?>" class="img-preview" alt="Current Gallery Image"></p>
                <?php endif; ?>
                <input type="file" id="image_url" name="image_url" accept="image/*" <?php echo $item_to_edit ? '' : 'required'; ?>>

                <button type="submit" name="submit_item">
                    <?php echo $item_to_edit ? 'Save Changes' : 'Add Gallery Item'; ?>
                </button>
            </form>
        </div>

        <h2>Current Gallery Items</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Preview</th>
                    <th>Title / Description</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_items as $item): ?>
                <tr>
                    <td><?php echo $item['item_id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="img-preview" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                        <small><?php echo substr(htmlspecialchars($item['description']), 0, 50); ?>...</small>
                    </td>
                    <td><?php echo ucfirst(htmlspecialchars($item['category'])); ?></td>
                    <td><?php echo htmlspecialchars($item['event_date']); ?></td>
                    <td><?php echo $item['is_active'] ? '✅ Active' : '❌ Inactive'; ?></td>
                    <td>
                        <a href="?edit_id=<?php echo $item['item_id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $item['item_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this gallery item?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>