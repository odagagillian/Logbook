<?php
include('connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  $_SESSION['login_time'] = time(); // Start session timer
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['selected_service'] = $_POST['service'];
    $user_id = $_SESSION['user_id'];
    $service_name = $_POST['service'];
    $time_spent = $_POST['time_spent']; // e.g., "12m 30s"

    $stmt = $conn->prepare("INSERT INTO logs (user_id, service_name, time_spent, log_date)
                        VALUES (?, ?, ?, NOW())");

    if ($stmt) {

        $stmt->bind_param("sss", $user_id, $service_name, $time_spent);
        if (mysqli_stmt_execute($stmt)) {

            echo "<script>
                    alert('Session logged successfully! Time spent: {$time_spent}');
                    window.location.href = 'dashboard.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Error saving log.');</script>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <style>
    
/* Full-viewport centering */
html, body {
  height: 100%;
}

body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background-color: #2c0040; /* dark purple */
  color: white;

  /* Center everything */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Centered content container */
.container {
  width: 100%;
  max-width: 1100px;
  padding: 40px 24px;
  box-sizing: border-box;
}

/* Optional: constrain the inner panel and center its text header */
.right-panel {
  margin: 0 auto;
  max-width: 900px;
}

/* Headings spacing */
.right-panel h2 {
  font-size: 2.2em;
  text-align: center;
  margin: 0 0 8px;
}

.right-panel h3 {
  font-size: 1.2em;
  text-align: center;
  margin: 0 0 24px;
}

/* 2x2 grid that fits and doesn’t get cut off */
.service-cards {
  display: grid;
  grid-template-columns: repeat(2, minmax(280px, 1fr));
  gap: 24px;
  /* Ensure cards flow and aren’t clipped by parent */
  overflow: visible;
}

/* Card styles */
.card {
  background-color: white;
  color: black;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  cursor: pointer;
  transition: transform 0.2s ease;
}

.card:hover {
  transform: scale(1.03);
}

.card.selected {
  background-color: #ffb6c1; /* soft pink */
  color: white;
}

.card h4 {
  margin-top: 0;
  font-size: 1.3em;
}

.card p {
  font-size: 0.98em;
  color: #555;
}

/* Timer and submit button */
.timer-display {
  font-size: 1.1em;
  font-weight: 600;
  margin: 16px 0;
  color: #e6e6e6;
  text-align: center;
}

button[type="submit"] {
  display: block;
  margin: 8px auto 0;
  background-color: #ffb6c1;
  color: white;
  border: none;
  padding: 12px 24px;
  font-size: 1em;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

button[type="submit"]:hover {
  background-color: #e89bb0;
}

/* Avoid any external layout cutting (if another stylesheet sets these) */
.right-panel, .container, form {
  overflow: visible !important;
  height: auto !important;
}

  </style>
</head>
<body>
  <div class="container">
     <div class="right-panel">
    <h2>Save Log Entry</h2>

    <form action="log_activity.php" method="POST">
  <h3>Select a Service:</h3>
  <div class="service-cards">
    <div class="card" data-service="Case Management and Advocacy">
      <h4>Case Management & Advocacy</h4>
      <p>Support and representation for clients navigating complex social systems.</p>
    </div>
    <div class="card" data-service="Counselling">
      <h4>Counselling</h4>
      <p>Professional guidance for emotional, mental, and behavioral challenges.</p>
    </div>
    <div class="card" data-service="Workshops and Classes">
      <h4>Workshops & Classes</h4>
      <p>Skill-building sessions and educational programs for personal development.</p>
    </div>
    <div class="card" data-service="Jamii Sacco">
      <h4>Jamii Sacco</h4>
      <p>Community-based savings and credit services for financial empowerment.</p>
    </div>
  </div>

  <input type="hidden" id="service" name="service" required>
  <div class="timer-display" id="timer">Time Spent: 0m 0s</div>
  <input type="hidden" id="time_spent" name="time_spent">
  <button type="submit" onclick="stopTimer()">Save</button>
</form>

  </div>

  <script>
  const cards = document.querySelectorAll('.card');
  const serviceInput = document.getElementById('service');

  cards.forEach(card => {
    card.addEventListener('click', () => {
      cards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      serviceInput.value = card.getAttribute('data-service');
    });
  });
</script>

</body>
</html>
