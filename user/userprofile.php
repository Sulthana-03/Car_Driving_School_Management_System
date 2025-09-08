<?php
// Absolute first line - no whitespace before!
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: login.php');
    exit;
}

// Include config
require_once('../config.php');

// Database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get current user data
$user_id = $_SESSION['user_id'];
$qry = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
if ($qry->num_rows <= 0) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: login.php');
    exit;
}

$user = $qry->fetch_assoc();

// Get enrollee data for this user
$enrollee_qry = $conn->query("SELECT * FROM enrollees WHERE user_id = '$user_id'");
$enrollee = $enrollee_qry->num_rows > 0 ? $enrollee_qry->fetch_assoc() : null;

// Set default values for potentially missing fields
$address = $enrollee['address'] ?? '';
$phone = $user['phone'] ?? '';
$email = $user['email'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Get form data
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate phone
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    // Check if password is being changed
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect';
        }
        
        // Validate new password
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        }
        
        // Confirm password match
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        $password_changed = true;
    }
    
    // Update database if no errors
    if (empty($errors)) {
        // Update users table
        $password_sql = $password_changed ? ", password = '" . password_hash($new_password, PASSWORD_DEFAULT) . "'" : "";
        
        $update_user_sql = "UPDATE users SET 
            phone = '$phone', 
            email = '$email'
            $password_sql
            WHERE id = '$user_id'";
        
        // Update enrollees table (address)
        $update_enrollee_sql = "UPDATE enrollees SET 
            address = '$address'
            WHERE user_id = '$user_id'";
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update users table
            if (!$conn->query($update_user_sql)) {
                throw new Exception("Failed to update user: " . $conn->error);
            }
            
            // Update enrollees table if user is an enrollee
            if ($enrollee !== null && !$conn->query($update_enrollee_sql)) {
                throw new Exception("Failed to update enrollee: " . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success_message'] = 'Profile updated successfully';
            
            // Refresh user data
            $qry = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
            $user = $qry->fetch_assoc();
            
            // Refresh enrollee data
            if ($enrollee !== null) {
                $enrollee_qry = $conn->query("SELECT * FROM enrollees WHERE user_id = '$user_id'");
                $enrollee = $enrollee_qry->fetch_assoc();
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo base_url ?>/plugins/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url ?>/plugins/fontawesome-free/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .settings-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .settings-header {
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            display: block;
            font-size: 16px;
        }
        .form-control {
            height: 50px;
            border-radius: 6px;
            width: 100%;
            font-size: 16px;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 15px;
            margin-top: 8px;
        }
        .password-toggle {
            color: #0062cc;
            font-weight: 500;
            cursor: pointer;
            display: inline-block;
            margin-top: 15px;
            font-size: 16px;
        }
        .password-toggle:hover {
            color: #004ba0;
            text-decoration: underline;
        }
        .btn-update {
            background-color: #0062cc;
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 18px;
            border-radius: 6px;
            width: 100%;
            margin-top: 30px;
            height: 50px;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
        .password-section {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }
        .alert {
            font-size: 16px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <?php 
    // Include top bar navigation
    require_once('inc/topBarNav.php'); 
    ?>
    
    <div class="container">
        <div class="settings-container">
            <h1 class="settings-header">User Profile</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i><?= $_SESSION['success_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= $_SESSION['error_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" name="phone" value="<?= htmlspecialchars($phone) ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= htmlspecialchars($email) ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>">
                </div>
                
                <div class="password-section">
                    <span class="password-toggle" onclick="togglePasswordFields()">
                        <i class="fas fa-key mr-1"></i> Change Password
                    </span>
                    
                    <div id="password-fields" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" name="current_password">
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" name="new_password">
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-update">
                    Update Profile
                </button>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?php echo base_url ?>/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePasswordFields() {
            $('#password-fields').slideToggle();
        }
        
        $(document).ready(function() {
            // Show password fields if there are password errors
            <?php if (isset($errors['current_password']) || isset($errors['new_password']) || isset($errors['confirm_password'])): ?>
                $('#password-fields').show();
            <?php endif; ?>
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 5000);
        });
    </script>
</body>
</html>
<?php
ob_end_flush();