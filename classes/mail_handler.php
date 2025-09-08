<?php
// /cdsms/classes/mail_handler.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader for PHPMailer
require __DIR__ . '/../vendor/autoload.php';
require_once(__DIR__ . '/../config.php'); // Ensure config.php path is correct relative to this file

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid Request'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    $mail = new PHPMailer(true);
    try {
        // Server settings (Use your actual SMTP credentials from config.php if they are defined there, otherwise hardcode or use environment variables)
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'remove'; // Your Gmail address
        $mail->Password = 'remove'; // Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('remove', 'Driving School Management');

        switch ($action) {
            case 'send_instructor_assignment_email':
                $user_email = $_POST['user_email'] ?? '';
                $user_name = $_POST['user_name'] ?? '';
                $instructor_email = $_POST['instructor_email'] ?? '';
                $instructor_name = $_POST['instructor_name'] ?? '';
                $enrollment_no = $_POST['enrollment_no'] ?? '';
                $package_name = $_POST['package_name'] ?? '';
                $time_slot = $_POST['time_slot'] ?? '';
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';

                if (empty($user_email) || empty($instructor_email)) {
                    throw new Exception("Missing recipient email addresses.");
                }

                // Email to User
                $mail->clearAddresses();
                $mail->addAddress($user_email, $user_name);
                $mail->Subject = "Your Driving Course Instructor Has Been Assigned - Enrollment No: {$enrollment_no}";
                $mail->Body = "
                    <html>
                    <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <h2 style='color: #007bff; text-align: center;'>Instructor Assignment Notification</h2>
                            <p>Dear {$user_name},</p>
                            <p>We are pleased to inform you that an instructor has been assigned for your driving course enrollment.</p>
                            
                            <h3 style='color: #007bff;'>Enrollment Details:</h3>
                            <ul>
                                <li><strong>Enrollment Number:</strong> {$enrollment_no}</li>
                                <li><strong>Package:</strong> {$package_name}</li>
                                <li><strong>Time Slot:</strong> {$time_slot}</li>
                                <li><strong>Start Date:</strong> " . date('F d, Y', strtotime($start_date)) . "</li>
                                <li><strong>End Date:</strong> " . date('F d, Y', strtotime($end_date)) . "</li>
                            </ul>

                            <h3 style='color: #007bff;'>Assigned Instructor Details:</h3>
                            <ul>
                                <li><strong>Instructor Name:</strong> {$instructor_name}</li>
                                <li><strong>Instructor Email:</strong> {$instructor_email}</li>
                                </ul>
                            
                            <p>Your instructor will get in touch with you shortly to finalize your training schedule and details. Please ensure your contact information is up to date.</p>
                            <p>If you have any questions, please do not hesitate to contact us.</p>
                            <p>Best regards,<br>The Driving School Management Team</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin-top: 20px;'>
                            <p style='font-size: 0.8em; color: #888; text-align: center;'>This is an automated email, please do not reply.</p>
                        </div>
                    </body>
                    </html>
                ";
                $mail->isHTML(true);
                $mail->send();

                // Email to Instructor
                $mail->clearAddresses();
                $mail->addAddress($instructor_email, $instructor_name);
                $mail->Subject = "New Student Assignment - Enrollment No: {$enrollment_no}";
                $mail->Body = "
                    <html>
                    <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <h2 style='color: #007bff; text-align: center;'>New Student Assignment</h2>
                            <p>Dear {$instructor_name},</p>
                            <p>You have been assigned a new student for the driving course.</p>
                            
                            <h3 style='color: #007bff;'>Student & Enrollment Details:</h3>
                            <ul>
                                <li><strong>Student Name:</strong> {$user_name}</li>
                                <li><strong>Student Email:</strong> {$user_email}</li>
                                <li><strong>Enrollment Number:</strong> {$enrollment_no}</li>
                                <li><strong>Package:</strong> {$package_name}</li>
                                <li><strong>Time Slot:</strong> {$time_slot}</li>
                                <li><strong>Start Date:</strong> " . date('F d, Y', strtotime($start_date)) . "</li>
                                <li><strong>End Date:</strong> " . date('F d, Y', strtotime($end_date)) . "</li>
                            </ul>
                            
                            <p>Please reach out to the student to coordinate the training schedule and details. You can find their contact information through the system.</p>
                            <p>Thank you for your dedication!</p>
                            <p>Best regards,<br>The Driving School Management Team</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin-top: 20px;'>
                            <p style='font-size: 0.8em; color: #888; text-align: center;'>This is an automated email, please do not reply.</p>
                        </div>
                    </body>
                    </html>
                ";
                $mail->isHTML(true);
                $mail->send();

                $response = ['status' => 'success', 'message' => 'Instructor assignment emails sent successfully.'];
                break;

            case 'send_payment_status_email':
                $user_email = $_POST['user_email'] ?? '';
                $user_name = $_POST['user_name'] ?? '';
                $enrollment_no = $_POST['enrollment_no'] ?? '';
                $package_cost = $_POST['package_cost'] ?? 0;
                $total_paid = $_POST['total_paid'] ?? 0;
                $balance = $_POST['balance'] ?? 0;
                $payment_status = $_POST['payment_status'] ?? '';

                if (empty($user_email)) {
                    throw new Exception("Missing user email address.");
                }

                $mail->clearAddresses();
                $mail->addAddress($user_email, $user_name);
                $mail->Subject = "Payment Status Update for Enrollment No: {$enrollment_no}";
                $mail->Body = "
                    <html>
                    <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <h2 style='color: #007bff; text-align: center;'>Payment Status Update</h2>
                            <p>Dear {$user_name},</p>
                            <p>This is an update regarding the payment status for your driving course enrollment.</p>
                            
                            <h3 style='color: #007bff;'>Enrollment and Payment Summary:</h3>
                            <ul>
                                <li><strong>Enrollment Number:</strong> {$enrollment_no}</li>
                                <li><strong>Total Package Cost:</strong> ₹" . number_format($package_cost, 2) . "</li>
                                <li><strong>Total Amount Paid:</strong> ₹" . number_format($total_paid, 2) . "</li>
                                <li><strong>Remaining Balance:</strong> ₹" . number_format($balance, 2) . "</li>
                                <li><strong>Current Payment Status:</strong> <strong style='color: " . ($payment_status == 'Paid' ? '#28a745' : ($payment_status == 'Partially Paid' ? '#ffc107' : '#dc3545')) . ";'>" . htmlspecialchars($payment_status) . "</strong></li>
                            </ul>
                            
                            <p>Please check your dashboard for detailed payment history or contact us if you have any questions.</p>
                            <p>Thank you for your prompt payments!</p>
                            <p>Best regards,<br>The Driving School Management Team</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin-top: 20px;'>
                            <p style='font-size: 0.8em; color: #888; text-align: center;'>This is an automated email, please do not reply.</p>
                        </div>
                    </body>
                    </html>
                ";
                $mail->isHTML(true);
                $mail->send();

                $response = ['status' => 'success', 'message' => 'Payment status email sent successfully.'];
                break;

            case 'send_enrollment_status_email':
                $user_email = $_POST['user_email'] ?? '';
                $user_name = $_POST['user_name'] ?? '';
                $enrollment_no = $_POST['enrollment_no'] ?? '';
                $enrollment_status = $_POST['enrollment_status'] ?? '';
                
                if (empty($user_email)) {
                    throw new Exception("Missing user email address.");
                }

                $mail->clearAddresses();
                $mail->addAddress($user_email, $user_name);
                $mail->Subject = "Enrollment Status Update - Enrollment No: {$enrollment_no}";
                $mail->Body = "
                    <html>
                    <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <h2 style='color: #007bff; text-align: center;'>Enrollment Status Update</h2>
                            <p>Dear {$user_name},</p>
                            <p>This is an update regarding the status of your driving course enrollment.</p>
                            
                            <h3 style='color: #007bff;'>Enrollment Details:</h3>
                            <ul>
                                <li><strong>Enrollment Number:</strong> {$enrollment_no}</li>
                                <li><strong>Current Enrollment Status:</strong> <strong style='color: " . ($enrollment_status == 'Verified' ? '#28a745' : ($enrollment_status == 'Cancelled' ? '#dc3545' : '#007bff')) . ";'>" . htmlspecialchars($enrollment_status) . "</strong></li>
                            </ul>
                            
                            <p>Please log in to your dashboard for more details or contact us if you have any questions.</p>
                            <p>Best regards,<br>The Driving School Management Team</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin-top: 20px;'>
                            <p style='font-size: 0.8em; color: #888; text-align: center;'>This is an automated email, please do not reply.</p>
                        </div>
                    </body>
                    </html>
                ";
                $mail->isHTML(true);
                $mail->send();

                $response = ['status' => 'success', 'message' => 'Enrollment status email sent successfully.'];
                break;

            default:
                throw new Exception("Unknown action.");
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Mailer Error: ' . $e->getMessage()];
    }
}

echo json_encode($response);
exit;