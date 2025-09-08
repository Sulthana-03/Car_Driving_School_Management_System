<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Only require CSRF token if it exists in session
if (isset($_SESSION['csrf_token'])) {
    if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['csrf_token']) {
        // More user-friendly error handling
        $_SESSION['error'] = "Invalid security token. Please try logging out again.";
        header("Location: /admin/index.php");
        exit;
    }
}

// Specifically unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in']);
unset($_SESSION['userdata']); // If using this for admin auth

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

// Redirect to admin login page with success message
$_SESSION['logout_success'] = "You have been successfully logged out.";
header("Location: /cdsms/admin/login.php");
exit;
?>