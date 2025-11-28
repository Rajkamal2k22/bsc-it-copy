<?php
session_start();

// --- Error Reporting ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. AUTHENTICATION ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../db_connect.php';

// --- 2. CONFIGURATION & HELPER FUNCTIONS ---
$upload_dir_img = __DIR__ . '/../images/';
$upload_dir_cv = __DIR__ . '/../assets/';
$current_page = basename($_SERVER['PHP_SELF']);

// Flash Message Helper
function setFlashMessage($type, $msg) {
    $_SESSION['admin_flash_msg'] = ['type' => $type, 'text' => $msg];
}

// File Upload Helper
function handle_upload($file_key, $target_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp']) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => null];
    }
    $file = $_FILES[$file_key];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Upload error: ' . $file['error']];

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) return ['error' => "Invalid type. Allowed: " . implode(', ', $allowed_extensions)];

    $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        if (strpos($target_dir, '/images/') !== false) return ['success' => 'images/' . $new_file_name];
        if (strpos($target_dir, '/assets/') !== false) return ['success' => 'assets/' . $new_file_name];
        return ['success' => $new_file_name];
    }
    return ['error' => "Failed to save file."];
}

// --- 3. FETCH DATA FOR HEADER (Announcements) ---
// This is needed so the header ticker works exactly like dashboard
$announcements_result = $conn->query("SELECT short_text FROM announcements WHERE is_active = 1 LIMIT 3");
$announcements = ($announcements_result) ? $announcements_result->fetch_all(MYSQLI_ASSOC) : [];

// --- 4. ACTION HANDLERS (CRUD LOGIC) ---

// A. Block/Unblock
if (isset($_GET['action']) && isset($_GET['id']) && in_array($_GET['action'], ['block', 'unblock'])) {
    $fid = intval($_GET['id']);
    $new_status = ($_GET['action'] == 'block') ? 0 : 1;
    $stmt = $conn->prepare("UPDATE faculty SET is_active = ? WHERE faculty_id = ?");
    $stmt->bind_param("ii", $new_status, $fid);
    if ($stmt->execute()) {
        setFlashMessage('success', ($new_status == 0) ? "Faculty blocked." : "Faculty activated.");
    }
    header("Location: " . $current_page);
    exit;
}

// B. Delete
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlashMessage('success', "Faculty deleted successfully.");
    }
    header("Location: " . $current_page);
    exit;
}

// C. Create/Update (Form Submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_faculty'])) {
    $id = $_POST['faculty_id'] ?? null;
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $subject = trim($_POST['subject']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $about = trim($_POST['about']);
    $is_coordinator = isset($_POST['is_coordinator']) ? 1 : 0;
    $sort_order = (int)$_POST['sort_order'];
    $image_path = $_POST['existing_image'] ?? null;
    $cv_path = $_POST['existing_cv'] ?? null;

    // Upload Image
    $img_res = handle_upload('image_url', $upload_dir_img);
    if (isset($img_res['error'])) { setFlashMessage('error', $img_res['error']); header("Location: ".$current_page); exit; }
    if ($img_res['success']) $image_path = $img_res['success'];

    // Upload CV
    $cv_res = handle_upload('cv_url', $upload_dir_cv, ['pdf']);
    if (isset($cv_res['error'])) { setFlashMessage('error', $cv_res['error']); header("Location: ".$current_page); exit; }
    if ($cv_res['success']) $cv_path = $cv_res['success'];

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE faculty SET name=?, designation=?, subject=?, email=?, phone=?, about=?, is_coordinator=?, sort_order=?, image_url=?, cv_url=? WHERE faculty_id=?");
        $stmt->bind_param("ssssssiissi", $name, $designation, $subject, $email, $phone, $about, $is_coordinator, $sort_order, $image_path, $cv_path, $id);
        $msg = "Updated successfully.";
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO faculty (name, designation, subject, email, phone, about, is_coordinator, sort_order, image_url, cv_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssssiiss", $name, $designation, $subject, $email, $phone, $about, $is_coordinator, $sort_order, $image_path, $cv_path);
        $msg = "Added successfully.";
    }

    if ($stmt->execute()) setFlashMessage('success', $msg);
    else setFlashMessage('error', "DB Error: " . $stmt->error);
    
    header("Location: " . $current_page);
    exit;
}

