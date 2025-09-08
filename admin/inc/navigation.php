<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4 sidebar-no-expand bg-dark">
    <!-- Brand Logo -->
    <a href="<?php echo base_url ?>admin" class="brand-link bg-transparent text-sm shadow-sm">
        <img src="<?php echo validate_image($_settings->info('logo'))?>" alt="Store Logo" class="brand-image img-circle elevation-3 bg-black" style="width: 1.8rem;height: 1.8rem;max-height: unset;object-fit:scale-down;object-position:center center">
        <span class="brand-text font-weight-light"><?php echo $_settings->info('short_name') ?></span>
    </a>
    
    <!-- Sidebar -->
    <div class="sidebar os-host os-theme-light os-host-overflow os-host-overflow-y os-host-resize-disabled os-host-transition os-host-scrollbar-horizontal-hidden">
        <div class="os-resize-observer-host observed">
            <div class="os-resize-observer" style="left: 0px; right: auto;"></div>
        </div>
        <div class="os-size-auto-observer observed" style="height: calc(100% + 1px); float: left;">
            <div class="os-resize-observer"></div>
        </div>
        <div class="os-content-glue" style="margin: 0px -8px; width: 249px; height: 646px;"></div>
        <div class="os-padding">
            <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
                <div class="os-content" style="padding: 0px 8px; height: 100%; width: 100%;">
                    <!-- Sidebar Menu -->
                    <nav class="mt-4">
                        <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-compact nav-flat nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item">
                                <a href="./" class="nav-link nav-home">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            
                            <li class="nav-item has-treeview" id="enrollments-menu">
                                <a href="#" class="nav-link nav-enrollments">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Enrollments
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=New" class="nav-link nav-enrollments_new tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>New Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=Pending" class="nav-link nav-enrollments_pending tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pending Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=Verified" class="nav-link nav-enrollments_verified tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Verified Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=In-Session" class="nav-link nav-enrollments_in_session tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>In-Session Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=Completed" class="nav-link nav-enrollments_completed tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Completed Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index&status=Cancelled" class="nav-link nav-enrollments_cancelled tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Cancelled Enrollments</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?php echo base_url ?>admin/?page=enrollments/index" class="nav-link nav-enrollments_index tree-item">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>All Enrollments</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                             <li class="nav-item has-treeview" id="payments-menu">
    <a href="#" class="nav-link nav-payments">
        <i class="nav-icon fas fa-rupee-sign"></i>
        <p>
            Payment Status
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        
        <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=payments/index&status=Pending" class="nav-link nav-payments_pending tree-item">
                <i class="far fa-circle nav-icon"></i>
                <p>Pending Payments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=payments/index&status=Partially Paid" class="nav-link nav-payments_partial tree-item">
                <i class="far fa-circle nav-icon"></i>
                <p>Partial Payments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=payments/index&status=Paid" class="nav-link nav-payments_paid tree-item">
                <i class="far fa-circle nav-icon"></i>
                <p>Paid Payments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=payments/index" class="nav-link nav-payments_index tree-item">
                <i class="far fa-circle nav-icon"></i>
                <p>All Payments</p>
            </a>
        </li>
    </ul>
</li>

                            <li class="nav-item">
    <a href="<?php echo base_url ?>admin/?page=payments/new_upi_payment" class="nav-link nav-new-upi">
        <i class="nav-icon fas fa-money-check-alt"></i>
        <p>New UPI Payment</p>
    </a>
</li>




                            <li class="nav-header">Reports</li>
                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=reports" class="nav-link nav-reports">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>Payment Report</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=reports/datewise_reg" class="nav-link nav-reports_datewise_reg">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>Registration</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=instructor/instructor_report" class="nav-link nav-instructor_report">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>Instructor Wise Work</p>
                                </a>
                            </li>
                             <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=packages/packagewise_report" class="nav-link nav-packagewise_report">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>Packagewise student report</p>
                                </a>
                            </li>
                            
                            <?php if($_settings->userdata('type') == 1): ?>
                            <li class="nav-header">Maintenance</li>
                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=packages" class="nav-link nav-packages">
                                    <i class="nav-icon fas fa-th-list"></i>
                                    <p>Package</p>
                                </a>
                            </li>


                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=car/list_car" class="nav-link nav-car_list">
                                    <i class="nav-icon fas fa-car"></i>
                                    <p>Cars</p>
                                </a>
                            </li>
   

                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=instructor/list_instructor" class="nav-link nav-user_list">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Instructor</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?php echo base_url ?>admin/?page=system_info" class="nav-link nav-system_info">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Settings</p>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</aside>

