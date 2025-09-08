<?php
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
        <h3 class="card-title">Package-wise Enrollment Report</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="package_filter">Filter by Package:</label>
                    <select class="form-control form-control-sm select2" id="package_filter">
                        <option value="">All Packages</option>
                        <?php 
                        $packages = $conn->query("SELECT * FROM package_list WHERE status = 1 ORDER BY name ASC");
                        while($row = $packages->fetch_assoc()):
                        ?>
                        <option value="<?= $row['id'] ?>" <?= isset($_GET['package_id']) && $_GET['package_id'] == $row['id'] ? 'selected' : '' ?>>
                            <?= ucwords($row['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status_filter">Filter by Status:</label>
                    <select class="form-control form-control-sm select2" id="status_filter">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= isset($_GET['status']) && $_GET['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Verified" <?= isset($_GET['status']) && $_GET['status'] == 'Verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="In-Session" <?= isset($_GET['status']) && $_GET['status'] == 'In-Session' ? 'selected' : '' ?>>In-Session</option>
                        <option value="Completed" <?= isset($_GET['status']) && $_GET['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label><br>
                    <button class="btn btn-sm btn-primary" id="filter_btn">Apply Filter</button>
                    <button class="btn btn-sm btn-secondary" id="reset_btn">Reset</button>
                    <button class="btn btn-sm btn-outline-primary" id="print">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                </div>
            </div>
            <div class="col-md-4">
  <label for="gender_filter">Filter by Gender:</label>
  <select class="form-control form-control-sm select2" id="gender_filter">
    <option value="">All Genders</option>
    <option value="Male" <?= isset($_GET['gender']) && $_GET['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
    <option value="Female" <?= isset($_GET['gender']) && $_GET['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
  </select>
</div>


            <table class="table table-hover table-striped" id="reportTable">
                <thead>
                    <tr class="text-center">
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
                <tbody class="text-center">
                    <?php
$i = 1;

$package_filter = '';
$status_filter = '';
$gender_filter = ''; // ✅ Add this line too

// Existing: Package filter
if (isset($_GET['package_id']) && $_GET['package_id'] != '') {
    $package_id = $conn->real_escape_string($_GET['package_id']);
    $package_filter = "AND e.package_id = '{$package_id}'";
}

// Existing: Status filter
if (isset($_GET['status']) && $_GET['status'] != '') {
    $status = $conn->real_escape_string($_GET['status']);
    $status_filter = "AND e.enrollment_status = '{$status}'";
}

// ✅ NEW: Gender filter
if (isset($_GET['gender']) && $_GET['gender'] != '') {
    $gender = $conn->real_escape_string($_GET['gender']);
    $gender_filter = "AND e.gender = '{$gender}'";
}

                    $qry = $conn->query("SELECT e.*, u.name as student, p.name as package_name, CONCAT(i.firstname,' ',i.lastname) as instructor_name
    FROM enrollees e
    LEFT JOIN users u ON u.id = e.user_id
    LEFT JOIN package_list p ON p.id = e.package_id
    LEFT JOIN instructor i ON i.id = e.assigned_instructor_id
    WHERE 1 $package_filter $status_filter $gender_filter
    ORDER BY p.name ASC, e.created_at DESC");


                    while($row = $qry->fetch_assoc()):
                        $enrollment_date = date("M d, Y h:i A", strtotime($row['created_at']));
                    ?>
                    <tr>
                        <td data-label="#"><?= $i++ ?></td>
                        <td data-label="Enrollment No"><?= $row['enrollment_no'] ?></td>
                        <td data-label="Student"><?= ucwords($row['student']) ?></td>
                        <td data-label="Gender"><?= ucfirst($row['gender']) ?></td>

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
                                $badge_class = 'secondary';
                                switch ($status) {
                                    case 'Pending': $badge_class = 'secondary'; break;
                                    case 'Verified': $badge_class = 'primary'; break;
                                    case 'In-Session': $badge_class = 'warning'; break;
                                    case 'Completed': $badge_class = 'success'; break;
                                    case 'Cancelled': $badge_class = 'danger'; break;
                                }
                            ?>
                            <span class="badge badge-pill badge-<?= $badge_class ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td data-label="Enrollment Date"><?= $enrollment_date ?></td>
                        <td data-label="Action">
                            <a href="<?= base_url ?>admin/?page=enrollments/view_enrollments&id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="View Enrollment">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<iframe id="print_frame" name="print_frame" style="display: none;"></iframe>

<script>
$(document).ready(function(){
    $('#reportTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf'],
        responsive: true,
        ordering: false,
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 8 },
            { responsivePriority: 3, targets: 2 },
            { responsivePriority: 4, targets: 3 }
        ]
    });

    $('.select2').select2();

    $('#filter_btn').click(function(){
    var package_id = $('#package_filter').val();
    var status = $('#status_filter').val();
    var gender = $('#gender_filter').val(); // ✅ Get the gender filter value
    var url = '?page=packages/packagewise_report';
    if(package_id != '') url += '&package_id=' + package_id;
    if(status != '') url += '&status=' + status;
    if(gender != '') url += '&gender=' + gender; // ✅ Add gender param if selected
    location.href = url;
});

    $('#reset_btn').click(function(){
        location.href = '?page=packages/packagewise_report';
    });

    $('#print').click(function(){
    var title = "Package-wise Enrollment Report";
    var package = $('#package_filter option:selected').text();
    var status = $('#status_filter option:selected').text();

    if(package != 'All Packages') title += " - Package: " + package;
    if(status != 'All Statuses') title += " - Status: " + status;

    var logo = "<?= validate_image($_settings->info('logo')) ?>";
    var schoolName = "<?= $_settings->info('name') ?>";
    var address = "<?= $_settings->info('address') ?>";

    var head = document.head.innerHTML;
    var content = $('#reportTable').clone();
    content.find('th:last-child, td:last-child').remove(); // Remove Action column

    var html = `
    <html>
    <head>${head}</head>
    <body>
        <div style="text-align:center">
            <img src="${logo}" style="height:80px;"><br>
            <h3 style="margin:0">${schoolName}</h3>
            <p style="margin:0">${address}</p>
            <hr>
            <h4>${title}</h4>
        </div>
        ${content.prop('outerHTML')}
    </body>
    </html>`;

    var frame = document.getElementById('print_frame');
    var frameDoc = frame.contentWindow.document;
    frameDoc.open();
    frameDoc.write(html);
    frameDoc.close();
    
    setTimeout(() => {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }, 500);
});


    function addDataLabels() {
        if ($(window).width() < 768) {
            $('#reportTable thead tr th').each(function(i) {
                $('#reportTable tbody tr td:nth-child(' + (i + 1) + ')').attr('data-label', $(this).text());
            });
        }
    }

    addDataLabels();
    $(window).resize(addDataLabels);
});
</script>
