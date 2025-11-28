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
    $url = "faculty_login.php?message=" . urlencode($message) . "&status=" . urlencode($status) . "&mode=" . urlencode($mode);
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
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['phone']) || empty($_POST['subject'])) {
        redirect_with_message("Please fill in all required fields (Name, Email, Phone, Subject, Password).", "error", 'register');
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        redirect_with_message("Passwords do not match.", "error", 'register');
    }

    // Securely hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // --- Check for existing profile by email ---
    $check_stmt = $conn->prepare("SELECT faculty_id, password_hash FROM faculty WHERE email = ?");
    
    if ($check_stmt === false) {
         redirect_with_message("Database error (C1): Could not prepare statement. Contact IT support.", "error", 'register');
    }
    
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    $check_stmt->bind_result($faculty_id, $existing_hash);
    $check_stmt->fetch();

    if ($check_stmt->num_rows > 0) {
        // CASE A: Profile exists
        if (!empty($existing_hash)) {
            // Already registered and has a password
            redirect_with_message("This email is already registered with a password. Please log in.", "error", 'login');
        } else {
            // Profile exists but password_hash is NULL (first time setting up login)
            // UPDATE the existing record with the password and other provided details
            $update_stmt = $conn->prepare("UPDATE faculty SET name = ?, phone = ?, subject = ?, password_hash = ?, is_active = 1 WHERE faculty_id = ?");
            
            if ($update_stmt === false) {
                 redirect_with_message("Database error (U1): Could not prepare update statement. Contact IT support.", "error", 'register');
            }
            
            // Note: Setting is_active = 1 upon registration update
            if (!$update_stmt->bind_param("ssssi", $name, $phone, $subject, $password_hash, $faculty_id)) {
                 redirect_with_message("Database error (U2): Bind parameter failed. Contact IT support.", "error", 'register');
            }
            
            if ($update_stmt->execute()) {
                redirect_with_message("Profile updated and login details set! You can now log in.", "success", 'login');
            } else {
                error_log("Faculty registration update failed: " . $update_stmt->error);
                redirect_with_message("Registration update failed. Please try again.", "error", 'register');
            }
            $update_stmt->close();
        }
    } else {
        // CASE B: New Faculty (Insert new row)
        // Note: is_active column defaults to 1 in the database, ensuring new users are active.
        $insert_stmt = $conn->prepare("INSERT INTO faculty (name, email, phone, subject, password_hash) VALUES (?, ?, ?, ?, ?)");
        
        if ($insert_stmt === false) {
             redirect_with_message("Database error (I1): Could not prepare insert statement. Contact IT support.", "error", 'register');
        }
        
        if (!$insert_stmt->bind_param("sssss", $name, $email, $phone, $subject, $password_hash)) {
             redirect_with_message("Database error (I2): Bind parameter failed. Contact IT support.", "error", 'register');
        }

        if ($insert_stmt->execute()) {
            redirect_with_message("Registration successful! You can now log in.", "success", 'login');
        } else {
            error_log("Faculty registration failed: " . $insert_stmt->error);
            if ($conn->errno == 1062) { 
                 redirect_with_message("Registration failed: Email address is already in use.", "error", 'register');
            }
            redirect_with_message("Registration failed. Please try again. Error: " . $conn->error, "error", 'register');
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
} 
// --------------------------------------------------
// 2. LOGIN LOGIC
// --------------------------------------------------
elseif ($action === 'login') {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        redirect_with_message("Please enter both email and password.", "error");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Retrieve faculty details, INCLUDING is_active STATUS
    $stmt = $conn->prepare("SELECT faculty_id, name, password_hash, is_active FROM faculty WHERE email = ?");
    
    if ($stmt === false) {
         redirect_with_message("Database error (L1): Could not prepare statement. Contact IT support.", "error");
    }
    
    if (!$stmt->bind_param("s", $email)) {
         redirect_with_message("Database error (L2): Bind parameter failed. Contact IT support.", "error");
    }
    
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password, $is_active);
        $stmt->fetch();

        if (empty($hashed_password)) {
             redirect_with_message("Your profile exists, but you need to register a password first. Please use the Register tab.", "error", 'register');
        }
        
        // 🚨 SECURITY CHECK 1: Verify the password hash
        if (password_verify($password, $hashed_password)) {
            
            // 🚨 SECURITY CHECK 2: Check if the faculty is active (not blocked by admin)
            if ($is_active == 0) {
                redirect_with_message("Your account has been temporarily suspended by the administrator.", "error");
            }

            // Success: Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'faculty'; 

            // Redirect to dashboard
            header("Location: faculty_dashboard.php");
            exit();

        } else {
            // Failure: Password mismatch
            redirect_with_message("Login failed: Invalid email or password.", "error");
        }
    } else {
        // Failure: User not found
        redirect_with_message("Login failed: Invalid email or password.", "error");
    }

    $stmt->close();
} 
else {
    redirect_with_message("Unknown action requested.", "error");
}

$conn->close();
?>