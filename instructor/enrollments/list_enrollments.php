<?php
// instructor/list_enrollments.php
require_once('inc/sess_auth.php');

if ($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?= $_settings->flashdata('success') ?>", 'success')
</script>
<?php endif; ?>

<style>
.badge-orange {
    background-color: orange;
    color: black;
    padding: 0.35em 0.6em;
    font-size: 0.75em;
    border-radius: 0.25rem;
}
@media (max-width: 768px) {
    .card-body { padding: 0.5rem; overflow-x: auto; }
    .table { width: 100%; margin-bottom: 1rem; display: block; }
    .table thead { display: none; }
    .table tbody { display: block; }
    .table tr { display: block; margin-bottom: 1rem; border: 1px solid #dee2e6; border-radius: 0.25rem; }
    .table td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; text-align: right; border-bottom: 1px solid #dee2e6; }
    .table td:before { content: attr(data-label); font-weight: bold; padding-right: 1rem; text-align: left; }
    .btn-group { justify-content: flex-end; }
}
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">My Assigned Enrollments</h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped" id="enrollmentTable">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Enrollment No</th>
                    <th>Student</th>
                    <th>Package</th>
                    <th>Timeslot</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $status_filter = "WHERE e.assigned_instructor_id = {$instructor_id}";

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = $_GET['status'];
    $allowed_statuses = ['Pending', 'Verified', 'In-Session', 'Completed', 'Cancelled', 'New'];

    if (in_array($status, $allowed_statuses)) {
        $status_escaped = $conn->real_escape_string($status);

        if ($status_escaped === 'New') {
            // New = created within last 24h and still Pending
            $status_filter .= " AND TIMESTAMPDIFF(HOUR, e.created_at, NOW()) <= 24 AND e.enrollment_status = 'verified'";
        } else {
            $status_filter .= " AND e.enrollment_status = '{$status_escaped}'";
        }
    }
}

// Now use $status_filter in the query
$qry = $conn->query("SELECT e.*, u.name AS student, p.name AS package_name 
    FROM enrollees e
    LEFT JOIN users u ON u.id = e.user_id
    LEFT JOIN package_list p ON p.id = e.package_id
    {$status_filter}
    ORDER BY e.created_at DESC");


                $i = 1;
                while ($row = $qry->fetch_assoc()):
                ?>
                <tr>
                    <td data-label="#"><?= $i++ ?></td>
                    <td data-label="Enrollment No"><?= $row['enrollment_no'] ?></td>
                    <td data-label="Student"><?= ucwords($row['student']) ?></td>
                    <td data-label="Package"><?= ucwords($row['package_name']) ?></td>
                    <td data-label="Timeslot"><?= $row['time_slot'] ?></td>
                    <td data-label="Status">
                        <?php
                            $status = $row['enrollment_status'];
                            $badge_class = match($status) {
                                'Verified' => 'primary',
                                'In-Session' => 'warning',
                                'Completed' => 'success',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge badge-pill badge-<?= $badge_class ?>"><?= $status ?></span>
                    </td>
                    <td data-label="Action">
                        <div class="btn-group">
                            <a href="?page=enrollments/view_enrollments&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#enrollmentTable').DataTable({ ordering: false, responsive: true });

    function addDataLabels() {
        if ($(window).width() < 768) {
            $('#enrollmentTable thead tr th').each(function(i) {
                $('#enrollmentTable tbody tr td:nth-child(' + (i + 1) + ')').attr('data-label', $(this).text());
            });
        }
    }
    
    addDataLabels();
    $(window).resize(addDataLabels);
});
</script>

