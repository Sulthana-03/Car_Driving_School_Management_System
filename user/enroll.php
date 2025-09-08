<?php
require_once('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: userlogin.php');
    exit;
}

// Fetch active packages with duration and cost
$package_query = $conn->query("SELECT id, name, training_duration, cost FROM package_list WHERE status = 1");
if (!$package_query) {
    die("Package query failed: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $package_id = $_POST['package_id'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $LLR_no = $_POST['LLR_no'];
    $address = $_POST['address'];
    $alternative_no = $_POST['alternative_no'];
    $time_slot = $_POST['time_slot'];

    // File upload settings
    $target_dir = "../uploads/LLR_copy/";
    $max_file_size = 2 * 1024 * 1024; // 2 MB
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if (isset($_FILES['copy_of_LLR']) && $_FILES['copy_of_LLR']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['copy_of_LLR']['tmp_name'];
        $original_name = basename($_FILES['copy_of_LLR']['name']);
        $file_size = $_FILES['copy_of_LLR']['size'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only PDF, JPG, JPEG, and PNG allowed.";
            header("Location: enroll.php");
            exit();
        }

        if ($file_size > $max_file_size) {
            $_SESSION['error'] = "File is too large. Max 2 MB allowed.";
            header("Location: enroll.php");
            exit();
        }

        $new_file_name = "LLR_{$user_id}_" . time() . "_$original_name";
        $target_file = $target_dir . $new_file_name;

        if (!move_uploaded_file($file_tmp, $target_file)) {
            $_SESSION['error'] = "Failed to upload LLR copy.";
            header("Location: enroll.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please upload a valid LLR copy file.";
        header("Location: enroll.php");
        exit();
    }

    // Calculate age
    $dob = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    if ($age < 18) {
        $_SESSION['error'] = "You must be at least 18 years old to enroll.";
        header("Location: enroll.php");
        exit();
    }

    $enrollment_no = strtoupper(uniqid('ENR'));

    $stmt = $conn->prepare("INSERT INTO enrollees (enrollment_no, user_id, package_id, gender, date_of_birth, age, LLR_no, copy_of_LLR, address, alternative_no, time_slot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siississsss", $enrollment_no, $user_id, $package_id, $gender, $date_of_birth, $age, $LLR_no, $new_file_name, $address, $alternative_no, $time_slot);

    if ($stmt->execute()) {
        // Get user details for email
        $user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
        $user = $user_query->fetch_assoc();
        
        // Get package details
        $package_query = $conn->query("SELECT * FROM package_list WHERE id = $package_id");
        $package = $package_query->fetch_assoc();
        
        // Send email notification
        require '../vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = 'smtp.gmail.com';
            $mail->Username = 'remove';
            $mail->Password = 'remove';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('remove', 'Indian Driving School');
            $mail->addAddress($user['email'], $user['name']); // Send to user
            $mail->addAddress('remove'); // Also send to admin

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Enrollment Confirmation - $enrollment_no";
            $mail->Body = "
                <h2>Enrollment Confirmation</h2>
                <p>Dear {$user['name']},</p>
                <p>Thank you for enrolling in our driving course. Below are your enrollment details:</p>
                
                <h3>Enrollment Information</h3>
                <p><strong>Enrollment Number:</strong> $enrollment_no</p>
                <p><strong>Package Name:</strong> {$package['name']}</p>
                <p><strong>Training Duration:</strong> {$package['training_duration']}</p>
                <p><strong>Package Cost:</strong> {$package['cost']}</p>
                <p><strong>Time Slot:</strong> $time_slot</p>
                
                <h3>Personal Information</h3>
                <p><strong>Name:</strong> {$user['name']}</p>
                <p><strong>Email:</strong> {$user['email']}</p>
                <p><strong>Phone:</strong> $alternative_no</p>
                <p><strong>Date of Birth:</strong> $date_of_birth</p>
                <p><strong>Age:</strong> $age</p>
                <p><strong>LLR Number:</strong> $LLR_no</p>
                <p><strong>Address:</strong> $address</p>
                
                <p>We will contact you shortly with further instructions. Please keep this enrollment number for future reference.</p>
                <p>Best regards,<br>Driving School Management Team</p>
            ";

            $mail->send();
            
            $_SESSION['enrollment_success'] = true;
            $_SESSION['enrollment_message'] = "Enrollment successful! Your enrollment number is: $enrollment_no. A confirmation email has been sent to your registered email address.";
            $_SESSION['enrollment_no'] = $enrollment_no;
            header("Location: enroll.php");
            exit();
        } catch (Exception $e) {
            // Email failed but enrollment was successful
            $_SESSION['enrollment_success'] = true;
            $_SESSION['enrollment_message'] = "Enrollment successful! Your enrollment number is: $enrollment_no. (Email notification failed to send)";
            $_SESSION['enrollment_no'] = $enrollment_no;
            header("Location: enroll.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Failed to enroll. Please try again.";
        header("Location: enroll.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Driving Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f8ff;
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
        }
        .package-details {
            display: none;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .redirect-message {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #6c757d;
        }
        .success-container {
            text-align: center;
            padding: 30px;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['enrollment_success']) && $_SESSION['enrollment_success']): ?>
    <div class="container success-container">
        <div class="alert alert-success">
            <?= $_SESSION['enrollment_message'] ?>
        </div>
        <p class="redirect-message">You will be redirected to your dashboard shortly...</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "index.php";
        }, 5000); // Redirect after 5 seconds to give time to read the message
    </script>
    <?php 
    unset($_SESSION['enrollment_success']);
    unset($_SESSION['enrollment_message']);
    unset($_SESSION['enrollment_no']);
    exit(); 
    ?>
<?php endif; ?>

<div class="container">
    <h2 class="mb-4 text-center">Enroll in Driving Course</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class='alert alert-danger'><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="enroll.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Package</label>
            <select name="package_id" id="package_select" class="form-select" required>
                <option value="">Select Package</option>
                <?php 
                $packages = [];
                while ($package = $package_query->fetch_assoc()) { 
                    $packages[$package['id']] = $package;
                    ?>
                    <option value="<?= $package['id'] ?>" 
                        data-duration="<?= htmlspecialchars($package['training_duration']) ?>"
                        data-cost="<?= htmlspecialchars($package['cost']) ?>">
                        <?= htmlspecialchars($package['name']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div id="packageDetails" class="package-details">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Training Duration</label>
                        <input type="text" id="package_duration" class="form-control" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Package Cost</label>
                        <input type="text" id="package_cost" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Age</label>
            <input type="number" name="age" id="age" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">LLR Number</label>
            <input type="text" name="LLR_no" class="form-control" maxlength="14" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Copy of LLR (upload file)</label>
            <input type="file" name="copy_of_LLR" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Alternative Phone Number</label>
            <input type="tel" name="alternative_no" class="form-control" maxlength="10" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Preferred Time Slot</label>
            <select name="time_slot" class="form-select" required>
                <option value="">Select Slot</option>
                <option value="6 AM - 8 AM">Early Morning (6 AM - 8 AM)</option>
                <option value="9 AM - 11 AM">Mid-Morning (9 AM - 11 AM)</option>
                <option value="12 PM - 2 PM">Afternoon (12 PM - 2 PM)</option>
                <option value="4 PM - 6 PM">Evening (4 PM - 6 PM)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit Enrollment</button>
    </form>
</div>

<script>
    // Package details display
    document.getElementById('package_select').addEventListener('change', function() {
        const packageDetails = document.getElementById('packageDetails');
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            packageDetails.style.display = 'block';
            document.getElementById('package_duration').value = selectedOption.getAttribute('data-duration');
            document.getElementById('package_cost').value = selectedOption.getAttribute('data-cost');
        } else {
            packageDetails.style.display = 'none';
        }
    });

    // Age calculation with automatic refresh if under 18
    document.getElementById('date_of_birth').addEventListener('change', function() {
        const dobInput = this;
        const dob = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        
        document.getElementById('age').value = age > 0 ? age : 0;

        if (age < 18) {
            alert("You must be at least 18 years old to enroll.");
            dobInput.value = ''; // Clear the date input
            document.getElementById('age').value = ''; // Clear the age field
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>