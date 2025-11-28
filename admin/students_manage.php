<?php
session_start();

// --- Error Reporting ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. AUTHENTICATION & DATABASE ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../db_connect.php';

$page_title = 'Student User Management';
$current_page = basename($_SERVER['PHP_SELF']);
$message = '';
$status = ''; 

function setFlashMessage($type, $msg) {
    $_SESSION['admin_flash_msg'] = ['type' => $type, 'text' => $msg];
}

// ----------------------------------------------------
// 2. ACTION HANDLERS (Block/Unblock/Delete)
// ----------------------------------------------------

// --- A. BLOCK / UNBLOCK LOGIC ---
if (isset($_GET['action']) && isset($_GET['id']) && in_array($_GET['action'], ['block', 'unblock'])) {
    $sid = intval($_GET['id']);
    $new_status = ($_GET['action'] == 'block') ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE students SET is_active = ? WHERE student_id = ?");
    $stmt->bind_param("ii", $new_status, $sid);
    
    if ($stmt->execute()) {
        $msg = ($new_status == 0) ? "Student blocked. Login denied." : "Student activated. Access restored.";
        setFlashMessage('success', $msg);
    } else {
        setFlashMessage('error', "Database error updating status: " . $conn->error);
    }
    header("Location: " . $current_page);
    exit;
}

// --- B. DELETE LOGIC ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        setFlashMessage('success', "Student user deleted permanently.");
    } else {
        setFlashMessage('error', "Error deleting record: " . $conn->error);
    }
    header("Location: " . $current_page);
    exit;
}

// --- Retrieve Flash Messages ---
if (isset($_SESSION['admin_flash_msg'])) {
    $message = $_SESSION['admin_flash_msg']['text'];
    $status = $_SESSION['admin_flash_msg']['type'];
    unset($_SESSION['admin_flash_msg']);
}


// ----------------------------------------------------
// 3. RETRIEVE ALL STUDENT DETAILS
// ----------------------------------------------------
$student_list = [];
$stmt = $conn->prepare("SELECT student_id, name, reg_number, email, phone, is_active, created_at FROM students ORDER BY created_at DESC");

if ($stmt === false) {
    die("SQL Prepare Failed: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $student_list[] = $row;
}
$stmt->close();

// --- FETCH ANNOUNCEMENTS FOR HEADER ---
// This part mimics the logic needed by the header/nav bar
$announcements_result = $conn->query("SELECT short_text FROM announcements WHERE is_active = 1 LIMIT 3");
$announcements = $announcements_result ? $announcements_result->fetch_all(MYSQLI_ASSOC) : [];

// --- FETCH FOOTER SETTINGS ---
$contact_phone = get_setting('contact_phone');
$contact_email_main = get_setting('contact_email_main');
$contact_address = get_setting('contact_address');
$current_year = get_setting('footer_copyright_year') ?? date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- 1. GLOBAL STYLES (Public Website Styles) -->
    <link rel="stylesheet" href="../styles.css"> 
    
    <!-- 2. ICONS & FONTS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

    <!-- 3. DASHBOARD SPECIFIC CSS -->
    <style>
        .dashboard-wrapper { max-width: 1200px; margin: 40px auto; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        
        /* Alerts */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 5px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 5px solid #ef4444; }

        /* Layout */
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .admin-header h1 { color: #2c3e50; margin: 0; }
        .btn-back { background: #64748b; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        
        /* Table Styles */
        .table-wrapper { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; }
        th { background-color: #2c3e50; color: white; font-weight: 600; }
        tr:hover { background-color: #f8fafc; }
        
        .status-active { color: #10b981; font-weight: 600; }
        .status-blocked { color: #f97316; font-weight: 600; }

        .btn-action { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin-right: 5px; transition: 0.2s; }
        .btn-block { background-color: #f87171; color: white; }
        .btn-block:hover { background-color: #ef4444; }
        .btn-unblock { background-color: #93c5fd; color: #1e3a8a; }
        .btn-unblock:hover { background-color: #60a5fa; }
        .btn-delete { background: #ef4444; color: white; }

        .actions { display: flex; gap: 5px; }
        .actions a { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; text-decoration: none; color: white; transition: 0.2s; }
    </style>
</head>

<body>
    <!-- ========================================= -->
    <!-- PUBLIC HEADER INTEGRATION (Matching index.php style) -->
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
    <div class="admin-header">
        <h1>Student User Management</h1>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $status; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <h2>Registered Students (Total: <?php echo count($student_list); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name / Reg No.</th>
                    <th>Contact Info</th>
                    <th>Registered On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($student_list)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No students have registered yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($student_list as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                <small style="color: #64748b;"><?php echo htmlspecialchars($student['reg_number']); ?></small>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <?php if($student['email']) echo '<i class="fas fa-envelope"></i> ' . htmlspecialchars($student['email']) . '<br>'; ?>
                                    <?php if($student['phone']) echo '<i class="fas fa-phone"></i> ' . htmlspecialchars($student['phone']); ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    echo date('d M Y', strtotime($student['created_at'])) . '<br>';
                                    echo '<small style="color: #64748b;">' . date('h:i A', strtotime($student['created_at'])) . '</small>';
                                ?>
                            </td>
                            <td>
                                <?php if ($student['is_active'] == 1): ?>
                                    <span class="status-active"><i class="fas fa-check-circle"></i> Active</span>
                                <?php else: ?>
                                    <span class="status-blocked"><i class="fas fa-times-circle"></i> Blocked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($student['is_active'] == 1): ?>
                                        <a href="?action=block&id=<?php echo $student['student_id']; ?>" 
                                           class="btn-action btn-block"
                                           title="Block Login"
                                           onclick="return confirm('Are you sure you want to BLOCK access for <?php echo htmlspecialchars($student['name']); ?>?');">
                                            <i class="fas fa-user-lock"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=unblock&id=<?php echo $student['student_id']; ?>" 
                                           class="btn-action btn-unblock"
                                           title="Unblock Login"
                                           onclick="return confirm('Are you sure you want to UNBLOCK access for <?php echo htmlspecialchars($student['name']); ?>?');">
                                            <i class="fas fa-unlock"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?delete_id=<?php echo $student['student_id']; ?>" 
                                       class="btn-action btn-delete"
                                       title="Delete User"
                                       onclick="return confirm('Are you sure you want to permanently DELETE this user?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


    <!-- ========================================= -->
    <!-- FOOTER INTEGRATION -->
    <!-- ========================================= -->
    <?php
    // Fetch Footer Settings
    // Note: get_setting() must be accessible or defined here if not in db_connect.php
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
                        <a href="https://www.google.com/maps/search/?api=1&query=Opposite+Rajendra+Nagar+Terminal,+Kankarbagh+Main+Road,+Patna,+Bihar+800020" target="_blank" class="footercontactIcon">
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
                <!-- NOTE: Paths updated with ../ for admin area -->
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

    <!-- Scripts (Adjusted Path) -->
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