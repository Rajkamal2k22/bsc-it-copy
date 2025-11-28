<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$upload_dir_img = '../images/'; // For preview images
$upload_dir_file = '../pdfs/';  
$error_message = '';
$success_message = '';
$download_to_edit = null;

// --- Helper function for file upload (Reusable) ---
function handle_upload($file_key, $target_dir, $allowed_extensions) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_name = basename($_FILES[$file_key]['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (!in_array($file_ext, $allowed_extensions)) {
            return ['error' => "Invalid file type. Allowed: " . implode(', ', $allowed_extensions)];
        }
        
        if (!is_dir($target_dir)) {
             mkdir($target_dir, 0775, true);
        }
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            return ['success' => substr($target_file, 3)]; 
        } else {
            return ['error' => "File upload failed. Check permissions on " . $target_dir];
        }
    }
    return ['success' => null];
}

// --- 1. DELETE Logic ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM downloads WHERE download_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Question Paper/Syllabus record deleted successfully!";
        header("Location: downloads_manage.php?status=deleted");
        exit;
    } else {
        $error_message = "Error deleting record: " . $conn->error;
    }
}

// --- 2. CREATE / UPDATE Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_download'])) {
    $id = $_POST['download_id'] ?? null;
    $year_class = trim($_POST['year_class']);
    $paper_name = trim($_POST['paper_name']);
    $exam_year = trim($_POST['exam_year']);
    $category = trim($_POST['category']);

    $file_path = $_POST['existing_file'] ?? null;
    $image_path = $_POST['existing_image'] ?? null;

    // Handle PDF/File Upload
    $file_upload_result = handle_upload('file_url', $upload_dir_file, ['pdf']);
    if (isset($file_upload_result['error'])) {
        $error_message = "File Upload Error: " . $file_upload_result['error'];
        goto end_crud;
    } elseif ($file_upload_result['success']) {
        $file_path = $file_upload_result['success'];
    }

    // Handle Image Upload
    $img_upload_result = handle_upload('preview_image_url', $upload_dir_img, ['jpg', 'jpeg', 'png', 'webp']);
    if (isset($img_upload_result['error'])) {
        $error_message = "Image Upload Error: " . $img_upload_result['error'];
        goto end_crud;
    } elseif ($img_upload_result['success']) {
        $image_path = $img_upload_result['success'];
    }

    if (!$file_path) {
        $error_message = "File is required. Please upload a PDF file.";
        goto end_crud;
    }

    if ($id) {
        // UPDATE Operation
        $stmt = $conn->prepare("UPDATE downloads SET year_class=?, paper_name=?, file_url=?, preview_image_url=?, exam_year=?, category=? WHERE download_id=?");
        $stmt->bind_param("ssssssi", $year_class, $paper_name, $file_path, $image_path, $exam_year, $category, $id);
        $action = "updated";
    } else {
        // INSERT Operation
        $stmt = $conn->prepare("INSERT INTO downloads (year_class, paper_name, file_url, preview_image_url, exam_year, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $year_class, $paper_name, $file_path, $image_path, $exam_year, $category);
        $action = "added";
    }

    if ($stmt->execute()) {
        header("Location: downloads_manage.php?status=" . $action);
        exit;
    } else {
        $error_message = "Database Error: " . $stmt->error;
    }
}
end_crud:

// --- 3. FETCH FOR EDIT FORM ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM downloads WHERE download_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $download_to_edit = $result->fetch_assoc();
}

// --- 4. FETCH ALL DOWNLOADS FOR LISTING ---
$downloads_result = $conn->query("SELECT * FROM downloads ORDER BY exam_year DESC, year_class ASC, paper_name ASC");
$all_downloads = $downloads_result->fetch_all(MYSQLI_ASSOC);

// --- Status Message Handling ---
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $success_message = "New Question Paper/Syllabus added successfully!";
    } elseif ($_GET['status'] == 'updated') {
        $success_message = "Question Paper/Syllabus record updated successfully!";
    } elseif ($_GET['status'] == 'deleted') {
        $success_message = "Question Paper/Syllabus record deleted successfully!";
    }
}

