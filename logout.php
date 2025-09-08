<?php
// Start session and set security headers
session_start();
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Verify CSRF token first
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("CSRF token missing");
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    die("Invalid CSRF token");
}

// Determine user type and set redirect URL
$redirect_url = '/cdsms/login.php'; // Default redirect
$user_type = 'guest';

if (isset($_SESSION['admin_id'])) {
    $user_type = 'admin';
    $redirect_url = '/cdsms/admin/login.php';
} elseif (isset($_SESSION['instructor_id'])) {
    $user_type = 'instructor';
    $redirect_url = '/cdsms/instructor/instructorlogin.php';
} elseif (isset($_SESSION['user_id'])) {
    $user_type = 'user';
    $redirect_url = '/cdsms/user/userlogin.php';
}

// Generate new CSRF token before destroying session
$new_csrf_token = bin2hex(random_bytes(32));

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set new CSRF token for login page (without logout message)
setcookie(
    'csrf_token',
    $new_csrf_token,
    time() + 3600,
    '/',
    '',
    true,  // Secure flag
    true   // HttpOnly flag
);

// Redirect to appropriate login page without logout parameter
header("Location: $redirect_url");
exit;
?>