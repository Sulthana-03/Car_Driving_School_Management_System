<?php
// new_upi_payments.php - List of New UPI Payments (last 24 hours)
if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?= $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<style>
    /* Combine styles from both pages */
    .badge-orange {
        background-color: orange;
        color: black;
        padding: 0.35em 0.6em;
        font-size: 0.75em;
        border-radius: 0.25rem;
    }
    
    .payment-mode-badge {
        font-size: 0.75em;
        border-radius: 0.25rem;
        padding: 0.35em 0.6em;
    }
    .badge-upi {
        background-color: #007bff;
        color: white;
    }
    
    /* Responsive enhancements */
    @media (max-width: 768px) {
        .card-body {
            padding: 0.5rem;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            display: block;
        }
        .table thead {
            display: none;
        }
        .table tbody {
            display: block;
        }
        .table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            text-align: right;
            border-bottom: 1px solid #dee2e6;
        }
        .table td:before {
            content: attr(data-label);
            font-weight: bold;
            padding-right: 1rem;
            text-align: left;
        }
        .btn-group {
            justify-content: flex-end;
        }
    }
    
    /* Summary card from reports */
    .report-summary {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .summary-item {
        margin-bottom: 5px;
        font-size: 16px;
    }
    .summary-item strong {
        font-weight: 600;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">New UPI Payments (Last 24 Hours)</h3>
    </div>
    <div class="card-body">
        <?php
        // Calculate summary data for UPI payments in last 24 hours
        $summary_qry = $conn->query("SELECT 
            COUNT(*) as total_payments,
            SUM(amount) as total_amount
            FROM payment_list 
            WHERE payment_mode = 'upi' 
            AND payment_status = 'paid'
            AND date_created >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
        
        $summary = $summary_qry->fetch_assoc();
        $total_payments = $summary['total_payments'] ?? 0;
        $total_amount = $summary['total_amount'] ?? 0;
        ?>
        
        <!-- Summary Section -->
        <div class="report-summary">
            
            <div class="summary-item"><strong>Total UPI Payments:</strong> <?= $total_payments ?></div>
            <div class="summary-item"><strong>Total Amount:</strong> ₹<?= number_format($total_amount, 2) ?></div>
        </div>
        
        <!-- Payments Table -->
        <table class="table table-hover table-striped" id="upiPaymentsTable">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Enrollment No</th>
                    <th>Student</th>
                    <th>Package</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Reference</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $i = 1;
                $qry = $conn->query("SELECT 
                    p.*, 
                    e.enrollment_no, 
                    e.package_id,
                    u.name as student_name,
                    pl.name as package_name
                    FROM payment_list p
                    JOIN enrollees e ON e.id = p.enrollees_id
                    JOIN users u ON u.id = e.user_id
                    LEFT JOIN package_list pl ON pl.id = e.package_id
                    WHERE p.payment_mode = 'upi'
                    AND p.payment_status = 'paid'
                    AND p.date_created >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                    ORDER BY p.date_created DESC");

                while($row = $qry->fetch_assoc()):
                    $payment_date = date("M d, Y h:i A", strtotime($row['date_created']));
                ?>
                <tr>
                    <td data-label="#"><?= $i++ ?></td>
                    <td data-label="Enrollment No"><?= $row['enrollment_no'] ?></td>
                    <td data-label="Student"><?= ucwords($row['student_name']) ?></td>
                    <td data-label="Package"><?= ucwords($row['package_name']) ?></td>
                    <td data-label="Amount">₹<?= number_format($row['amount'], 2) ?></td>
                    <td data-label="Payment Date"><?= $payment_date ?></td>
                    <td data-label="Reference"><?= !empty($row['upi_reference']) ? $row['upi_reference'] : 'N/A' ?></td>
                    <td data-label="Action">
                        <div class="btn-group">
                            <a href="?page=enrollments/view_enrollments&id=<?= $row['enrollees_id'] ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                            
                            
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($i == 1): ?>
                <tr>
                    <td colspan="8" class="text-center">No new UPI payments found in the last 24 hours</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#upiPaymentsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        ordering: false,
        responsive: true
    });
    
    // Add responsive labels for mobile view
    function addDataLabels() {
        if ($(window).width() < 768) {
            $('#upiPaymentsTable thead tr th').each(function(i) {
                $('#upiPaymentsTable tbody tr td:nth-child(' + (i + 1) + ')').attr('data-label', $(this).text());
            });
        }
    }
    
    // Run on load and resize
    addDataLabels();
    $(window).resize(addDataLabels);
});
</script>