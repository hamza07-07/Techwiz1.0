  <?php
  session_start();

  // Redirect logged-in users
  if (isset($_SESSION['role'])) {
      if ($_SESSION['role'] == "owner") {
          header("Location: pages/owner_dashboard.php");
          exit();
      } elseif ($_SESSION['role'] == "vet") {
          header("Location: pages/vet_dashboard.php");
          exit();
      } elseif ($_SESSION['role'] == "shelter") {
          header("Location: pages/shelter_dashboard.php");
          exit();
      }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FurShield - Premium Landing Page</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
      html {
        scroll-behavior: smooth;
      }
      body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
        overflow-x: hidden;
      }

      /* Navbar */
      .navbar {
        padding: 1rem 2rem;
        background: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: background 0.3s;
      }
      .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
      }
      .navbar-nav .nav-link {
        position: relative;
        margin-left: 1rem;
        transition: 0.3s;
      }
      .navbar-nav .nav-link::after {
        content: "";
        position: absolute;
        width: 0;
        height: 2px;
        left: 50%;
        bottom: -2px;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        transition: 0.3s;
        transform: translateX(-50%);
      }
      .navbar-nav .nav-link:hover::after {
        width: 100%;
      }

      /* Floating paw shapes */
      .floating-shape {
        position: absolute;
        width: 80px;
        height: 80px;
        background: rgba(255,77,109,0.2);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
        z-index: 0;
      }
      @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(45deg); }
      }

      /* Hero Section */
      .hero {
        position: relative;
        background: linear-gradient(to right, #0d6efd, #6a11cb);
        color: #fff;
        padding: 120px 0;
        overflow: hidden;
      }
      .hero h1 {
        font-size: 3rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 20px;
        animation: fadeInUp 1s ease forwards;
      }
      .hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        animation: fadeInUp 1s ease 0.3s forwards;
        opacity: 0;
      }
      .btn-cta {
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        border: none;
        padding: 14px 35px;
        font-size: 1.2rem;
        border-radius: 50px;
        color: #fff;
        font-weight: 600;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 1s ease 0.6s forwards;
      }
      .btn-cta:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 25px rgba(255,77,109,0.4);
      }
      .hero img {
        max-width: 100%;
        border-radius: 20px;
        animation: fadeInUp 1s ease 0.8s forwards;
        opacity: 0;
        transform: translateY(30px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        position: relative;
        z-index: 2;
      }
      @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
      }

      /* Section animation */
      section {
        opacity: 0;
        transform: translateY(50px);
        transition: all 0.8s ease-out;
      }
      section.in-view {
        opacity: 1;
        transform: translateY(0);
      }

      /* Services Section */
      .services {
        padding: 100px 20px;
        background: #121212;
      }
      .services h2 {
        text-align: center;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 60px;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .card-custom {
        background: rgba(255,255,255,0.05);
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: transform 0.4s, box-shadow 0.4s;
        backdrop-filter: blur(10px);
        color: #fff;
        cursor: pointer;
        height: 100%;
      }
      .card-custom:hover {
        transform: translateY(-15px) scale(1.05);
        box-shadow: 0 0 20px #ff4d6d, 0 0 40px #ffcc00;
      }
      .card-custom i {
        font-size: 3rem;
        margin-bottom: 20px;
        color: #ff4d6d;
      }

      /* Why Choose Us Section */
      .why-choose {
        position: relative;
        padding: 100px 20px;
        text-align: center;
        background: #000000ff;
        color: #fff;
        overflow: hidden;
      }
      .why-choose h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 60px;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .why-choose .feature {
        max-width: 300px;
        margin: 20px auto;
        background: rgba(255,255,255,0.05);
        padding: 30px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        color: #fff;
        transition: transform 0.4s, box-shadow 0.4s;
      }
      .why-choose .feature i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #ff4d6d;
      }
      .why-choose .feature:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 0 20px #ff4d6d, 0 0 40px #ffcc00;
      }

      /* Counter Section */
      .counter-section {
        background: linear-gradient(90deg,#0d6efd,#6a11cb);
        color: #fff;
        padding: 60px 20px;
        text-align: center;
        overflow: hidden;
      }
      .counter-section h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 5px;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .counter-section p {
        font-size: 1.2rem;
        margin: 0;
        color: #fff;
      }

      /* About Us Section */
      .about-us {
        background: url('assets/aboutus-cat-and-dog-with-toy.jpeg') no-repeat center center/cover;
        position: relative;
        color: #fff;
        text-align: center;
        padding: 100px 20px;
      }
      .about-us .overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 1;
      }
      .about-us .container {
        position: relative;
        z-index: 2;
      }
      .about-us h2 {
        font-size: 2.5rem;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .about-us p {
        font-size: 1.2rem;
        line-height: 1.8;
      }

      /* Separator */
      .separator svg {
        display: block;
        width: 100%;
        height: 50px;
      }

      /* Contact Section */
      .contact-section {
        background: url('assets/contact-bg-pic.jpeg') no-repeat center center/cover;
        position: relative;
        overflow: hidden;
        color: #fff;
        padding: 100px 20px;
        text-align: center;
      }
      .contact-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(13, 97, 239, 0.7);
        z-index: 1;
        mix-blend-mode: multiply;
      }
      .contact-section .container {
        position: relative;
        z-index: 2;
      }
      .contact-section h2 {
        font-size: 2.5rem;
        background: linear-gradient(90deg,#ff4d6d,#ffcc00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }
      .contact-section p {
        font-size: 1.2rem;
        margin-bottom: 40px;
      }
      .contact-form .form-control {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        border-radius: 10px;
        padding: 15px;
        transition: all 0.3s ease;
      }
      .contact-form .form-control::placeholder {
        color: rgba(255,255,255,0.7);
      }
      .contact-form .form-control:focus {
        outline: none;
        box-shadow: 0 0 15px #ff4d6d;
        backdrop-filter: blur(15px);
      }

      footer {
        background: #1c1c1c;
        color: #fff;
        padding: 40px 0;
        text-align: center;
      }

      @media (max-width:768px){.hero h1{font-size:2.5rem;}.hero p{font-size:1rem;}}
      @media (max-width:576px){.hero h1{font-size:2rem;}.hero p{font-size:0.95rem;}.btn-cta{padding:12px 28px;font-size:1rem;}}
    </style>
  </head>
  <body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container">
        <a class="navbar-brand fw-bold" href="#">🐾 FurShield</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="./index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="./login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="./register.php">Register</a></li>
            <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero d-flex align-items-center" style="min-height:100vh; position: relative;">
      <div class="floating-shape" style="top: 5%; left: 5%; width: 60px; height: 60px;"></div>
      <div class="floating-shape" style="top: 15%; right: 10%; width: 80px; height: 80px;"></div>
      <div class="floating-shape" style="top: 25%; left: 20%; width: 100px; height: 100px;"></div>
      <div class="floating-shape" style="top: 35%; right: 25%; width: 50px; height: 50px;"></div>
      <div class="floating-shape" style="bottom: 20%; left: 10%; width: 90px; height: 90px;"></div>
      <div class="floating-shape" style="bottom: 10%; right: 15%; width: 120px; height: 120px;"></div>
      <div class="floating-shape" style="bottom: 25%; left: 25%; width: 70px; height: 70px;"></div>
      <div class="floating-shape" style="top: 50%; right: 40%; width: 60px; height: 60px;"></div>
      <div class="floating-shape" style="top: 60%; left: 35%; width: 80px; height: 80px;"></div>
      <div class="floating-shape" style="bottom: 5%; left: 50%; width: 100px; height: 100px;"></div>

      <div class="container">
        <div class="row align-items-center text-md-start text-center">
          <div class="col-md-6 mb-4 mb-md-0">
            <h1>Every Paw Deserves a Shield of Love</h1>
            <p>Manage pet profiles, book vet appointments, explore shelters, and shop essentials all in one place.</p>
            <button class="btn btn-cta">Get Started</button>
          </div>
          <div class="col-md-6 text-center">
            <img src="https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=600&q=80" alt="Cute dog">
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="services">
      <div class="container">
        <h2>Our Services</h2>
        <div class="row g-4">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="card-custom">
              <i class="fa-solid fa-dog"></i>
              <h5>Pet Owners</h5>
              <p>Create pet profiles, track health, and receive reminders for vaccines and grooming.</p>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="card-custom">
              <i class="fa-solid fa-user-doctor"></i>
              <h5>Veterinarians</h5>
              <p>Manage appointments, review pet histories, and log treatments seamlessly.</p>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="card-custom">
              <i class="fa-solid fa-house"></i>
              <h5>Shelters</h5>
              <p>Showcase adoptable pets and connect with loving families.</p>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="card-custom">
              <i class="fa-solid fa-cart-shopping"></i>
              <h5>Shop</h5>
              <p>Find food, grooming items, toys, and healthcare products for your pets.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose">
      <div class="container">
        <h2>Why Choose Us?</h2>
        <div class="row justify-content-center">
          <div class="col-md-3 feature">
            <i class="fa-solid fa-heart"></i>
            <h5 class="mt-3">Trusted Care</h5>
            <p>Reliable and compassionate care for your pets at every step.</p>
          </div>
          <div class="col-md-3 feature">
            <i class="fa-solid fa-user-check"></i>
            <h5 class="mt-3">Verified Experts</h5>
            <p>Work with certified veterinarians and trusted shelters.</p>
          </div>
          <div class="col-md-3 feature">
            <i class="fa-solid fa-bolt"></i>
            <h5 class="mt-3">Fast & Easy</h5>
            <p>Quick bookings, easy access, and intuitive platform for everyone.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Counter Section -->
    <section class="counter-section">
      <div class="container">
        <div class="row text-center">
          <div class="col-md-3 col-6 mb-0">
            <h2 class="counter" data-target="50">0</h2>
            <p>Customers</p>
          </div>
          <div class="col-md-3 col-6 mb-0">
            <h2 class="counter" data-target="8000">0</h2>
            <p>Professionals</p>
          </div>
          <div class="col-md-3 col-6 mb-0">
            <h2 class="counter" data-target="30">0</h2>
            <p>Products</p>
          </div>
          <div class="col-md-3 col-6 mb-0">
            <h2 class="counter" data-target="60">0</h2>
            <p>Pets Hosted</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="about-us">
      <div class="overlay"></div>
      <div class="container position-relative">
        <h2 class="fw-bold mb-4">About Us</h2>
        <p class="lead mx-auto" style="max-width: 800px;">
          FurShield is a comprehensive pet care platform built to simplify the lives of pet owners, 
          veterinarians, and animal shelters. Our mission is to ensure every paw and wing 
          receives the love, health, and safety it deserves. From managing medical records and appointments 
          to supporting adoptions and providing access to essential products — we bring it all together in one place.
        </p>
      </div>
    </section>

  <!-- Separator -->
  <div class="separator" style="height:80px; overflow:hidden;">
    <svg viewBox="0 0 500 50" preserveAspectRatio="none" style="height:100%; width:100%;">
      <defs>
        <linearGradient id="oceanGradient" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" style="stop-color:#0d6efd; stop-opacity:1" />
          <stop offset="100%" style="stop-color:#6a11cb; stop-opacity:1" />
        </linearGradient>
      </defs>
      <path d="M0,0 C150,100 350,0 500,100 L500,0 L0,0 Z" fill="url(#oceanGradient)"></path>
    </svg>
  </div>



    <!-- Contact Section -->
    <section id="contact" class="contact-section">
      <div class="contact-overlay"></div>
      <div class="floating-shape" style="top: 10%; left: 10%; width: 60px; height: 60px;"></div>
      <div class="floating-shape" style="top: 30%; right: 15%; width: 80px; height: 80px;"></div>
      <div class="floating-shape" style="bottom: 20%; left: 20%; width: 100px; height: 100px;"></div>
      <div class="container position-relative">
        <h2 class="fw-bold mb-4">Get in Touch</h2>
        <p class="lead mb-5" style="max-width: 700px; margin: auto;">
          Have questions or suggestions? We'd love to hear from you. Fill out the form below and our team will get back to you promptly.
        </p>
        <form class="contact-form mx-auto" style="max-width: 600px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.1); padding: 40px; border-radius: 20px;">
          <div class="mb-3">
            <input type="text" class="form-control" placeholder="Your Name" required>
          </div>
          <div class="mb-3">
            <input type="email" class="form-control" placeholder="Your Email" required>
          </div>
          <div class="mb-3">
            <input type="text" class="form-control" placeholder="Subject" required>
          </div>
          <div class="mb-3">
            <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" class="btn btn-cta w-100">Send Message</button>
        </form>
      </div>
    </section>

    <!-- Footer -->
    <footer>
      <div class="container text-center">
        <p>&copy; 2025 FurShield | Built with <i class="fa-solid fa-heart" style="color:#ff4d6d;"></i> for pets by FrameWorkForce
      </p>
      </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      // Counter Animation
      const counters = document.querySelectorAll('.counter');
      const speed = 200;
      function animateCounters() {
        counters.forEach(counter => {
          const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / speed;
            if(count < target){
              counter.innerText = Math.ceil(count + increment);
              setTimeout(updateCount, 20);
            } else {
              counter.innerText = target;
            }
          };
          updateCount();
        });
      }

      let countersStarted = false;
      window.addEventListener('scroll', () => {
        const section = document.querySelector('.counter-section');
        const sectionPos = section.getBoundingClientRect().top;
        const screenPos = window.innerHeight;
        if(sectionPos < screenPos && !countersStarted){
          animateCounters();
          countersStarted = true;
        }
      });

      // Scroll animation for sections
      const sections = document.querySelectorAll('section');
      const observerOptions = { threshold: 0.2 };
      const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if(entry.isIntersecting){
            entry.target.classList.add('in-view');
            observer.unobserve(entry.target);
          }
        });
      }, observerOptions);
      sections.forEach(section => observer.observe(section));
    </script>

  </body>
  </html>
