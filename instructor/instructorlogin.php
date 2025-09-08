<?php
require_once('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



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
    $stmt = $conn->prepare("SELECT id, firstname, lastname, password FROM instructor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $firstname, $lastname, $hashed_password);
        $stmt->fetch();

        // Verify password - works with both MD5 and password_hash() formats
        if (password_verify($password, $hashed_password) || md5($password) === $hashed_password) {
            // If password was stored as MD5, update to password_hash
            if (md5($password) === $hashed_password) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE instructor SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hash, $id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            session_regenerate_id(true);
            $_SESSION['instructor_id'] = $id;
            $_SESSION['instructor_name'] = $firstname . ' ' . $lastname;
            header("Location: /cdsms/instructor/index.php");
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Login - Indian Driving School</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }
    
    .login-box {
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 25px;
    }
    
    .login-header h2 {
      color: #4361ee;
      margin-bottom: 10px;
      font-weight: 600;
    }
    
    .login-header p {
      color: #6c757d;
      font-size: 14px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-control {
      border-radius: 8px;
      padding: 12px;
      font-size: 15px;
      border: 1px solid #ddd;
    }
    
    .form-control:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
    }
    
    .btn-login {
      background-color: #4361ee;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px;
      width: 100%;
      font-size: 16px;
      font-weight: 600;
      margin-top: 10px;
    }
    
    .btn-login:hover {
      background-color: #3a56d4;
    }
    
    .login-links {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }
    
    .login-links a {
      color: #4361ee;
      text-decoration: none;
      margin: 0 10px;
    }
    
    .login-links a:hover {
      text-decoration: underline;
    }
    
    .alert {
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .alert-danger {
      background-color: #fee;
      color: #d32f2f;
      border: 1px solid #f5c6cb;
    }
    
    .icon {
      margin-right: 8px;
    }
  </style>
</head>
<body>

<div class="login-box">
  <div class="login-header">
    <i class="fas fa-car-alt" style="font-size: 2rem; color: #4361ee;"></i>
    <h2>Instructor Login</h2>
  </div>

  <?php
  if (isset($_SESSION['error'])) {
      echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle icon"></i>' . $_SESSION['error'] . '</div>';
      unset($_SESSION['error']);
  }
  ?>

  <form action="instructorlogin.php" method="POST">
    <div class="form-group">
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
      </div>
    </div>
    
    <div class="form-group">
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
      </div>
    </div>
    
    <button type="submit" class="btn btn-login">
      <i class="fas fa-sign-in-alt icon"></i>Sign In
    </button>
    
    <div class="login-links">
      <a href="forgot_password.php"><i class="fas fa-key icon"></i>Forgot Password?</a>
      <a href="../index.php"><i class="fas fa-home icon"></i>Back to Home</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>