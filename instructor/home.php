<h1>Welcome to <?php echo $_settings->info('name') ?></h1>
<hr class="border-info">
<div class="row">
    <!-- Training Packages Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-th-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Training Packages</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `package_list` where status = 1")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- New Enrollments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-file-signature"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">New Enrollments</span>
                <span class="info-box-number text-right">
                    <?php 
                        $new_count = $conn->query("SELECT COUNT(*) as count FROM enrollees 
                                                  WHERE assigned_instructor_id = {$instructor_id} 
                                                  AND TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 
                                                  AND enrollment_status = 'Pending'")->fetch_assoc()['count'];
                        echo $new_count;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Verified Enrollments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Verified Enrollments</span>
                <span class="info-box-number text-right">
                    <?php 
                        $verified_count = $conn->query("SELECT COUNT(*) as count FROM enrollees 
                                                      WHERE assigned_instructor_id = {$instructor_id} 
                                                      AND enrollment_status = 'Verified'")->fetch_assoc()['count'];
                        echo $verified_count;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- In-Session Enrollments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">In-Session</span>
                <span class="info-box-number text-right">
                    <?php 
                        $in_session_count = $conn->query("SELECT COUNT(*) as count FROM enrollees 
                                                         WHERE assigned_instructor_id = {$instructor_id} 
                                                         AND enrollment_status = 'In-Session'")->fetch_assoc()['count'];
                        echo $in_session_count;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Completed Enrollments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-orange elevation-1"><i class="fas fa-certificate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Completed</span>
                <span class="info-box-number text-right">
                    <?php 
                        $completed_count = $conn->query("SELECT COUNT(*) as count FROM enrollees 
                                                        WHERE assigned_instructor_id = {$instructor_id} 
                                                        AND enrollment_status = 'Completed'")->fetch_assoc()['count'];
                        echo $completed_count;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- All Enrollments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">All Enrollments</span>
                <span class="info-box-number text-right">
                    <?php 
                        $all_count = $conn->query("SELECT COUNT(*) as count FROM enrollees 
                                                  WHERE assigned_instructor_id = {$instructor_id}")->fetch_assoc()['count'];
                        echo $all_count;
                    ?>
                </span>
            </div>
        </div>
    </div>


<!-- Car Assigned Card -->
<div class="col-12 col-sm-12 col-md-6 col-lg-3">
    <div class="info-box bg-light shadow">
        <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-car"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">Car Assigned</span>
            <span class="info-box-number text-right">
                <?php 
                    $car = $conn->query("SELECT c.car_code, c.car_model FROM instructor i 
                                        LEFT JOIN cars c ON i.assigned_vehicle_id = c.id 
                                        WHERE i.id = {$instructor_id}")->fetch_assoc();
                    echo isset($car['car_model']) ? $car['car_model'] . " (" . $car['car_code'] . ")" : "Not Assigned";
                ?>
            </span>
        </div>
    </div>
</div>

