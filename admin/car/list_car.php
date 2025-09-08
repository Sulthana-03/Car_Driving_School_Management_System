<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif; ?>

<style>
    .img-car {
        width: 60px;
        height: 45px;
        object-fit: cover;
        object-position: center center;
        border-radius: 5px;
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Cars</h3>
        <div class="card-tools">
           <a href="?page=car/manage_car" class="btn btn-flat btn-primary">
               <span class="fas fa-plus"></span> Add New Car
           </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-hover table-striped" id="carTable">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Image</th>
                        <th>Car Code</th>
                        <th>Model</th>
                        <th>Manufacturer</th>
                        <th>Fuel</th>
                        <th>Year</th>
                        <th>Status</th> <!-- New column -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT * FROM `cars` ORDER BY `car_code` ASC");
                        while($row = $qry->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td class="text-center">
                            <img src="<?php echo validate_image($row['car_image']) ?>" class="img-car img-thumbnail p-0 border-2" alt="car_image">
                        </td>
                        <td><?php echo $row['car_code']; ?></td>
                        <td><?php echo $row['car_model']; ?></td>
                        <td><?php echo $row['manufacturer']; ?></td>
                        <td><?php echo $row['fuel_type']; ?></td>
                        <td><?php echo $row['year_of_purchase']; ?></td>
                        <td>
                            <?php if($row['status'] == 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td align="center">
                            <div class="btn-group">
                                <a class="btn btn-sm btn-info" href="?page=car/view_car&id=<?php echo $row['id']; ?>">
                                    <i class="fa fa-eye"></i> View
                                </a>&nbsp
                                <a class="btn btn-sm btn-primary" href="?page=car/manage_car&id=<?php echo $row['id']; ?>">
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
        $('#carTable').DataTable({
            ordering: false
        });

        $('.delete_data').click(function(){
            var id = $(this).data('id');
            _conf("Are you sure to delete this car permanently?", "delete_car", [id]);
        });
    });

    function delete_car(id){
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Car.php?f=delete",
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
