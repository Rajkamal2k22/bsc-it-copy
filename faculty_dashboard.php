<?php
// === PHP START: ERROR REPORTING AND SESSION ===
// Note: Keeping error reporting enabled for debugging, disable in production.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Start Output Buffering (Allows headers to be sent after output)
ob_start(); 
session_start();

// 2. Database connection
require_once __DIR__ . '/db_connect.php'; 

// 3. Get the current file name dynamically 
$current_page = basename($_SERVER['PHP_SELF']);

// ----------------------------------------------------
// 4. AUTHENTICATION CHECK
// ----------------------------------------------------
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'faculty') {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];
$faculty_name = $_SESSION['user_name'];
$upload_dir = __DIR__ . '/uploads/materials/'; // Absolute path for file system

// Ensure folder exists and set permissions
if (!is_dir($upload_dir)) {
    // Attempt to create directory with permissions (0777 is high access)
    // NOTE: If this fails, file uploads will still fail, indicating a persistent OS permission issue.
    mkdir($upload_dir, 0777, true);
}

// Helper function for Flash Messages (PRG Pattern)
function setFlashMessage($type, $msg) {
    $_SESSION['flash_msg'] = ['type' => $type, 'text' => $msg];
}

// Helper function for PRG redirect
function redirect_to_self() {
    header("Location: " . basename($_SERVER['PHP_SELF']));
    // Clean buffer before exit
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    exit();
}

// ----------------------------------------------------
// 5. CRUD LOGIC (Delete, Upload, Update)
// ----------------------------------------------------

// A. DELETE MATERIAL (GET Request - uses material_id)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // 1. Get file path first
    $stmt = $conn->prepare("SELECT file_path FROM study_materials WHERE material_id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $delete_id, $faculty_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $full_path = __DIR__ . '/' . $row['file_path'];
        
        // 2. Delete from DB
        $del_stmt = $conn->prepare("DELETE FROM study_materials WHERE material_id = ? AND faculty_id = ?");
        $del_stmt->bind_param("ii", $delete_id, $faculty_id);
        
        if ($del_stmt->execute()) {
            // Delete actual file
            if (file_exists($full_path) && is_file($full_path)) {
                unlink($full_path);
            }
            setFlashMessage('success', "Material deleted successfully.");
        } else {
            error_log("DB DELETE Failed: " . $del_stmt->error);
            setFlashMessage('error', "Database error during deletion. See logs.");
        }
        $del_stmt->close();
    } else {
        setFlashMessage('error', "Material not found or access denied.");
    }
    $stmt->close();
    
    redirect_to_self();
}

// B. HANDLE FORM SUBMISSION (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // --- CREATE (UPLOAD) ---
    if (isset($_POST['upload_material'])) {
        $file = $_FILES['material_file'];
        
        if (empty($title) || empty($file['name'])) {
            setFlashMessage('error', "Title and File are required.");
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid('mat_', true) . '.' . $extension;
            $destination_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                $relative_path = 'uploads/materials/' . $unique_filename;
                
                $stmt = $conn->prepare("INSERT INTO study_materials (faculty_id, title, description, file_path, file_name, file_type) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $faculty_id, $title, $description, $relative_path, $file['name'], $file['type']);
                
                if ($stmt->execute()) {
                    setFlashMessage('success', "Material uploaded successfully!");
                } else {
                    unlink($destination_path);
                    error_log("DB INSERT Failed: " . $stmt->error);
                    setFlashMessage('error', "Database Error: " . $conn->error);
                }
                $stmt->close();
            } else {
                setFlashMessage('error', "File upload failed. Check permissions.");
            }
        }
        redirect_to_self();
    }

    // --- C. UPDATE (POST Request - uses material_id) ---
    if (isset($_POST['update_material'])) {
        $edit_id = intval($_POST['edit_id']); // This is the material_id
        $success = false;

        if (!empty($_FILES['material_file']['name'])) {
            // New file selected - Delete old file and move new one
            $file = $_FILES['material_file'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid('mat_', true) . '.' . $extension;
            $destination_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                // Fetch and delete old file
                $old_q = $conn->prepare("SELECT file_path FROM study_materials WHERE material_id=? AND faculty_id=?");
                $old_q->bind_param("ii", $edit_id, $faculty_id);
                $old_q->execute();
                $res = $old_q->get_result();
                $old_row = $res->fetch_assoc();
                
                if ($old_row && file_exists(__DIR__ . '/' . $old_row['file_path'])) {
                    unlink(__DIR__ . '/' . $old_row['file_path']);
                }
                $old_q->close();
                
                // Update with new file path
                $relative_path = 'uploads/materials/' . $unique_filename;
                $stmt = $conn->prepare("UPDATE study_materials SET title=?, description=?, file_path=?, file_name=?, file_type=? WHERE material_id=? AND faculty_id=?");
                $stmt->bind_param("sssssii", $title, $description, $relative_path, $file['name'], $file['type'], $edit_id, $faculty_id);
                
                $success = (isset($stmt) && $stmt->execute());
                if(isset($stmt)) $stmt->close();
            }
        } else {
            // Text update only
            $stmt = $conn->prepare("UPDATE study_materials SET title=?, description=? WHERE material_id=? AND faculty_id=?");
            $stmt->bind_param("ssii", $title, $description, $edit_id, $faculty_id);
            $success = ($stmt->execute());
            $stmt->close();
        }
        
        if ($success) {
            setFlashMessage('success', "Material updated successfully!");
        } else {
            error_log("DB UPDATE Failed: " . (isset($stmt) ? $stmt->error : "File move failed or text update query failed."));
            setFlashMessage('error', "Update failed.");
        }
        
        redirect_to_self();
    }
}

