<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Indian Driving School - Training Packages</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2563eb;
      --secondary-color: #1e40af;
      --accent-color: #f59e0b;
      --dark-color: #1e293b;
      --light-color: #f8fafc;
    }
    body { font-family: 'Poppins', sans-serif; color: #334155; line-height: 1.6; margin: 0; padding: 0; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; font-weight: 700; }
    .site-title { font-size: 3.5rem; font-weight: 800; color: white; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); margin-bottom: 1.5rem; }
    .section-title { font-size: 2.8rem; font-weight: 700; margin-bottom: 1.5rem; position: relative; display: inline-block; color: var(--dark-color); }
    .section-title:after { content: ""; position: absolute; width: 60px; height: 4px; background: var(--accent-color); bottom: -10px; left: 50%; transform: translateX(-50%); border-radius: 2px; }
    .btn-primary { background-color: var(--primary-color); border: none; padding: 12px 30px; font-weight: 600; transition: all 0.3s ease; }
    .btn-primary:hover { background-color: var(--secondary-color); transform: translateY(-2px); }
    .card { border: none; border-radius: 12px; overflow: hidden; transition: all 0.3s ease; background: white; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); }
    .bg-light { background-color: #f8fafc !important; }
    .nav-link { font-weight: 500; padding: 0.5rem 1rem; color: var(--dark-color); }
    .nav-link:hover { color: var(--primary-color); }
    .collapse-icon { transition: transform 0.3s ease; }
    .container { padding-bottom: 0; margin-bottom: 0; }
    .wrapper { min-height: 100vh; display: flex; flex-direction: column; }
    main { flex: 1; }
    footer { margin-top: auto; }
     .nav-link {
      font-weight: 500;
      padding: 0.5rem 1rem;
      color: var(--dark-color);
    }
    
    .nav-link:hover {
      color: var(--primary-color);
    }
    
    #top-Nav a.nav-link.active {
      color: var(--primary-color);
      font-weight: 600;
      position: relative;
    }
    #top-Nav a.nav-link.active:after {
      content: "";
      position: absolute;
      border-bottom: 3px solid var(--accent-color);
      width: 50%;
      left: 25%;
      bottom: 0;
    }
  </style>
</head>

<body class="layout-top-nav layout-fixed layout-navbar-fixed">
<div class="wrapper">
<?php require_once('inc/header.php') ?>
  <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
  <?php require_once('inc/topBarNav.php') ?>
  <?php if($_settings->chk_flashdata('success')): ?>
    <script>
      alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
    </script>
  <?php endif; ?>   

<main class="flex-shrink-0">
<div class="container my-5">
  <h1 class="text-center section-title">Our Training Packages</h1>
  <div class="row justify-content-center mb-4">
    <div class="col-md-6">
      <div class="input-group">
        <input type="search" id="search" class="form-control" placeholder="Search Package here...">
        <button type="button" class="btn btn-primary">
          <i class="fa fa-search"></i>
        </button>
      </div>
    </div>
  </div>

  <div class="list-group" id="package-list">
    <?php 
      $package = $conn->query("SELECT * FROM `package_list` WHERE `status` = 1 ORDER BY `name` ASC");
      while($row = $package->fetch_assoc()):
    ?>
    <div class="list-group-item package-item">
      <a class="d-flex justify-content-between align-items-center text-decoration-none" href="#package_<?= $row['id'] ?>" data-bs-toggle="collapse">
        <h5 class="mb-0"><?= ucwords($row['name']) ?></h5>
        <i class="fa fa-plus collapse-icon"></i>
      </a>
      <div class="collapse mt-2" id="package_<?= $row['id'] ?>">
        <div class="mt-2">
          <small class="text-muted"><i class="fa fa-calendar me-2"></i><?= $row['training_duration'] ?></small>
          <small class="text-muted ms-3"><i class="fa fa-tags me-2"></i>₹<?= number_format($row['cost'], 2) ?></small>
          <p class="mt-2"><?= $row['description'] ?></p>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if($package->num_rows < 1): ?>
      <div id="no_result"><center><span class="text-muted">No package listed yet.</span></center></div>
    <?php endif; ?>
  </div>
</div>
</main>

<?php require_once('inc/footer.php') ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(function(){
    $('.collapse').on('show.bs.collapse', function () {
      $(this).parent().siblings().find('.collapse').collapse('hide');
      $(this).parent().find('.collapse-icon').removeClass('fa-plus').addClass('fa-minus');
    }).on('hide.bs.collapse', function () {
      $(this).parent().find('.collapse-icon').removeClass('fa-minus').addClass('fa-plus');
    });

    $('#search').on("input", function() {
      var _search = $(this).val().toLowerCase();
      var hasVisible = false;
      $('#package-list .package-item').each(function() {
        var _txt = $(this).text().toLowerCase();
        if (_txt.includes(_search)) {
          $(this).show();
          hasVisible = true;
        } else {
          $(this).hide();
        }
      });
      if (!hasVisible) {
        $('#no_result').show();
      } else {
        $('#no_result').hide();
      }
    });
  });
</script>

</body>
</html>
