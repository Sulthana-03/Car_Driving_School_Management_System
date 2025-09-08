<?php
require_once('../config.php');

// Session check
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorlogin.php");
    exit();
}

// Fetch instructor info
$instructor_id = $_SESSION['instructor_id'];
$qry = $conn->query("SELECT * FROM instructor WHERE id = '{$instructor_id}'");
if ($qry->num_rows > 0) {
    foreach ($qry->fetch_assoc() as $k => $v) {
        $$k = $v; // Extract the data and assign it to variables
    }
}
?>

<style>
    .img-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        object-position: center center;
        border-radius: 50%;
        border: 3px solid #007bff;
    }
    .profile-header {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .form-control:disabled {
        background-color: #e9ecef !important;
        opacity: 1 !important;
    }
    .document-link {
        display: block;
        margin-top: 5px;
        color: #007bff;
    }
    .document-link:hover {
        text-decoration: underline;
    }
    .section-title {
        font-size: 1.2rem;
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    .btn-submit {
        min-width: 150px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">My Profile</h3>
    </div>
    <div class="card-body">
        <!-- Profile Header with Avatar -->
        <div class="row profile-header">
            <div class="col-md-2 text-center">
                <img src="<?php echo validate_image($avatar ?? 'uploads/avatar/default.png') ?>" class="img-preview mb-2" id="avatar-preview" alt="Profile Image">
                <button type="button" class="btn btn-sm btn-primary" onclick="$('#avatar').click()">Change Photo</button>
            </div>
            <div class="col-md-10">
                <h3><?php echo ucwords($firstname.' '.$lastname) ?></h3>
                <p class="text-muted">Instructor Code: <?php echo $instructor_code ?></p>
                <p class="text-muted">Joined: <?php echo date('M d, Y', strtotime($joined_date)) ?></p>
                <p class="text-muted">License Number: <?php echo $license_number ?></p>
                <p class="text-muted">Status: <span class="badge badge-<?php echo $status == 1 ? 'success' : 'danger' ?>">
                    <?php echo $status == 1 ? 'Active' : 'Inactive' ?>
                </span></p>
            </div>
        </div>

        <form action="" id="instructor-profile-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id ?>">

            <!-- Personal Information Section -->
            <h4 class="section-title">Personal Information</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="firstname" class="control-label">First Name</label>
                        <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo $firstname ?? '' ?>" required readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="lastname" class="control-label">Last Name</label>
                        <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo $lastname ?? '' ?>" required readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gender" class="control-label">Gender</label>
                        <select name="gender" id="gender" class="form-control" required readonly>
                            <option value="male" <?php echo isset($gender) && $gender == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?php echo isset($gender) && $gender == 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?php echo isset($gender) && $gender == 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo $email ?? '' ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="phone" class="control-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $phone ?? '' ?>" maxlength="10" required>
                    </div>
                </div>
               
            </div>

            <div class="form-group">
                <label for="address" class="control-label">Address</label>
                <textarea name="address" id="address" class="form-control" rows="2" required><?php echo $address ?? '' ?></textarea>
            </div>

            <!-- Professional Information Section -->
            <h4 class="section-title">Professional Information</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="license_number" class="control-label">License Number</label>
                        <input type="text" name="license_number" id="license_number" class="form-control" value="<?php echo $license_number ?? '' ?>" maxlength="14" required readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="assigned_vehicle_id" class="control-label">Assigned Vehicle ID</label>
                        <input type="text" name="assigned_vehicle_id" id="assigned_vehicle_id" class="form-control" value="<?php echo $assigned_vehicle_id ?? '' ?>" maxlength="6" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="joined_date" class="control-label">Joined Date</label>
                        <input type="date" name="joined_date" id="joined_date" class="form-control" value="<?php echo $joined_date ?? '' ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="age" class="control-label">Age</label>
                        <input type="number" name="age" id="age" class="form-control" value="<?php echo $age ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="experience" class="control-label">Experience (years)</label>
                        <input type="number" name="experience" id="experience" class="form-control" value="<?php echo $experience ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="education" class="control-label">Education</label>
                        <input type="text" name="education" id="education" class="form-control" value="<?php echo $education ?? '' ?>">
                    </div>
                </div>
            </div>

            <!-- Account Information Section -->
            <h4 class="section-title">Account Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="avatar" class="control-label">Profile Photo</label>
                        <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
                        <?php if (isset($avatar) && !empty($avatar)): ?>
                            <a href="<?php echo validate_image($avatar) ?>" class="document-link" >View Current Photo</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="control-label">Password (leave blank to keep unchanged)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <h4 class="section-title">Documents</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="license_copy" class="control-label">License Copy</label>
                        <input type="file" name="license_copy" id="license_copy" class="form-control" accept="image/*,.pdf">
                        <?php if (isset($license_copy) && !empty($license_copy)): ?>
                            <a href="<?php echo validate_image($license_copy) ?>" class="document-link">View Current License Copy</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_proof" class="control-label">ID Proof</label>
                        <input type="file" name="id_proof" id="id_proof" class="form-control" accept="image/*,.pdf">
                        <?php if (isset($id_proof) && !empty($id_proof)): ?>
                            <a href="<?php echo validate_image($id_proof) ?>" class="document-link">View Current ID Proof</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg btn-submit">Update Profile</button>
                <a href="index.php" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Preview avatar before upload
    $('#avatar').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#instructor-profile-form').submit(function(e) {
        e.preventDefault();
        var _this = $(this);
        var formData = new FormData(_this[0]);
        
        // Add current file paths to form data
        formData.append('current_avatar', '<?php echo $avatar ?? "" ?>');
        formData.append('current_license_copy', '<?php echo $license_copy ?? "" ?>');
        formData.append('current_id_proof', '<?php echo $id_proof ?? "" ?>');

        // Validate at least one field is being changed
        var hasChanges = false;
        $(_this).find('input:not([type=file]):not([type=hidden]), textarea, select').each(function() {
            if ($(this).val() !== $(this).attr('data-original-value')) {
                hasChanges = true;
                return false; // break loop
            }
        });
        
        if (!hasChanges && $(_this).find('input[type=file]').filter(function() {
            return this.files.length > 0;
        }).length === 0) {
            alert_toast('No changes detected.', 'warning');
            return;
        }

        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Instructor.php?f=save",
            method: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            error: function(xhr, status, error) {
                try {
                    // Try to parse the response as JSON
                    var response = JSON.parse(xhr.responseText);
                    alert_toast(response.message || "An error occurred", 'error');
                } catch (e) {
                    // If not JSON, show raw response
                    console.error("Raw error response:", xhr.responseText);
                    alert_toast("An error occurred. Please check console for details.", 'error');
                }
                end_loader();
            },
            success: function(resp) {
                if (resp && resp.status == 'success') {
                    alert_toast(resp.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast(resp.message || "Update failed", 'error');
                    end_loader();
                }
            }
        });
    });

    // Store original values for change detection
    $('input:not([type=file]):not([type=hidden]), textarea, select').each(function() {
        $(this).attr('data-original-value', $(this).val());
    });
});
</script>