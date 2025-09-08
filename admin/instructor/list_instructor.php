<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<style>
    .img-avatar {
        width: 45px;
        height: 45px;
        object-fit: cover;
        object-position: center center;
        border-radius: 100%;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Instructors</h3>
        <div class="card-tools">
           <a href="?page=instructor/manage_instructor" class="btn btn-flat btn-primary">
    <span class="fas fa-plus"></span> Create New
</a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-hover table-striped" id="instructorTable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Avatar</th>
                        <th>Instructor Code</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Assigned Car</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php 
                        $i = 1;
                        $qry = $conn->query("
    SELECT i.*, CONCAT(i.firstname, ' ', i.lastname) AS name, c.car_code 
    FROM `instructor` i 
    LEFT JOIN `cars` c ON i.assigned_vehicle_id = c.id 
    ORDER BY CONCAT(i.firstname, ' ', i.lastname) ASC
");

                        while($row = $qry->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td class="text-center">
                            <img src="<?php echo validate_image($row['avatar']) ?>" class="img-avatar img-thumbnail p-0 border-2" alt="instructor_avatar">
                        </td>
                        <td><?php echo $row['instructor_code']; ?></td>
                        <td><?php echo ucwords($row['name']); ?></td>
                        <td><?php echo ucfirst($row['gender']); ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo isset($row['car_code']) ? $row['car_code'] : 'N/A'; ?></td>
                        <td>
                            <?php if($row['status'] == 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td align="center">
                            <div class="btn-group">
                                <a class="btn btn-sm btn-info" href="?page=instructor/view_instructor&id=<?php echo $row['id']; ?>">
                                    <i class="fa fa-eye"></i> View
                                </a>&nbsp
                                <a class="btn btn-sm btn-primary" href="?page=instructor/manage_instructor&id=<?php echo $row['id']; ?>">
                                    <i class="fa fa-edit"></i> Edit
                                </a>&nbsp
                                <button class="btn btn-sm btn-danger delete_data" data-id="<?php echo $row['id']; ?>">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#instructorTable').DataTable({
            ordering: false
        });

        // Add this new code for checking create button click
        $('[href="?page=instructor/manage_instructor"]').on('click', function(e){
            console.log('Create button clicked');
            // If you still have issues, you might need to prevent default and handle navigation manually
            // e.preventDefault();
            // window.location.href = $(this).attr('href');
        });

        // Existing delete button handler
        $('.delete_data').click(function(){
            var id = $(this).data('id');
            _conf("Are you sure to delete this instructor permanently?", "delete_instructor", [id]);
        });
    });

    function delete_instructor(id){
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Instructor.php?f=delete",
            method: "POST",
            data: { id: id },
            dataType: "json",
            error: function(err){
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(resp.status == 'success'){
                    location.reload();
                } else {
                    alert_toast(resp.message || "An error occurred.", 'error');
                    end_loader();
                }
            }
        });
    }
</script>