<?php
require_once('../config.php');

// Session check
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorlogin.php");
    exit();
}

// Fetch instructor info
$instructor_id = $_SESSION['instructor_id'];
$qry = $conn->query("SELECT * FROM instructor WHERE id = $instructor_id");
$instructor = $qry->fetch_assoc();

$instructor_name = ucwords($instructor['firstname'] . ' ' . $instructor['lastname']);

// FIXED: Proper avatar path handling
$avatar_filename = !empty($instructor['avatar']) ? trim($instructor['avatar']) : '';
$avatar_path = base_url . 'assets/default-avatar.png'; // Default fallback

if (!empty($avatar_filename)) {
    // Remove duplicate 'uploads/avatar' if exists in filename
    $cleaned_filename = str_replace('uploads/avatar/', '', $avatar_filename);
    
    // Check physical file existence
    $physical_path = '../uploads/avatar/' . $cleaned_filename;
    
    if (file_exists($physical_path)) {
        $avatar_path = base_url . 'uploads/avatar/' . $cleaned_filename;
    }
}
?>

<style>
  .user-img {
    position: absolute;
    height: 27px;
    width: 27px;
    object-fit: cover;
    left: -7%;
    top: -12%;
  }
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
      <a href="instructordashboard.php" class="nav-link"><b>
        <?php echo (!isMobileDevice()) ? $_settings->info('name') : $_settings->info('short_name'); ?> - Instructor
      </b></a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <div class="btn-group nav-link">
        <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
         <span>
            <img src="<?php echo $avatar_path; ?>" 
                 class="img-circle elevation-2 user-img" 
                 alt="User Image"
                 onerror="this.onerror=null;
                         this.src='<?php echo base_url . 'assets/default-avatar.png'; ?>';
                         this.style.display='block'">
          </span>

          <span class="ml-3"><?php echo $instructor_name; ?></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu" role="menu">
          <a class="dropdown-item" href="index.php?page=instructorprofile"><span class="fa fa-user"></span> My Account</a>
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
