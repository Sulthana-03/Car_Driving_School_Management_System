<?php
// index.php (located at C:\xampp\htdocs\cdsms\user\index.php)

// This is the VERY FIRST EXECUTABLE PHP CODE IN index.php
// Make sure there are NO spaces, newlines, or characters before this <?php tag.

// 1. Basic Setup (config, session, error reporting)
// The path to config.php should be correct as `../config.php` if config.php is in 'cdsms/'
require_once('../config.php');

// Assuming $_settings is initialized in config.php.
// If session is not started in config.php, start it here.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// For debugging, always keep these lines at the very top for now.
// REMOVE IN PRODUCTION!
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. *** CRITICAL AJAX POST HANDLING BLOCK ***
// This check MUST happen BEFORE any HTML output, or any include that outputs HTML.
// The console error says: Fatal error: ... in C:\xampp\htdocs\cdsms\user\index.php on line 32
// So, this block must be above line 32 in *your* index.php.
if (isset($_GET['page']) && $_GET['page'] == 'enrollment/view_enrollment' &&
    $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_payment'])) {

    // IMPORTANT PATH FIX:
    // If THIS index.php is in 'C:\xampp\htdocs\cdsms\user\'
    // And view_enrollment.php is in 'C:\xampp\htdocs\cdsms\user\enrollment\'
    // Then the relative path from this index.php to view_enrollment.php is 'enrollment/view_enrollment.php'
    require_once(__DIR__ . '/enrollment/view_enrollment.php'); // THIS IS THE CORRECTED LINE
    exit; // Ensures no further HTML is sent for this AJAX request
}
// *** END OF CRITICAL AJAX POST HANDLING BLOCK ***


// 3. Normal Page Rendering (This part only executes if it's NOT the AJAX POST request)
// The rest of your index.php content that outputs HTML.
?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<?php require_once('inc/header.php') ?>
<body class="sidebar-mini layout-fixed control-sidebar-slide-open layout-navbar-fixed sidebar-mini-md sidebar-mini-xs" data-new-gr-c-s-check-loaded="14.991.0" data-gr-ext-installed="" style="height: auto;">
    <div class="wrapper">
        <?php require_once('inc/topBarNav.php') ?>
        <?php require_once('inc/navigation.php') ?>
        <?php if($_settings->chk_flashdata('success')): ?>
            <script>
                alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
            </script>
        <?php endif;?>

        <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
        <div class="content-wrapper pt-3" style="min-height: 567.854px;">

            <section class="content ">
                <div class="container-fluid">
                    <?php
                    // Your existing dynamic page loading logic for GET requests
                    // This is where view_enrollment.php is included for normal page viewing.
                    if(!file_exists($page.".php") && !is_dir($page)){
                        include '404.html';
                    }else{
                        if(is_dir($page))
                            include $page.'/index.php';
                        else
                            include $page.'.php';
                    }
                    ?>
                </div>
            </section>
            <div class="modal fade" id="confirm_modal" role='dialog'>
                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmation</h5>
                        </div>
                        <div class="modal-body">
                            <div id="delete_content"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="uni_modal" role='dialog'>
                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"></h5>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="uni_modal_right" role='dialog'>
                <div class="modal-dialog modal-full-height  modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span class="fa fa-arrow-right"></span>
                            </button>
                        </div>
                        <div class="modal-body">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="viewer_modal" role='dialog'>
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
                        <img src="" alt="">
                    </div>
                </div>
            </div>
        </div>
        <?php require_once('inc/footer.php') ?>
    </body>
</html>