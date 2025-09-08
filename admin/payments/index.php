<?php
// payments/index.php - List of Payments
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
        <h3 class="card-title">List of Payments</h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped" id="paymentTable">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Enrollment No</th>
                    <th>Student</th>
                    <th>Package</th>
                    <th>Enrollment Status</th>
                    <th>Timeslot</th>
                    <th>Payment Status</th>
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
                    $allowed_statuses = ['Pending', 'Partially Paid', 'Paid'];

                    if (in_array($status, $allowed_statuses)) {
                        $status_escaped = $conn->real_escape_string($status);

                        if ($status_escaped === 'New') {
                            // Only payments with Paid status created in the last 24 hours
                            $status_filter = "WHERE e.payment_status = 'Paid' AND TIMESTAMPDIFF(HOUR, e.created_at, NOW()) <= 24";
                        } else {
                            $status_filter = "WHERE e.payment_status = '{$status_escaped}'";
                        }
                    }
                }

                $qry = $conn->query("SELECT e.*, u.name as student, p.name as package_name
                    FROM enrollees e
                    LEFT JOIN users u ON u.id = e.user_id
                    LEFT JOIN package_list p ON p.id = e.package_id
                    $status_filter
                    ORDER BY e.created_at DESC");

                while($row = $qry->fetch_assoc()):
                    $enrollment_date = date("M d, Y h:i A", strtotime($row['created_at']));
                ?>
                <tr>
                    <td data-label="#"><?= $i++ ?></td>
                    <td data-label="Enrollment No"><?= $row['enrollment_no'] ?></td>
                    <td data-label="Student"><?= ucwords($row['student']) ?></td>
                    <td data-label="Package"><?= ucwords($row['package_name']) ?></td>
                    <td data-label="Enrollment Status">
    <?php
        $estatus = strtolower($row['enrollment_status']);
        $estatus_class = 'secondary';
        switch ($estatus) {
            case 'pending': $estatus_class = 'secondary'; break;
            case 'verified': $estatus_class = 'primary'; break;
            case 'in-session': $estatus_class = 'warning'; break;
            case 'completed': $estatus_class = 'success'; break;
            case 'cancelled': $estatus_class = 'danger'; break;
        }
    ?>
    <span class="badge badge-<?= $estatus_class ?>">
        <?= ucfirst($estatus) ?>
    </span>
</td>
                    <td data-label="Timeslot"><?= $row['time_slot'] ?></td>
                    <td data-label="Payment Status">
    <?php
        $pstatus = strtolower($row['payment_status']);
        $pstatus_class = 'secondary';
        switch ($pstatus) {
            case 'partially paid': $pstatus_class = 'warning'; break;
            case 'paid': $pstatus_class = 'success'; break;
            case 'pending': default: $pstatus_class = 'secondary'; break;
        }
    ?>
    <span class="badge badge-<?= $pstatus_class ?>">
        <?= ucfirst($pstatus) ?>
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
    $('#paymentTable').DataTable({
        ordering: false,
        responsive: true
    });

    function addDataLabels() {
        if ($(window).width() < 768) {
            $('#paymentTable thead tr th').each(function(i) {
                $('#paymentTable tbody tr td:nth-child(' + (i + 1) + ')').attr('data-label', $(this).text());
            });
        }
    }

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
        method:'POST',
        data: {id: id},
        dataType: 'json',
        success: function(resp){
            if(resp.status == 'success') location.reload();
            else{ alert_toast(resp.message, 'error'); end_loader(); }
        },
        error: function(){ alert_toast('An error occurred.', 'error'); end_loader(); }
    });
}
</script>