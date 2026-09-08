<?php include __DIR__ . '/../app/views/includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DormSync – Smarter Hostels, Better Student Lives</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/landing.css">
</head>
<body>

<!-- HERO SECTION -->
<section class="hero" id="hero" style="background-image: url('/assets/hero-image.png');">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <p class="hero-eyebrow">SMART HOSTEL MANAGEMENT SYSTEM</p>
    <h1>Smarter Hostels, <br><span class="emphasize">Better Student Lives</span></h1>
    <p class="hero-description">
      DormSync is a modern hostel management system that simplifies room allocation, fee management, complaints, visitors, and hostel administration — all in one place.
    </p>
  </div>
  <div class="scroll-indicator">
    <span>SCROLL DOWN</span>
    <div class="scroll-line"></div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="features" id="features">
  <div class="container">
    <div class="section-header">
      <span class="eyebrow">OUR FEATURES</span>
      <h2>Everything You Need for <br>Efficient Hostel Management</h2>
      <p>From room allocation to complaint tracking, DormSync makes hostel administration simple, secure and hassle-free.</p>
    </div>
    <div class="features-grid">
      <div class="feature-item"><i class="fas fa-users"></i><h3>Student Management</h3><p>Manage student records, profiles and hostel details.</p></div>
      <div class="feature-item"><i class="fas fa-door-open"></i><h3>Room Management</h3><p>Track room availability, allocation and occupancy.</p></div>
      <div class="feature-item"><i class="fas fa-coins"></i><h3>Fee Management</h3><p>Record payments, view fee status and generate reports.</p></div>
      <div class="feature-item"><i class="fas fa-exclamation-triangle"></i><h3>Complaint Management</h3><p>Submit, track and resolve maintenance requests.</p></div>
      <div class="feature-item"><i class="fas fa-user-clock"></i><h3>Visitor Management</h3><p>Register visitors and maintain visitor records.</p></div>
      <div class="feature-item"><i class="fas fa-chart-line"></i><h3>Reports & Analytics</h3><p>Get detailed hostel reports and dashboards.</p></div>
      <div class="feature-item"><i class="fas fa-shield-alt"></i><h3>Secure & Reliable</h3><p>Keep hostel information protected with role-based access.</p></div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works" id="how-it-works">
  <div class="container">
    <div class="section-header">
      <span class="steps-eyebrow">HOW IT WORKS</span>
    </div>
    <div class="steps" data-journey>
      <svg class="journey-line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
        <path class="journey-line-track" d="M 40 50 C 56 12, 78 88, 99 50" pathLength="1"></path>
      </svg>
      <div class="steps-intro">
        <h2>Simple Steps to a<br>Smarter Hostel</h2>
        <p>Get started with DormSync in just a few easy steps and experience a seamless hostel management process.</p>
        <a class="steps-button" href="#features">Learn More <span aria-hidden="true">→</span></a>
      </div>
      <div class="step" data-delay="0"><span class="step-number">01</span><span class="step-icon"><i class="fas fa-user-plus" aria-hidden="true"></i></span><h3>Sign Up</h3><p>Create your account and choose your role.</p></div>
      <div class="step-arrow" aria-hidden="true">→</div>
      <div class="step" data-delay="150"><span class="step-number">02</span><span class="step-icon"><i class="fas fa-cog" aria-hidden="true"></i></span><h3>Manage</h3><p>Handle rooms, fees, complaints and more through your dashboard.</p></div>
      <div class="step-arrow" aria-hidden="true">→</div>
      <div class="step" data-delay="300"><span class="step-number">03</span><span class="step-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></span><h3>Track</h3><p>Stay updated with real-time information and notifications.</p></div>
      <div class="step-arrow" aria-hidden="true">→</div>
      <div class="step" data-delay="450"><span class="step-number">04</span><span class="step-icon"><i class="far fa-smile" aria-hidden="true"></i></span><h3>Enjoy</h3><p>A safer, smarter and more organized hostel experience.</p></div>
    </div>
  </div>
</section>



<!-- TESTIMONIALS -->
<section class="testimonials" id="testimonials" aria-labelledby="testimonials-title">
  <div class="container testimonial-layout">
    <div class="testimonial-intro">
      <span class="testimonial-kicker">TESTIMONIALS</span>
      <h2 id="testimonials-title">Built for the people who keep hostels moving.</h2>
      <p>Real feedback from students, wardens, and administrators using DormSync every day.</p>
    </div>
    <div class="testimonial-carousel" data-testimonials>
      <div class="testimonial-card" aria-live="polite">
        <div class="testimonial-stars" aria-label="5 out of 5 stars">★★★★★</div>
        <blockquote id="testimonialQuote">“DormSync makes it much easier to keep track of my room, fees and complaints.”</blockquote>
        <div class="testimonial-author">
          <img id="testimonialAvatar" src="/assets/review1.jpg" alt="Portrait of Anisha Rai">
          <div><strong id="testimonialName">Anisha Rai</strong><span id="testimonialRole">Student</span></div>
        </div>
      </div>
      <div class="testimonial-dots" aria-label="Choose a testimonial">
        <button type="button" aria-label="Show testimonial 1" aria-current="true"></button>
        <button type="button" aria-label="Show testimonial 2" aria-current="false"></button>
        <button type="button" aria-label="Show testimonial 3" aria-current="false"></button>
        <button type="button" aria-label="Show testimonial 4" aria-current="false"></button>
        <button type="button" aria-label="Show testimonial 5" aria-current="false"></button>
      </div>
      <div class="testimonial-list" aria-label="All testimonials">
        <article><strong>Anisha Rai</strong><span>Student</span><p>“DormSync makes it much easier to keep track of my room, fees and complaints.”</p></article>
        <article><strong>Rohan Gurung</strong><span>Warden</span><p>“Managing students and complaints is much more organised with DormSync.”</p></article>
        <article><strong>Sita Thapa</strong><span>Student</span><p>“I always know what is happening with my room and visitor requests.”</p></article>
        <article><strong>Hari Karki</strong><span>Warden</span><p>“The hostel overview helps me act on issues before they become bigger problems.”</p></article>
        <article><strong>Priya Sharma</strong><span>Administrator</span><p>“The dashboard gives us a clear overview of hostel operations.”</p></article>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

<script src="js/main.js"></script>
</body>
</html>