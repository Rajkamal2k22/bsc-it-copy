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
$student_to_edit = null;

// Dynamically generate batches for dropdown (from 2015-2018 up to the current year + 3 years)
$current_year = date('Y');
$start_year = 2015;
$batches = [];

for ($year = $start_year; $year <= $current_year; $year++) {
    // 3-year degree program format
    $batches[] = $year . '-' . ($year + 3);
}

// Add upcoming batch just in case data is pre-entered for new students
$batches[] = ($current_year + 1) . '-' . ($current_year + 4);

$default_img = 'images/profile image.webp';

// --- Helper function for file upload (copied from faculty_manage.php) ---
function handle_upload($file_key, $target_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp']) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_name = basename($_FILES[$file_key]['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (!in_array($file_ext, $allowed_extensions)) {
            return ['error' => "Invalid file type. Allowed: " . implode(', ', $allowed_extensions)];
        }
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            return ['success' => $target_dir . $new_file_name];
        } else {
            return ['error' => "File upload failed."];
        }
    }
    return ['success' => null]; // No file uploaded
}

// --- 1. DELETE Logic ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM placed_students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Student record deleted successfully!";
        header("Location: placed_students_manage.php?status=deleted");
        exit;
    } else {
        $error_message = "Error deleting record: " . $conn->error;
    }
}

// --- 2. CREATE / UPDATE Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student'])) {
    $id = $_POST['student_id'] ?? null;
    $sl_no = is_numeric($_POST['sl_no']) ? (int)$_POST['sl_no'] : 999;
    $name = trim($_POST['name']);
    $company = trim($_POST['company']);
    $batch = trim($_POST['batch']);
    $role = trim($_POST['role']);
    $card_description = trim($_POST['card_description']);
    
    $image_path = $_POST['existing_image'] ?? $default_img; // Use existing path or default

    // Handle Image Upload
    $img_upload_result = handle_upload('image_url', $upload_dir_img);
    if (isset($img_upload_result['error'])) {
        $error_message = "Image Error: " . $img_upload_result['error'];
        goto end_crud; // Skip DB operation
    } elseif ($img_upload_result['success']) {
        $image_path = $img_upload_result['success'];
    }

    if ($id) {
        // UPDATE Operation
        $stmt = $conn->prepare("UPDATE placed_students SET sl_no=?, name=?, company=?, batch=?, role=?, card_description=?, image_url=? WHERE id=?");
        $stmt->bind_param("issssssi", $sl_no, $name, $company, $batch, $role, $card_description, $image_path, $id);
        $action = "updated";
    } else {
        // INSERT Operation
        $stmt = $conn->prepare("INSERT INTO placed_students (sl_no, name, company, batch, role, card_description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $sl_no, $name, $company, $batch, $role, $card_description, $image_path);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: placed_students_manage.php?status=" . $action);
        exit;
    } else {
        $error_message = "Database Error: " . $stmt->error;
    }
}
end_crud:

// --- 3. FETCH FOR EDIT FORM ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM placed_students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_to_edit = $result->fetch_assoc();
}

// --- 4. FETCH ALL STUDENTS FOR LISTING ---
// Order by batch DESC for recent batches first, then by manual sl_no
$students_result = $conn->query("SELECT * FROM placed_students ORDER BY batch DESC, sl_no ASC");
$all_students = $students_result->fetch_all(MYSQLI_ASSOC);

// --- Status Message Handling ---
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $success_message = "Student record added successfully!";
    } elseif ($_GET['status'] == 'updated') {
        $success_message = "Student record updated successfully!";
    } elseif ($_GET['status'] == 'deleted') {
        $success_message = "Student record deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Placed Students</title>
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
        .card input[type="text"], .card input[type="email"], .card input[type="tel"], .card textarea, .card input[type="number"], .card select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .card input[type="checkbox"] { margin-right: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f2f2f2; }
        .btn-edit, .btn-delete { background: #f0ad4e; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #d9534f; }
        .img-preview { max-width: 50px; max-height: 50px; border-radius: 50%; vertical-align: middle; object-fit: cover; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Placed Students</h1>
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
            <h3><?php echo $student_to_edit ? 'Edit Student: ' . htmlspecialchars($student_to_edit['name']) : 'Add New Placed Student'; ?></h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="student_id" value="<?php echo $student_to_edit['id'] ?? ''; ?>">
                
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label for="name">Name (Full Name):</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student_to_edit['name'] ?? ''); ?>" required>
                    </div>
                    <div style="flex: 0.5;">
                        <label for="sl_no">Serial No. (Table Order):</label>
                        <input type="number" id="sl_no" name="sl_no" value="<?php echo htmlspecialchars($student_to_edit['sl_no'] ?? '999'); ?>" required>
                    </div>
                </div>

                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label for="company">Company:</label>
                        <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($student_to_edit['company'] ?? ''); ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="batch">Batch (e.g., 2021-2024):</label>
                        <select id="batch" name="batch" required>
                            <option value="">Select Batch</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?php echo $b; ?>" <?php echo (isset($student_to_edit['batch']) && $student_to_edit['batch'] == $b) ? 'selected' : ''; ?>>
                                    <?php echo $b; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label for="role">Role/Profile (for slider, e.g., Software Engineer):</label>
                <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($student_to_edit['role'] ?? ''); ?>" placeholder="Optional: E.g., Data Analyst at AMAZON">
                
                <label for="card_description">Short Description (for slider/card):</label>
                <textarea id="card_description" name="card_description" rows="2" placeholder="Optional: E.g., Expert in full-stack dev & data structures."><?php echo htmlspecialchars($student_to_edit['card_description'] ?? ''); ?></textarea>
                
                <hr>
                
                <label for="image_url">Profile Image (Used in Scrolling Cards):</label>
                <?php $current_img = $student_to_edit['image_url'] ?? $default_img; ?>
                <?php if ($student_to_edit && $current_img): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($current_img); ?>" class="img-preview"> (<?php echo basename($current_img); ?>)</p>
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($current_img); ?>">
                <?php endif; ?>
                <input type="file" id="image_url" name="image_url" accept="image/*">
                
                <button type="submit" name="submit_student">
                    <?php echo $student_to_edit ? 'Save Changes' : 'Add Student'; ?>
                </button>
            </form>
        </div>

        <h2>Current Placed Students List</h2>
        <table>
            <thead>
                <tr>
                    <th>SL No.</th>
                    <th>Image</th>
                    <th>Name / Batch</th>
                    <th>Company / Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_students as $s): ?>
                <tr>
                    <td><?php echo $s['sl_no']; ?></td>
                    <td>
                        <?php if ($s['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($s['image_url']); ?>" class="img-preview">
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($s['name']); ?></strong><br>
                        <small>Batch: <?php echo htmlspecialchars($s['batch']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($s['company']); ?><br>
                        <small><?php echo htmlspecialchars($s['role'] ?? 'N/A'); ?></small>
                    </td>
                    <td>
                        <a href="?edit_id=<?php echo $s['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $s['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($s['name']); ?>?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>