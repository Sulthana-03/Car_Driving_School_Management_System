<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Indian Driving School - Contact</title>
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
    body { font-family: 'Poppins', sans-serif; color: #334155; line-height: 1.6; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; font-weight: 700; }
    .section-title { font-size: 2.8rem; font-weight: 700; margin-bottom: 1.5rem; position: relative; display: inline-block; color: var(--dark-color); }
    .section-title:after { content: ""; position: absolute; width: 60px; height: 4px; background: var(--accent-color); bottom: -10px; left: 50%; transform: translateX(-50%); border-radius: 2px; }
    .card { border: none; border-radius: 12px; overflow: hidden; transition: all 0.3s ease; background: white; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); }
    .nav-link { font-weight: 500; padding: 0.5rem 1rem; color: var(--dark-color); }
    .nav-link:hover { color: var(--primary-color); }
    .social-icons a { font-size: 1.5rem; margin-right: 15px; color: var(--dark-color); transition: color 0.3s ease; }
    .social-icons a:hover { color: var(--primary-color); }
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

<body class="layout-top-nav layout-fixed layout-navbar-fixed" style="height: auto;">
<?php require_once('inc/header.php') ?>
<div class="wrapper">

  <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
  <?php require_once('inc/topBarNav.php') ?>
  <?php if($_settings->chk_flashdata('success')): ?>
    <script>
      alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
    </script>
  <?php endif; ?>

<div class="container my-5">
  <h1 class="text-center section-title">Contact Us</h1>
  <div class="row justify-content-center">
    <div class="col-12 col-sm-12 col-md-5">
      <div class="card card-outline card-navy rounded-0 shadow">
        <div class="card-header">
          <h4 class="card-title">Contact</h4>
        </div>
        <div class="card-body rounded-0">
          <dl>
            <dt class="text-muted"><i class="fas fa-envelope"></i> Email</dt>
            <dd class="pr-4 text-break">
              <a href="mailto:<?= $_settings->info('email') ?>"><?= $_settings->info('email') ?></a>
            </dd>
            <dt class="text-muted"><i class="fas fa-phone"></i> Contact #</dt>
            <dd class="pr-4">
              <a href="tel:<?= $_settings->info('contact') ?>"><?= $_settings->info('contact') ?></a>
            </dd>
            <dt class="text-muted"><i class="fas fa-map-marked-alt"></i> Location</dt>
            <dd class="pr-4 text-break"><?= $_settings->info('address') ?></dd>
            <dt class="text-muted"><i class="fas fa-share-alt"></i> Follow Us</dt>
            <dd class="pr-4 social-icons">
              <a href="https://www.facebook.com/indiandrivingschoolpondicherry" target="_blank"><i class="fab fa-facebook"></i></a>
              <a href="https://twitter.com/indiandriving" target="_blank"><i class="fab fa-twitter"></i></a>
              <a href="https://www.instagram.com/indiandrivingschoolpondy" target="_blank"><i class="fab fa-instagram"></i></a>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once('inc/footer.php') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
