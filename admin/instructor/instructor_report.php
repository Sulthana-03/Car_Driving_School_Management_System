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
    .report-card {
        transition: all 0.3s ease;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Instructor Wise Report</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="row">
                <?php 
                    $qry = $conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM `instructor` WHERE status = 1 ORDER BY CONCAT(firstname, ' ', lastname) ASC");
                    while($row = $qry->fetch_assoc()):
                        
                    // Count assigned students
                    $students_qry = $conn->query("SELECT COUNT(id) as total_students FROM `enrollees` WHERE assigned_instructor_id = '{$row['id']}' AND enrollment_status IN ('Verified', 'In-Session', 'Completed')");
                    $students_count = $students_qry->fetch_assoc()['total_students'];
                ?>
                <div class="col-md-4 col-sm-6 col-12 mb-4">
                    <div class="report-card card shadow">
                        <div class="card-body text-center">
                            <img src="<?php echo validate_image($row['avatar']) ?>" class="img-avatar img-thumbnail p-0 border-2 mb-3" alt="instructor_avatar" style="width: 80px; height: 80px;">
                            <h4 class="mb-1"><?php echo ucwords($row['name']); ?></h4>
                            <p class="text-muted mb-2"><?php echo $row['instructor_code']; ?></p>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Gender:</span>
                                <span><?php echo ucfirst($row['gender']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone:</span>
                                <span><?php echo $row['phone']; ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Students:</span>
                                <span class="badge badge-primary"><?php echo $students_count; ?></span>
                            </div>
                            
                            <a href="<?php echo base_url ?>admin/?page=instructor/view_user&instructor_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fa fa-users"></i> View Students
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if($qry->num_rows == 0): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">No Active Instructors Found</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>