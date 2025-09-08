<?php
$admin_id = $_settings->userdata('id');
$qry = $conn->query("SELECT * FROM admin WHERE id = '{$admin_id}'");
$meta = $qry->fetch_assoc();
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h5 class="card-title">Update Admin Profile</h5>
    </div>
    <div class="card-body">
        <form action="/cdsms/classes/Adminprofile.php?f=save_admin" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $admin_id ?>">

            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" name="firstname" required class="form-control" value="<?php echo $meta['firstname'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" name="lastname" required class="form-control" value="<?php echo $meta['lastname'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" required class="form-control" value="<?php echo $meta['username'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                <small class="text-muted">Leave this blank if you don't want to change the password.</small>
            </div>

            <div class="form-group">
                <label for="avatar">Avatar</label>
                <input type="file" name="avatar" class="form-control-file" accept="image/*" onchange="displayImg(this)">
            </div>

            <div class="form-group text-center">
                <img src="<?php echo isset($meta['avatar']) && is_file(base_app . $meta['avatar']) ? base_url . $meta['avatar'] . '?v=' . time() : 'https://cdn-icons-png.flaticon.com/512/3177/3177440.png' ?>" alt="Avatar" id="cimg" class="img-fluid img-thumbnail rounded-circle" style="width: 120px; height: 120px;">
            </div>

            <div class="form-group text-right">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function displayImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('cimg').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>