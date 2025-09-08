<?php
// Fetch data for editing
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM cars WHERE id = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>

<style>
    .img-preview {
        width: 100px;
        height: 70px;
        object-fit: cover;
        object-position: center center;
        border-radius: 8px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><?php echo isset($id) ? "Edit Car" : "Add New Car" ?></h3>
    </div>
    <div class="card-body">
        <form action="" id="car-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <input type="hidden" name="current_car_image" value="<?php echo isset($car_image) ? $car_image : '' ?>">
            <input type="hidden" name="current_rc_copy" value="<?php echo isset($rc_copy) ? $rc_copy : '' ?>">
            <input type="hidden" name="current_insurance_copy" value="<?php echo isset($insurance_copy) ? $insurance_copy : '' ?>">
            <input type="hidden" name="current_fitness_certificate_copy" value="<?php echo isset($fitness_certificate_copy) ? $fitness_certificate_copy : '' ?>">

            <?php if (isset($id)): ?>
            <div class="row">
                <div class="col-md-6">
                    <label for="car_code" class="control-label">Car Code</label>
                    <input type="text" class="form-control" value="<?php echo isset($car_code) ? $car_code : 'Auto Generated' ?>" disabled>
                    <input type="hidden" name="car_code" value="<?php echo isset($car_code) ? $car_code : '' ?>">
                </div>
            </div>
            <?php endif; ?>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="car_model" class="control-label">Car Model</label>
                    <input type="text" name="car_model" id="car_model" class="form-control" value="<?php echo isset($car_model) ? $car_model : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="manufacturer" class="control-label">Manufacturer</label>
                    <input type="text" name="manufacturer" id="manufacturer" class="form-control" value="<?php echo isset($manufacturer) ? $manufacturer : '' ?>">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="year_of_purchase" class="control-label">Year of Purchase</label>
                    <input type="number" name="year_of_purchase" id="year_of_purchase" class="form-control" min="1990" max="<?php echo date('Y') ?>" value="<?php echo isset($year_of_purchase) ? $year_of_purchase : '' ?>">
                </div>
                <div class="col-md-6">
                    <label for="fuel_type" class="control-label">Fuel Type</label>
                    <select name="fuel_type" id="fuel_type" class="form-control">
                        <option value="Petrol" <?php echo (isset($fuel_type) && $fuel_type == 'Petrol') ? 'selected' : '' ?>>Petrol</option>
                        <option value="Diesel" <?php echo (isset($fuel_type) && $fuel_type == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                        <option value="Electric" <?php echo (isset($fuel_type) && $fuel_type == 'Electric') ? 'selected' : '' ?>>Electric</option>
                    </select>
                </div>
                
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="car_colour" class="control-label">Car Colour</label>
                    <input type="text" name="car_colour" id="car_colour" class="form-control" value="<?php echo isset($car_colour) ? $car_colour : '' ?>">
                </div>
                <div class="col-md-6">
                    <label for="car_image" class="control-label">Car Image</label>
                    <input type="file" name="car_image" id="car_image" class="form-control" onchange="previewImage(this, 'car_image_preview')">
                    <?php if (isset($car_image) && !empty($car_image)): ?>
                        <img src="<?php echo validate_image($car_image) ?>" class="img-preview mt-2" id="car_image_preview" alt="car image">
                    <?php else: ?>
                        <img src="" class="img-preview mt-2 d-none" id="car_image_preview" alt="car image">
                    <?php endif; ?>
                </div>
                
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="rc_copy" class="control-label">RC Copy</label>
                    <input type="file" name="rc_copy" id="rc_copy" class="form-control" onchange="previewFile(this, 'rc_preview')">
                    <?php if (isset($rc_copy) && !empty($rc_copy)): ?>
                        <a href="<?php echo base_url . $rc_copy ?>" target="_blank" id="rc_preview">View Current File</a>
                    <?php else: ?>
                        <a href="#" target="_blank" class="d-none" id="rc_preview">View File</a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="insurance_copy" class="control-label">Insurance Copy</label>
                    <input type="file" name="insurance_copy" id="insurance_copy" class="form-control" onchange="previewFile(this, 'insurance_preview')">
                    <?php if (isset($insurance_copy) && !empty($insurance_copy)): ?>
                        <a href="<?php echo base_url . $insurance_copy ?>" target="_blank" id="insurance_preview">View Current File</a>
                    <?php else: ?>
                        <a href="#" target="_blank" class="d-none" id="insurance_preview">View File</a>
                    <?php endif; ?>
                </div>
            </div>   
            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="fitness_certificate_copy" class="control-label">Fitness Certificate</label>
                    <input type="file" name="fitness_certificate_copy" id="fitness_certificate_copy" class="form-control" onchange="previewFile(this, 'fitness_preview')">
                    <?php if (isset($fitness_certificate_copy) && !empty($fitness_certificate_copy)): ?>
                        <a href="<?php echo base_url . $fitness_certificate_copy ?>" target="_blank" id="fitness_preview">View Current File</a>
                    <?php else: ?>
                        <a href="#" target="_blank" class="d-none" id="fitness_preview">View File</a>
                    <?php endif; ?>
                </div>
                    <div class="col-md-6">
                       <label for="status" class="control-label">Status</label>
                       <select name="status" id="status" class="form-control">
                       <option value="1" <?php echo (isset($status) && $status == 1) ? 'selected' : '' ?>>Active</option>
                       <option value="0" <?php echo (isset($status) && $status == 0) ? 'selected' : '' ?>>Inactive</option>
                       </select>
                    </div>
                </div>

            

            <div class="mt-3 text-center">
                <button class="btn btn-primary">Save Car</button>
                <a href="?page=car/list_car" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + previewId).attr('src', e.target.result).removeClass('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#' + previewId).attr('src', '').addClass('d-none');
        }
    }

    function previewFile(input, previewId) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var fileURL = URL.createObjectURL(file);
            $('#' + previewId).attr('href', fileURL).text('View New File').removeClass('d-none');
        } else {
            $('#' + previewId).attr('href', '#').text('View File').addClass('d-none');
        }
    }

    $(document).ready(function(){
        $('#car-form').submit(function(e){
            e.preventDefault();
            var formData = new FormData(this);
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Car.php?f=save",
                method: "POST",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                error: function(xhr, status, error){
                    console.log("Error:", xhr.responseText);
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                },
                success: function(resp){
                    if(resp.status == 'success'){
                        alert_toast("Car saved successfully!", 'success');
                        setTimeout(() => {
                            location.href = "?page=car/list_car";
                        }, 1500);
                    } else {
                        alert_toast(resp.message || "An error occurred.", 'error');
                        end_loader();
                    }
                }
            });
        });
    });
</script>
