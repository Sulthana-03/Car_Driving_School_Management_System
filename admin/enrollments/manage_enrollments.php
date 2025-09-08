<?php
// Handle AJAX request to get instructor details FIRST
if (isset($_GET['action']) && $_GET['action'] === 'get_instructor' && isset($_GET['id'])) {
    require_once __DIR__ . '/../../config.php'; // Make sure DB constants are available
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
    }

    $id = $conn->real_escape_string($_GET['id']);
    $qry = $conn->query("SELECT
        id, instructor_code, CONCAT(firstname, ' ', lastname) as name,
        firstname, lastname, avatar, phone, email, address
        FROM instructor WHERE id = '$id' AND status = 1");

    if ($qry && $qry->num_rows > 0) {
        $data = $qry->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'id' => $data['id'],
            'instructor_code' => $data['instructor_code'],
            'name' => $data['name'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'],
            'avatar' => !empty($data['avatar'])
                ? '/cdsms/uploads/avatar/' . basename($data['avatar'])
                : '/cdsms/images/default_avatar.png'

        ]);
    } else {
        echo json_encode(['status' => 'failed', 'message' => 'Instructor not found']);
    }
    exit;
}

// --- Check for enrollment ID ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Enrollment ID is required.'); location.href = '?page=enrollments';</script>";
    exit;
}

// Connect to database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$id = $conn->real_escape_string($_GET['id']);
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

if ($qry->num_rows <= 0) {
    echo "<script>alert('Enrollment not found.'); location.href = '?page=enrollments';</script>";
    exit;
}

$row = $qry->fetch_assoc();
// Store the old row data for comparison later
$old_row = $row;


// Get payment history for this enrollment
$payment_history = $conn->query("SELECT * FROM payment_list WHERE enrollees_id = '$id' ORDER BY date_created DESC");

// Calculate total paid (only 'paid' payments)
$total_paid = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'paid'")->fetch_assoc()['total'];
$total_paid = $total_paid ? $total_paid : 0;

// Calculate total pending (only 'pending' payments)
$total_pending = $conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'pending'")->fetch_assoc()['total'];
$total_pending = $total_pending ? $total_pending : 0;

// Calculate balance (total cost minus *only paid* payments)
$balance = $row['cost'] - $total_paid;


// Check if partial payment was made (considering only 'paid' partials for this check if desired)
$has_partial_payment = $conn->query("SELECT 1 FROM payment_list WHERE enrollees_id = '$id' AND amount_type = 'partial' AND payment_status = 'paid'")->num_rows > 0;

// Close the database connection
$conn->close();
?>

<style>
.img-avatar {
    width: 100px;
    height: 100px;
    object-fit: cover;
    object-position: center;
    border-radius: 100%;
}

.section-title {
    font-size: 1.2rem;
    color: #007bff;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
    margin-bottom: 15px;
}

.payment-summary-card {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}

.payment-history-table th {
    background-color: #e9ecef;
}

.status-badge {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
}

.payment-form {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
}
/* Add this to your style section */
.payment-history-table td {
    vertical-align: middle !important;
}

.payment-history-table .edit-mode {
    display: none;
    width: 100%;
}

