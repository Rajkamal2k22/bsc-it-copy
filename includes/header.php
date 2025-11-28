<?php
// Note: This file must be included AFTER session_start() in the main file
// But for standalone functionality (like index.php), session_start() might be needed here too.
// We assume session_start() is handled by the including file if needed.

// Database connection aur functions ko load karein.
// Assuming db_connect.php is located one level up from 'includes/'
require_once __DIR__ . '/../db_connect.php';

// Fetch dynamic announcements for the scrolling bar
$announcements_result = $conn->query("SELECT short_text, full_message FROM announcements WHERE is_active = 1 LIMIT 3");
$announcements = $announcements_result->fetch_all(MYSQLI_ASSOC);

// Fetch dynamic contact settings (used in the header title/meta if needed)
// Assuming get_setting() is defined in db_connect.php
$contact_phone = get_setting('contact_phone');
$contact_email_main = get_setting('contact_email_main');
$contact_address = get_setting('contact_address');

// Determine current user status for Navbar links
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? '';
$user_name = $_SESSION['user_name'] ?? ''; // Assuming user name is stored in session
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <!-- $page_title must be set in the file that includes this header -->
    <title><?php echo $page_title ?? 'B.Sc. IT - College of Commerce, Patna | PPU'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Official website of the Department of B.Sc. IT at College of Commerce, Arts and Science, Patna (PPU).'; ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    </head>

<body>
    <div id="preloader">
        <div class="preloaderContent">
            <div class="preloaderText">B.Sc IT</div>
        </div>
    </div>

    <div class="colorFiller"> </div>
    <div class="notificationBar">
        <div class="notificationTrack">
            <?php foreach ($announcements as $announcement): ?>
            <div class="notificationItem" data-full="<?php echo htmlspecialchars($announcement['full_message']); ?>">
                <?php echo htmlspecialchars($announcement['short_text']); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="header2">
        <div class="cocas">
            <div class="cocasLogo">
                <a href="index.php"><img src="./images/Logo_of_College_of_Commerce,_Arts_and_Science.webp"
                        alt="Logo of College of Commerce, Arts and Science, Patna" .>
                </a>
            </div>
            <div class="cocasName">
                <h2>College of Commerce, Arts and Science, Patna</h2>
                <p>Department of B.Sc. IT</p>
            </div>
        </div>
    </div>

    <nav class="navbar">
        <div class="logo">
            <a href="index.php">
                <h4>B.Sc. IT</h4>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li class="dropdown">
                <a href="#" id="about" class="drop-btn">About Us ▾</a>
                <ul class="dropdown-menu">
                    <li><a href="history.php#scrollTohistory">Our History</a></li>
                    <li><a href="faculties.php#scrollTofaculties"> Our Faculties</a></li>
                    <li><a href="contactUs.php">Contact Us</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" id="Academics" class="drop-btn">Academics ▾</a>
                <ul class="dropdown-menu">
                    <li><a href="academics.php#scrollToacademics">Syllabus</a></li>
                    <li><a href="study_materials.php">Study Material</a></li> 
                    <li><a href="examination.php#scrollToExamination">Question Bank</a></li> 
                </ul>
            </li>

            <li><a href="placedstudent.php#scrollToPlacedstu">Placed Student</a></li>
            <li><a href="gallery.php#scrollTogallery">Gallery</a></li>
            
            <?php if ($is_logged_in): ?>
                <!-- If Logged In: Show Dashboard/Logout Link -->
                <li class="dropdown login-link">
                    <a href="#" id="userProfile" class="drop-btn">
                        <?php echo htmlspecialchars($user_name); ?> (<?php echo strtoupper(substr($user_role, 0, 1)); ?>) ▾
                    </a> 
                    <ul class="dropdown-menu">
                        <?php if ($user_role == 'faculty'): ?>
                            <li><a href="faculty_dashboard.php">Faculty Dashboard</a></li>
                        <?php endif; ?>
                        <!-- Logout link works for all roles -->
                        <li><a href="logout.php">Logout</a></li> 
                    </ul>
                </li>
            <?php else: ?>
                <!-- If NOT Logged In: Show Login Panel -->
                <li class="dropdown login-link">
                    <a href="#" id="loginPanel" class="drop-btn">Login Panel ▾</a> 
                    <ul class="dropdown-menu">
                        <li><a href="student_login.php">Student Login</a></li>
                        <li><a href="faculty_login.php">Faculty Login</a></li>
                        <li><a href="admin/index.php">Departmental Login</a></li> 
                    </ul>
                </li>
            <?php endif; ?>
            </ul>

        <div class="hamBurger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
    <!-- End of includes/header.php -->