<?php
require_once('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: userlogin.php');
    exit();
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();
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
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="userdashboard.php" class="nav-link"><b>Student Portal</b></a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <div class="btn-group nav-link">
                    <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
                        <span>
                            <img src="<?php echo validate_image($user['avatar']) ?>" class="img-circle elevation-2 user-img" alt="User Image">
                        </span>
                        <span class="ml-3"><?php echo ucwords($user['name']); ?></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="student_account.php"><span class="fa fa-user"></span> My Account</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><span class="fas fa-sign-out-alt"></span> Logout</a>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Content -->
    <div class="content-wrapper p-3">
        <section class="content">
            <div class="container-fluid">
                <h2>Welcome, <?php echo ucwords($user['name']); ?>!</h2>
                <p>This is your student dashboard.</p>

                <div class="card">
                    <div class="card-body">
                        <h5>Your Email</h5>
                        <p><?php echo $user['email']; ?></p>

                        <h5>Registered On</h5>
                        <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>

                        <!-- Add more student-specific sections here -->
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>
</body>
</html>
