<?php
require_once('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// Start session and set security headers

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Initialize CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check for logout message
if (isset($_GET['logout']) && isset($_COOKIE['logout_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">'
        . htmlspecialchars($_COOKIE['logout_message'])
        . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        . '</div>';
    
    // Clear the message
    setcookie('logout_message', '', time() - 3600, '/', '', true, true);
}

// Use CSRF token from cookie if available
if (isset($_COOKIE['csrf_token']) && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = $_COOKIE['csrf_token'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;

            // Check if user has an enrollment record
            $enrollment_check = $conn->prepare("SELECT id FROM enrollees WHERE user_id = ?");
            $enrollment_check->bind_param("i", $id);
            $enrollment_check->execute();
            $enrollment_check->store_result();

            if ($enrollment_check->num_rows > 0) {
                // User has an enrollment, redirect to dashboard
                header("Location: index.php");
            } else {
                // User has no enrollment, redirect to enroll page
                header("Location: enroll.php");
            }
            $enrollment_check->close();
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
        }
    } else {
        $_SESSION['error'] = "Invalid email or password.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Indian Driving School</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background: #d0f0fd;
        font-family: 'Poppins', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .login-container {
        background: #fff;
        border-radius: 12px;
        padding: 40px 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 380px;
    }
    .login-container h2 {
        text-align: center;
        color: #00796b;
        margin-bottom: 25px;
        font-weight: 600;
    }
    .form-control {
        border-radius: 8px;
        padding: 10px;
        font-size: 15px;
    }
    .btn-custom {
        background-color: #00796b;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px;
        width: 100%;
        font-size: 16px;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    .btn-custom:hover {
        background-color: #005a4f;
    }
    .forgot-password {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
    }
    .forgot-password a {
        color: #00796b;
        text-decoration: none;
    }
    .forgot-password a:hover {
        text-decoration: underline;
    }
    .alert-danger {
        background-color: #ffe6e6;
        color: #d32f2f;
        border-radius: 8px;
        padding: 10px;
        font-size: 14px;
        text-align: center;
        margin-bottom: 20px;
    }
    
  </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="userlogin.php" method="POST">
        <div class="mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email Address" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-custom">Login</button>
    </form>

    <div class="forgot-password">
        <p>Don't have an account? <a href="usersignup.php"><b>Sign up here</b></a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
        <p><a href="../index.php" style="color:blue;">Back to Home</a></p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>