<script>
$(document).ready(function() {
    // Initialize menu state
    initMenu();
    
    // Handle menu clicks
    $('.nav-link').on('click', function(e) {
        // For the enrollments parent menu
        if ($(this).hasClass('nav-enrollments')) {
            e.preventDefault();
            toggleEnrollmentMenu();
            return false;
        }
        if ($(this).hasClass('nav-payments')) {
            e.preventDefault();
            togglePaymentMenu();
            return false;
        }
        
        // For regular links, allow normal navigation
        return true;
    });
});

function initMenu() {
    var page = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>'.split('?')[0];
    var status = '<?php echo isset($_GET['status']) ? $_GET['status'] : '' ?>';
    
    // Reset all active states
    $('.nav-link').removeClass('active');
    $('.nav-item').removeClass('menu-open');
    $('.right.fas').removeClass('fa-angle-down').addClass('fa-angle-left');
    
    // Handle dashboard/home page
    if (page === 'home' || page === 'admin' || page === '') {
        $('.nav-link.nav-home').addClass('active');
        return;
    }
    
    // Handle new UPI payment page
    if (page === 'payments/new_upi_payment') {
        $('.nav-link.nav-new-upi').addClass('active');
        return;
    }

    
    // Handle reports pages
    if (page === 'reports') {
        $('.nav-link.nav-reports').addClass('active');
        return;
    }
    if (page === 'reports/datewise_reg') {
        $('.nav-link.nav-reports_datewise_reg').addClass('active');
        return;
    }
    if (page === 'instructor/instructor_report') {
        $('.nav-link.nav-instructor_report').addClass('active');
        return;
    }
    if (page === 'packages/packagewise_report') {
        $('.nav-link.nav-packagewise_report').addClass('active');
        return;
    }
    
    // Handle enrollment pages
    if (page.startsWith('enrollments')) {
        $('#enrollments-menu').addClass('menu-open');
        $('.nav-link.nav-enrollments').addClass('active')
            .find('.right.fas').removeClass('fa-angle-left').addClass('fa-angle-down');
        
        if (status) {
            $('.nav-link.nav-enrollments_' + status.toLowerCase().replace('-', '_')).addClass('active');
        } else {
            $('.nav-link.nav-enrollments_index').addClass('active');
        }
        return;
    }


    // Handle payment status pages
    if (page.startsWith('payments')) {
        $('#payments-menu').addClass('menu-open');
        $('.nav-link.nav-payments').addClass('active')
            .find('.right.fas').removeClass('fa-angle-left').addClass('fa-angle-down');
        
        if (status) {
            $('.nav-link.nav-payments_' + status.toLowerCase().replace('-', '_')).addClass('active');
        } else {
            $('.nav-link.nav-payments_index').addClass('active');
        }
        return;
    }
    
    
    // Handle maintenance pages
    if (page === 'packages') {
        $('.nav-link.nav-packages').addClass('active');
        return;
    }
    if (page === 'car/list_car') {
    $('.nav-link.nav-car_list').addClass('active');
    return;
    }
    
    if (page === 'instructor/list_instructor') {
        $('.nav-link.nav-user_list').addClass('active');
        return;
    }
    if (page === 'system_info') {
        $('.nav-link.nav-system_info').addClass('active');
        return;
    }
}

function toggleEnrollmentMenu() {
    var menu = $('#enrollments-menu');
    var isOpen = menu.hasClass('menu-open');
    var icon = $('.nav-link.nav-enrollments').find('.right.fas');
    
    // Toggle this menu
    menu.toggleClass('menu-open', !isOpen);
    icon.toggleClass('fa-angle-left fa-angle-down');
    
    // Set active state
    $('.nav-link').removeClass('active');
    $('.nav-link.nav-enrollments').addClass('active');
    
    // If opening, activate the index page by default
    if (!isOpen) {
        $('.nav-link.nav-enrollments_index').addClass('active');
    }
    
    // Prevent other click handlers from interfering
    return false;
}


function togglePaymentMenu() {
    var menu = $('#payments-menu');
    var isOpen = menu.hasClass('menu-open');
    var icon = $('.nav-link.nav-payments').find('.right.fas');
    
    // Toggle this menu
    menu.toggleClass('menu-open', !isOpen);
    icon.toggleClass('fa-angle-left fa-angle-down');
    
    // Set active state
    $('.nav-link').removeClass('active');
    $('.nav-link.nav-payments').addClass('active');
    
    // If opening, activate the index page by default
    if (!isOpen) {
        $('.nav-link.nav-payments_index').addClass('active');
    }
    
    // Prevent other click handlers from interfering
    return false;
}
</script>
