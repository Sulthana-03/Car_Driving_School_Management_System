<?php
require_once('./config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Location: enquiry.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Indian Driving School - Enquiry</title>
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
    
    body {
      font-family: 'Poppins', sans-serif;
      color: #334155;
      line-height: 1.6;
      background-color: #f8fafc;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
    }
    
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      position: relative;
      display: inline-block;
      color: var(--dark-color);
    }
    
    .section-title:after {
      content: "";
      position: absolute;
      width: 60px;
      height: 4px;
      background: var(--accent-color);
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 2px;
    }
    
    .subtitle {
      font-size: 1.1rem;
      color: #64748b;
      margin-bottom: 2rem;
      text-align: center;
    }
    
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      background: #fff;
      padding: 30px;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark-color);
    }
    
    .form-control {
      border-radius: 12px;
      padding: 12px 15px;
      border: 1px solid #ddd;
    }
    
    .btn-submit {
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      border: none;
      border-radius: 50px;
      font-weight: 600;
      padding: 12px;
      color: #fff;
      transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    /* Fixed navbar styling */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Main content container */
    .main-content {
      margin-top: 70px; /* Adjusted to match navbar height */
      padding: 20px 0;
      flex: 1;
    }
    
    /* Footer styling */
    footer {
      background-color: var(--dark-color);
      color: white;
      padding: 20px 0;
      margin-top: auto;
    }
    
    /* Wrapper adjustment */
    .wrapper {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
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
  <?php require_once('inc/header.php'); ?>
  <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
  <?php require_once('inc/topBarNav.php') ?>
  
  <?php if($_settings->chk_flashdata('success')): ?>
    <script>
      alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
    </script>
  <?php endif; ?>

  <div class="main-content">
    <div class="container">
      <h1 class="text-center section-title">Enquiry Form</h1>
      <p class="text-center subtitle">Have a Question? Send us your enquiry and we'll get back to you as soon as possible.</p>
      
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card">
            <form action="mail.php" method="POST">
              <div class="form-group mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
              </div>
              <div class="form-group mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
              </div>
              <div class="form-group mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" maxlength="10" required>
              </div>
              <div class="form-group mb-3">
                <label for="message" class="form-label">Your Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message here" required></textarea>
              </div>
              <button type="submit" class="btn btn-submit w-100">
                <i class="fas fa-paper-plane me-2"></i>Submit Enquiry
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php require_once('inc/footer.php'); ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>