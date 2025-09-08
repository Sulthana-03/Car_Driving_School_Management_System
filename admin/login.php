<?php require_once('../config.php')?>
<?php if (isset($_SESSION['logout_success'])): ?>
<div class="alert alert-success">
    <?php echo $_SESSION['logout_success']; ?>
    <?php unset($_SESSION['logout_success']); ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <?php echo $_SESSION['error']; ?>
    <?php unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

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
 ?>







<!DOCTYPE html>

<html lang="en" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: #e6f0ff;">

<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    background: linear-gradient(135deg, #f5f9ff 0%, #e6f0ff 100%);
  }
  .login-card {
    background: #fff;
    border-radius: 12px;
    padding: 35px 30px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    text-align: center;
    border: 1px solid rgba(0,0,0,0.05);
  }
  .login-card h4 {
    margin-bottom: 20px;
    font-weight: 700;
    color: #004080; /* Updated deep blue for Login title */
  }
  .form-control {
    border-radius: 8px;
    border: 1px solid #d6e0f0;
    margin-bottom: 15px;
    padding: 12px;
    width: 100%;
    font-size: 14px;
    transition: all 0.3s;
    background-color: #f8fafc;
  }
  .form-control:focus {
    border-color: #001f3f;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 31, 63, 0.1);
    background-color: #fff;
  }
  .btn-primary {
    background-color: #004080;  /* Updated deep blue for button */
    border: none;
    border-radius: 8px;
    padding: 12px;
    width: 100%;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 0.5px;
  }
  .btn-primary:hover {
    background-color: #0059b3;  /* Slightly brighter on hover */
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 31, 63, 0.2);
  }
  .logo-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 12px;
    border: 2px solid rgba(0, 31, 63, 0.1);
  }
  .system-name {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #000000;
  }
  .go-link {
    display: block;
    margin-bottom: 15px;
    font-size: 16px;
    color: #28a745;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
  }
  .go-link:hover {
    text-decoration: underline;
    color: #218838;
  }
</style>

<div class="login-card">
  <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" class="logo-img">
  <div class="system-name"><?php echo $_settings->info('name') ?> Admin</div>
  <h4>Login</h4>
  <form id="login-frm" action="" method="post">
    <input type="text" class="form-control" name="username" placeholder="Username" required>
    <input type="password" class="form-control" name="password" placeholder="Password" required>
    <a href="<?php echo base_url ?>" class="go-link">Go to Website</a>
    <button type="submit" class="btn btn-primary">Sign In</button>
  </form>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script>
  $(document).ready(function(){
    end_loader();
  });
</script>

</body>
</html>
