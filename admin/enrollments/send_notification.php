<?php
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_POST['user_email'] ?? '';
    $userName = $_POST['user_name'] ?? '';
    $enrollmentData = $_POST['enrollment_data'] ?? [];
    $instructorData = $_POST['instructor_data'] ?? null;
    $statusChange = $_POST['status_change'] ?? false;
    
    // Decode JSON data if it's sent as JSON string
    if (is_string($enrollmentData)) {
        $enrollmentData = json_decode($enrollmentData, true);
    }
    if (is_string($instructorData)) {
        $instructorData = json_decode($instructorData, true);
    }
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
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
        $mail->addAddress($userEmail, $userName);

        // Determine subject based on status change or instructor assignment
        if ($statusChange) {
            $subject = "Your Enrollment Status Update";
        } else {
            $subject = "Instructor Assigned to Your Enrollment";
        }
        
        $mail->Subject = $subject;

        // Build the email body
        $body = "<h2>Dear $userName,</h2>";
        
        if ($statusChange) {
            $body .= "<p>Your enrollment status has been updated to: <strong>{$enrollmentData['enrollment_status']}</strong></p>";
            
            if ($enrollmentData['enrollment_status'] == 'Cancelled') {
                $body .= "<p>Your enrollment has been cancelled. If this was unexpected, please contact our support team.</p>";
            } elseif ($enrollmentData['enrollment_status'] == 'Verified') {
                $body .= "<p>Your enrollment has been verified and is now being processed.</p>";
            } elseif ($enrollmentData['enrollment_status'] == 'In-Session') {
                $body .= "<p>Your training sessions have now begun. Please ensure you attend all scheduled classes.</p>";
            } elseif ($enrollmentData['enrollment_status'] == 'Completed') {
                $body .= "<p>Congratulations! You have successfully completed your driving course.</p>";
            }
        } else {
            $body .= "<p>An instructor has been assigned to your enrollment:</p>";
        }
        
        // Add enrollment details
        $body .= "<h3>Enrollment Details:</h3>";
        $body .= "<p><strong>Enrollment Number:</strong> {$enrollmentData['enrollment_no']}</p>";
        $body .= "<p><strong>Package:</strong> {$enrollmentData['package_name']}</p>";
        $body .= "<p><strong>Duration:</strong> {$enrollmentData['training_duration']}</p>";
        $body .= "<p><strong>Start Date:</strong> {$enrollmentData['start_date']}</p>";
        $body .= "<p><strong>End Date:</strong> {$enrollmentData['end_date']}</p>";
        $body .= "<p><strong>Timeslot:</strong> {$enrollmentData['time_slot']}</p>";
        $body .= "<p><strong>Current Status:</strong> {$enrollmentData['enrollment_status']}</p>";
        
        // Add instructor details if available
        if ($instructorData) {
            $body .= "<h3>Instructor Details:</h3>";
            $body .= "<p><strong>Name:</strong> {$instructorData['name']}</p>";
            $body .= "<p><strong>Contact Number:</strong> {$instructorData['phone']}</p>";
            $body .= "<p><strong>Email:</strong> {$instructorData['email']}</p>";
        }
        
        $body .= "<p>If you have any questions, please don't hesitate to contact us.</p>";
        $body .= "<p>Best regards,<br>Indian Driving School Team</p>";
        
        $mail->isHTML(true);
        $mail->Body = $body;
        
        if ($mail->send()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Mail could not be sent']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>