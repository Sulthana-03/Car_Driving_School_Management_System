<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to user login
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'userlogin.php') {
    header("Location: userlogin.php");
    exit();
}

// If already logged in and trying to access login page again
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'userlogin.php') {
    header("Location: userdashboard.php");
    exit();
}
?>
