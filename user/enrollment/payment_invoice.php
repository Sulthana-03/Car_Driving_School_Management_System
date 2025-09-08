<?php
// view_enrollment.php - For users (students) to view their own enrollment

// ALWAYS AT THE VERY TOP OF THE FILE
// Set display errors for debugging ONLY. REMOVE IN PRODUCTION!
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure no whitespace or BOM before this PHP tag.
// Ensure config.php also has no whitespace/BOM before its opening <?php tag.

require_once('../config.php'); // Adjust path as needed

// Start session only if not already started. This should be the first executable code if it's not handled by config.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for user_id and enrollment_id early
if (!isset($_SESSION['user_id'])) {
    // Redirect using header for security if session isn't active
    header("Location: userlogin.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Enrollment ID required.');location.href='list_enrollment.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$enrollment_id = $_GET['id'];

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    // Log error, don't die with direct error message in production
    error_log("Database connection failed: " . $conn->connect_error);
    // Return a JSON error or redirect to a generic error page
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_payment'])) {
        // Clear any buffered output before sending JSON header
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Please try again later.']);
        exit;
    } else {
        echo "<script>alert('Database connection failed. Please try again later.');location.href='userdashboard.php';</script>";
        exit;
    }
}

// Handle form submission using AJAX for payment


// === From this point downwards, the script proceeds to render the HTML page ===
// This part only executes if it's NOT a POST request for payment.
// So, if the above `if` block for POST request is executed, the script `exit`s and
// this part is never reached for that specific request.

// Check for user_id and enrollment_id (already done at the top, but keeping this for clarity
// in the context of the HTML rendering path)
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Enrollment ID required.');location.href='list_enrollment.php';</script>";
    exit;
}

// Re-establish connection if it was closed in the POST block (though it shouldn't be for this path)
if (!isset($conn) || $conn->connect_error) {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed during HTML rendering: " . $conn->connect_error);
        echo "<script>alert('Database connection failed. Please try again later.');location.href='userdashboard.php';</script>";
        exit;
    }
}

