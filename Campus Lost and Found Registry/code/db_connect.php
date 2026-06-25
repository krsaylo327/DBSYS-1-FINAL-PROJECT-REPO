<?php
// ============================================================
//  db_connect.php — MySQLi Database Connection
//  Campus Lost and Found Registry
// ============================================================
define('DB_NAME',    'campus_lost_found');
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');           // Default XAMPP password is empty
define('DB_CHARSET', 'utf8mb4');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // In production, log this error; never expose it to the user
    error_log('Database connection failed: ' . $conn->connect_error);
    die(json_encode([
        'status'  => 'error',
        'message' => 'A database connection error occurred. Please contact the administrator.'
    ]));
}

// Enforce UTF-8 encoding for all queries
if (!$conn->set_charset(DB_CHARSET)) {
    error_log('Error setting charset: ' . $conn->error);
}

// ── Session helper ────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns the currently logged-in user array, or redirects to login.
 * Usage: $user = require_login();
 */
function require_login(): array {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['user'];
}

/**
 * Checks if the current user has admin or staff role.
 */
function is_admin_or_staff(): bool {
    return isset($_SESSION['user']['role'])
        && in_array($_SESSION['user']['role'], ['admin', 'staff'], true);
}

/**
 * Sanitizes a string for safe HTML output (XSS prevention).
 */
function e($val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

/**
 * Returns a Bootstrap badge class string based on item/claim status.
 */
function status_badge(string $status): string {
    return match ($status) {
        'active'   => 'success',
        'claimed'  => 'primary',
        'archived' => 'secondary',
        'pending'  => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'lost'     => 'danger',
        'found'    => 'info',
        default    => 'secondary',
    };
}