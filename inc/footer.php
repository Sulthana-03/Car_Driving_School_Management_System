<!-- Back to Top Button -->
<a href="#" class="back-to-top rounded-circle shadow" style="display: none;">
  <i class="fas fa-arrow-up"></i>
</a>

<!-- Enhanced Footer -->
<footer class="main-footer text-sm bg-dark text-light py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4 mb-md-0">
        <h5 class="text-warning mb-3">Indian Driving School</h5>
        <p>Professional driving lessons with certified instructors and modern vehicles.</p>
        <div class="social-links mt-3">
          <a href="#" class="text-light mr-2" data-toggle="tooltip" title="Facebook"><i class="fab fa-facebook-f fa-lg"></i></a>
          <a href="#" class="text-light mr-2" data-toggle="tooltip" title="Twitter"><i class="fab fa-twitter fa-lg"></i></a>
          <a href="#" class="text-light mr-2" data-toggle="tooltip" title="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
          <a href="#" class="text-light" data-toggle="tooltip" title="YouTube"><i class="fab fa-youtube fa-lg"></i></a>
        </div>
      </div>
      <div class="col-md-4 mb-4 mb-md-0">
        <h5 class="text-warning mb-3">Quick Links</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="index.php" class="text-light">Home</a></li>
          <li class="mb-2"><a href="packages.php" class="text-light">Package</a></li>
          <li class="mb-2"><a href="contact.php" class="text-light">Contact</a></li>
          <li class="mb-2"><a href="enquiry.php" class="text-light">Enquiry</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h5 class="text-warning mb-3">Contact Info</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i> No.77, Sardar Vallabhai Patel Salai, near to Lotus Hotel, Ponnaiyapet, Puducherry, 605001</li>
          <li class="mb-2"><i class="fas fa-phone mr-2"></i> +91 7947416361</li>
          <li class="mb-2"><i class="fas fa-envelope mr-2"></i>indiandrivingschool@gmail.com</li>
          <li><i class="fas fa-clock mr-2"></i> Mon-Sat: 8:00 AM - 8:00 PM</li>
        </ul>
      </div>
    </div>
    <hr class="bg-secondary my-3">
    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-left">
        <strong>Copyright &copy; <?php echo date('Y') ?> Indian Driving School.</strong> All rights reserved.
      </div>
      <div class="col-md-6 text-center text-md-right">
        <b><?php echo $_settings->info('short_name') ?> (by: <a href="mailto:drivigschoolmanagement01@gmail.com" class="text-warning" target="blank">indiandrivingschool</a>)</b>
      </div>
    </div>
  </div>
</footer>

<!-- Modal Templates -->
<div class="modal fade" id="uni_modal" role="dialog">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content rounded-lg shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"></h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewer_modal" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded-lg shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Preview</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm_modal" role="dialog">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content rounded-lg shadow">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Confirmation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirm" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Required Libraries -->
<div id="libraries">
  <!-- jQuery UI -->
  <script>
    $.widget.bridge('uibutton', $.ui.button);
  </script>
  
  <!-- Bootstrap 4 -->
  <script src="<?php echo base_url ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  
  <!-- ChartJS -->
  <script src="<?php echo base_url ?>plugins/chart.js/Chart.min.js"></script>
  
  <!-- Sparkline -->
  <script src="<?php echo base_url ?>plugins/sparklines/sparkline.js"></script>
  
  <!-- Select2 -->
  <script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>
  
  <!-- JQVMap -->
  <script src="<?php echo base_url ?>plugins/jqvmap/jquery.vmap.min.js"></script>
  <script src="<?php echo base_url ?>plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
  
  <!-- jQuery Knob Chart -->
  <script src="<?php echo base_url ?>plugins/jquery-knob/jquery.knob.min.js"></script>
  
  <!-- Date Range Picker -->
  <script src="<?php echo base_url ?>plugins/moment/moment.min.js"></script>
  <script src="<?php echo base_url ?>plugins/daterangepicker/daterangepicker.js"></script>
  
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="<?php echo base_url ?>plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  
  <!-- Summernote -->
  <script src="<?php echo base_url ?>plugins/summernote/summernote-bs4.min.js"></script>
  
  <!-- DataTables -->
  <script src="<?php echo base_url ?>plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="<?php echo base_url ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?php echo base_url ?>plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="<?php echo base_url ?>plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  
  <!-- AdminLTE App -->
  <script src="<?php echo base_url ?>dist/js/adminlte.js"></script>
  
  <!-- Custom Scripts -->
  <script>
    // Initialize Select2
    $('.select2').select2({
      theme: 'bootstrap4'
    });
    
    // Initialize Summernote
    $('.summernote').summernote({
      height: 200,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    });
    
    // Initialize DataTables
    $('.data-table').DataTable({
      responsive: true,
      autoWidth: false,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search...",
      }
    });
  </script>
</div>
