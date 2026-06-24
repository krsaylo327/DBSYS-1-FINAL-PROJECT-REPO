<?php
// login.php - Simple login with hardcoded clinic name and password

// Start session
session_start();

// Define your clinic name and password here
$CORRECT_CLINIC_NAME = "campUs cAre";
$CORRECT_PASSWORD = "admin123";

// Get form data
$clinic_name = isset($_POST['clinic_name']) ? trim($_POST['clinic_name']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// For testing - see what user entered (remove this after testing)
// echo "Entered Clinic Name: " . $clinic_name . "<br>";
// echo "Entered Password: " . $password . "<br>";
// echo "Correct Clinic Name: " . $CORRECT_CLINIC_NAME . "<br>";
// echo "Correct Password: " . $CORRECT_PASSWORD . "<br>";

// Validation
if (empty($clinic_name) || empty($password)) {
    header("Location: login.html?error=empty");
    exit();
}

// Check clinic name
if ($clinic_name !== $CORRECT_CLINIC_NAME) {
    // Log failed login attempt
    $log_message = date('Y-m-d H:i:s') . " - FAILED LOGIN - Clinic: " . $clinic_name . " - Wrong Clinic Name\n";
    file_put_contents('login_logs.txt', $log_message, FILE_APPEND);
    
    header("Location: login.html?error=wrong_clinic_name");
    exit();
}

// Check password
if ($password !== $CORRECT_PASSWORD) {
    // Log failed login attempt
    $log_message = date('Y-m-d H:i:s') . " - FAILED LOGIN - Clinic: " . $clinic_name . " - Wrong Password\n";
    file_put_contents('login_logs.txt', $log_message, FILE_APPEND);
    
    header("Location: login.html?error=wrong_password");
    exit();
}

// Authentication successful
// Set session variables
$_SESSION['logged_in'] = true;
$_SESSION['clinic_name'] = $clinic_name;
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'University of Antique School Clinic';
$_SESSION['user_role'] = 'Admin';
$_SESSION['login_time'] = time();

// Log successful login
$log_message = date('Y-m-d H:i:s') . " - SUCCESSFUL LOGIN - Clinic: " . $clinic_name . " - User: Admin User\n";
file_put_contents('login_logs.txt', $log_message, FILE_APPEND);

// Redirect to dashboard
header("Location: index.php");
exit();
?>