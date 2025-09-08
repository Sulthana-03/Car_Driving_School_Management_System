<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to instructor login
if (!isset($_SESSION['instructor_id']) && basename($_SERVER['PHP_SELF']) !== 'instructorlogin.php') {
    header("Location: instructorlogin.php");
    exit();
}

// If already logged in and trying to access login page again
if (isset($_SESSION['instructor_id']) && basename($_SERVER['PHP_SELF']) === 'instructorlogin.php') {
    header("Location: instructordashboard.php");
    exit();
}
?>
