<?php
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

require_once('./config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';                                                      
    $message = $_POST['message'] ?? '';

    // Save to database
    $sql = "INSERT INTO enquiries (name, email, phone, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $phone, $message);
    $stmt->execute();

    // Send mail using Gmail SMTP
    $mail = new PHPMailer(true);
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
        $mail->setFrom('remove', 'Indian Driving school Contact');
        $mail->addAddress('remove'); // Admin receives

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Enquiry from $name";
        $mail->Body = "
            <h2>New Enquiry Received</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        ";

        $mail->send();
        $_settings->set_flashdata('success', 'Your enquiry has been submitted successfully!');
    } catch (Exception $e) {
        $_settings->set_flashdata('error', 'There was an error submitting your enquiry.');
    }
    
    header('Location: enquiry.php');
    exit;
}
?>