// --- 5. FETCH DATA FOR DISPLAY ---
$faculty_to_edit = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $faculty_to_edit = $stmt->get_result()->fetch_assoc();
}

$all_faculty = $conn->query("SELECT * FROM faculty ORDER BY sort_order ASC, name ASC")->fetch_all(MYSQLI_ASSOC);

// Retrieve Flash Msg
$msg_type = ''; $msg_text = '';
if (isset($_SESSION['admin_flash_msg'])) {
    $msg_type = $_SESSION['admin_flash_msg']['type'];
    $msg_text = $_SESSION['admin_flash_msg']['text'];
    unset($_SESSION['admin_flash_msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty | Admin Panel</title>
    
    <!-- 1. GLOBAL STYLES (Adjusted Paths for ../) -->
    <link rel="stylesheet" href="../styles.css"> 
    
    <!-- 2. ICONS & FONTS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

    <!-- 3. PAGE SPECIFIC CSS -->
    <style>
        .dashboard-wrapper { max-width: 1200px; margin: 40px auto; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        
        /* Alerts */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 5px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 5px solid #ef4444; }

        /* Form Styling */
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; border-top: 4px solid #3b82f6; }
        .form-title { margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #eee; margin-bottom: 20px; color: #2c3e50; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #4b5563; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; transition: 0.3s; box-sizing: border-box; }
        .form-group input:focus { border-color: #3b82f6; outline: none; }
        
        .btn-submit { background: #3b82f6; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 1rem; }
        .btn-cancel { background: #ef4444; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; margin-left: 10px; font-weight: 600; display: inline-block; }

        /* Table Styling */
        .table-container { background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #1e293b; color: white; text-align: left; padding: 15px; }
        td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; color: #334155; }
        
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }
        
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .active-badge { background: #dcfce7; color: #166534; }
        .blocked-badge { background: #fee2e2; color: #991b1b; }

        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; color: white; margin-right: 5px; text-decoration: none; transition: 0.2s; }
        .btn-edit { background: #f59e0b; }
        .btn-lock { background: #f97316; }
        .btn-unlock { background: #10b981; }
        .btn-del { background: #ef4444; }
        .action-btn:hover { transform: translateY(-2px); opacity: 0.9; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .back-link { background: #64748b; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>

<body>

    <!-- ========================================= -->
    <!-- PUBLIC HEADER INTEGRATION (From index.php) -->
    <!-- ========================================= -->

    <div id="preloader">
        <div class="preloaderContent"><div class="preloaderText">B.Sc IT</div></div>
    </div>

    <div class="colorFiller"> </div>

    <div class="notificationBar">
        <div class="notificationTrack">
            <?php foreach ($announcements as $announcement): ?>
            <div class="notificationItem"><?php echo htmlspecialchars($announcement['short_text']); ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="header2">
        <div class="cocas">
            <div class="cocasLogo">
                <a href="../index.php"><img src="../images/Logo_of_College_of_Commerce,_Arts_and_Science.webp" alt="College Logo"></a>
            </div>
            <div class="cocasName">
                <h2>College of Commerce, Arts and Science, Patna</h2>
                <p>Department of B.Sc. IT (Admin Panel)</p>
            </div>
        </div>
    </div>

    <nav class="navbar">
        <div class="logo"><a href="../index.php"><h4>B.Sc. IT</h4></a></div>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li class="dropdown"><a href="#" class="drop-btn">About Us ▾</a>
                <ul class="dropdown-menu">
                    <li><a href="../history.php">Our History</a></li>
                    <li><a href="../faculties.php">Our Faculties</a></li>
                    <li><a href="../contactUs.php">Contact Us</a></li>
                </ul>
            </li>
            <li class="dropdown"><a href="#" class="drop-btn">Academics ▾</a>
                <ul class="dropdown-menu">
                    <li><a href="../academics.php">Syllabus</a></li>
                    <li><a href="../study_materials.php">Study Material</a></li> 
                    <li><a href="../examination.php">Question Bank</a></li> 
                </ul>
            </li>
            <li><a href="../placedstudent.php">Placed Student</a></li>
            <li><a href="../gallery.php">Gallery</a></li>
            
            <li class="dropdown login-link">
                <a href="#" class="drop-btn" style="color: #ffcccb;">
                    <i class="fas fa-user-shield"></i> Admin (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>) ▾
                </a> 
                <ul class="dropdown-menu">
                    <li><a href="index.php">Dashboard Home</a></li>
                    <li><a href="?logout=true" style="color: red;">Logout</a></li> 
                </ul>
            </li>
        </ul>
        <div class="hamBurger"><span></span><span></span><span></span></div>
    </nav>
    
    <!-- ========================================= -->
    <!-- MAIN ADMIN CONTENT -->
    <!-- ========================================= -->
    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1>Faculty Management</h1>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <?php if ($msg_text): ?>
            <div class="alert alert-<?php echo $msg_type; ?>">
                <?php echo htmlspecialchars($msg_text); ?>
            </div>
        <?php endif; ?>

        <!-- 1. ADD / EDIT FORM -->
        <div class="form-card" id="formSection">
            <h3 class="form-title">
                <?php echo $faculty_to_edit ? 'Edit Faculty: ' . htmlspecialchars($faculty_to_edit['name']) : 'Add New Faculty Member'; ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="faculty_id" value="<?php echo $faculty_to_edit['faculty_id'] ?? ''; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($faculty_to_edit['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" name="designation" value="<?php echo htmlspecialchars($faculty_to_edit['designation'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" value="<?php echo htmlspecialchars($faculty_to_edit['subject'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo htmlspecialchars($faculty_to_edit['sort_order'] ?? '99'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($faculty_to_edit['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($faculty_to_edit['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>About</label>
                    <textarea name="about" rows="3"><?php echo htmlspecialchars($faculty_to_edit['about'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="is_coordinator" style="width: auto;" <?php echo (isset($faculty_to_edit['is_coordinator']) && $faculty_to_edit['is_coordinator']) ? 'checked' : ''; ?>>
                        Set as Coordinator?
                    </label>
                </div>

                <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Image</label>
                        <?php if (isset($faculty_to_edit['image_url']) && $faculty_to_edit['image_url']): ?>
                            <img src="../<?php echo htmlspecialchars($faculty_to_edit['image_url']); ?>" height="40" style="vertical-align: middle; margin-bottom: 5px;">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($faculty_to_edit['image_url']); ?>">
                        <?php endif; ?>
                        <input type="file" name="image_url" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>CV (PDF)</label>
                        <?php if (isset($faculty_to_edit['cv_url']) && $faculty_to_edit['cv_url']): ?>
                            <small style="color: blue;">CV Exists</small>
                            <input type="hidden" name="existing_cv" value="<?php echo htmlspecialchars($faculty_to_edit['cv_url']); ?>">
                        <?php endif; ?>
                        <input type="file" name="cv_url" accept="application/pdf">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" name="submit_faculty" class="btn-submit">
                        <?php echo $faculty_to_edit ? 'Update Faculty' : 'Save Faculty'; ?>
                    </button>
                    <?php if ($faculty_to_edit): ?>
                        <a href="<?php echo $current_page; ?>" class="btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 2. DATA TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Faculty</th>
                        <th>Contact</th>
                        <th>Role/Sub</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_faculty)): ?>
                        <tr><td colspan="5" style="text-align:center;">No faculty members found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_faculty as $f): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <img src="<?php echo !empty($f['image_url']) ? "../" . $f['image_url'] : "../images/default_user.png"; ?>" class="user-img">
                                    <div>
                                        <strong><?php echo htmlspecialchars($f['name']); ?></strong><br>
                                        <small>ID: <?php echo $f['faculty_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($f['email']); ?></small><br>
                                <small><?php echo htmlspecialchars($f['phone']); ?></small>
                            </td>
                            <td>
                                <?php if ($f['is_coordinator']): ?><span style="color:#d97706; font-weight:bold;">Coordinator</span><br><?php endif; ?>
                                <?php echo htmlspecialchars($f['subject']); ?>
                            </td>
                            <td>
                                <?php if ($f['is_active']): ?>
                                    <span class="status-badge active-badge">Active</span>
                                <?php else: ?>
                                    <span class="status-badge blocked-badge">Blocked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit_id=<?php echo $f['faculty_id']; ?>#formSection" class="action-btn btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                
                                <?php if ($f['is_active']): ?>
                                    <a href="?action=block&id=<?php echo $f['faculty_id']; ?>" class="action-btn btn-lock" title="Block" onclick="return confirm('Block User?');"><i class="fas fa-user-lock"></i></a>
                                <?php else: ?>
                                    <a href="?action=unblock&id=<?php echo $f['faculty_id']; ?>" class="action-btn btn-unlock" title="Unblock" onclick="return confirm('Activate User?');"><i class="fas fa-unlock"></i></a>
                                <?php endif; ?>

                                <a href="?delete_id=<?php echo $f['faculty_id']; ?>" class="action-btn btn-del" title="Delete" onclick="return confirm('Delete permanently?');"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================= -->
    <!-- FOOTER INTEGRATION (From index.php) -->
    <!-- ========================================= -->
    <?php
    // Fetch Footer Settings (Using same logic as dashboard)
    $contact_phone = (function_exists('get_setting')) ? get_setting('contact_phone') : 'N/A';
    $contact_email_main = (function_exists('get_setting')) ? get_setting('contact_email_main') : 'N/A';
    $contact_address = (function_exists('get_setting')) ? get_setting('contact_address') : 'Patna, Bihar';
    $current_year = (function_exists('get_setting')) ? get_setting('footer_copyright_year') : date('Y');
    ?>

    <div class="mainFooter">
        <div class="footerBox">
            <div class="firstBox">
                <p id="footerHeading">Social Media</p>
                <ul id="footerIcon">
                    <li><a href="" title="Instagram" target="_blank" id="instagram"><i class='bx bxl-instagram-alt'></i></a></li>
                    <li><a href="" title="Facebook" target="_blank" id="facebook"><i class='bx bxl-facebook-circle'></i></a></li>
                    <li><a href="" title="Youtube" target="_blank" id="youtube"><i class='bx bxl-youtube'></i></a></li>
                    <li><a href="" title="Linkedin" target="_blank" id="linkedin"><i class='bx bxl-linkedin-square'></i></a></li>
                </ul>
            </div>

            <div class="secondBox">
                <p id="footerHeading">Contact</p>
                <ul>
                    <li>
                        <a href="https://www.google.com/maps" target="_blank" class="footercontactIcon">
                            <i class='bx bx-current-location bx-flip-vertical bx-tada' style='color:#bd232a'></i>
                            <?php echo htmlspecialchars($contact_address); ?>
                        </a>
                    </li>
                    <li>
                        <a href="tel:<?php echo htmlspecialchars($contact_phone); ?>" class="footercontactIcon">
                            <i class='bx bxs-phone-call bx-flip-vertical bx-tada' style='color:#1eade1'></i>
                            <?php echo htmlspecialchars($contact_phone); ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:<?php echo htmlspecialchars($contact_email_main); ?>" class="footercontactIcon">
                            <i class='bx bxl-gmail bx-tada' style='color:#e11e29'></i>
                            <?php echo htmlspecialchars($contact_email_main); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="thirdBox">
                <p id="footerHeading">Quick Links</p>
                <a href="../index.php">Home</a>
                <a href="../history.php#scrollTohistory">About Us</a>
                <a href="../academics.php#scrollToacademics">Syllabus</a>
                <a href="../gallery.php#scrollTogallery">Gallery</a>
                <a href="../contactUs.php">Contact Us</a>
                <a href="../contactUs.php#callBackBtn">Feedback</a>
            </div>

            <div class="fourthBox">
                <p id="footerHeading">Navigate</p>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3804.251600603494!2d85.1593298756202!3d25.60126097745311!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ed5f3562555553%3A0x9c0c5f0fd6bfd704!2sCollege%20of%20Commerce%2C%20Arts%20and%20Science!5e1!3m2!1sen!2sin!4v1739352710239!5m2!1sen!2sin" width="100%" height="70%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
    <div class="line"></div>
    <div class="belowFooter">
        <p>© <?php echo $current_year; ?> B.Sc. IT Department. All rights reserved.</p>
        <div class="credit">
            <p>Designed & Developed by <a href="https://www.linkedin.com/in/rajkamal-kumar-singh/" target="_blank">Rajkamal</a></p>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const hamBurger = document.querySelector(".hamBurger");
        const navLinks = document.querySelector(".nav-links");

        hamBurger.addEventListener("click", () => {
            navLinks.classList.toggle("active");
            hamBurger.classList.toggle("active");
        });

        window.addEventListener('load', function() {
            var preloader = document.getElementById('preloader');
            if(preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
    <script src="../script.js?v=<?php echo time(); ?>"></script>
</body>
</html>