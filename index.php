<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Jamii Resource Centre</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #000;
      color: #fff;
      font-family: 'Poppins', sans-serif;
    }

    /* NAVIGATION */
    .navbar {
      background-color: #111;
      padding: 20px 40px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .nav-links a {
      color: #ccc;
      text-decoration: none;
      margin-left: 30px;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .nav-links a:hover {
      color: #ff00ff;
    }

    /* HERO SECTION */
    .hero {
      text-align: center;
      padding: 80px 20px 60px;
      background-color: #612c8db9;
    }

    .hero h1 {
      font-size: 6rem;
      color: #f5ebf5ff;
      margin-bottom: 20px;
    }

    .hero p {
      font-size: 2rem;
      color: #ccc;
      max-width: 700px;
      margin: auto;
      margin-bottom: 40px;
    }

    /* CTA FORM */
    .cta-form {
      text-align: center;
      margin-bottom: 40px;
    }

    .cta-form input[type="email"] {
      padding: 12px;
      width: 300px;
      border-radius: 6px;
      border: none;
      margin-right: 10px;
    }

    .cta-form button {
      padding: 12px 25px;
      background: linear-gradient(to right, #8a2be2, #ff007f);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }

    .cta-form button:hover {
      background: linear-gradient(to right, #ff4da6, #a64bf0);
    }

    .cta-confirm {
      text-align: center;
      font-size: 0.9rem;
      color: #aaa;
      max-width: 600px;
      margin: auto;
    }

    .cta-confirm a {
      color: #ff00ff;
      text-decoration: underline;
    }

    /* ABOUT SECTION */
    .about {
  background-image: url('images/logo.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-color: #1a1a1a; 
  padding: 100px 40px;
  text-align: center;
  position: relative;
  color: #ffffffff;
}


    .about h2 {
      color: #ff00ff;
      margin-bottom: 40px;
      font-size: 4rem;
    }

    .about p {
      max-width: 800px;
      margin: auto;
      line-height: 1.6;
      color: #ddd;
    }

    /* CONTACTS */
    footer {
      background-color: #270824ff;
      padding: 20px 10px;
      text-align: center;
      color: #ffffffff;
      border-top: 1px solid #8d1b84ff;
    }

    footer h3 {
      color: #ff00ff;
      margin-bottom: 20px;
      font-size: 2.5rem;
    }

    footer p {
      margin: 5px 0;
    }

    @media (max-width: 768px) {
      .cta-form input[type="email"] {
        width: 100%;
        margin-bottom: 10px;
      }

      .cta-form button {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <!-- NAVIGATION -->
  <div class="navbar">
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="about.php">About Us</a>
      <a href="login.php">Login</a>
      <a href="register.php">Sign Up</a>
      <a href="#contact">Contact</a>
    </div>
  </div>

  <!-- HERO SECTION -->
  <section class="hero">
    <h1>Jamii Resource Centre</h1>
    <p>Empowering communities through access to essential services, education, and digital tools. Welcome to a space where growth, support, and opportunity meet.</p>

    <!-- ABOUT JAMII -->
  <section class="about">
    <h2>About Jamii</h2>
    <p>
      Jamii Resource Centre is a community-driven initiative offering services like case management, SACCO support, workshops, and digital literacy. We uplift individuals by providing access to resources that foster personal and professional development. Whether you're seeking support, training, or a safe space to grow, Jamii is here for you.
    </p>
  </section> <br>

  <!-- CONTACTS -->
  <footer id="contact">
    <h3>Contact Us</h3>
    <p>Email: jamiiresourcecenter@gmail.com</p>
    <p>Phone: +254 704 809 739</p>
    <p>Location: Nairobi, Kenya</p>
  </footer> <br>

<div class="cta-form">
      <input type="email" placeholder="Enter your email">
      <button>Join Jamii</button>
      <p>Stay updated with our latest news and events!</p>
    </div>

    <div class="cta-confirm">
      <label>
        <input type="checkbox"> I confirm that I have read and agree to Jamii’s
        <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
      </label>
    </div>
  </section>
</body>
</html>
