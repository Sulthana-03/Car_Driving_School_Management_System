<?php
// payment_report.php - Payment Report
if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?= $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<style>
    /* Your existing CSS styles */
    .report-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .date-filters {
        background-color: #f8f9fa;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
 .report-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .date-filters {
        background-color: #f8f9fa;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
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
    .table-container {
        overflow-x: auto;
    }
    .status-badge {
        padding: 0.35em 0.6em;
        font-size: 0.75em;
        border-radius: 0.25rem;
    }
    .btn-filter {
        margin-right: 5px;
    }
    .payment-mode-badge {
        font-size: 0.75em;
        border-radius: 0.25rem;
        padding: 0.35em 0.6em;
    }
    .badge-cash {
        background-color: #28a745;
        color: white;
    }
    .badge-upi {
        background-color: #007bff;
        color: white;
    }
    
    @media (max-width: 768px) {
        .date-filters .form-group {
            margin-bottom: 10px;
        }
        .table td {
            white-space: nowrap;
        }
    }
    /* ... rest of your CSS ... */
    @media print {
    .btn, .card-header, .date-filters, .dataTables_length,
    .dataTables_filter, .dataTables_info, .dataTables_paginate,
    .dt-buttons, .text-right {
        display: none !important;
    }

    .d-print-block {
        display: block !important;
    }

    .table-container {
        overflow: visible;
    }

    body {
        background: white !important;
        color: black;
    }
}

</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Payment Report</h3>
    </div>
    <div class="card-body">

    <div id="print-header" class="d-none d-print-block text-center mb-4">
    <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" style="height: 80px;">
    <h3 class="mt-2 mb-1"><?= $_settings->info('name') ?></h3>
    <p><?= $_settings->info('address') ?></p>
    <hr>
</div>

        <!-- Report Header -->
        <div class="report-header">
            <h4>Payment Report</h4>
        </div>

        <!-- Date Filters -->
        <div class="date-filters">
            <form id="date-filter-form" method="GET">
                <input type="hidden" name="page" value="reports/index">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" 
                                value="<?= isset($_GET['from_date']) ? $_GET['from_date'] : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" 
                                value="<?= isset($_GET['to_date']) ? $_GET['to_date'] : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 text-right">
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="fa fa-print"></i> Print Page
    </button>
</div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-filter">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <a href="?page=reports/index" id="reset-btn" class="btn btn-secondary btn-filter">
                                <i class="fa fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // Initialize summary array with default values
        $summary = [
            'total_payments' => 0,
            'total_amount' => 0,
            'cash_count' => 0,
            'cash_amount' => 0,
            'upi_count' => 0,
            'upi_amount' => 0
        ];

        // Get filter dates from GET parameters
        $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
        $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : null;
        
        // Validate dates
        if ($from_date && !strtotime($from_date)) $from_date = null;
        if ($to_date && !strtotime($to_date)) $to_date = null;
        
        // Ensure to_date is not before from_date if both are set
        if ($from_date && $to_date && strtotime($to_date) < strtotime($from_date)) {
            $to_date = $from_date;
        }
        
        // Format dates for display
        $display_from_date = $from_date ? date('M d, Y', strtotime($from_date)) : 'All records';
        $display_to_date = $to_date ? date('M d, Y', strtotime($to_date)) : 'All records';
        
        // Build WHERE clause for queries - only show paid payments
        $where_clause = "WHERE p.payment_status = 'paid'";
        if ($from_date && $to_date) {
            $where_clause .= " AND DATE(p.date_created) BETWEEN '$from_date' AND '$to_date'";
        } elseif ($from_date) {
            $where_clause .= " AND DATE(p.date_created) >= '$from_date'";
        } elseif ($to_date) {
            $where_clause .= " AND DATE(p.date_created) <= '$to_date'";
        }

        try {
            // Get summary data for paid payments only
            $summary_qry = $conn->query("SELECT 
                COUNT(*) as total_payments,
                SUM(p.amount) as total_amount,
                SUM(CASE WHEN p.payment_mode = 'cash' THEN 1 ELSE 0 END) as cash_count,
                SUM(CASE WHEN p.payment_mode = 'cash' THEN p.amount ELSE 0 END) as cash_amount,
                SUM(CASE WHEN p.payment_mode = 'upi' THEN 1 ELSE 0 END) as upi_count,
                SUM(CASE WHEN p.payment_mode = 'upi' THEN p.amount ELSE 0 END) as upi_amount
                FROM payment_list p
                $where_clause");

            if ($summary_qry) {
                $summary = $summary_qry->fetch_assoc();
                // Ensure all summary values are set
                $summary['total_payments'] = $summary['total_payments'] ?? 0;
                $summary['total_amount'] = $summary['total_amount'] ?? 0;
                $summary['cash_count'] = $summary['cash_count'] ?? 0;
                $summary['cash_amount'] = $summary['cash_amount'] ?? 0;
                $summary['upi_count'] = $summary['upi_count'] ?? 0;
                $summary['upi_amount'] = $summary['upi_amount'] ?? 0;
            }
        } catch (Exception $e) {
            // Log error but continue with default summary values
            error_log("Error in summary query: " . $e->getMessage());
        }
        ?>

        <!-- Report Summary -->
        <div class="report-summary">
            <div class="summary-item"><strong>Report Period:</strong> <?= $display_from_date ?> to <?= $display_to_date ?></div>
            <div class="summary-item"><strong>Total Payments:</strong> <?= $summary['total_payments'] ?></div>
            <div class="summary-item"><strong>Total Amount:</strong> ₹<?= number_format($summary['total_amount'], 2) ?></div>
            <div class="summary-item"><strong>Cash Payments:</strong> <?= $summary['cash_count'] ?> (₹<?= number_format($summary['cash_amount'], 2) ?>)</div>
            <div class="summary-item"><strong>UPI Payments:</strong> <?= $summary['upi_count'] ?> (₹<?= number_format($summary['upi_amount'], 2) ?>)</div>
        </div>

        <!-- Payment Table -->
        <div class="table-container">
            <table class="table table-hover table-striped table-bordered" id="reportTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Enrollment No</th>
                        <th>Student</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Reference</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    try {
                        // Get payment data based on filters - only paid payments
                        $qry = $conn->query("SELECT p.*, e.enrollment_no, u.name as student_name
                            FROM payment_list p
                            JOIN enrollees e ON e.id = p.enrollees_id
                            JOIN users u ON u.id = e.user_id
                            $where_clause
                            ORDER BY p.date_created DESC");

                        if ($qry) {
                            while($row = $qry->fetch_assoc()):
                                $payment_date = date("M d, Y h:i A", strtotime($row['date_created']));
                                
                                // Determine badge class based on payment mode
                                $badge_class = $row['payment_mode'] == 'cash' ? 'badge-cash' : 'badge-upi';
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['enrollment_no'] ?></td>
                        <td><?= ucwords($row['student_name']) ?></td>
                        <td><?= $payment_date ?></td>
                        <td>₹<?= number_format($row['amount'], 2) ?></td>
                        <td>
                            <span class="payment-mode-badge <?= $badge_class ?>">
                                <?= strtoupper($row['payment_mode']) ?>
                            </span>
                        </td>
                        <td><?= !empty($row['upi_reference']) ? $row['upi_reference'] : 'N/A' ?></td>
                        <td>
                            <a href="?page=enrollments/view_enrollments&id=<?= $row['enrollees_id'] ?>" 
                               class="btn btn-sm btn-info" title="View Enrollment">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                            endwhile;
                            
                            if ($i == 1) {
                                echo '<tr><td colspan="8" class="text-center">No paid payments found for the selected period</td></tr>';
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error in payment query: " . $e->getMessage());
                        echo '<tr><td colspan="8" class="text-center">Error loading payment data</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Initialize DataTable with export buttons
    $('#reportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true
    });

    // Set max date for "to_date" based on "from_date" selection
    $('#from_date').change(function() {
        $('#to_date').attr('min', $(this).val());
    });
});
</script>