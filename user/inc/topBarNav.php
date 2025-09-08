<?php
require_once('../config.php');

// Session check
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$qry = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $qry->fetch_assoc();

$user_name = ucwords($user['name']);
?>

<style>
  .btn-rounded {
    border-radius: 50px;
  }
</style>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-light border border-dark border-top-0 border-left-0 border-right-0 navbar-light text-sm shadow-sm">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="userdashboard.php" class="nav-link"><b>
        <?php echo (!isMobileDevice()) ? $_settings->info('name') : $_settings->info('short_name'); ?> - User
      </b></a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <div class="btn-group nav-link">
        <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
          <span class="ml-3"><?php echo $user_name; ?></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right" role="menu">
          <a class="dropdown-item" href="index.php?page=userprofile"><span class="fa fa-user"></span> My Account</a>
          <div class="dropdown-divider"></div>
                   <a href="/cdsms/logout.php?token=<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" 
   class="dropdown-item logout-item">
   <i class="fas fa-sign-out-alt mr-2"></i>Logout
</a>

<script>
// Alternative confirmation method
document.querySelectorAll('.logout-item').forEach(item => {
    item.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to logout?')) {
            e.preventDefault();
        }
    });
});
</script>
        </div>
      </div>
    </li>
  </ul>
</nav>
