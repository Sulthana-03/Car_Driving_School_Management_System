<?php
// view_enrollments.php - Read-only detail view

include_once('../config.php');

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
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Enrollment Details</h3>
        <div class="card-tools">

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
                            case 'In-Session': $badge_class = 'badge-warning'; break;
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
                        $payment_status = $row['payment_status'];
                        $badge_class = '';
                        switch($payment_status) {
                            case 'Pending': $badge_class = 'badge-secondary'; break;
                            case 'Paid': $badge_class = 'badge-success'; break;
                            case 'Partially Paid': $badge_class = 'badge-warning'; break;
                            case 'Cancelled': $badge_class = 'badge-danger'; break;
                            default: $badge_class = 'badge-secondary';
                        }
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= $payment_status ?></span>
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
        <?php endif; ?>

        <!-- Date Created -->
        <div class="row mt-3">
            <div class="col-md-12 text-muted">
                <small>Record created on <?= date('F d, Y H:i', strtotime($row['created_at'])) ?></small>
            </div>
        </div>
    </div>
</div>