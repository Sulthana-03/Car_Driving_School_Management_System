<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
<head>
  <meta charset="UTF-8">
  <title>Indian Driving School</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
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
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
    }
    
    #header {
      height: 90vh;
      width: 100%;
      position: relative;
      background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4));
    }
    
    #header:before {
      content: "";
      position: absolute;
      height: 100%;
      width: 100%;
      background-image: url(<?= validate_image($_settings->info("cover")) ?>);
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      z-index: -1;
    }
    
    #header > div {
      position: absolute;
      height: 100%;
      width: 100%;
      z-index: 2;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
    }
    
    .site-title {
      font-size: 3.5rem;
      font-weight: 800;
      color: white;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
      margin-bottom: 1.5rem;
      line-height: 1.2;
    }
    
    #enrollment {
      background-color: var(--accent-color);
      border: none;
      padding: 12px 30px;
      font-size: 1.1rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    #enrollment:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
      background-color: #e67e22;
    }
    
    .section-title {
      font-size: 2.8rem;
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
    
    .feature-icon {
      font-size: 3.5rem;
      margin-bottom: 1.5rem;
      color: var(--primary-color);
      background: linear-gradient(135deg, #e0f2fe, #bfdbfe);
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin-left: auto;
      margin-right: auto;
    }
    
    .about-section {
      background: linear-gradient(135deg, #f0f9ff, #ffffff);
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }
    
    .about-img {
      max-height: 400px;
      width: 100%;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    
    .about-img:hover {
      transform: scale(1.02);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border: none;
      padding: 12px 30px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
    }
    
    .card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      background: white;
    }
    
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .testimonial-card {
      border-left: 4px solid var(--accent-color);
    }
    
    .testimonial-card .text-warning {
      font-size: 1.2rem;
    }
    
    .accordion-button {
      font-weight: 600;
      padding: 1.2rem 1.25rem;
    }
    
    .accordion-button:not(.collapsed) {
      background-color: rgba(37, 99, 235, 0.1);
      color: var(--primary-color);
    }
    
    .bg-light {
      background-color: #f8fafc !important;
    }
    
    .bg-gradient {
      background: linear-gradient(135deg, #2563eb, #1e40af);
      color: white;
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
    
    footer {
      background-color: var(--dark-color);
      color: white;
      padding: 3rem 0;
    }
    
    @media (max-width: 768px) {
      .site-title {
        font-size: 2.5rem;
      }
      
      #header {
        height: 70vh;
      }
      
      .section-title {
        font-size: 2.2rem;
      }
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
  
  <div style="min-height: 567.854px;">
    <?php if($page == "home" || $page == "about_us"): ?>
      <div id="header" class="shadow mb-4">
        <div class="d-flex justify-content-center h-100 w-100 align-items-center flex-column px-3 text-white text-center">
       <br><br>
          <h1 class="w-100 text-center site-title"><?php echo $_settings->info('name') ?></h1>
             <h2 class="mb-3" style="font-size: 1.8rem; letter-spacing: 1px;">"Drive Safe, Drive Smart"</h2>
          <p class="lead mb-4" style="max-width: 700px; font-size: 1.2rem;">Professional driving lessons with certified instructors and modern vehicles</p>
          <a href="user/usersignup.php" class="btn btn-lg btn-light rounded-pill px-4" id="enrollment"><b>Enroll Now</b></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- About Section -->
    <section class="container py-5 my-5">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="uploads/aboutimage.jpeg" class="img-fluid rounded shadow about-img" alt="About Indian Driving School">
        </div>
        <div class="col-lg-6">
          <span class="text-primary fw-bold mb-2 d-block">ABOUT US</span>
          <?php include("about_us.html") ?>
          <a href="user/usersignup.php" class="btn btn-primary mt-3 px-4 py-3">Join Us Today <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-5 bg-light">
      <div class="container text-center py-5">
        <span class="text-primary fw-bold mb-2 d-block">WHY CHOOSE US</span>
        <h2 class="section-title mb-5">Our Key Features</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4">
              <div class="card-body text-center">
                <div class="feature-icon mb-4 text-secondary">
                  <i class="fas fa-user-shield"></i>
                </div>
                <h5 class="fw-bold mb-3">Certified Instructors</h5>
                <p class="text-muted">Learn from experienced instructors with excellent track records and patient teaching methods.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4">
              <div class="card-body text-center">
                <div class="feature-icon mb-4 text-success">
                  <i class="fas fa-car"></i>
                </div>
                <h5 class="fw-bold mb-3">Modern Fleet</h5>
                <p class="text-muted">Our fleet includes the latest models with dual controls for maximum safety during training.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm p-4">
              <div class="card-body text-center">
                <div class="feature-icon mb-4 text-warning">
                  <i class="fas fa-calendar-check"></i>
                </div>
                <h5 class="fw-bold mb-3">Flexible Scheduling</h5>
                <p class="text-muted">Book lessons at your convenience with our easy-to-use online scheduling system.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5">
      <div class="container py-5">
        <div class="text-center mb-5">
          <span class="text-primary fw-bold mb-2 d-block">TESTIMONIALS</span>
          <h2 class="section-title mb-3">What Our Students Say</h2>
          <p class="lead text-muted">Hear from our successful graduates</p>
        </div>
        <div class="row g-4 justify-content-center">
          <?php 
            $testimonials = [
              ['name' => 'Rahul Sharma', 'rating' => 5, 'text' => 'The instructors are very patient and professional. I passed my driving test on the first attempt thanks to their excellent training!'],
              ['name' => 'Priya Patel', 'rating' => 5, 'text' => 'I was extremely nervous about learning to drive, but my instructor made me feel comfortable right away. Highly recommend this school!'],
              ['name' => 'Amit Singh', 'rating' => 4.5, 'text' => 'The online booking system is very convenient, and the instructors are knowledgeable. Great experience overall.']
            ];
            foreach ($testimonials as $t) {
              echo '<div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 testimonial-card">
                  <div class="card-body text-center">
                    <div class="mb-4">
                      <img src="https://ui-avatars.com/api/?name='.urlencode($t['name']).'&background=random&size=80&rounded=true" alt="'.htmlspecialchars($t['name']).'" class="rounded-circle mb-3">
                      <h5 class="fw-bold mb-2">'.$t['name'].'</h5>
                      <div class="text-warning mb-3">';
              $fullStars = floor($t['rating']);
              $halfStar = $t['rating'] - $fullStars >= 0.5;
              for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i>';
 if ($halfStar) echo '<i class="fas fa-star-half-alt"></i>';

              echo '</div>
                    </div>
                    <p class="text-muted fst-italic">"'.$t['text'].'"</p>
                  </div>
                </div>
              </div>';
            }
          ?>
        </div>
      </div>
    </section>
   

    <!-- FAQs Section -->
    <section class="container py-5 my-5">
      <div class="text-center mb-5">
        <span class="text-primary fw-bold mb-2 d-block">HAVE QUESTIONS?</span>
        <h2 class="section-title mb-3">Frequently Asked Questions</h2>
        <p class="lead text-muted">Find answers to common questions about our driving school</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item mb-3 border-0 shadow-sm">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                  <i class="fas fa-question-circle me-3 text-primary"></i> How long does it take to learn driving?
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  The duration varies depending on individual learning pace, but most students complete our program in 15-20 sessions. We offer flexible scheduling to accommodate your availability.
                </div>
              </div>
            </div>
            <div class="accordion-item mb-3 border-0 shadow-sm">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                  <i class="fas fa-question-circle me-3 text-primary"></i> Do I need any experience before enrolling?
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  No prior experience is necessary! We welcome complete beginners and tailor our instruction to each student's level. Our instructors are specially trained to work with first-time drivers.
                </div>
              </div>
            </div>
            <div class="accordion-item mb-3 border-0 shadow-sm">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                  <i class="fas fa-question-circle me-3 text-primary"></i> What documents do I need to bring?
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  For enrollment, please bring:
                  <ul class="mt-2">
                    <li>Government-issued ID proof (Aadhaar, Passport, etc.)</li>
                    <li>2 passport-sized photographs</li>
                    <li>Medical certificate (if applicable)</li>
                    <li>Any existing learner's permit or license</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require_once('inc/footer.php') ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple animation for counters
    document.addEventListener('DOMContentLoaded', function() {
      const counters = document.querySelectorAll('.counter h2');
      const speed = 200;
      
      counters.forEach(counter => {
        const target = +counter.innerText.replace(/\D/g, '');
        const count = +counter.innerText.replace(/\D/g, '');
        const increment = target / speed;
        let current = 0;
        
        const updateCount = () => {
          current += increment;
          if (current < count) {
            counter.innerText = Math.ceil(current) + (counter.innerText.includes('%') ? '%' : '+');
            setTimeout(updateCount, 1);
          } else {
            counter.innerText = count + (counter.innerText.includes('%') ? '%' : '+');
          }
        };
        
        updateCount();
      });
    });
  </script>
</body>
</html>