<?php
// ============================================================
//  logout.php — Session Termination
//  Campus Lost and Found Registry
// ============================================================
require_once 'db_connect.php';

// Remove all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// Redirect to login
header('Location: login.php');
exit;