// Define select options
$year_classes = ['1st', '2nd', '3rd'];
$exam_years = range(date('Y') + 1, 2018); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Question Bank</title>
    <style>
        /* CSS styles */
        body { font-family: Arial, sans-serif; background: #e9ebee; margin: 0; padding: 0; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e5c9; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .card h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .card input[type="text"], .card input[type="file"], .card input[type="number"], .card select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background: #f2f2f2; }
        .btn-edit, .btn-delete { background: #f0ad4e; color: white; padding: 5px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; margin-right: 5px; }
        .btn-delete { background: #d9534f; }
        .img-preview { max-width: 40px; max-height: 50px; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Question Bank (PYQ)</h1>
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
            <h3><?php echo $download_to_edit ? 'Edit Question Paper: ' . htmlspecialchars($download_to_edit['paper_name']) : 'Add New Question Paper'; ?></h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="download_id" value="<?php echo $download_to_edit['download_id'] ?? ''; ?>">
                
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label for="year_class">Year/Part:</label>
                        <select id="year_class" name="year_class" required>
                            <option value="">Select Year</option>
                            <?php foreach ($year_classes as $yc): ?>
                                <option value="<?php echo $yc; ?>" <?php echo (isset($download_to_edit['year_class']) && $download_to_edit['year_class'] == $yc) ? 'selected' : ''; ?>>
                                    <?php echo $yc; ?> Year
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label for="exam_year">Exam Year:</label>
                        <select id="exam_year" name="exam_year" required>
                            <option value="">Select Exam Year</option>
                            <?php foreach ($exam_years as $ey): ?>
                                <option value="<?php echo $ey; ?>" <?php echo (isset($download_to_edit['exam_year']) && $download_to_edit['exam_year'] == $ey) ? 'selected' : ''; ?>>
                                    <?php echo $ey; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label for="paper_name">Paper Name:</label>
                <input type="text" id="paper_name" name="paper_name" value="<?php echo htmlspecialchars($download_to_edit['paper_name'] ?? ''); ?>" required placeholder="e.g., Mathematics - 2025 or B.Sc. IT 1st Year Syllabus">
                
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="PYQ" <?php echo (isset($download_to_edit['category']) && $download_to_edit['category'] == 'PYQ') ? 'selected' : ''; ?>>PYQ (Question Paper)</option>
                    <option value="Syllabus" <?php echo (isset($download_to_edit['category']) && $download_to_edit['category'] == 'Syllabus') ? 'selected' : ''; ?>>Syllabus</option>
                </select>
                
                <hr>

                <label for="file_url">PDF File (Required - New upload overrides existing):</label>
                <?php if ($download_to_edit && $download_to_edit['file_url']): ?>
                    <p>Current File: <a href="<?php echo htmlspecialchars($download_to_edit['file_url']); ?>" target="_blank">View File (<?php echo basename($download_to_edit['file_url']); ?>)</a></p>
                    <input type="hidden" name="existing_file" value="<?php echo htmlspecialchars($download_to_edit['file_url']); ?>">
                <?php endif; ?>
                <input type="file" id="file_url" name="file_url" accept="application/pdf">

                <label for="preview_image_url">Preview Image (Optional - Thumbnail):</label>
                <?php if ($download_to_edit && $download_to_edit['preview_image_url']): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($download_to_edit['preview_image_url']); ?>" class="img-preview" alt="Preview Image"> (<?php echo basename($download_to_edit['preview_image_url']); ?>)</p>
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($download_to_edit['preview_image_url']); ?>">
                <?php endif; ?>
                <input type="file" id="preview_image_url" name="preview_image_url" accept="image/*">

                
                <button type="submit" name="submit_download">
                    <?php echo $download_to_edit ? 'Save Changes' : 'Add Download'; ?>
                </button>
            </form>
        </div>

        <h2>Current Question Papers & Syllabi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Year / Type</th>
                    <th>Paper Name</th>
                    <th>Exam Year</th>
                    <th>Preview</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_downloads as $d): ?>
                <tr>
                    <td><?php echo $d['download_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($d['year_class']); ?> Year</strong><br>
                        <small><?php echo htmlspecialchars($d['category']); ?></small>
                    </td>
                    <td>
                        <a href="<?php echo htmlspecialchars($d['file_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($d['paper_name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($d['exam_year']); ?></td>
                    <td>
                        <?php if ($d['preview_image_url']): ?>
                            <img src="<?php echo htmlspecialchars($d['preview_image_url']); ?>" class="img-preview" alt="Preview">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit_id=<?php echo $d['download_id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $d['download_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>