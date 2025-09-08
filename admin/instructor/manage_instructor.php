<?php
// Check if an ID is passed for editing an instructor
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM instructor WHERE id = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v; // Extract the data and assign it to variables
        }
    }
}
?>
<?php
// Fetch unassigned cars or the current car assigned to this instructor
$car_qry = $conn->query("SELECT * FROM cars WHERE id NOT IN (SELECT assigned_vehicle_id FROM instructor WHERE assigned_vehicle_id IS NOT NULL) " . (isset($assigned_vehicle_id) ? "OR id = '{$assigned_vehicle_id}'" : ""));
$available_cars = [];
while ($row = $car_qry->fetch_assoc()) {
    $available_cars[] = $row;
}
?>


<style>
    .img-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        object-position: center center;
        border-radius: 50%;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo isset($id) ? "Edit Instructor" : "Add New Instructor" ?>
        </h3>
    </div>
    <div class="card-body">
        <form action="" id="instructor-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

            <?php if (isset($id)): ?>
            <div class="row">
                <div class="col-md-6">
                    <label for="instructor_code" class="control-label">Instructor Code</label>
                    <input type="text" id="instructor_code" class="form-control" value="<?php echo isset($instructor_code) ? $instructor_code : 'Auto Generated' ?>" disabled>
                    <input type="hidden" name="instructor_code" value="<?php echo isset($instructor_code) ? $instructor_code : '' ?>">
                </div>
            </div>
            <?php endif; ?>
            
            <input type="hidden" name="current_avatar" value="<?php echo isset($avatar) ? $avatar : '' ?>">
            <input type="hidden" name="current_license_copy" value="<?php echo isset($license_copy) ? $license_copy : '' ?>">
            <input type="hidden" name="current_id_proof" value="<?php echo isset($id_proof) ? $id_proof : '' ?>">

            <div class="row">
                <div class="col-md-6">
                    <label for="firstname" class="control-label">First Name</label>
                    <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo isset($firstname) ? $firstname : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="lastname" class="control-label">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo isset($lastname) ? $lastname : '' ?>" required>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($email) ? $email : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="control-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo isset($phone) ? $phone : '' ?>" maxlength="10" required>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <label for="address" class="control-label">Address</label>
                    <textarea name="address" id="address" class="form-control" rows="2"><?php echo isset($address) ? $address : '' ?></textarea>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="username" class="control-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($username) ? $username : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="control-label">Password (leave blank to keep unchanged)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="license_number" class="control-label">License Number</label>
                    <input type="text" name="license_number" id="license_number" class="form-control" value="<?php echo isset($license_number) ? $license_number : '' ?>" maxlength="14" required>
                </div>
                <div class="col-md-6">
    <label for="assigned_vehicle_id" class="control-label">Assigned Car</label>
    <select name="assigned_vehicle_id" id="assigned_vehicle_id" class="form-control">
        <option value="">-- Select a Car --</option>
        <?php foreach ($available_cars as $car): ?>
            <option value="<?php echo $car['id']; ?>" <?php echo (isset($assigned_vehicle_id) && $assigned_vehicle_id == $car['id']) ? 'selected' : '' ?>>
                <?php echo $car['car_code'] . " - " . $car['car_model']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="gender" class="control-label">Gender</label>
                    <select name="gender" id="gender" class="form-control">
                        <option value="Male" <?php echo isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?php echo isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?php echo isset($gender) && $gender == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="joined_date" class="control-label">Joined Date</label>
                    <input type="date" name="joined_date" id="joined_date" class="form-control" value="<?php echo isset($joined_date) ? $joined_date : '' ?>">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="education" class="control-label">Education</label>
                    <input type="text" name="education" id="education" class="form-control" value="<?php echo isset($education) ? $education : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="age" class="control-label">Age</label>
                    <input type="number" name="age" id="age" class="form-control" value="<?php echo isset($age) ? $age : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="experience" class="control-label">Experience (years)</label>
                    <input type="number" name="experience" id="experience" class="form-control" value="<?php echo isset($experience) ? $experience : '' ?>">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="avatar" class="control-label">Avatar</label>
                    <input type="file" name="avatar" id="avatar" class="form-control" onchange="previewImage(this, 'avatar_preview')">
                    <?php if (isset($avatar) && !empty($avatar)): ?>
                        <img src="<?php echo validate_image($avatar) ?>" class="img-preview mt-2" id="avatar_preview" alt="avatar">
                    <?php else: ?>
                        <img src="" class="img-preview mt-2 d-none" id="avatar_preview" alt="avatar">
                    <?php endif; ?>
                </div>
          

           
                <div class="col-md-6">
                    <label for="license_copy" class="control-label">License Copy</label>
                    <input type="file" name="license_copy" id="license_copy" class="form-control" onchange="previewFile(this, 'license_copy_preview_link')">
                    <?php if (isset($license_copy) && !empty($license_copy)): ?>
                            <a href="<?php echo 'http://localhost/cdsms/' . $license_copy; ?>" target="_blank" id="license_copy_preview_link">View Current File</a>
                    <?php else: ?>
                            <a href="#" target="_blank" id="license_copy_preview_link" class="d-none">View Current File</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label for="id_proof" class="control-label">ID Proof</label>
                    <input type="file" name="id_proof" id="id_proof" class="form-control" onchange="previewFile(this, 'id_proof_preview_link')">
                    <?php if (isset($id_proof) && !empty($id_proof)): ?>
                            <a href="<?php echo 'http://localhost/cdsms/' . $id_proof; ?>" target="_blank" id="id_proof_preview_link">View Current File</a>
                    <?php else: ?>
                            <a href="#" target="_blank" id="id_proof_preview_link" class="d-none">View Current File</a>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="status" class="control-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" <?php echo isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?php echo isset($status) && $status == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 text-center">
                <button class="btn btn-primary">Save Instructor</button>
                <a href="?page=instructor/list_instructor" class="btn btn-default">Cancel</a>
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
            // If no new file, hide link or show "No file selected"
            $('#' + previewId).addClass('d-none').attr('href', '#').text('View Current File');
            // If there was a current file, show its link again
            if($('input[name="current_' + input.name + '"]').val()) {
                $('#' + previewId).attr('href', 'http://localhost/cdsms/' + $('input[name="current_' + input.name + '"]').val()).text('View Current File').removeClass('d-none');
            }
        }
    }

    $(document).ready(function(){
        $('#instructor-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            var formData = new FormData(_this[0]);

            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Instructor.php?f=save",
                method: "POST",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                error: function(jqXHR, textStatus, errorThrown){
                    console.log("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                },
                success: function(resp){
                    if(resp.status == 'success'){
                        alert_toast('Instructor details saved successfully.', 'success');
                        setTimeout(() => {
                            location.href = "?page=instructor/list_instructor";
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