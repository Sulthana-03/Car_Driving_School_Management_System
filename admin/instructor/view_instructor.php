<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script> alert('Instructor ID is required.'); location.href = '?page=instructor/list_instructor'; </script>";
    exit;
}

$id = $_GET['id'];
$qry = $conn->query("
    SELECT i.*, c.car_code, c.car_model, c.manufacturer, c.fuel_type, c.year_of_purchase 
    FROM instructor i 
    LEFT JOIN cars c ON i.assigned_vehicle_id = c.id 
    WHERE i.id = '{$id}'
");

if ($qry->num_rows <= 0) {
    echo "<script> alert('Instructor not found.'); location.href = '?page=instructor/list_instructor'; </script>";
    exit;
}

$row = $qry->fetch_assoc();
?>

<style>
    .img-avatar {
        width: 150px;
        height: 150px;
        object-fit: cover;
        object-position: center;
        border-radius: 100%;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Instructor Details</h3>
        <div class="card-tools">
            <a href="?page=instructor/manage_instructor&id=<?php echo $row['id']; ?>" class="btn btn-flat btn-primary">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="?page=instructor/list_instructor" class="btn btn-flat btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="<?php echo validate_image($row['avatar']); ?>" alt="Instructor Avatar" class="img-avatar img-thumbnail">
            <h4 class="mt-2"><?php echo ucwords($row['firstname'] . ' ' . $row['lastname']); ?></h4>
            <span class="badge badge-<?php echo ($row['status'] == 1) ? 'success' : 'danger'; ?>">
                <?php echo ($row['status'] == 1) ? 'Active' : 'Inactive'; ?>
            </span>
        </div>

        <table class="table table-bordered">
            <tr>
                <th>Instructor Code</th>
                <td><?php echo $row['instructor_code']; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo $row['email']; ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo $row['phone']; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo $row['address']; ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?php echo $row['gender']; ?></td>
            </tr>
            <tr>
                <th>Joined Date</th>
                <td><?php echo date("F d, Y", strtotime($row['joined_date'])); ?></td>
            </tr>
            <tr>
                <th>Education</th>
                <td><?php echo $row['education']; ?></td>
            </tr>
            <tr>
                <th>Age</th>
                <td><?php echo $row['age']; ?></td>
            </tr>
            <tr>
                <th>Experience (years)</th>
                <td><?php echo $row['experience']; ?></td>
            </tr>
            <tr>
                <th>License Number</th>
                <td><?php echo $row['license_number']; ?></td>
            </tr>
            <tr>
    <th>Assigned Car</th>
    <td>
        <?php if (!empty($row['car_code'])): ?>
            <strong><?php echo $row['car_code']; ?></strong> - 
            <?php echo $row['car_model']; ?> 
            (<?php echo $row['manufacturer']; ?>, <?php echo $row['fuel_type']; ?>, <?php echo $row['year_of_purchase']; ?>)
        <?php else: ?>
            <em>No car assigned</em>
        <?php endif; ?>
    </td>
</tr>

            <tr>
                <th>License Copy</th>
                <td>
                    <?php if (!empty($row['license_copy'])): ?>
                        <a href="<?php echo 'http://localhost/cdsms/' . $row['license_copy']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file"></i> View File
                        </a>

                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>ID Proof</th>
                <td>
                    <?php if (!empty($row['id_proof'])): ?>
                        <a href="<?php echo 'http://localhost/cdsms/' . $row['id_proof']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file"></i> View File
                        </a>
                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Date Created</th>
                <td><?php echo date("F d, Y H:i", strtotime($row['date_created'])); ?></td>
            </tr>
        </table>
    </div>
</div>
