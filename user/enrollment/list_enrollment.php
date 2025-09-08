<?php
// list_enrollment.php - User's List of Enrollments
require_once('inc/sess_auth.php'); // Ensure user is logged in

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
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">My Enrollments</h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped" id="enrollmentTable">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Enrollment No</th>
                    <th>Package</th>
                    <th>Instructor</th>
                    <th>Timeslot</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php
                $i = 1;
                $user_id = $_SESSION['user_id'];
                $qry = $conn->query("SELECT e.*, p.name as package_name, CONCAT(i.firstname,' ',i.lastname) as instructor_name
                    FROM enrollees e
                    LEFT JOIN package_list p ON p.id = e.package_id
                    LEFT JOIN instructor i ON i.id = e.assigned_instructor_id
                    WHERE e.user_id = '{$user_id}'
                    ORDER BY e.created_at DESC");

                while($row = $qry->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['enrollment_no'] ?></td>
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
                        <?php
                            $status = $row['enrollment_status'];
                            $badge_class = match($status) {
                                'Verified' => 'primary',
                                'In-Session' => 'warning',
                                'Completed' => 'success',
                                'Cancelled' => 'danger',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge badge-pill badge-<?= $badge_class ?>">
                            <?= $status ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?page=enrollment/view_enrollment&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>

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
});
</script>
