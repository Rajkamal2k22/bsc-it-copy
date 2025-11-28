<?php
session_start();

// 🚨 सुरक्षा सुधार: सुनिश्चित करें कि नए लॉगिन से पहले पुराना सत्र साफ़ हो जाए
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start(); // एक नया सत्र शुरू करें
}

require_once __DIR__ . '/db_connect.php'; 

// Function to safely redirect with a message
function redirect_with_message($message, $status, $mode = 'login') {
    $url = "student_login.php?message=" . urlencode($message) . "&status=" . urlencode($status) . "&mode=" . urlencode($mode);
    header("Location: $url");
    exit();
}

if (!isset($_POST['action'])) {
    redirect_with_message("Invalid request.", "error");
}

$action = $_POST['action'];

// --------------------------------------------------
// 1. REGISTRATION LOGIC
// --------------------------------------------------
if ($action === 'register') {
    if (empty($_POST['name']) || empty($_POST['reg_number']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
        redirect_with_message("Please fill in all required fields.", "error", 'register');
    }

    $name = trim($_POST['name']);
    $reg_number = trim($_POST['reg_number']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password validation: Must be exactly the last 6 digits of the reg_number
    $expected_password_part = substr($reg_number, -6);

    if ($password !== $confirm_password) {
        redirect_with_message("Passwords do not match.", "error", 'register');
    }
    
    if (strlen($password) !== 6 || $password !== $expected_password_part) {
        redirect_with_message("Password must be the last 6 digits of your Registration Number.", "error", 'register');
    }

    // Check if registration number already exists
    $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE reg_number = ?");
    $check_stmt->bind_param("s", $reg_number);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        redirect_with_message("This Registration Number is already registered. Please log in.", "error", 'login');
    }
    $check_stmt->close();

    // Securely hash the password (last 6 digits)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new student (is_active defaults to 1 in the database)
    $stmt = $conn->prepare("INSERT INTO students (name, reg_number, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
         error_log("Student Reg Prep Failed: " . $conn->error);
         redirect_with_message("Database error. Please try again.", "error", 'register');
    }
    
    $stmt->bind_param("sssss", $name, $reg_number, $email, $phone, $password_hash);

    if ($stmt->execute()) {
        redirect_with_message("Registration successful! You can now log in.", "success", 'login');
    } else {
        error_log("Student Registration Failed: " . $stmt->error);
        redirect_with_message("Registration failed. Database error.", "error", 'register');
    }

    $stmt->close();
} 

// --------------------------------------------------
// 2. LOGIN LOGIC
// --------------------------------------------------
elseif ($action === 'login') {
    if (empty($_POST['reg_number']) || empty($_POST['password'])) {
        redirect_with_message("Please enter Registration Number and Password.", "error");
    }

    $reg_number = trim($_POST['reg_number']);
    $password = $_POST['password']; // Last 6 digits

    // Retrieve student details including is_active status
    $stmt = $conn->prepare("SELECT student_id, name, password_hash, is_active FROM students WHERE reg_number = ?");
    
    if ($stmt === false) {
         redirect_with_message("Database error. Please try again.", "error");
    }
    
    $stmt->bind_param("s", $reg_number);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password, $is_active);
        $stmt->fetch();

        // 🚨 SECURITY CHECK 1: Verify the password hash
        if (password_verify($password, $hashed_password)) {
            
            // 🚨 SECURITY CHECK 2: Check if the student is active (Blocked by admin)
            if ($is_active == 0) {
                redirect_with_message("Your account has been blocked by the administrator.", "error");
            }

            // Success: Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'student'; 
            $_SESSION['reg_number'] = $reg_number;

            // Redirect to the protected materials page (or saved redirect URL)
            $redirect_url = $_SESSION['redirect_to'] ?? 'study_materials.php';
            unset($_SESSION['redirect_to']); 

            header("Location: $redirect_url");
            exit();
        } else {
            redirect_with_message("Login failed: Invalid Registration Number or Password.", "error");
        }
    } else {
        redirect_with_message("Login failed: Registration Number not found.", "error");
    }

    $stmt->close();
} 

$conn->close();
?>