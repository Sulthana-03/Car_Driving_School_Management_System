<?php
require_once('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$show_success_popup = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
    } else {
        // Check if email exists in instructor table
        $check = $conn->prepare("SELECT id FROM instructor WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE instructor SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email);

            if ($update->execute()) {
                $show_success_popup = true;
            } else {
                $_SESSION['error'] = 'Failed to reset password. Please try again.';
            }
            $update->close();
        } else {
            $_SESSION['error'] = 'Email not found.';
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Instructor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f9f0ff;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background: #fff;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .reset-container h2 {
            text-align: center;
            color: #6a1b9a;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
            font-size: 15px;
        }
        .btn-custom {
            background-color: #6a1b9a;
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
            background-color: #4a0072;
        }
        .bottom-links {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .bottom-links a {
            color: #6a1b9a;
            text-decoration: none;
        }
        .bottom-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <h2>Reset Instructor Password</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form method="POST" action="forgot_password.php">
        <div class="mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email Address" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="New Password" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="btn btn-custom">Reset Password</button>
    </form>

    <div class="bottom-links">
        <p><a href="../index.php" style="color:blue;">Back to Home</a></p>
    </div>
</div>

<?php if ($show_success_popup): ?>
<script type="text/javascript">
    alert('Your password has been reset successfully. Please log in.');
    window.location.href = 'instructorlogin.php';
</script>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