// Get enrollment details
$qry = $conn->query("SELECT
    e.*,
    e.assigned_instructor_id,
    u.name as fullname,
    u.phone,
    u.email,
    p.name as package_name,
    p.training_duration,
    p.cost,
    p.partial1,
    i.id AS instructor_id,
    i.instructor_code,
    i.firstname AS instructor_firstname,
    i.lastname AS instructor_lastname,
    i.avatar AS instructor_avatar,
    i.phone AS instructor_phone,
    i.email AS instructor_email,
    i.address AS instructor_address
FROM enrollees e
JOIN users u ON e.user_id = u.id
JOIN package_list p ON e.package_id = p.id
LEFT JOIN instructor i ON e.assigned_instructor_id = i.id
WHERE e.id = '$enrollment_id' AND e.user_id = '$user_id'");

if ($qry->num_rows <= 0) {
    echo "<script>alert('Enrollment not found or access denied.');location.href='list_enrollment.php';</script>";
    exit;
}

$row = $qry->fetch_assoc();

// Get payment history for this enrollment
// Keep this as is (shows all payments in history):
$payment_history = $conn->query("SELECT * FROM payment_list WHERE enrollees_id = '$enrollment_id' ORDER BY date_created DESC");
$total_paid = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$enrollment_id' AND payment_status = 'paid'")->fetch_assoc()['total'];
$total_paid = $total_paid ? $total_paid : 0;

$total_pending = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$enrollment_id' AND payment_status = 'pending'")->fetch_assoc()['total'];
$total_pending = $total_pending ? $total_pending : 0;
$balance = $row['cost'] - $total_paid;

// Check if partial payment was made
$has_partial_payment = $conn->query("SELECT 1 FROM payment_list WHERE enrollees_id = '$enrollment_id' AND amount_type = 'partial'")->num_rows > 0;

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .img-avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
            object-position: center;
            border-radius: 100%;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .card-title {
            font-weight: 600;
            color: #343a40;
        }
        fieldset {
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        legend {
            width: auto;
            padding: 0 10px;
            font-size: 1.1rem;
            color: #495057;
            font-weight: 500;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .table-bordered td, .table-bordered th {
            border-color: #dee2e6;
        }
        legend {
            color: #007bff;
        }
        .progress {
            height: 5px;
            margin-top: 10px;
        }
        .progress-bar {
            transition: width 0.1s linear;
        }
        .payment-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .payment-summary-card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .payment-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .payment-history-table th {
            background-color: #e9ecef;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        .upi-payment-container {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .upi-options {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .upi-option {
            flex: 1;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .upi-qr-code {
            max-width: 200px;
            margin: 0 auto;
            display: block;
        }
        .upi-details {
            margin-top: 15px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .transaction-form {
            margin-top: 20px;
        }
        .btn-close-upi {
            float: right;
            margin-top: -10px;
            margin-right: -10px;
        }
        @media print {
            .no-print, .card-tools, .payment-form, #upiPaymentContainer {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="card card-outline card-primary mt-4">
        <div class="card-header">
            <h3 class="card-title">Enrollment Details</h3>
            <div class="card-tools">
                <a href="?page=enrollment/list_enrollment" class="btn btn-flat btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                <?php if ($row['payment_status'] == 'Paid'): ?>
                    <button onclick="window.print()" class="btn btn-flat btn-primary no-print"><i class="fa fa-print"></i> Print</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div id="message-container" style="display: none;"></div>

            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_msg'] ?></div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>

            <fieldset>
                <legend>User Details</legend>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Enrollment No</label>
                        <div class="info-value"><?= htmlspecialchars($row['enrollment_no']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Name</label>
                        <div class="info-value"><?= htmlspecialchars($row['fullname']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Phone No</label>
                        <div class="info-value"><?= htmlspecialchars($row['phone']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Alternative No</label>
                        <div class="info-value"><?= htmlspecialchars($row['alternative_no'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Email</label>
                        <div class="info-value"><?= htmlspecialchars($row['email']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Gender</label>
                        <div class="info-value"><?= htmlspecialchars($row['gender']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Date of Birth</label>
                        <div class="info-value"><?= htmlspecialchars($row['date_of_birth']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Age</label>
                        <div class="info-value"><?= htmlspecialchars($row['age']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Address</label>
                        <div class="info-value"><?= htmlspecialchars($row['address']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">LLR No</label>
                        <div class="info-value"><?= htmlspecialchars($row['LLR_no']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">LLR Copy</label>
                        <div class="info-value">
                            <?php if (!empty($row['copy_of_LLR'])): ?>
                                <a href="<?= htmlspecialchars('http://localhost/cdsms/uploads/LLR_copy/' . $row['copy_of_LLR']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Document</a>
                            <?php else: ?>
                                No file uploaded
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Package Details</legend>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Package Name</label>
                        <div class="info-value"><?= htmlspecialchars($row['package_name']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Duration</label>
                        <div class="info-value"><?= htmlspecialchars($row['training_duration']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Cost</label>
                        <div class="info-value">₹<?= number_format($row['cost'], 2) ?></div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Class Details</legend>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Enrollment Status</label>
                        <div class="info-value">
                            <?php
                            $status = $row['enrollment_status'];
                            $badge_class = '';
                            switch($status) {
                                case 'Pending': $badge_class = 'bg-secondary'; break;
                                case 'Verified': $badge_class = 'bg-primary'; break;
                                case 'In-Session': $badge_class = 'bg-warning'; break;
                                case 'Completed': $badge_class = 'bg-success'; break;
                                case 'Cancelled': $badge_class = 'bg-danger'; break;
                                default: $badge_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Timeslot</label>
                        <div class="info-value"><?= htmlspecialchars($row['time_slot']) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Start Date</label>
                        <div class="info-value">
                            <?= !empty($row['start_date']) && $row['start_date'] != '0000-00-00' ?
                                date('F d, Y', strtotime($row['start_date'])) :
                                'Not set' ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">End Date</label>
                        <div class="info-value">
                            <?= !empty($row['end_date']) && $row['end_date'] != '0000-00-00' ?
                                date('F d, Y', strtotime($row['end_date'])) :
                                'Not set' ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="info-label">Payment Status</label>
                        <div class="info-value">
                            <?php
                            $payment_status = $row['payment_status'];
                            $badge_class = '';
                            switch($payment_status) {
                                case 'Pending': $badge_class = 'bg-secondary'; break;
                                case 'Paid': $badge_class = 'bg-success'; break;
                                case 'Partially Paid': $badge_class = 'bg-warning text-dark'; break;
                                case 'Cancelled': $badge_class = 'bg-danger'; break;
                                default: $badge_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($payment_status) ?></span>
                        </div>
                    </div>
                </div>
            </fieldset>

            <?php if (!empty($row['assigned_instructor_id'])): ?>
            <fieldset>
                <legend>Instructor Details</legend>
                <div class="row align-items-center">
                    <div class="col-md-2 text-center mb-3">
                        <img src="<?= !empty($row['instructor_avatar']) ? '/cdsms/uploads/avatar/' . basename($row['instructor_avatar']) : '/cdsms/images/default_avatar.png' ?>"
                             class="img-avatar img-thumbnail" alt="Instructor Avatar">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="info-label">Instructor Code</label>
                                <div class="info-value"><?= htmlspecialchars($row['instructor_code']) ?></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="info-label">Name</label>
                                <div class="info-value"><?= htmlspecialchars($row['instructor_firstname'] . ' ' . $row['instructor_lastname']) ?></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="info-label">Phone</label>
                                <div class="info-value"><?= htmlspecialchars($row['instructor_phone']) ?></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="info-label">Email</label>
                                <div class="info-value"><?= htmlspecialchars($row['instructor_email']) ?></div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="info-label">Address</label>
                                <div class="info-value"><?= htmlspecialchars($row['instructor_address']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <?php endif; ?>
<!-- Payment Details (only shown when instructor is assigned) -->
            <div id="payment-details-section" style="display: <?= !empty($row['assigned_instructor_id']) ? 'block' : 'none' ?>;">
               
           <fieldset>
    <legend>Payment Details</legend>
    
    <div class="payment-summary-card">
        <h5>Payment Summary</h5>
        <table class="table table-borderless mb-0">
            <tbody>
                <tr>
                    <th width="30%">Total Amount</th>
                    <td>₹<?= number_format($row['cost'], 2) ?></td>
                </tr>
                <tr>
                    <th>Total Paid</th>
                    <td>₹<?= number_format($total_paid, 2) ?></td>
                </tr>
                <?php if($total_pending > 0): ?>
                <tr>
                    <th>Pending Payments</th>
                    <td>₹<?= number_format($total_pending, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th class="align-middle">Balance</th>
                    <td class="fw-bold fs-5">₹<?= number_format($row['cost'] - $total_paid, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if ($payment_history->num_rows > 0): ?>
    <div class="mt-4">
        <h5>Payment History</h5>
        <div class="table-responsive">
            <table class="table table-bordered payment-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($payment = $payment_history->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($payment['date_created'])) ?></td>
                        <td><?= ucfirst($payment['amount_type']) ?></td>
                        <td>₹<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= strtoupper($payment['payment_mode']) ?></td>
                        <td>
                            <?php
                            $status = $payment['payment_status'];
                            $badge_class = '';
                            switch($status) {
                                case 'pending': $badge_class = 'bg-secondary'; break;
                                case 'paid': $badge_class = 'bg-success'; break;
                                default: $badge_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge status-badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td><?= !empty($payment['upi_reference']) ? htmlspecialchars($payment['upi_reference']) : 'N/A' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($balance > 0): ?>
        <div class="alert alert-info mt-3">
            <i class="fa fa-info-circle"></i> Balance of ₹<?= number_format($balance, 2) ?> remaining.
        </div>
    <?php else: ?>
        <div class="alert alert-success mt-3">
            <i class="fa fa-check-circle"></i> Payment completed. No balance remaining.
        </div>
    <?php endif; ?>
</fieldset>

            <div class="row mt-3">
                <div class="col-md-12 text-muted">
                    <small>Record created on <?= date('F d, Y H:i', strtotime($row['created_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const paymentForm = document.getElementById("paymentForm");

    // Only run scripts if the payment form exists on the page
    if (!paymentForm) return;

    const amountType = document.getElementById("amount_type");
    const paymentMode = document.getElementById("payment_mode");
    const paymentAmount = document.getElementById("payment_amount");
    const saveBtn = document.getElementById("save_btn"); // For Cash
    const payBtn = document.getElementById("pay_btn");     // For UPI (to show instructions)
    const messageContainer = document.getElementById("message-container");
    const upiPaymentContainer = document.getElementById("upiPaymentContainer");
    const closeUpiBtn = document.querySelector(".btn-close-upi");
    const copyUpiIdBtn = document.getElementById("copyUpiId");
    const upiIdInput = document.getElementById("upiId");
    const submitUpiPaymentBtn = document.getElementById("submitUpiPayment"); // To submit UPI reference
    const upiReferenceInput = document.getElementById("upi_reference");

    const partialAmount = parseFloat(document.getElementById("partial_amount").value);
    const fullAmount = parseFloat(document.getElementById("full_amount").value);

    // Initial UI update
    updatePaymentUI();

    function updatePaymentUI() {
        const type = amountType.value;
        const mode = paymentMode.value;

        // Set payment amount
        if (type === "partial") {
            paymentAmount.value = partialAmount.toFixed(2);
        } else if (type === "full") {
            paymentAmount.value = fullAmount.toFixed(2);
        } else {
            paymentAmount.value = "";
        }

        // Hide both buttons and UPI container initially
        saveBtn.classList.add("d-none");
        payBtn.classList.add("d-none");
        upiPaymentContainer.style.display = 'none'; // Ensure UPI container is hidden on UI change

        // Show the correct button if both type and mode are selected
        if (type && mode) {
            if (mode === "cash") {
                saveBtn.classList.remove("d-none");
            } else if (mode === "upi") {
                payBtn.classList.remove("d-none");
            }
        }
    }

    // Add event listeners
    amountType.addEventListener("change", updatePaymentUI);
    paymentMode.addEventListener("change", updatePaymentUI);

    // Handle UPI "Pay" button click (shows UPI instructions)
    payBtn.addEventListener("click", function() {
        if (!paymentForm.checkValidity()) {
            paymentForm.reportValidity(); // Show HTML5 validation errors
            return;
        }

        // Update UPI amount display (if needed, though it's already set to balance)
        document.getElementById("upiAmount").value = parseFloat(paymentAmount.value).toFixed(2); // Ensure it's formatted

        // Show UPI payment container
        upiPaymentContainer.style.display = 'block';

        // Scroll to UPI container
        upiPaymentContainer.scrollIntoView({ behavior: 'smooth' });
    });

    // Close UPI payment container
    closeUpiBtn.addEventListener("click", function() {
        upiPaymentContainer.style.display = 'none';
        // Clear UPI reference if the user closes it without submitting
        upiReferenceInput.value = '';
    });

    // Copy UPI ID to clipboard
    copyUpiIdBtn.addEventListener("click", function() {
        upiIdInput.select();
        upiIdInput.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand("copy");

        // Show tooltip or feedback
        const originalText = copyUpiIdBtn.innerHTML;
        copyUpiIdBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            copyUpiIdBtn.innerHTML = originalText;
        }, 2000);
    });

    // Submit UPI payment (this is the actual form submission with UPI reference)
    submitUpiPaymentBtn.addEventListener("click", function() {
        if (!upiReferenceInput.value) {
            showMessage("Please enter the UPI transaction reference number.", 'danger');
            upiReferenceInput.focus();
            return;
        }

        // Gather all necessary form data for submission
        const formData = new FormData();
        formData.append("save_payment", "1");
        formData.append("amount_type", amountType.value);
        formData.append("amount", paymentAmount.value);
formData.append("payment_mode", "upi");
formData.append("upi_reference", upiReferenceInput.value);

submitFormWithFetch(formData, submitUpiPaymentBtn);
});

// Submit Cash payment (this uses the main form's submit event)
paymentForm.addEventListener("submit", function(e) {
    e.preventDefault(); // Prevent default form submission

    // Only proceed if payment mode is cash
    if (paymentMode.value === "cash") {
        const formData = new FormData(paymentForm); // Gets data from the original form
        submitFormWithFetch(formData, saveBtn);
    } else {
        // This case should ideally not be reached if buttons are correctly hidden/shown
        console.warn("Attempted to submit payment form with non-cash mode via default submit handler.");
    }
});

function submitFormWithFetch(formData, triggerButton) {
    const originalBtnText = triggerButton.innerHTML;
    triggerButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    triggerButton.disabled = true;
    messageContainer.style.display = 'none'; // Hide previous messages

    fetch('', { // Current page URL
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        console.log('Response Content-Type:', contentType); // LOG THE CONTENT-TYPE TO CONSOLE

        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            // If it's not JSON, read as text and LOG IT
            return response.text().then(text => {
                console.error('Server did not return a JSON response. Raw output received:', text); // LOG RAW OUTPUT TO CONSOLE
                throw new Error('Server did not return a JSON response. See console for raw output.');
            });
        }
    })
    .then(data => {
        console.log('Parsed JSON Data:', data); // LOG PARSED JSON TO CONSOLE
        if (data.status === 'success') {
            showMessage(data.message, 'success');
            setTimeout(() => window.location.reload(), 5000); // Reload page to show updated history/balance
        } else {
            showMessage(data.message, 'danger');
            triggerButton.innerHTML = originalBtnText;
            triggerButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Fetch Error in catch block:', error); // LOG ANY FETCH ERRORS TO CONSOLE
        showMessage('An unexpected error occurred during payment. Please check console for details.', 'danger');
        triggerButton.innerHTML = originalBtnText;
        triggerButton.disabled = false;
    });
}

// Function to display messages
function showMessage(msg, type) {
    messageContainer.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    messageContainer.style.display = 'block';
    messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        messageContainer.style.display = 'none';
    }, 5000);
}

// Initial call to set button visibility
updatePaymentUI();
});
</script>
</body>
</html>