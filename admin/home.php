<h1>Welcome to <?php echo $_settings->info('name') ?></h1>
<hr class="border-info">
<div class="row">
    <!-- Training Packages (Active) Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-th-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Packages</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `package_list` where status = 1")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Total Packages Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-teal elevation-1"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Packages</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `package_list`")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Total Instructors Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-navy elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Instructors</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `instructor`")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Active Instructors Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-purple elevation-1"><i class="fas fa-user-tie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Instructors</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `instructor` WHERE status = 1")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- New Enrollees (Last 24 hours) Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-user-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">New Enrollees</span>
                <span class="info-box-number text-right">
                    <?php
                        echo $conn->query("SELECT * FROM enrollees WHERE TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 AND enrollment_status = 'Pending'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Pending Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Enrollees</span>
                <span class="info-box-number text-right">
                    <?php
                        echo $conn->query("SELECT * FROM enrollees WHERE enrollment_status = 'Pending'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Verified Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Verified Enrollees</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `enrollment_status` = 'Verified'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- In-Session Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">In-Session</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `enrollment_status` = 'In-Session'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Completed Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Completed</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `enrollment_status` = 'Completed'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Cancelled Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cancelled</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `enrollment_status` = 'Cancelled'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Total Enrollees Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-indigo elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Enrollees</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees`")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>

 <!-- Pending Payments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-orange elevation-1"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Payments</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `payment_status` = 'pending'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>
    <!-- Partial Payments Card -->
<div class="row">
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-yellow elevation-1"><i class="fas fa-money-bill-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Partial Payments</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `payment_status` = 'partially paid'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Paid Payments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-green elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Paid Payments</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees` WHERE `payment_status` = 'paid'")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Total Payments Card -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-gradient-blue elevation-1"><i class="fas fa-cash-register"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Payments</span>
                <span class="info-box-number text-right">
                    <?php 
                        echo $conn->query("SELECT * FROM `enrollees`")->num_rows;
                    ?>
                </span>
            </div>
        </div>
    </div>

<!-- New UPI Payments (Last 24 hours) Card -->
<div class="col-12 col-sm-12 col-md-6 col-lg-3">
    <div class="info-box bg-light shadow">
        <span class="info-box-icon bg-gradient-pink elevation-1"><i class="fas fa-mobile-alt"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">New UPI Payments</span>
            <span class="info-box-number text-right">
                <?php 
                    echo $conn->query("SELECT * FROM `payment_list` WHERE `payment_mode` = 'upi' AND TIMESTAMPDIFF(HOUR, `date_created`, NOW()) <= 24")->num_rows;
                ?>
            </span>
        </div>
    </div>
</div>
</div>
<div class="row">
    <!-- Total Cars -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-car"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Cars</span>
                <span class="info-box-number text-right">
                    <?php echo $conn->query("SELECT * FROM `cars`")->num_rows; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Active Cars -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-car-side"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Cars</span>
                <span class="info-box-number text-right">
                    <?php echo $conn->query("SELECT * FROM `cars` WHERE `status` = 1")->num_rows; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Inactive Cars -->
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-car-crash"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Inactive Cars</span>
                <span class="info-box-number text-right">
                    <?php echo $conn->query("SELECT * FROM `cars` WHERE `status` = 0")->num_rows; ?>
                </span>
            </div>
        </div>
    </div>
</div>
