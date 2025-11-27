<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - Jamii Resource Centre</title>
  <style>
    body { margin:0; font-family:'Poppins',sans-serif; background:#000; color:#fff; }
    .navbar { background:#111; padding:20px 40px; display:flex; justify-content:flex-end; }
    .nav-links a { color:#ccc; text-decoration:none; margin-left:30px; font-weight:500; }
    .nav-links a:hover { color:#ff00ff; }
    .about { padding:80px 40px; text-align:center; background:#1a1a1a; }
    .about h2 { color:#ff00ff; font-size:3rem; margin-bottom:30px; }
    .about h3 { color:#a64bf0; font-size:1.8rem; margin-top:30px; }
    .about p { max-width:900px; margin:auto; line-height:1.6; color:#ddd; font-size:1.1rem; }
    .staff-section { background:#111; padding:60px 40px; text-align:center; }
    .staff-section h2 { color:#ff00ff; font-size:2.5rem; margin-bottom:20px; }
    .staff-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:30px; max-width:1000px; margin:auto; }
    .staff-card { background:#1a1a1a; padding:20px; border-radius:10px; box-shadow:0 3px 8px rgba(255,0,255,0.2); }
    .staff-card h4 { color:#a64bf0; margin-bottom:10px; }
    .staff-card p { color:#ccc; font-size:0.95rem; line-height:1.5; }
    footer { background:#270824; padding:30px 10px; text-align:center; color:#fff; border-top:1px solid #8d1b84; }
    footer h3 { color:#ff00ff; margin-bottom:20px; font-size:2rem; }
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

  <!-- ABOUT US CONTENT -->
  <section class="about">
    <h2>About Jamii Resource Centre</h2>
    <h3>Our Story</h3>
    <p>Established in 2018, Jamii Resource Centre was founded to bridge gaps in social support, digital literacy, and community empowerment. Over the years, we have grown into a safe and inclusive space where individuals of all ages can access guidance, training, and opportunities that enhance their wellbeing.</p>

    <h3>Our Services</h3>
    <p>We provide case management, SACCO empowerment programs, counselling, community workshops, and digital skills training. Each program is designed to uplift vulnerable groups, promote self‑reliance, and nurture long‑term personal growth.</p>

    <h3>Our Mission</h3>
    <p>We aim to strengthen community resilience by providing resources, education, and opportunities that empower individuals to build better futures for themselves and their families. Jamii Resource Centre stands as a symbol of hope, unity, and positive transformation within the community.</p>
  </section>

  <!-- STAFF SECTION -->
  <section class="staff-section">
    <h2>Meet Our Team</h2>
    <div class="staff-grid">
      <div class="staff-card">
        <h4>Social Workers</h4>
        <p>Our trained social workers provide case management and advocacy, helping clients navigate social services and access the support they need.</p>
      </div>
      <div class="staff-card">
        <h4>Community Mobilizers</h4>
        <p>They engage with local communities, organize workshops, and ensure that vulnerable groups are included in empowerment programs.</p>
      </div>
      <div class="staff-card">
        <h4>ICT Instructors</h4>
        <p>Our ICT trainers deliver digital literacy and skills programs, equipping individuals with tools to thrive in today’s tech‑driven world.</p>
      </div>
      <div class="staff-card">
        <h4>Financial Coaches</h4>
        <p>They lead SACCO and financial literacy programs, teaching savings, budgeting, and entrepreneurship to promote self‑reliance.</p>
      </div>
      <div class="staff-card">
        <h4>Youth Mentors</h4>
        <p>Our mentors inspire and guide young people, fostering creativity, leadership, and resilience for the next generation.</p>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <footer id="contact">
    <h3>Contact Us</h3>
    <p>Email: jamiiresourcecenter@gmail.com</p>
    <p>Phone: +254 704 809 739</p>
    <p>Location: Nairobi, Kenya</p>
  </footer>

</body>
</html>
