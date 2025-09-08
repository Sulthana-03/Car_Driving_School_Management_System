<?php
require_once('../config.php'); // adjust path if needed
if (!isset($_SESSION['userdata'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <script src="../plugins/jquery/jquery.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
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
</head>
<body class="hold-transition layout-navbar-fixed layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-light border border-dark border-top-0 border-left-0 border-right-0 navbar-light text-sm shadow-sm">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index.php" class="nav-link"><b>Student Portal</b></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <div class="btn-group nav-link">
                    <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
                        <span>
                            <img src="<?php echo validate_image($_settings->userdata('avatar')) ?>" class="img-circle elevation-2 user-img" alt="User Image">
                        </span>
                        <span class="ml-3"><?php echo ucwords($_settings->userdata('firstname').' '.$_settings->userdata('lastname')) ?></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="student_account.php"><span class="fa fa-user"></span> My Account</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../classes/Login.php?f=logout"><span class="fas fa-sign-out-alt"></span> Logout</a>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Content Wrapper -->
    <div class="content-wrapper p-3">
        <section class="content">
            <div class="container-fluid">
                <h2>Welcome, <?php echo ucwords($_settings->userdata('firstname')) ?>!</h2>
                <p>This is your student dashboard.</p>

                <!-- Add student-specific content here -->
                <div class="card">
                    <div class="card-body">
                        <h5>Your Courses</h5>
                        <p>(Display enrolled courses or progress here.)</p>
                    </div>
                </div>

            </div>
        </section>
    </div>
    <!-- /.content-wrapper -->

</div>
</body>
</html>
