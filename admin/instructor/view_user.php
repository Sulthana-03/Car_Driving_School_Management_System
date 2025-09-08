<?php 
if(!isset($_GET['instructor_id'])){
    echo "<script>location.replace('?page=reports/instructor_report');</script>";
    exit;
}

$instructor_id = $_GET['instructor_id'];
$instructor = $conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM `instructor` WHERE id = '{$instructor_id}'")->fetch_assoc();

if(!$instructor){
    echo "<script>location.replace('?page=reports/instructor_report');</script>";
    exit;
}

// Handle delete action
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    $delete_qry = $conn->query("DELETE FROM `enrollees` WHERE id = '{$delete_id}'");
    if($delete_qry){
        $_settings->set_flashdata('success', 'Student record deleted successfully');
        echo "<script>location.replace('?page=instructor/view_user&instructor_id={$instructor_id}')</script>";
        exit;
    } else {
        echo "<script>alert('Error deleting student record')</script>";
    }
}
?>

<iframe id="print_frame" name="print_frame" style="display:none;"></iframe>

<style>
    .img-avatar {
        width: 45px;
        height: 45px;
        object-fit: cover;
        object-position: center center;
        border-radius: 100%;
    }
    .user-card {
        transition: all 0.3s ease;
    }
    .user-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .action-btns .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        margin: 0 2px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Students Assigned to <?php echo ucwords($instructor['name']); ?></h3>
        <div class="card-tools">
            <button class="btn btn-flat btn-sm bg-gradient-primary" onclick="printReport()">
                <i class="fa fa-print"></i> Print Report
            </button>
            <a href="<?php echo base_url ?>admin/?page=instructor/instructor_report" class="btn btn-flat btn-default">
                <i class="fa fa-arrow-left"></i> Back to Instructors
            </a>
        </div>
    </div>
    <div class="card-body" id="printable">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="border bg-light p-3 rounded">
                        <h5>Instructor Details</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Name:</span>
                            <span><?php echo ucwords($instructor['name']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Code:</span>
                            <span><?php echo $instructor['instructor_code']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phone:</span>
                            <span><?php echo $instructor['phone']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge badge-<?php echo $instructor['status'] == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $instructor['status'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border bg-light p-3 rounded">
                        <h5>Assignment Summary</h5>
                        <?php
                        $total = $conn->query("SELECT COUNT(id) as total FROM `enrollees` WHERE assigned_instructor_id = '{$instructor_id}' AND enrollment_status IN ('Verified', 'In-Session', 'Completed')")->fetch_assoc()['total'];
                        $active = $conn->query("SELECT COUNT(id) as total FROM `enrollees` WHERE assigned_instructor_id = '{$instructor_id}' AND enrollment_status = 'In-Session'")->fetch_assoc()['total'];
                        $completed = $conn->query("SELECT COUNT(id) as total FROM `enrollees` WHERE assigned_instructor_id = '{$instructor_id}' AND enrollment_status = 'Completed'")->fetch_assoc()['total'];
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Students:</span>
                            <span><?php echo $total; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Active Students:</span>
                            <span class="badge badge-warning"><?php echo $active; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Completed:</span>
                            <span class="badge badge-success"><?php echo $completed; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-hover table-striped" id="studentTable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Enrollment No</th>
                        <th>Student Name</th>
                        <th>Package</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT e.*, u.name as student_name, p.name as package_name 
                                            FROM `enrollees` e 
                                            JOIN `users` u ON e.user_id = u.id 
                                            JOIN `package_list` p ON e.package_id = p.id 
                                            WHERE e.assigned_instructor_id = '{$instructor_id}'
                                            ORDER BY e.enrollment_no ASC");
                        while($row = $qry->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo $row['enrollment_no']; ?></td>
                        <td><?php echo ucwords($row['student_name']); ?></td>
                        <td><?php echo $row['package_name']; ?></td>
                        <td class="text-center"><?php echo $row['start_date'] ? date("M d, Y", strtotime($row['start_date'])) : 'N/A'; ?></td>
                        <td class="text-center"><?php echo $row['end_date'] ? date("M d, Y", strtotime($row['end_date'])) : 'N/A'; ?></td>
                        <td class="text-center">
                            <?php 
                                $status_class = '';
                                switch($row['enrollment_status']){
                                    case 'Pending': $status_class = 'secondary'; break;
                                    case 'Verified': $status_class = 'primary'; break;
                                    case 'In-Session': $status_class = 'warning'; break;
                                    case 'Completed': $status_class = 'success'; break;
                                    case 'Dropped':
                                    case 'Cancelled': $status_class = 'danger'; break;
                                    default: $status_class = 'info';
                                }
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>">
                                <?php echo $row['enrollment_status']; ?>
                            </span>
                        </td>
                        <td align="center" class="action-btns">
                            <a class="btn btn-sm btn-flat btn-info" href="<?php echo base_url ?>admin/?page=enrollments/view_enrollments&id=<?php echo $row['id']; ?>">
                                <i class="fa fa-eye"></i> View
                            </a>
                            <a class="btn btn-sm btn-flat btn-danger" href="javascript:void(0)" onclick="confirmDelete('<?php echo $row['id']; ?>')">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($qry->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center">No students assigned to this instructor</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#studentTable').DataTable({ ordering: false });
});

function printReport() {
    const logo = "<?= validate_image($_settings->info('logo')) ?>";
    const name = "<?= $_settings->info('name') ?>";
    const address = "<?= $_settings->info('address') ?>";
    const title = 'Students Assigned to <?= addslashes($instructor['name']) ?>';

    const original = document.getElementById('printable');
    const clone = original.cloneNode(true);

    // 🧹 Remove DataTables elements (search bar, pagination, etc.)
    clone.querySelectorAll('.dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate').forEach(el => el.remove());

    // 🧹 Remove the "Action" column from <thead>
    const thRow = clone.querySelector('thead tr');
    if (thRow && thRow.children.length > 7) {
        thRow.removeChild(thRow.lastElementChild);
    }

    // 🧹 Remove the "Action" column from each <tbody> row
    clone.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.children.length > 7) {
            tr.removeChild(tr.lastElementChild);
        }
    });

    const html = `
        <html>
        <head>
            <title>${title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .text-center { text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                th { background-color: #f8f9fa; }
                .badge-success { background-color: #28a745; color: white; padding: 3px 7px; border-radius: 3px; }
                .badge-primary { background-color: #007bff; color: white; padding: 3px 7px; border-radius: 3px; }
                .badge-warning { background-color: #ffc107; color: black; padding: 3px 7px; border-radius: 3px; }
                .badge-danger { background-color: #dc3545; color: white; padding: 3px 7px; border-radius: 3px; }
                .badge-secondary { background-color: #6c757d; color: white; padding: 3px 7px; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class="text-center">
                <img src="${logo}" style="height: 80px;"><br>
                <h3>${name}</h3>
                <p>${address}</p>
                <hr>
                <h4>${title}</h4>
            </div>
            ${clone.innerHTML}
        </body>
        </html>
    `;

    const frame = document.getElementById('print_frame');
    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    setTimeout(() => {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }, 500);
}


function confirmDelete(id) {
    start_loader();
    if(confirm("Are you sure you want to delete this student record? This action cannot be undone!")) {
        window.location.href = `?page=instructor/view_user&instructor_id=<?php echo $instructor_id; ?>&delete_id=${id}`;
    }
    end_loader();
}
</script>
