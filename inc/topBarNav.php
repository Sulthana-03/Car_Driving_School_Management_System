<!-- Required Meta & Bootstrap -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Top Login Navbar -->
<style>
  #login-nav {
    background-color: #001f3f;
    color: white;
    padding: 0.5em 1em;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1040;
  }

  #top-Nav {
    margin-top: 3rem;
  }

  .navbar .nav-link.active {
    font-weight: bold;
    color: #007bff !important;
  }

  .navbar-brand span {
    font-weight: bold;
    font-size: 20px;
    margin-left: 5px;
  }
</style>

<nav id="login-nav" class="d-flex justify-content-between align-items-center">
  <div>
    <i class="fa fa-map-marker-alt" style="color: limegreen;"></i>
    <span class="ml-1">Ponnaiyapet, Puducherry</span>
  </div>
  <div>
    <?php if ($_settings->userdata('id') > 0): ?>
      <a href="./admin" class="text-white mx-2">Admin Panel</a>
      <span class="mx-2">Howdy, <?= $_settings->userdata('username') ?></span>
      <a href="<?= base_url . 'classes/Login.php?f=logout' ?>" class="text-white mx-2"><i class="fa fa-power-off"></i></a>
    <?php else: ?>
      <a href="./admin" class="text-white mx-2">Admin Login</a>
      <a href="instructor/instructorlogin.php" class="text-white mx-2">Instructor Login</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="top-Nav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="./">
      <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" height="40">
      <span><?= $_settings->info('short_name') ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCollapse">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a>
        </li>
        <li class="nav-item">
          <a href="packages.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : '' ?>">Package</a>
        </li>
        <li class="nav-item">
          <a href="contact.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contact</a>
        </li>
        <li class="nav-item">
          <a href="enquiry.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'enquiry.php' ? 'active' : '' ?>">Enquiry</a>
        </li>
        <li class="nav-item">
          <a href="user/userlogin.php" class="nav-link <?= basename($_SERVER['SCRIPT_NAME']) == 'userlogin.php' ? 'active' : '' ?>">User Login</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Optional: Auto-collapse navbar on mobile after link click -->
<script>
  $(document).ready(function () {
    $('.navbar-collapse a').click(function () {
      $('.navbar-collapse').collapse('hide');
    });
  });
</script>
