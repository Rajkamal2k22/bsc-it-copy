<?php
session_start();

// --- Error Reporting ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Connect to Database (Adjusted path for admin folder)
require_once __DIR__ . '/../db_connect.php';

// --- ADMIN LOGIN LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // सुरक्षा सुधार: सुनिश्चित करें कि नए एडमिन लॉगिन से पहले पुराना सत्र साफ़ हो जाए
    if (isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        session_start();
    }
    
    $username = trim($conn->real_escape_string($_POST['username']));
    $password = $_POST['password'];

    $login_error = "Invalid username or password.";

    if (defined('ADMIN_USERNAME') && $username === ADMIN_USERNAME) {
        $stmt = $conn->prepare("SELECT value FROM settings WHERE key_name = 'admin_password'");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['value'])) {
                $_SESSION['user_id'] = 99999; 
                $_SESSION['user_name'] = $username;
                $_SESSION['user_role'] = 'admin'; 
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }
}

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']); 
    exit;
}

// --- CHECK IF LOGGED IN ---
$is_admin = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);

// =========================================================
//  PROFESSIONAL LOGIN FORM (If not logged in)
// =========================================================
if (!$is_admin) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Admin Login | B.Sc. IT</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary: #2c3e50;
                --accent: #3498db;
                --bg: #f1f5f9;
                --text: #334155;
            }
            body { 
                background: var(--bg); 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                min-height: 100vh; 
                margin: 0; 
                font-family: 'Segoe UI', sans-serif; 
            }
            .login-wrapper {
                width: 100%;
                max-width: 420px;
                padding: 20px;
            }
            .login-card { 
                background: white; 
                padding: 40px 30px; 
                border-radius: 12px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
                border-top: 5px solid var(--primary);
            }
            .login-header { text-align: center; margin-bottom: 30px; }
            .login-header i { font-size: 3rem; color: var(--primary); margin-bottom: 15px; }
            .login-header h2 { margin: 0; color: var(--text); font-weight: 700; font-size: 1.8rem; }
            .login-header p { margin: 5px 0 0; color: #64748b; font-size: 0.95rem; }

            /* Input Groups */
            .input-group { position: relative; margin-bottom: 20px; }
            
            .input-group i.field-icon { 
                position: absolute; 
                left: 15px; 
                top: 50%; 
                transform: translateY(-50%); 
                color: #94a3b8; 
                font-size: 1.1rem;
                z-index: 2;
            }
            
            .input-group input { 
                width: 100%; 
                padding: 14px 15px 14px 45px; /* Left padding for icon */
                border: 2px solid #e2e8f0; 
                border-radius: 8px; 
                font-size: 1rem; 
                box-sizing: border-box; 
                transition: all 0.3s ease;
                color: var(--text);
                background: #f8fafc;
            }
            
            .input-group input:focus { 
                border-color: var(--primary); 
                background: #fff; 
                outline: none; 
                box-shadow: 0 0 0 4px rgba(44, 62, 80, 0.1);
            }

            /* Password Toggle */
            .toggle-password { 
                position: absolute; 
                right: 15px; 
                top: 50%; 
                transform: translateY(-50%); 
                cursor: pointer; 
                color: #94a3b8; 
                transition: color 0.3s;
                z-index: 3;
            }
            .toggle-password:hover { color: var(--primary); }

            /* Warning Text */
            .password-warning {
                display: flex;
                align-items: center;
                gap: 5px;
                color: #d97706; /* Amber color */
                font-size: 0.85rem;
                margin-top: -15px;
                margin-bottom: 20px;
                font-weight: 600;
            }

            /* Button */
            .btn-login { 
                width: 100%; 
                background: var(--primary); 
                color: white; 
                padding: 14px; 
                border: none; 
                border-radius: 8px; 
                font-size: 1rem; 
                font-weight: 600; 
                cursor: pointer; 
                transition: all 0.3s ease; 
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
            }
            .btn-login:hover { 
                background: #1e293b; 
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3); 
            }

            /* Error Message */
            .error-msg { 
                background: #fee2e2; 
                color: #991b1b; 
                padding: 12px; 
                border-radius: 8px; 
                margin-bottom: 20px; 
                text-align: center; 
                font-size: 0.95rem; 
                border: 1px solid #fecaca;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
        </style>
    </head>
    <body>
        <div class="login-wrapper">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-user-shield"></i>
                    <h2>Admin Panel</h2>
                    <p>Secure Access Only</p>
                </div>
                
                <?php if (isset($login_error)) { echo "<div class='error-msg'><i class='fas fa-exclamation-circle'></i> $login_error</div>"; } ?>
                
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    
                    <!-- Username Field -->
                    <div class="input-group">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" name="username" placeholder="Username" required autocomplete="off">
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password" id="adminPass" placeholder="Password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePass(this)"></i>
                    </div>

                    <!-- Case Sensitive Warning -->
                    <div class="password-warning">
                        <i class="fas fa-info-circle"></i> Note: Password is case-sensitive
                    </div>

                    <button type="submit" class="btn-login">
                        Secure Login <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <script>
            function togglePass(icon) {
                var input = document.getElementById("adminPass");
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    input.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// =========================================================
//  DASHBOARD CONTENT (Only shown if logged in)
// =========================================================

// --- FETCHING STATS ---
$total_faculty = $conn->query("SELECT COUNT(*) FROM faculty")->fetch_row()[0] ?? 0;
$total_placed_students = $conn->query("SELECT COUNT(*) FROM placed_students")->fetch_row()[0] ?? 0; 
$total_registered_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
$active_announcements = $conn->query("SELECT COUNT(*) FROM announcements WHERE is_active = 1")->fetch_row()[0] ?? 0;
$total_study_materials = $conn->query("SELECT COUNT(*) FROM study_materials")->fetch_row()[0] ?? 0; 
$total_gallery_items = $conn->query("SELECT COUNT(*) FROM gallery_items WHERE is_active = 1")->fetch_row()[0] ?? 0; 

// Fetch Announcements for the Ticker
$announcements_result = $conn->query("SELECT short_text FROM announcements WHERE is_active = 1 LIMIT 3");
$announcements = $announcements_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | B.Sc. IT</title>
    
    <!-- 1. GLOBAL STYLES (Public Website Styles) -->
    <link rel="stylesheet" href="../styles.css"> 
    
    <!-- 2. ICONS & FONTS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

    <!-- 3. DASHBOARD SPECIFIC CSS -->
    <style>
        .dashboard-wrapper { max-width: 1200px; margin: 40px auto; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card {
            background: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid #3498db;
            transition: transform 0.2s; position: relative;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2rem; font-weight: 700; color: #2c3e50; }
        .stat-label { font-size: 0.9rem; color: #7f8c8d; font-weight: 600; }
        .stat-icon { position: absolute; right: 20px; top: 20px; font-size: 2rem; color: #ecf0f1; }
        .stat-link { display: block; margin-top: 15px; color: #3498db; text-decoration: none; font-weight: 600; font-size: 0.9rem; }

        /* Quick Actions Grid */
        .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .action-btn {
            display: flex; align-items: center; gap: 15px; padding: 20px;
            background: #fff; border-radius: 8px; text-decoration: none; color: #333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; transition: 0.2s;
        }
        .action-btn:hover { border-color: #3498db; background: #f9f9f9; transform: translateX(5px); }
        .action-icon {
            width: 45px; height: 45px; background: #e0f2fe; color: #3498db;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .action-info h4 { margin: 0; font-size: 1rem; color: #2c3e50; }
        .action-info p { margin: 2px 0 0; font-size: 0.8rem; color: #94a3b8; }

        /* Color Variants */
        .border-blue { border-left-color: #3b82f6; }
        .border-green { border-left-color: #22c55e; }
        .border-orange { border-left-color: #f97316; }
        .border-purple { border-left-color: #a855f7; }
        .border-red { border-left-color: #ef4444; }

        .section-title { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #2c3e50; }
    </style>
</head>

<body>
    <!-- ========================================= -->
    <!-- PUBLIC HEADER INTEGRATION -->
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
    <!-- ADMIN DASHBOARD CONTENT -->
    <!-- ========================================= -->
    <div class="dashboard-wrapper">
        <h2 class="section-title">Admin Dashboard Overview</h2>
        
        <div class="stats-grid">
            <div class="stat-card border-blue">
                <i class="fas fa-chalkboard-teacher stat-icon"></i>
                <div class="stat-number"><?php echo $total_faculty; ?></div>
                <div class="stat-label">Faculty</div>
                <a href="faculty_manage.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="stat-card border-green">
                <i class="fas fa-award stat-icon"></i>
                <div class="stat-number"><?php echo $total_placed_students; ?></div> 
                <div class="stat-label">Placed Students</div>
                <a href="placed_students_manage.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="stat-card border-purple">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-number"><?php echo $total_registered_students; ?></div> 
                <div class="stat-label">Registered Student Users</div>
                <a href="students_manage.php" class="stat-link">Manage Users <i class="fas fa-arrow-right"></i></a>
            </div>


            <div class="stat-card border-orange">
                <i class="fas fa-bullhorn stat-icon"></i>
                <div class="stat-number"><?php echo $active_announcements; ?></div>
                <div class="stat-label">Active Notices</div>
                <a href="announcements_manage.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="stat-card border-purple">
                <i class="fas fa-book stat-icon"></i>
                <div class="stat-number"><?php echo $total_study_materials; ?></div>
                <div class="stat-label">Study Materials Uploaded</div>
                <a href="downloads_manage.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="stat-card border-red">
                <i class="fas fa-images stat-icon"></i>
                <div class="stat-number"><?php echo $total_gallery_items; ?></div>
                <div class="stat-label">Gallery Items</div>
                <a href="gallery_manage.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <h2 class="section-title">Quick Actions</h2>
        <div class="action-grid">
            <a href="home_content_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-home"></i></div>
                <div class="action-info"><h4>Homepage Content</h4><p>Edit Text & Sliders</p></div>
            </a>
            <a href="faculty_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-users-cog"></i></div>
                <div class="action-info"><h4>Manage Faculty</h4><p>Add/Edit/Delete Faculty</p></div>
            </a>
            <a href="students_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="action-info"><h4>Manage Registered Students</h4><p>View & Manage Student Users</p></div>
            </a>
            <a href="placed_students_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-award"></i></div>
                <div class="action-info"><h4>Manage Placed Students</h4><p>Update Placement Records</p></div>
            </a>
            <a href="announcements_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-scroll"></i></div>
                <div class="action-info"><h4>Announcements</h4><p>Control Scrolling Ticker</p></div>
            </a>
            <a href="downloads_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="action-info"><h4>Question Bank/Materials</h4><p>Upload Syllabus & Papers</p></div>
            </a>
            <a href="gallery_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-photo-video"></i></div>
                <div class="action-info"><h4>Photo Gallery</h4><p>Upload Event Photos</p></div>
            </a>
            <a href="settings_manage.php" class="action-btn">
                <div class="action-icon"><i class="fas fa-cogs"></i></div>
                <div class="action-info"><h4>General Settings</h4><p>Passwords & Contact Info</p></div>
            </a>
        </div>
    </div>

    <!-- ========================================= -->
    <!-- FOOTER INTEGRATION -->
    <!-- ========================================= -->
    <?php
    // Fetch Footer Settings
    $contact_phone = get_setting('contact_phone');
    $contact_email_main = get_setting('contact_email_main');
    $contact_address = get_setting('contact_address');
    $current_year = get_setting('footer_copyright_year') ?? date('Y');
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