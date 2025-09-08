<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script> alert('Car ID is required.'); location.href = '?page=car/list_car'; </script>";
    exit;
}

$id = $_GET['id'];
$qry = $conn->query("SELECT * FROM cars WHERE id = '$id'");
if ($qry->num_rows <= 0) {
    echo "<script> alert('Car not found.'); location.href = '?page=car/list_car'; </script>";
    exit;
}

$row = $qry->fetch_assoc();
?>

<style>
    .img-car {
        width: 200px;
        height: 120px;
        object-fit: cover;
        object-position: center;
        border-radius: 8px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Car Details</h3>
        <div class="card-tools">
            <a href="?page=car/manage_car&id=<?php echo $row['id']; ?>" class="btn btn-flat btn-primary">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="?page=car/list_car" class="btn btn-flat btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="<?php echo validate_image($row['car_image']); ?>" alt="Car Image" class="img-car img-thumbnail">
            <h4 class="mt-2"><?php echo $row['car_model']; ?></h4>
        </div>

        <table class="table table-bordered">
            <tr>
                <th>Car Code</th>
                <td><?php echo $row['car_code']; ?></td>
            </tr>
            <tr>
                <th>Car Model</th>
                <td><?php echo $row['car_model']; ?></td>
            </tr>
            <tr>
                <th>Manufacturer</th>
                <td><?php echo $row['manufacturer']; ?></td>
            </tr>
            <tr>
                <th>Year of Purchase</th>
                <td><?php echo $row['year_of_purchase']; ?></td>
            </tr>
            <tr>
                <th>Fuel Type</th>
                <td><?php echo $row['fuel_type']; ?></td>
            </tr>
            <tr>
                <th>Car Colour</th>
                <td><?php echo $row['car_colour']; ?></td>
            </tr>
            <tr>
                <th>RC Copy</th>
                <td>
                    <?php if (!empty($row['rc_copy'])): ?>
                        <a href="<?php echo base_url . $row['rc_copy']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file"></i> View File
                        </a>
                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Insurance Copy</th>
                <td>
                    <?php if (!empty($row['insurance_copy'])): ?>
                        <a href="<?php echo base_url . $row['insurance_copy']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file"></i> View File
                        </a>
                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Fitness Certificate</th>
                <td>
                    <?php if (!empty($row['fitness_certificate_copy'])): ?>
                        <a href="<?php echo base_url . $row['fitness_certificate_copy']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-file"></i> View File
                        </a>
                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
    <th>Status</th>
    <td>
        <?php if ($row['status'] == 1): ?>
            <span class="badge badge-success">Active</span>
        <?php else: ?>
            <span class="badge badge-danger">Inactive</span>
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
