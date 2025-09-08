<?php
// datewise_report.php - Datewise Enrollment Report
if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?= $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<style>
    /* Your existing CSS styles remain the same */
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
    .badge-orange {
        background-color: orange;
        color: black;
        padding: 0.35em 0.6em;
        font-size: 0.75em;
        border-radius: 0.25rem;
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
    .view-link {
        color: #007bff;
        cursor: pointer;
    }
    .view-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .date-filters .form-group {
            margin-bottom: 10px;
        }
    }

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
<style>
@media screen {
  table.dataTable td {
    white-space: nowrap;
  }
}
</style>


<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Datewise Enrollment Report</h3>
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
            <h4>Datewise Enrollment Report</h4>
        </div>

        <!-- Date Filters -->
        <div class="date-filters">
            <form id="date-filter-form" method="GET">
                <input type="hidden" name="page" value="reports/datewise_reg">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" 
                                value="<?= isset($_GET['from_date']) ? $_GET['from_date'] : '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" 
                                value="<?= isset($_GET['to_date']) ? $_GET['to_date'] : '' ?>" required>
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
                            <button type="button" id="reset-btn" class="btn btn-secondary btn-filter">
                                <i class="fa fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // Initialize summary array with default values
        $summary = [
            'total_enrollments' => 0,
            'completed' => 0,
            'in_session' => 0,
            'pending' => 0,
            'cancelled' => 0
        ];

        // Get filter dates from GET parameters
        $from_date = isset($_GET['from_date']) && !empty($_GET['from_date']) ? $_GET['from_date'] : null;
        $to_date = isset($_GET['to_date']) && !empty($_GET['to_date']) ? $_GET['to_date'] : null;
        
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
        
        // Build WHERE clause for queries
        $where_clause = "";
        if ($from_date && $to_date) {
            $where_clause = "WHERE DATE(e.created_at) BETWEEN '$from_date' AND '$to_date'";
        } elseif ($from_date) {
            $where_clause = "WHERE DATE(e.created_at) >= '$from_date'";
        } elseif ($to_date) {
            $where_clause = "WHERE DATE(e.created_at) <= '$to_date'";
        }

        try {
            // Get summary data
            $summary_qry = $conn->query("SELECT 
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN e.enrollment_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN e.enrollment_status = 'In-Session' THEN 1 ELSE 0 END) as in_session,
                SUM(CASE WHEN e.enrollment_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN e.enrollment_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM enrollees e
                $where_clause");

            if ($summary_qry) {
                $summary = $summary_qry->fetch_assoc();
                // Ensure all summary values are set
                $summary['total_enrollments'] = $summary['total_enrollments'] ?? 0;
                $summary['completed'] = $summary['completed'] ?? 0;
                $summary['in_session'] = $summary['in_session'] ?? 0;
                $summary['pending'] = $summary['pending'] ?? 0;
                $summary['cancelled'] = $summary['cancelled'] ?? 0;
            }
        } catch (Exception $e) {
            // Log error but continue with default summary values
            error_log("Error in summary query: " . $e->getMessage());
        }
        ?>

        <!-- Report Summary -->
        <div class="report-summary">
            <div class="summary-item"><strong>Report Period:</strong> <?= $display_from_date ?> to <?= $display_to_date ?></div>
            <div class="summary-item"><strong>Total Enrollments:</strong> <?= $summary['total_enrollments'] ?></div>
            <div class="summary-item"><strong>Completed:</strong> <?= $summary['completed'] ?></div>
            <div class="summary-item"><strong>In-Session:</strong> <?= $summary['in_session'] ?></div>
            <div class="summary-item"><strong>Pending:</strong> <?= $summary['pending'] ?></div>
            <div class="summary-item"><strong>Cancelled:</strong> <?= $summary['cancelled'] ?></div>
        </div>

        <!-- Enrollment Table -->
        <div class="table-container">
            <table class="table table-hover table-striped table-bordered" id="reportTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Enrollment No</th>
                        <th>Student</th>
                        <th>Gender</th> 
                        <th>Package</th>
                        <th>Instructor</th>
                        <th>Timeslot</th>
                        <th>Status</th>
                        <th>Enrollment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    try {
                        // Get enrollment data based on filters
                        $qry = $conn->query("SELECT e.*, u.name as student, p.name as package_name, 
                            CONCAT(i.firstname,' ',i.lastname) as instructor_name
                            FROM enrollees e
                            LEFT JOIN users u ON u.id = e.user_id
                            LEFT JOIN package_list p ON p.id = e.package_id
                            LEFT JOIN instructor i ON i.id = e.assigned_instructor_id
                            $where_clause
                            ORDER BY e.created_at DESC");

                        if ($qry) {
                            while($row = $qry->fetch_assoc()):
                                $enrollment_date = date("M d, Y h:i A", strtotime($row['created_at']));
                                
                                // Determine badge class based on status
                                $badge_class = '';
                                switch($row['enrollment_status']) {
                                    case 'Pending':
                                        $badge_class = 'badge-secondary';
                                        break;
                                    case 'Verified':
                                        $badge_class = 'badge-primary';
                                        break;
                                    case 'In-Session':
                                        $badge_class = 'badge-warning';
                                        break;
                                    case 'Completed':
                                        $badge_class = 'badge-success';
                                        break;
                                    case 'Cancelled':
                                        $badge_class = 'badge-danger';
                                        break;
                                    default:
                                        $badge_class = 'badge-secondary';
                                }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                           
                                <?= $row['enrollment_no'] ?>
                            </a>
                        </td>
                        <td><?= ucwords($row['student']) ?></td>
                        <td><?= $row['gender'] ? ucfirst(strtolower($row['gender'])) : 'N/A' ?></td>

                        <td><?= ucwords($row['package_name']) ?></td>
                        <td>
                            <?php if (!empty($row['instructor_name'])): ?>
                                <?= htmlspecialchars($row['instructor_name']) ?>
                            <?php else: ?>
                                <span class="badge badge-orange">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['time_slot'] ?></td>
                        <td>
                            <span class="status-badge <?= $badge_class ?>">
                                <?= $row['enrollment_status'] ?>
                            </span>
                        </td>
                        <td><?= $enrollment_date ?></td>
                        <td>
                            <a href="?page=enrollments/view_enrollments&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php 
                            endwhile;
                        }
                    } catch (Exception $e) {
                        error_log("Error in enrollment query: " . $e->getMessage());
                        echo '<tr><td colspan="9" class="text-center">Error loading enrollment data</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

  // ✅ Initialize DataTable
  var table = $('#reportTable').DataTable({
    responsive: false,
    search: {
      caseInsensitive: true
    }
  });

  // ✅ Force custom search to lowercase
  $('#reportTable_filter input').off().on('keyup', function(){
    let val = this.value.toLowerCase().trim();
    table.search(val).draw();
  });

  // ✅ Reset button
  $('#reset-btn').click(function(){
    $('#from_date').val('');
    $('#to_date').val('');
    $('#date-filter-form').submit();
  });

  // ✅ Direct custom filter for "male"
  $('#reportTable_filter input').on('input', function(){
    let search = this.value.trim().toLowerCase();
    if(search === 'male'){
      table.column(3).search('^male$', true, false).draw();
    } else {
      table.column(3).search('').draw();
      table.search(search).draw();
    }
  });

});
</script>


