<h1>Welcome to <?php echo $_settings->info('name') ?></h1>
<hr class="border-info">
<div class="row">
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Enrollment Status</span>
                <span class="info-box-number text-right">
                    <?php 
                    $user_id = $_SESSION['user_id'];
                    $status_query = $conn->query("SELECT enrollment_status FROM enrollees WHERE user_id = '$user_id'");
                    if($status_query->num_rows > 0) {
                        $status = $status_query->fetch_assoc()['enrollment_status'];
                        // Display badge based on status
                        $badge_class = '';
                        switch($status) {
                            case 'Pending': $badge_class = 'badge-secondary'; break;
                            case 'Verified': $badge_class = 'badge-primary'; break;
                            case 'In-Session': $badge_class = 'badge-warning'; break;
                            case 'Completed': $badge_class = 'badge-success'; break;
                            case 'Cancelled': $badge_class = 'badge-danger'; break;
                            default: $badge_class = 'badge-secondary';
                        }
                        echo "<span class='badge $badge_class'>$status</span>";
                    } else {
                        echo "<span class='badge badge-secondary'>Not Enrolled</span>";
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-12 col-md-6 col-lg-3">
        <div class="info-box bg-light shadow">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Payment Status</span>
                <span class="info-box-number text-right">
                    <?php 
                    if($status_query->num_rows > 0) {
                        $payment_query = $conn->query("SELECT payment_status FROM enrollees WHERE user_id = '$user_id'");
                        $payment_status = $payment_query->fetch_assoc()['payment_status'];
                        // Display badge based on payment status
                        $badge_class = '';
                        switch($payment_status) {
                            case 'Pending': $badge_class = 'badge-secondary'; break;
                            case 'Paid': $badge_class = 'badge-success'; break;
                            case 'Partially Paid': $badge_class = 'badge-warning'; break;
                            case 'Cancelled': $badge_class = 'badge-danger'; break;
                            default: $badge_class = 'badge-secondary';
                        }
                        echo "<span class='badge $badge_class'>$payment_status</span>";
                    } else {
                        echo "<span class='badge badge-secondary'>N/A</span>";
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>