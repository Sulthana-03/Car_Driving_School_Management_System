<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Save basic system settings
    $_settings->set_info('name', $_POST['name']);
    $_settings->set_info('short_name', $_POST['short_name']);
    $_settings->set_info('email', $_POST['email']);
    $_settings->set_info('location', $_POST['location']);
    $_settings->set_info('contact', $_POST['contact']);
    $_settings->set_info('address', $_POST['address']);

    // Save welcome and about us content to HTML files
    file_put_contents(base_app . 'welcome.html', $_POST['content']['welcome']);
    file_put_contents(base_app . 'about_us.html', $_POST['content']['about_us']);

    // Handle system logo upload
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $logoPath = 'uploads/logo_' . time() . '_' . basename($_FILES['img']['name']);
        if (move_uploaded_file($_FILES['img']['tmp_name'], $logoPath)) {
            $_settings->set_info('logo', $logoPath);
        } else {
            $_settings->set_flashdata('error', 'Failed to upload logo.');
        }
    }

    // Handle cover upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $coverPath = 'uploads/cover_' . time() . '_' . basename($_FILES['cover']['name']);
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
            $_settings->set_info('cover', $coverPath);
        } else {
            $_settings->set_flashdata('error', 'Failed to upload cover image.');
        }
    }

    $_settings->set_flashdata('success', 'System information updated successfully.');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php if ($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success');
</script>
<?php endif; ?>

<?php if ($_settings->chk_flashdata('error')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('error') ?>", 'error');
</script>
<?php endif; ?>

<style>
    img#cimg {
        height: 15vh;
        width: 15vh;
        object-fit: scale-down;
        border-radius: 100%;
    }
    img#cimg2 {
        height: 50vh;
        width: 100%;
        object-fit: contain;
    }
</style>

<div class="col-lg-12">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h5 class="card-title">System Information</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="system-frm" method="POST" enctype="multipart/form-data">
                <div id="msg" class="form-group"></div>

                <div class="form-group">
                    <label for="name" class="control-label">System Name</label>
                    <input type="text" class="form-control form-control-sm" name="name" id="name" value="<?php echo $_settings->info('name') ?>">
                </div>
                
                <div class="form-group">
                    <label for="short_name" class="control-label">System Short Name</label>
                    <input type="text" class="form-control form-control-sm" name="short_name" id="short_name" value="<?php echo $_settings->info('short_name') ?>">
                </div>
                
                <div class="form-group">
                    <label for="about_us" class="control-label">About Us</label>
                    <textarea class="form-control form-control-sm summernote" name="content[about_us]" id="about_us"><?php echo is_file(base_app . 'about_us.html') ? file_get_contents(base_app . 'about_us.html') : '' ?></textarea>
                </div>

               
                <fieldset>
                    <legend>School Information</legend>
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" id="email" value="<?php echo $_settings->info('email') ?>">
                    </div>
                    <div class="form-group">
                        <label for="location" class="control-label">Location</label>
                        <input type="text" class="form-control form-control-sm" name="location" id="location" value="<?php echo $_settings->info('location') ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact" class="control-label">Contact</label>
                        <input type="text" class="form-control form-control-sm" name="contact" id="contact" value="<?php echo $_settings->info('contact') ?>">
                    </div>
                    <div class="form-group">
                        <label for="address" class="control-label">Address</label>
                        <textarea rows="3" class="form-control form-control-sm" name="address" id="address" style="resize:none"><?php echo $_settings->info('address') ?></textarea>
                    </div>
                </fieldset>

                <div class="card-footer">
                    <button class="btn btn-sm btn-primary">Update</button>

            </form>
        </div>
    </div>
</div>

<script>
    function displayImg(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#cimg').attr('src', e.target.result);
                _this.siblings('.custom-file-label').html(input.files[0].name);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function displayImg2(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#cimg2').attr('src', e.target.result);
                _this.siblings('.custom-file-label').html(input.files[0].name);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function(){
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview', 'help']]
            ]
        });
    });
</script>
