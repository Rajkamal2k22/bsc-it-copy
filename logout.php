<?php
session_start();

// सुनिश्चित करें कि कोई भी मौजूदा सत्र डेटा साफ़ हो जाए
if (isset($_SESSION['user_id'])) {
    session_unset(); // सत्र के सभी वैरिएबल हटा दें
    session_destroy(); // सत्र को नष्ट कर दें
}

// सभी को छात्र लॉगिन पृष्ठ पर रीडायरेक्ट करें (या आप इसे index.php में भी बदल सकते हैं)
header("Location: student_login.php"); 
exit();
?>