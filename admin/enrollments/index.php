<?php
// index.php - List of Enrollments
if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?= $_settings->flashdata('success') ?>",'success')
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
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Enrollments</h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped" id="enrollmentTable">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Enrollment No</th>
                    <th>Student</th>
                    <th>Package</th>
                    <th>Instructor</th>
                    <th>Timeslot</th>
                    <th>Status</th>
                    <th>Enrollment Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $i = 1;
                $status_filter = '';
                if (isset($_GET['status']) && $_GET['status'] !== '') {
                    $status = $_GET['status'];
                    $allowed_statuses = ['Pending', 'Verified', 'In-Session', 'Completed', 'Cancelled', 'New'];

                    if (in_array($status, $allowed_statuses)) {
                        $status_escaped = $conn->real_escape_string($status);

                        if ($status_escaped === 'New') {
                            // Only enrollments created in the last 24 hours AND still Pending
                            $status_filter = "WHERE TIMESTAMPDIFF(HOUR, e.created_at, NOW()) <= 24 AND e.enrollment_status = 'Pending'";
                        } else {
                            $status_filter = "WHERE e.enrollment_status = '{$status_escaped}'";
                        }
                    }
                }

                $qry = $conn->query("SELECT e.*, u.name as student, p.name as package_name, CONCAT(i.firstname,' ',i.lastname) as instructor_name
                    FROM enrollees e
                    LEFT JOIN users u ON u.id = e.user_id
                    LEFT JOIN package_list p ON p.id = e.package_id
                    LEFT JOIN instructor i ON i.id = e.assigned_instructor_id
                    $status_filter
                    ORDER BY e.created_at DESC");

                while($row = $qry->fetch_assoc()):
                    // Format the created_at date
                    $enrollment_date = date("M d, Y h:i A", strtotime($row['created_at']));
                ?>
                <tr>
                    <td data-label="#"><?= $i++ ?></td>
                    <td data-label="Enrollment No"><?= $row['enrollment_no'] ?></td>
                    <td data-label="Student"><?= ucwords($row['student']) ?></td>
                    <td data-label="Package"><?= ucwords($row['package_name']) ?></td>
                    <td data-label="Instructor">
                        <?php if (!empty($row['instructor_name'])): ?>
                            <?= htmlspecialchars($row['instructor_name']) ?>
                        <?php else: ?>
                            <span class="badge badge-orange">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Timeslot"><?= $row['time_slot'] ?></td>
                    <td data-label="Status">
                        <?php
                            $status = $row['enrollment_status'];
                            $badge_class = 'secondary'; // Default

                            switch ($status) {
                                case 'Pending':
                                    $badge_class = 'secondary';
                                    break;
                                case 'Verified':
                                    $badge_class = 'primary';
                                    break;
                                case 'In-Session':
                                    $badge_class = 'warning';
                                    break;
                                case 'Completed':
                                    $badge_class = 'success';
                                    break;
                                case 'Cancelled':
                                    $badge_class = 'danger';
                                    break;
                            }
                        ?>
                        <span class="badge badge-pill badge-<?= $badge_class ?>">
                            <?= $status ?>
                        </span>
                    </td>
                    <td data-label="Enrollment Date"><?= $enrollment_date ?></td>
                    <td data-label="Action">
                        <div class="btn-group">
                            <a href="?page=enrollments/view_enrollments&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                            <a href="?page=enrollments/manage_enrollments&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                            <button class="btn btn-sm btn-danger delete_data" data-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i></button>
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
    $('#enrollmentTable').DataTable({
        ordering: false,
        responsive: true
    });
    
    // Add responsive labels for mobile view
    function addDataLabels() {
        if ($(window).width() < 768) {
            $('#enrollmentTable thead tr th').each(function(i) {
                $('#enrollmentTable tbody tr td:nth-child(' + (i + 1) + ')').attr('data-label', $(this).text());
            });
        }
    }
    
    // Run on load and resize
    addDataLabels();
    $(window).resize(addDataLabels);
    
    $('.delete_data').click(function(){
        _conf("Are you sure to delete this enrollment permanently?","delete_enrollment",[$(this).data('id')]);
    });
});

function delete_enrollment(id){
    start_loader();
    $.ajax({
        url: _base_url_+"classes/Enrollments.php?action=delete",
        method:'POST',data:{id:id},dataType:'json',
        success: function(resp){
            if(resp.status=='success') location.reload();
            else{ alert_toast(resp.message,'error'); end_loader(); }
        },error:function(){ alert_toast('An error occurred.','error'); end_loader(); }
    });
}
</script>