.payment-history-table .form-control {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.payment-history-table .btn-group {
    white-space: nowrap;
}

.payment-history-table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

#upi_reference_field {
    display: none;
}
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Manage Enrollment</h3>
    </div>
    <form id="enrollment-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
        <div class="card-body">

            <fieldset>
                <legend class="section-title"><h5>User Details</h5></legend>
                <div class="row">
                    <div class="col-md-4">
                        <label>Enrollment No</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['enrollment_no']) ?>">
                        <input type="hidden" id="enrollment_no_hidden" value="<?= htmlspecialchars($row['enrollment_no']) ?>">
                        <input type="hidden" id="user_email_hidden" value="<?= htmlspecialchars($row['email']) ?>">
                        <input type="hidden" id="user_name_hidden" value="<?= htmlspecialchars($row['fullname']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Name</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['fullname']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Phone No</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['phone']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Alternative No</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['alternative_no']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Email</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['email']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Gender</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['gender']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Date of Birth</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['date_of_birth']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Age</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['age']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Address</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['address']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>LLR No</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['LLR_no']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>LLR Copy</label><br>
                        <?php if (!empty($row['copy_of_LLR'])): ?>
                            <a href="<?= htmlspecialchars('http://localhost/cdsms/uploads/LLR_copy/' . $row['copy_of_LLR']) ?>" target="_blank">View</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mt-4">
                <legend class="section-title"><h5>Package Details</h5></legend>
                <div class="row">
                    <div class="col-md-4">
                        <label>Package Name</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['package_name']) ?>">
                        <input type="hidden" id="package_name_hidden" value="<?= htmlspecialchars($row['package_name']) ?>">
                        <input type="hidden" id="package_cost_hidden" value="<?= htmlspecialchars($row['cost']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Duration</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['training_duration']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Cost</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['cost']) ?>">
                    </div>
                </div>
            </fieldset>


            <fieldset class="mt-4">
                <legend class="section-title"><h5>Class Details</h5></legend>
                <div class="row">
                    <div class="col-md-4">
                        <label>Enrollment Status</label>
                        <select name="enrollment_status" class="form-control" id="enrollment_status">
                            <option value="Pending" <?= $row['enrollment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Verified" <?= $row['enrollment_status'] == 'Verified' ? 'selected' : '' ?>>Verified</option>
                            <option value="In-Session" <?= $row['enrollment_status'] == 'In-Session' ? 'selected' : '' ?>>In-Session</option>
                            <option value="Completed" <?= $row['enrollment_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $row['enrollment_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Payment Status</label>
                        <select name="payment_status" class="form-control" id="payment_status">
                            <option value="not set" <?= ($row['payment_status'] == 'not set' || empty($row['payment_status'])) ? 'selected' : '' ?>>not set</option>
                            <option value="Pending" <?= $row['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Partially Paid" <?= $row['payment_status'] == 'Partially Paid' ? 'selected' : '' ?>>Partially Paid</option>
                            <option value="Paid" <?= $row['payment_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Cancelled" <?= $row['payment_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Timeslot</label>
                        <input type="text" readonly class="form-control" value="<?= htmlspecialchars($row['time_slot']) ?>">
                        <input type="hidden" id="time_slot_hidden" value="<?= htmlspecialchars($row['time_slot']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($row['start_date']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($row['end_date']) ?>">
                    </div>
                </div>
            </fieldset>


            <fieldset class="mt-4">
                <legend class="section-title"><h5>Instructor Details</h5></legend>
                <div class="row">
                    <div class="col-md-6">
                        <label>Assign Instructor</label>
                        <select name="assigned_instructor_id" id="assigned_instructor_id" class="form-control select2">
                            <option value="">-- Select Instructor --</option>
                            <?php if (!empty($row['assigned_instructor_id']) && !empty($row['instructor_firstname'])): ?>
                                <option value="<?= $row['assigned_instructor_id'] ?>" selected>
                                    <?= htmlspecialchars($row['instructor_firstname'] . ' ' . $row['instructor_lastname']) ?>
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-3 align-items-center" id="instructor-info" style="display: <?= !empty($row['assigned_instructor_id']) ? 'flex' : 'none' ?>;">
                    <div class="col-md-2 text-center">
                        <img id="instructor-avatar" class="img-avatar img-thumbnail"
                             src="<?= !empty($row['instructor_avatar']) ? '/cdsms/uploads/avatar/' . basename($row['instructor_avatar']) : '/cdsms/images/default_avatar.png' ?>"
                             alt="Instructor Avatar">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Instructor Code</label>
                                <input type="text" readonly class="form-control" id="instructor-code" value="<?= htmlspecialchars($row['instructor_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Name</label>
                                <input type="text" readonly class="form-control" id="instructor-name" value="<?= htmlspecialchars(($row['instructor_firstname'] ?? '') . ' ' . ($row['instructor_lastname'] ?? '')) ?>">
                                <input type="hidden" id="instructor_name_hidden" value="<?= htmlspecialchars(($row['instructor_firstname'] ?? '') . ' ' . ($row['instructor_lastname'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Phone</label>
                                <input type="text" readonly class="form-control" id="instructor-phone" value="<?= htmlspecialchars($row['instructor_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="text" readonly class="form-control" id="instructor-email" value="<?= htmlspecialchars($row['instructor_email'] ?? '') ?>">
                                <input type="hidden" id="instructor_email_hidden" value="<?= htmlspecialchars($row['instructor_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-12">
                                <label>Address</label>
                                <input type="text" readonly class="form-control" id="instructor-address" value="<?= htmlspecialchars($row['instructor_address'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div id="payment-details-section" style="display: <?= !empty($row['assigned_instructor_id']) ? 'block' : 'none' ?>;">
                <fieldset class="mt-4">
                    <legend class="section-title"><h5>Payment Details</h5></legend>

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
                <td id="total_paid_display">₹<?= number_format($total_paid, 2) ?></td>
            </tr>
            <?php if($total_pending > 0): ?>
            <tr>
                <th>Pending Payments</th>
                <td id="total_pending_display">₹<?= number_format($total_pending, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="align-middle">Balance</th>
                <td class="fw-bold fs-5" id="balance_display">₹<?= number_format($row['cost'] - $total_paid, 2) ?></td>
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $payment_history->data_seek(0); // Reset pointer to start
            while($payment = $payment_history->fetch_assoc()):
            ?>
            <tr data-payment-id="<?= $payment['id'] ?>">
                <td class="payment-date"><?= date('d M Y H:i', strtotime($payment['date_created'])) ?></td>
                <td class="payment-type">
                    <span class="view-mode"><?= ucfirst($payment['amount_type']) ?></span>
                    <select class="form-control edit-mode" style="display:none">
                        <option value="partial" <?= $payment['amount_type'] == 'partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="full" <?= $payment['amount_type'] == 'full' ? 'selected' : '' ?>>Full</option>
                    </select>
                </td>
                <td class="payment-amount">
    <span class="view-mode">₹<?= number_format($payment['amount'], 2) ?></span>
    <input type="number" class="form-control edit-mode" style="display:none"
           value="<?= $payment['amount'] ?>" step="0.01" min="0"> </td>
                <td class="payment-mode">
                    <span class="view-mode"><?= strtoupper($payment['payment_mode']) ?></span>
                    <select class="form-control edit-mode" style="display:none">
                        <option value="cash" <?= $payment['payment_mode'] == 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="upi" <?= $payment['payment_mode'] == 'upi' ? 'selected' : '' ?>>UPI</option>
                    </select>
                </td>
                <td class="payment-status">
                    <?php
                    $status = $payment['payment_status'];
                    $badge_class = '';
                    switch($status) {
                        case 'pending': $badge_class = 'bg-secondary'; break;
                        case 'paid': $badge_class = 'bg-success'; break;
                        default: $badge_class = 'bg-secondary';
                    }
                    ?>
                    <span class="badge status-badge view-mode <?= $badge_class ?>"><?= ucfirst($status) ?></span>
                    <select class="form-control edit-mode" style="display:none">
                        <option value="pending" <?= $payment['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="paid" <?= $payment['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                </td>
                <td class="payment-reference">
                    <span class="view-mode"><?= !empty($payment['upi_reference']) ? htmlspecialchars($payment['upi_reference']) : 'N/A' ?></span>
                    <input type="text" class="form-control edit-mode" style="display:none"
                           value="<?= htmlspecialchars($payment['upi_reference'] ?? '') ?>">
                </td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-payment">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-payment" data-payment-id="<?= $payment['id'] ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-success save-payment" style="display:none" data-payment-id="<?= $payment['id'] ?>">
                            <i class="fa fa-save"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary cancel-edit" style="display:none">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
                    </div>
                    <?php endif; ?>


                    <?php if ($balance > 0): ?>
                    <div class="payment-form">
                        <h5>Add Payment</h5>
                        <div class="row">
                            <div class="col-md-3">
    <label class="form-label fw-bold">Amount Type</label>
    <select name="payment_amount_type" class="form-control" id="payment_amount_type">
        <option value="">Select</option>
        <option value="partial">Partial Payment</option>
        <option value="full" <?= $has_partial_payment ? 'disabled' : '' ?>>Full Payment</option>
    </select>
    <?php if ($has_partial_payment): ?>
        <small class="text-muted">Partial payment already made. Only partial payment available.</small>
    <?php endif; ?>
</div>
<div class="col-md-3">
    <label class="form-label fw-bold">Amount</label>

    <input type="text" id="payment_amount" name="amount" class="form-control" readonly required>
    <input type="hidden" id="partial_amount" value="<?= $row['partial1'] ?>">
    <input type="hidden" id="full_amount_to_pay" value="<?= $balance ?>">
</div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Payment Mode</label>
                                <select name="payment_mode" class="form-control" id="payment_mode">
                                    <option value="">Select</option>
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Payment Status</label>
                                <select name="payment_entry_status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-3" id="upi_reference_field">
                                <label class="form-label fw-bold">Reference/Notes</label>
                                <input type="text" name="payment_reference" class="form-control" placeholder="Transaction reference or notes">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            <?php else: ?>
    <div class="alert alert-success mt-3">
        <i class="fa fa-check-circle"></i> Payment completed. No balance remaining.
    </div>
<?php endif; ?>

            <fieldset class="mt-4">
                <legend class="section-title"><h5>Additional details<small>(for internal use)</small></h5></legend>
                <div class="row">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($row['remarks']) ?></textarea>
                    </div>
                </div>
            </fieldset>

        </div>
        <div class="card-footer text-right">
            <button type="submit" id="save-btn" class="btn btn-flat btn-primary">Save</button>
            <a href="?page=enrollments/index" class="btn btn-default bg-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="<?php echo base_url ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo base_url ?>/plugins/select2/js/select2.full.min.js"></script>

<script>
// PHP variables available in JavaScript for comparison
const oldInstructorId = "<?= $old_row['assigned_instructor_id'] ?? '' ?>";
const oldEnrollmentStatus = "<?= $old_row['enrollment_status'] ?>";
const oldPaymentStatus = "<?= $old_row['payment_status'] ?>";
const oldTotalPaid = parseFloat("<?= $total_paid ?>"); // Current total paid from DB

$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    const $assignedInstructor = $('#assigned_instructor_id');
    const $startDate = $('input[name="start_date"]');
    const $endDate = $('input[name="end_date"]');
    const $enrollmentStatus = $('#enrollment_status');
    const $paymentStatus = $('#payment_status');
    const $paymentDetailsSection = $('#payment-details-section');
    const $paymentMode = $('#payment_mode');
    const $upiReferenceField = $('#upi_reference_field');

    // Show/hide UPI reference field based on payment mode
    $paymentMode.on('change', function() {
        if ($(this).val() === 'upi') {
            $upiReferenceField.show();
        } else {
            $upiReferenceField.hide();
        }
    });

    // Trigger change event on page load if payment mode is already selected
    if ($paymentMode.val() === 'upi') {
        $upiReferenceField.show();
    }

    const today = new Date().toISOString().split('T')[0];
    $startDate.attr('min', today);

    const updateEndDateMin = () => {
        const start = $startDate.val();
        if (start) {
            $endDate.attr('min', start);
            // If end date is before start date, reset it
            if ($endDate.val() && $endDate.val() < start) {
                $endDate.val(start);
            }
        } else {
            $endDate.removeAttr('min');
        }
    };


    // Handle payment amount type change
    $('#payment_amount_type').on('change', function() {
        const paymentType = $(this).val();
        const $amountField = $('#payment_amount');
        const partialAmount = parseFloat($('#partial_amount').val()) || 0;
        const fullAmountToPay = parseFloat($('#full_amount_to_pay').val()) || 0; // Use new ID
        
        if (paymentType === 'partial') {
            $amountField.val(partialAmount.toFixed(2));
        } else if (paymentType === 'full') {
            $amountField.val(fullAmountToPay.toFixed(2));
        } else {
            $amountField.val('');
        }
    });

    // Initial setup for amount field on page load
    const initialType = $('#payment_amount_type').val();
    if (initialType) {
        $('#payment_amount_type').trigger('change');
    }

    // Initial setup for dates
    updateEndDateMin();

    // Update end date min on start date change
    $startDate.on('change', updateEndDateMin);

    // Load instructor info if already selected
    const initialInstructorId = $assignedInstructor.val();
    if (initialInstructorId) {
        loadInstructorDetails(initialInstructorId);
    }

    // Handle enrollment status change
    $enrollmentStatus.on('change', function() {
        const status = $(this).val();
        
        if (status === 'Cancelled') {
            // If status is cancelled, set payment status to cancelled
            $paymentStatus.val('Cancelled');
            $paymentDetailsSection.hide();
        } else {
            // If instructor is assigned, show payment details
            if ($assignedInstructor.val()) {
                $paymentDetailsSection.show();
            }
            
            // Reset payment status if it was cancelled
            if ($paymentStatus.val() === 'Cancelled') {
                // You might want to revert to the old status or 'not set' depending on your logic
                // For now, let's just make it empty which will fall to 'not set' on next reload
                $paymentStatus.val('not set');
            }
        }
    });

    // Trigger initial enrollment status change to ensure correct UI state on load
    $enrollmentStatus.trigger('change');

    $assignedInstructor.change(function() {
        const instructorId = $(this).val();
        loadInstructorDetails(instructorId);
        
        // Show/hide payment details based on instructor assignment
        if (instructorId && $enrollmentStatus.val() !== 'Cancelled') {
            $paymentDetailsSection.show();
        } else {
            $paymentDetailsSection.hide();
        }
    });

    function loadAvailableInstructors() {
        const startDate = $('input[name="start_date"]').val();
        const endDate = $('input[name="end_date"]').val();
        const timeSlot = $('#time_slot_hidden').val(); // Get from hidden input
        const enrollmentId = "<?= $row['id'] ?>"; // Use PHP variable

        // Validate dates first
        if (!startDate || !endDate) return;
        if (new Date(startDate) > new Date(endDate)) {
            alert_toast('End date cannot be before start date', 'error');
            return;
        }

        start_loader();
        
        $.ajax({
            url: '<?php echo base_url ?>/classes/Enrollments.php?action=get_available_instructors',
            method: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate,
                time_slot: timeSlot,
                id: enrollmentId
            },
            dataType: 'json',
            success: function(res) {
                end_loader();
                const $dropdown = $('#assigned_instructor_id');
                const currentInstructor = oldInstructorId; // Use the old value for comparison
                
                $dropdown.empty().append(`<option value="">-- Select Instructor --</option>`);

                if (res.status === 'success') {
                    // Add available instructors
                    res.instructors.forEach(inst => {
                        $dropdown.append(
                            `<option value="${inst.id}" ${inst.id == currentInstructor ? 'selected' : ''}>
                                ${inst.name}
                            </option>`
                        );
                    });

                    // If current instructor exists but isn't available, keep them
                    // This handles cases where an instructor might become unavailable but is already assigned
                    if (currentInstructor && !res.instructors.some(i => i.id == currentInstructor)) {
                         const currentName = "<?= ($old_row['instructor_firstname'] ?? '') . ' ' . ($old_row['instructor_lastname'] ?? '') ?>";
                        // Only add if it's not already in the list to avoid duplicates
                        if ($dropdown.find(`option[value="${currentInstructor}"]`).length === 0) {
                            $dropdown.append(
                                `<option value="${currentInstructor}" selected>
                                    ${currentName}
                                </option>`
                            );
                        }
                    }
                     $dropdown.trigger('change.select2'); // Update select2 display
                } else {
                    alert_toast(res.message || 'Failed to load instructors', 'error');
                }
            },
            error: function(xhr) {
                end_loader();
                let msg = 'Error loading instructors';
                try {
                    const res = JSON.parse(xhr.responseText);
                    msg = res.message || msg;
                } catch(e) {}
                alert_toast(msg, 'error');
            }
        });
    }

    $('input[name="start_date"], input[name="end_date"]').on('change', function () {
        loadAvailableInstructors();
    });

    // Call once on page load
    loadAvailableInstructors();

    // Payment edit functionality
    $(document).on('click', '.edit-payment', function(e) {
        e.preventDefault();
        const $row = $(this).closest('tr');
        
        // Store original values for cancel functionality
        $row.data('original-type', $row.find('.payment-type select').val());
        $row.data('original-amount', $row.find('.payment-amount input').val());
        $row.data('original-mode', $row.find('.payment-mode select').val());
        $row.data('original-status', $row.find('.payment-status select').val());
        $row.data('original-reference', $row.find('.payment-reference input').val());

        // Hide view mode, show edit mode
        $row.find('.view-mode').hide();
        $row.find('.edit-mode').show();
        
        // Show save/cancel buttons, hide edit button
        $(this).hide();
        $row.find('.delete-payment').hide();
        $row.find('.save-payment').show();
        $row.find('.cancel-edit').show();
    });

    $(document).on('click', '.cancel-edit', function(e) {
        e.preventDefault();
        const $row = $(this).closest('tr');
        
        // Restore original values
        $row.find('.payment-type select').val($row.data('original-type'));
        $row.find('.payment-amount input').val($row.data('original-amount'));
        $row.find('.payment-mode select').val($row.data('original-mode'));
        $row.find('.payment-status select').val($row.data('original-status'));
        $row.find('.payment-reference input').val($row.data('original-reference'));

        // Show view mode, hide edit mode
        $row.find('.view-mode').show();
        $row.find('.edit-mode').hide();
        
        // Show edit/delete buttons, hide save/cancel
        $row.find('.edit-payment').show();
        $row.find('.delete-payment').show();
        $row.find('.save-payment').hide();
        $(this).hide();
    });

  $(document).on('click', '.save-payment', function(e) {
    e.preventDefault();
    const paymentId = $(this).data('payment-id');
    const $row = $(this).closest('tr');
    
    // Get raw amount value without formatting
    const rawAmount = $row.find('.payment-amount input').val();
    const amount = parseFloat(rawAmount);
    
    const paymentData = {
        amount_type: $row.find('.payment-type select').val(),
        amount: amount,
        payment_mode: $row.find('.payment-mode select').val(),
        payment_status: $row.find('.payment-status select').val(),
        upi_reference: $row.find('.payment-reference input').val().trim()
    };
    
    if (isNaN(paymentData.amount) || paymentData.amount <= 0) {
        alert_toast('Please enter a valid amount', 'error');
        return;
    }
    
    start_loader();
    
    $.ajax({
        url: '<?php echo base_url ?>/classes/Enrollments.php?action=update_payment',
        method: 'POST',
        data: {
            id: paymentId,
            ...paymentData
        },
        dataType: 'json',
        success: function(resp) {
            if(resp.status == 'success') {
                // Update view mode values
                $row.find('.payment-type .view-mode').text(paymentData.amount_type.charAt(0).toUpperCase() + paymentData.amount_type.slice(1));
                $row.find('.payment-amount .view-mode').text('₹' + paymentData.amount.toFixed(2));
                $row.find('.payment-mode .view-mode').text(paymentData.payment_mode.toUpperCase());
                
                // Update status badge
                const statusText = paymentData.payment_status.charAt(0).toUpperCase() + paymentData.payment_status.slice(1);
                let badgeClass = 'bg-secondary';
                if (paymentData.payment_status === 'paid') badgeClass = 'bg-success';
                
                $row.find('.payment-status .view-mode')
                    .removeClass('bg-secondary bg-success')
                    .addClass(badgeClass)
                    .text(statusText);
                
                $row.find('.payment-reference .view-mode').text(
                    paymentData.upi_reference || 'N/A'
                );
                
                // Switch back to view mode
                $row.find('.view-mode').show();
                $row.find('.edit-mode').hide();
                $row.find('.edit-payment').show();
                $row.find('.delete-payment').show();
                $row.find('.save-payment').hide();
                $row.find('.cancel-edit').hide();
                
                // If payment status was updated to paid, send email
                if (paymentData.payment_status === 'paid') {
                    const enrollmentNo = $('#enrollment_no_hidden').val();
                    const userEmail = $('#user_email_hidden').val();
                    const userName = $('#user_name_hidden').val();
                    const packageName = $('#package_name_hidden').val();
                    const totalPackageCost = parseFloat($('#package_cost_hidden').val());
                    
                    // Get updated payment totals from server
                    $.ajax({
                        url: '<?php echo base_url ?>/classes/Enrollments.php?action=get_payment_summary',
                        method: 'POST',
                        data: { id: "<?= $row['id'] ?>" },
                        dataType: 'json',
                        success: function(summary) {
                            if (summary.status === 'success') {
                            // Determine correct payment status based on balance
                                const paymentStatus = summary.balance <= 0 ? 'Paid' : 'Partially Paid';
                                // Send payment status email
                                $.ajax({
                                    url: '<?php echo base_url ?>/classes/mail_handler.php',
                                    method: 'POST',
                                    data: {
                                        action: 'send_payment_status_email',
                                        user_email: userEmail,
                                        user_name: userName,
                                        enrollment_no: enrollmentNo,
                                        package_cost: totalPackageCost,
                                        total_paid: summary.total_paid,
                                        balance: summary.balance,
                                        payment_status: paymentStatus // or 'Paid' depending on your logic
                                    },
                                    dataType: 'json',
                                    success: function(mailResp) {
                                        if (mailResp.status === 'success') {
                                            console.log('Payment status email sent');
                                        } else {
                                            console.error('Failed to send payment status email');
                                        }
                                        // Reload after email attempt
                                        location.reload();
                                    }
                                });
                            } else {
                                location.reload();
                            }
                        }
                    });
                } else {
                    location.reload();
                }
            } else {
                alert_toast(resp.message || "Failed to update payment", 'error');
                end_loader();
            }
        },
        error: function(xhr) {
            console.error(xhr);
            alert_toast("An error occurred while updating the payment", 'error');
            end_loader();
        }
    });
});

    $(document).on('click', '.delete-payment', function(e) {
        e.preventDefault();
        var paymentId = $(this).data('payment-id');
        
        if(confirm("Are you sure you want to delete this payment record?")) {
            start_loader();
            $.ajax({
                url: '<?php echo base_url ?>/classes/Enrollments.php?action=delete_payment',
                method: 'POST',
                data: {id: paymentId},
                dataType: 'json',
                success: function(resp) {
                    if(resp.status == 'success') {
                        alert_toast("Payment record successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload(); // Reload to update payment summary
                        }, 1500);
                    } else {
                        alert_toast(resp.message || "Failed to delete payment record", 'error');
                        end_loader();
                    }
               
            },
            error: function(xhr) {
                console.error(xhr);
                alert_toast("An error occurred while deleting the payment record", 'error');
                end_loader();
            }
        });
    }
});
    function loadInstructorDetails(id) {
        if (!id) {
            $('#instructor-info').hide();
            $('#instructor_name_hidden').val('');
            $('#instructor_email_hidden').val('');
            return;
        }
        $.ajax({
            url: '<?php echo base_url ?>/admin/enrollments/manage_enrollments.php?action=get_instructor&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    $('#instructor-code').val(data.instructor_code || '');
                    $('#instructor-name').val(data.name || '');
                    $('#instructor-phone').val(data.phone || '');
                    $('#instructor-email').val(data.email || '');
                    $('#instructor-address').val(data.address || '');
                    $('#instructor-avatar').attr('src', data.avatar);

                    // Update hidden fields for email sending
                    $('#instructor_name_hidden').val(data.name || '');
                    $('#instructor_email_hidden').val(data.email || '');

                    $('#instructor-info').show();
                } else {
                    $('#instructor-info').hide();
                    $('#instructor_name_hidden').val('');
                    $('#instructor_email_hidden').val('');
                    alert('Failed to load instructor details: ' + (data.message || 'Instructor not found'));
                }
            },
            error: function(err) {
                console.error('Error fetching instructor:', err);
                $('#instructor-info').hide();
                $('#instructor_name_hidden').val('');
                $('#instructor_email_hidden').val('');
                alert('Error loading instructor details. Check console for details.');
            }
        });
    }

  $('#enrollment-form').on('submit', function (e) {
    e.preventDefault();
    
    const $submitBtn = $('#save-btn');
    const formData = new FormData(this);

    // Get current values for comparison
    const currentAssignedInstructorId = $('#assigned_instructor_id').val();
    const currentEnrollmentStatus = $('#enrollment_status').val();
    const currentPaymentStatus = $('#payment_status').val(); // This is the main enrollment payment status

    // Payment validation - only if payment fields are filled
    const paymentType = $('#payment_amount_type').val();
    const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
    const paymentMode = $('select[name="payment_mode"]').val();
    const paymentEntryStatus = $('select[name="payment_entry_status"]').val();
    const paymentReference = $('input[name="payment_reference"]').val();

    // Only add payment data if payment type is selected AND amount is valid
    if (paymentType && paymentAmount > 0) {
        // Validate payment details only if payment is being made
        if (!paymentMode) {
            alert_toast('Please select payment mode', 'error');
            return;
        }
        if (!paymentEntryStatus) {
            alert_toast('Please select payment status for new entry', 'error');
            return;
        }
        if (paymentMode === 'upi' && !paymentReference) {
            alert_toast('Please enter UPI reference for UPI payments', 'error');
            return;
        }
        
        formData.append('save_payment', '1');
        formData.append('amount_type', paymentType);
        formData.append('amount', paymentAmount);
        formData.append('payment_mode', paymentMode);
        formData.append('payment_entry_status', paymentEntryStatus);
        formData.append('upi_reference', paymentReference);
    }

    // If enrollment is cancelled, set payment status to Cancelled and clear instructor/dates
    if (currentEnrollmentStatus === 'Cancelled') {
        formData.set('payment_status', 'Cancelled');
        formData.set('assigned_instructor_id', '');
        formData.set('start_date', '');
        formData.set('end_date', '');
    }

    start_loader();
    $submitBtn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: '<?php echo base_url ?>/classes/Enrollments.php?action=save', // Submit to central Enrollment handler
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                alert_toast('Enrollment details saved successfully.', 'success');

                // --- Email Sending Logic ---
                const enrollmentNo = $('#enrollment_no_hidden').val();
                const userEmail = $('#user_email_hidden').val();
                const userName = $('#user_name_hidden').val();
                const instructorEmail = $('#instructor_email_hidden').val();
                const instructorName = $('#instructor_name_hidden').val();
                const packageName = $('#package_name_hidden').val();
                const timeSlot = $('#time_slot_hidden').val();
                const startDate = $('input[name="start_date"]').val();
                const endDate = $('input[name="end_date"]').val();
                const totalPackageCost = parseFloat($('#package_cost_hidden').val());
                const currentTotalPaid = parseFloat($('#total_paid_display').text().replace('₹', '').replace(/,/g, ''));
                const currentBalance = parseFloat($('#balance_display').text().replace('₹', '').replace(/,/g, ''));


                // Task 1: Instructor assigned email (to user AND instructor)
                // Trigger if instructor changes AND enrollment is Verified
                if (currentAssignedInstructorId && currentEnrollmentStatus === 'Verified' && currentAssignedInstructorId !== oldInstructorId) {
                    if (instructorEmail && instructorName) { // Ensure instructor details are available
                        $.ajax({
                            url: '<?php echo base_url ?>/classes/mail_handler.php',
                            method: 'POST',
                            data: {
                                action: 'send_instructor_assignment_email',
                                user_email: userEmail,
                                user_name: userName,
                                instructor_email: instructorEmail,
                                instructor_name: instructorName,
                                enrollment_no: enrollmentNo,
                                package_name: packageName,
                                time_slot: timeSlot,
                                start_date: startDate,
                                end_date: endDate
                            },
                            dataType: 'json',
                            success: function(mailResp) {
                                if (mailResp.status === 'success') {
                                    console.log('Instructor assignment email sent: ' + mailResp.message);
                                } else {
                                    console.error('Failed to send instructor assignment email: ' + mailResp.message);
                                }
                            },
                            error: function(mailXhr) {
                                console.error('AJAX error sending instructor assignment email:', mailXhr.responseText);
                            }
                        });
                    } else {
                        console.warn("Instructor email or name not found for sending assignment email.");
                    }
                }

                // Task 2: Payment Status change email (to user only)
if (currentPaymentStatus !== oldPaymentStatus || (paymentType && paymentAmount > 0)) {
    $.ajax({
        url: '<?php echo base_url ?>/classes/Enrollments.php?action=get_payment_summary',
        method: 'POST',
        data: { id: "<?= $row['id'] ?>" },
        dataType: 'json',
        success: function(summary) {
            if (summary.status === 'success') {
                // Determine status based on payment entry status and balance
                let displayStatus;
if (currentPaymentStatus === 'Cancelled') {
    displayStatus = 'Cancelled';
} else {
    displayStatus = (paymentEntryStatus === 'paid') 
        ? (summary.balance <= 0 ? 'Paid' : 'Partially Paid')
        : 'Pending';
}
                
                $.ajax({
                    url: '<?php echo base_url ?>/classes/mail_handler.php',
                    method: 'POST',
                    data: {
                        action: 'send_payment_status_email',
                        user_email: userEmail,
                        user_name: userName,
                        enrollment_no: enrollmentNo,
                        package_cost: totalPackageCost,
                        total_paid: summary.total_paid,
                        balance: summary.balance,
                        payment_status: displayStatus
                    },
                    dataType: 'json'
                });
            }
        }
    });
}
                // Task 3: Enrollment Status change email (to user only)
                // Trigger if enrollment status changes (excluding the case where it becomes cancelled and payment status is also cancelled)
                if (currentEnrollmentStatus !== oldEnrollmentStatus) {
                    $.ajax({
                        url: '<?php echo base_url ?>/classes/mail_handler.php',
                        method: 'POST',
                        data: {
                            action: 'send_enrollment_status_email',
                            user_email: userEmail,
                            user_name: userName,
                            enrollment_no: enrollmentNo,
                            enrollment_status: currentEnrollmentStatus
                        },
                        dataType: 'json',
                        success: function(mailResp) {
                            if (mailResp.status === 'success') {
                                console.log('Enrollment status email sent: ' + mailResp.message);
                            } else {
                                console.error('Failed to send enrollment status email: ' + mailResp.message);
                            }
                        },
                        error: function(mailXhr) {
                            console.error('AJAX error sending enrollment status email:', mailXhr.responseText);
                        }
                    });
                }
                
                setTimeout(() => {
                    location.href = '<?php echo base_url ?>/admin/index.php?page=enrollments';
                }, 1500);

            } else {
                alert_toast(response.message || 'Something went wrong', 'error');
            }
        },
        error: function (xhr) {
            console.error('AJAX error:', xhr.responseText);
            try {
                const response = JSON.parse(xhr.responseText);
                alert_toast(response.message || 'Error occurred', 'error');
            } catch (e) {
                alert_toast('Unexpected server error', 'error');
            }
        },
        complete: function () {
            end_loader();
            $submitBtn.prop('disabled', false).text('Save');
        }
    });
});
});
</script>