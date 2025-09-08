<?php
require_once('inc/sess_auth.php');
require_once('../config.php');

$user_id = $_SESSION['user_id'] ?? null;
$payment_enrollment_id = null;

if ($user_id) {
    $payment_qry = $conn->query("SELECT id FROM enrollees WHERE user_id = '{$user_id}' ORDER BY created_at DESC LIMIT 1");
    if ($payment_qry && $payment_qry->num_rows > 0) {
        $payment_enrollment_id = $payment_qry->fetch_assoc()['id'];
    }
}
?>

</style>
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
                <!-- Sidebar user panel (optional) -->
                <div class="clearfix"></div>
                <!-- Sidebar Menu -->
                <nav class="mt-4">
                   <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-compact nav-flat nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item dropdown">
                      <a href="./" class="nav-link nav-home">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                          Dashboard
                        </p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="<?php echo base_url ?>user/?page=enrollment/list_enrollment" class="nav-link nav-enrollment">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Enrollment</p>
                      </a>
                    </li>
                    <!-- Sidebar Item for Payment -->
            <?php if ($payment_enrollment_id): ?>
<li class="nav-item">
  <a href="<?php echo base_url ?>user/?page=enrollment/view_enrollment&id=<?= $payment_enrollment_id ?>" class="nav-link nav-payment">
    <i class="nav-icon fas fa-credit-card"></i>
    <p>Payment</p>
  </a>
</li>

<?php endif; ?>



                    
                   
                    <?php if($_settings->userdata('type') == 1): ?>
                    
                   
                    
                    <?php endif; ?>

                  </ul>
                </nav>
                <!-- /.sidebar-menu -->
              </div>
            </div>
          </div>
          <div class="os-scrollbar os-scrollbar-horizontal os-scrollbar-unusable os-scrollbar-auto-hidden">
            <div class="os-scrollbar-track">
              <div class="os-scrollbar-handle" style="width: 100%; transform: translate(0px, 0px);"></div>
            </div>
          </div>
          <div class="os-scrollbar os-scrollbar-vertical os-scrollbar-auto-hidden">
            <div class="os-scrollbar-track">
              <div class="os-scrollbar-handle" style="height: 55.017%; transform: translate(0px, 0px);"></div>
            </div>
          </div>
          <div class="os-scrollbar-corner"></div>
        </div>
        <!-- /.sidebar -->
      </aside>
       <script>
        $(document).ready(function () {
    var page = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>';
    var status = '<?php echo isset($_GET['status']) ? $_GET['status'] : '' ?>';
    var normalizedPage = page.replace(/\//g, '_') + (status ? '_' + status.toLowerCase().replace('-', '_') : '');

    if (normalizedPage === 'instructor_list_instructor') {
        normalizedPage = 'user_list';
    }

    // Function to set active menu item
    function setActiveMenu() {
        // Remove all active classes first
        $('.nav-link').removeClass('active');
        $('.nav-item').removeClass('menu-open');

        // Set active state based on current page
        if (page.startsWith('enrollments')) {
            // For enrollments pages
            var enrollmentsMenu = $('.nav-link.nav-enrollments');
            enrollmentsMenu.addClass('active');
            enrollmentsMenu.closest('.has-treeview').addClass('menu-open');
            
            // Set active state for specific enrollment status if exists
            if (status) {
                $('.nav-link.nav-enrollments_' + status.toLowerCase().replace('-', '_')).addClass('active');
            } else {
                $('.nav-link.nav-enrollments_index').addClass('active');
            }
        } else {
            // For all other pages
            var targetLink = $('.nav-link.nav-' + normalizedPage);
            if (targetLink.length) {
                targetLink.addClass('active');
                if (targetLink.hasClass('tree-item')) {
                    targetLink.closest('.has-treeview').addClass('menu-open');
                    targetLink.closest('.has-treeview').find('> a.nav-link').addClass('active');
                }
            }
        }
    }

    // Initialize active states
    setActiveMenu();

    // Click handler for Enrollments menu
    $('.nav-link.nav-enrollments').on('click', function(e) {
        e.preventDefault();
        
        // Close all other menus
        $('.nav-item.has-treeview').not($(this).parent()).removeClass('menu-open');
        $('.nav-link').not(this).removeClass('active');
        
        // Get the parent item
        var parentItem = $(this).parent();
        var wasOpen = parentItem.hasClass('menu-open');
        
        // Close all menus first
        $('.nav-item.has-treeview').removeClass('menu-open');
        
        // Then open the enrollments menu after a small delay
        setTimeout(function() {
            parentItem.addClass('menu-open');
            $(this).addClass('active');
            
            // Ensure submenu is visible
            parentItem.find('.nav-treeview').first().css({
                'display': 'block',
                'height': 'auto',
                'overflow': 'visible'
            });
        }.bind(this), 50);
    });

    // Click handler for all other menu items
    $('.nav-link').not('.nav-enrollments').on('click', function(e) {
        // For non-link items (like headers), don't prevent default
        if ($(this).attr('href') === undefined || $(this).attr('href') === '#') {
            return;
        }
        
        e.preventDefault();
        
        // Remove all active classes
        $('.nav-link').removeClass('active');
        $('.nav-item').removeClass('menu-open');
        
        // Set active for clicked item
        $(this).addClass('active');
        
        // If it's a parent menu, open it
        if ($(this).hasClass('nav-is-tree')) {
            $(this).parent().addClass('menu-open');
        }
        
        // Load the page
        window.location.href = $(this).attr('href');
    });
});


</script>

