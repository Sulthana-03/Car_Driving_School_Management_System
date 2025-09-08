<?php
// view_enrollments.php - Read-only detail view
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<script>alert('Enrollment ID required');location.href='?page=enrollments/index';</script>";exit;
}
$id = $_GET['id'];

// Connect to database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$qry = $conn->query("SELECT 
    e.*, 
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
WHERE e.id = '$id'");

if($qry->num_rows <= 0){
    echo "<script>alert('Not found');location.href='?page=enrollments/index';</script>";exit;
}
$row = $qry->fetch_assoc();

// Get payment history
$payment_history = $conn->query("SELECT * FROM payment_list WHERE enrollees_id = '$id' ORDER BY date_created DESC");

// Calculate totals - ONLY COUNT PAID PAYMENTS
$total_paid = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'paid'")->fetch_assoc()['total'];
$total_paid = $total_paid ? $total_paid : 0;

// Calculate pending payments separately
$total_pending = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'pending'")->fetch_assoc()['total'];
$total_pending = $total_pending ? $total_pending : 0;

$balance = $row['cost'] - $total_paid;
?>

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
    legend{
        color: #007bff;
    }
    .action-btns .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    .payment-summary-card {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Enrollment Details</h3>
        <div class="card-tools">
            <a href="?page=enrollments/manage_enrollments&id=<?= $row['id'] ?>" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i> Edit</a>
            <a href="?page=enrollments/index" class="btn btn-flat btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="card-body">
        <!-- User Details Section -->
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

        <!-- Package Details Section -->
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

        <!-- Class Details Section -->
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
                    case 'Pending': $badge_class = 'badge-secondary'; break;
                    case 'Verified': $badge_class = 'badge-primary'; break;
                    case 'In-Session': $badge_class = 'badge-info'; break;
                    case 'Completed': $badge_class = 'badge-success'; break;
                    case 'Cancelled': $badge_class = 'badge-danger'; break;
                    default: $badge_class = 'badge-secondary';
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
                // Get payment status directly from enrollees table
                $payment_status = $row['payment_status'];
                $badge_class = '';
                switch($payment_status) {
                    case 'Pending': $badge_class = 'badge-secondary'; break;
                    case 'Paid': $badge_class = 'badge-success'; break;
                    case 'Partial': $badge_class = 'badge-warning text-dark'; break;
                    case 'Cancelled': $badge_class = 'badge-danger'; break;
                    default: $badge_class = 'badge-secondary';
                }
                ?>
                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($payment_status) ?></span>
            </div>
        </div>
    </div>
</fieldset>
        <!-- Instructor Details Section (only shown if instructor is assigned) -->
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
        
        <!-- Payment Details Section -->
        <fieldset>
            <legend>Payment Details</legend>
            
            <!-- Payment Summary -->
            <div class="payment-summary-card">
                <h5>Payment Summary</h5>
                <table class="table table-borderless">
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
                        <th>Balance</th>
                        <td class="fw-bold">₹<?= number_format($balance, 2) ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment History -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Payment History</h5>
                    <?php if ($payment_history->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                   
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
                                            case 'pending': $badge_class = 'badge-secondary'; break;
                                            case 'paid': $badge_class = 'badge-success'; break;
                                            default: $badge_class = 'badge-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">No payment history found</div>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>
        <?php endif; ?>

        <!-- Additional Details Section -->
        <fieldset>
            <legend>Additional Details</legend>
            <div class="row">
                <div class="col-md-12">
                    <label class="info-label">Remarks</label>
                    <div class="info-value"><?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : 'No remarks' ?></div>
                </div>
            </div>
        </fieldset>

        <!-- Date Created -->
        <div class="row mt-3">
            <div class="col-md-12 text-muted">
                <small>Record created on <?= date('F d, Y H:i', strtotime($row['created_at'])) ?></small>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle edit payment button click
    $('.edit-payment').click(function() {
        var payment_id = $(this).data('id');
        uni_modal("Edit Payment", "enrollments/manage_payment.php?id=" + payment_id + "&enrollment_id=<?= $id ?>", 'mid-large');
    });

    // Handle delete payment button click
    $('.delete-payment').click(function() {
        var payment_id = $(this).data('id');
        _conf("Are you sure you want to delete this payment record? This action cannot be undone.", "delete_payment", [payment_id]);
    });
});

function delete_payment(payment_id) {
    start_loader();
    $.ajax({
        url: '/cdsms/classes/Enrollments.php?action=delete_payment',
        method: 'POST',
        data: { id: payment_id },
        dataType: 'json',
        error: function(err) {
            console.error(err);
            alert_toast("An error occurred while deleting the payment record", 'error');
            end_loader();
        },
        success: function(resp) {
            if (resp.status === 'success') {
                alert_toast(resp.message || "Payment record successfully deleted", 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                alert_toast(resp.message || "Failed to delete payment record", 'error');
                end_loader();
            }
        }
    });
}
</script>