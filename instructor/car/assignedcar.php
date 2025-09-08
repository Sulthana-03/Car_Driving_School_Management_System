<?php

// Ensure instructor is logged in
if (!isset($_SESSION['instructor_id'])) {
    echo "<script>alert('Instructor session not found.'); location.href = '?page=home';</script>";
    exit;
}

$instructor_id = $_SESSION['instructor_id'];
// echo "Instructor ID from session: $instructor_id"; // Debugging line

// Step 1: Get assigned car ID from instructor table
$qry = $conn->query("SELECT assigned_vehicle_id FROM instructor WHERE id = '{$instructor_id}'");

if (!$qry || $qry->num_rows == 0) {
    echo "<script>alert('Instructor not found.'); location.href = '?page=home';</script>";
    exit;
}

$instructor = $qry->fetch_assoc();
$assigned_car_id = $instructor['assigned_vehicle_id'];

// Step 2: Check if car is assigned
if (empty($assigned_car_id)) {
    echo "<script>alert('No car assigned.'); location.href = '?page=home';</script>";
    exit;
}

// Step 3: Fetch car details from cars table
$car_qry = $conn->query("SELECT * FROM cars WHERE id = '{$assigned_car_id}'");

if (!$car_qry || $car_qry->num_rows == 0) {
    echo "<script>alert('Assigned car not found.'); location.href = '?page=home';</script>";
    exit;
}

$row = $car_qry->fetch_assoc();
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
        <h3 class="card-title">My Assigned Car</h3>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <img src="<?php echo validate_image($row['car_image']); ?>" alt="Car Image" class="img-car img-thumbnail">
            <h4 class="mt-2"><?php echo htmlspecialchars($row['car_model']); ?></h4>
        </div>

        <table class="table table-bordered">
            <tr><th>Car Code</th><td><?= htmlspecialchars($row['car_code']) ?></td></tr>
            <tr><th>Car Model</th><td><?= htmlspecialchars($row['car_model']) ?></td></tr>
            <tr><th>Manufacturer</th><td><?= htmlspecialchars($row['manufacturer']) ?></td></tr>
            <tr><th>Fuel Type</th><td><?= htmlspecialchars($row['fuel_type']) ?></td></tr>
            <tr><th>Car Colour</th><td><?= htmlspecialchars($row['car_colour']) ?></td></tr>
            <tr><th>RC Copy</th><td>
                <?php if (!empty($row['rc_copy'])): ?>
                    <a href="<?= base_url . $row['rc_copy'] ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i> View File</a>
                <?php else: ?>No file uploaded<?php endif; ?>
            </td></tr>
            <tr><th>Insurance Copy</th><td>
                <?php if (!empty($row['insurance_copy'])): ?>
                    <a href="<?= base_url . $row['insurance_copy'] ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i> View File</a>
                <?php else: ?>No file uploaded<?php endif; ?>
            </td></tr>
            <tr><th>Fitness Certificate</th><td>
                <?php if (!empty($row['fitness_certificate_copy'])): ?>
                    <a href="<?= base_url . $row['fitness_certificate_copy'] ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i> View File</a>
                <?php else: ?>No file uploaded<?php endif; ?>
            </td></tr>
        </table>
    </div>
</div>
