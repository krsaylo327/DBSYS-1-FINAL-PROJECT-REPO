<?php
// logout.php - Destroy session and logout user

// Start session
session_start();

// Get clinic name before destroying session
$clinic = isset($_SESSION['clinic_name']) ? $_SESSION['clinic_name'] : 'Unknown';
$name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Unknown';

// Log logout attempt
$log_message = date('Y-m-d H:i:s') . " - LOGOUT - Clinic: " . $clinic . " - User: " . $name . "\n";
file_put_contents('login_logs.txt', $log_message, FILE_APPEND);

// Destroy all session data
session_destroy();

// Redirect to login page
header("Location: login.html?error=logged_out");
exit();
?>