// ----------------------------------------------------
// 6. EDIT MODE DATA FETCH
// ----------------------------------------------------
$edit_mode = false;
$edit_data = [];

if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT *, material_id as id FROM study_materials WHERE material_id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $edit_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_mode = true;
        $edit_data = $row;
    }
    $stmt->close();
}

// ----------------------------------------------------
// 7. GET ALL MATERIALS
// ----------------------------------------------------
$materials = [];
// Select material_id and alias it to 'id' for consistency with table links
$result = $conn->query("SELECT material_id as id, title, description, file_path, file_name, uploaded_at FROM study_materials WHERE faculty_id = $faculty_id ORDER BY uploaded_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
}

// Close connection before HTML starts
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <style>
        /* (Your existing CSS here - using internal style for simplicity after removing header file) */
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .btn-logout { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .form-box { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-submit:hover { background: #219150; }
        .btn-cancel { background: #95a5a6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #3498db; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .action-btn { margin-right: 5px; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px; }
        .btn-edit { background: #f39c12; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-view { background: #3498db; color: white; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Faculty Dashboard</h2>
        <div>
            <span>Welcome, <strong><?php echo htmlspecialchars($faculty_name); ?></strong></span> | 
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_msg']['type']; ?>">
            <?php echo $_SESSION['flash_msg']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_msg']); ?>
    <?php endif; ?>

    <!-- Form Section -->
    <div class="form-box">
        <h3><?php echo $edit_mode ? 'Edit Material' : 'Upload New Material'; ?></h3>
        
        <form action="<?php echo $current_page; ?>" method="POST" enctype="multipart/form-data">
            
            <?php if ($edit_mode): ?>
                <input type="hidden" name="update_material" value="1">
                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="upload_material" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="3"><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label><?php echo $edit_mode ? 'Replace File (Optional):' : 'Select File:'; ?></label>
                <input type="file" name="material_file" <?php echo $edit_mode ? '' : 'required'; ?>>
                <?php if ($edit_mode): ?>
                    <small>Current file: <?php echo htmlspecialchars($edit_data['file_name']); ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">
                <?php echo $edit_mode ? 'Update Material' : 'Upload Material'; ?>
            </button>
            
            <?php if ($edit_mode): ?>
                <a href="<?php echo $current_page; ?>" class="btn-cancel">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table -->
    <h3>Your Study Materials</h3>
    <?php if (count($materials) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                    <td><?php echo date('d M Y', strtotime($item['uploaded_at'])); ?></td>
                    <td>
                        <a href="<?php echo htmlspecialchars($item['file_path']); ?>" target="_blank" class="action-btn btn-view"><i class="fas fa-eye"></i></a>
                        
                        <!-- Edit Link -->
                        <a href="<?php echo $current_page; ?>?edit_id=<?php echo $item['id']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                        
                        <!-- Delete Link -->
                        <a href="<?php echo $current_page; ?>?delete_id=<?php echo $item['id']; ?>" class="action-btn btn-delete" onclick="return confirm('क्या आप इस मटेरियल को मिटाना चाहते हैं: <?php echo htmlspecialchars($item['title']); ?>?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No materials uploaded yet.</p>
    <?php endif; ?>

</div>

</body>
</html>
<?php 
// End Output Buffering
ob_end_flush(); 
?>