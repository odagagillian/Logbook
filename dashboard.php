<?php
include('connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the latest service used by the user
$query = "SELECT service_name, time_spent, log_date FROM logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $service_name, $time_spent, $log_date);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Define service descriptions
$service_info = [
    "Case Management and Advocacy" => [
        "description" => "Support with navigating social services, legal aid, and personalized case follow-up.",
        "how_it_works" => "Meet with a case manager, set goals, and receive tailored support."
    ],
    "Counselling" => [
        "description" => "Confidential mental health support and emotional guidance.",
        "how_it_works" => "Book a session, meet with a counselor, and receive follow-up resources."
    ],
    "Workshops and Classes" => [
        "description" => "Skill-building sessions on topics like parenting, finance, and employment.",
        "how_it_works" => "Attend scheduled workshops and track your progress."
    ],
    "Jamii Sacco" => [
        "description" => "Community savings and credit program for financial empowerment.",
        "how_it_works" => "Join the Sacco, contribute regularly, and access loans or savings tools."
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Service Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container { max-width: 1000px; margin: auto; padding: 20px; }
    .card { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    h2 { color: #ffffffff; }
    .label { font-weight: bold; color: #555; }
  </style>
</head>
<body>
  <div class="container">
    <!-- Left abstract panel -->
    <div class="left-panel">
      <h1 class="panel-title">Jamii Resource Centre</h1>
      <p class="tagline">Empowering communities through service and support.</p>
    </div>

    <!-- Right dashboard panel -->
    <div class="right-panel">
      <h2>Service Dashboard</h2> <br>
     <div id="live-timer">Session Time: 0m 0s</div>
 

      <div class="card">
        <p class="label">Selected Service:</p>
        <p><?= htmlspecialchars($service_name) ?></p>

        <p class="label">Description:</p>
        <p><?= $service_info[$service_name]['description'] ?? 'No description available.' ?></p>

        <p class="label">How It Works:</p>
        <p><?= $service_info[$service_name]['how_it_works'] ?? 'No instructions available.' ?></p>
      </div>

      
      <div class="card">
        <p class="label">Last Session:</p>
        <p>Date: <?= date("F j, Y, g:i a", strtotime($log_date)) ?></p>
      </div>

      <a href="log_activity.php"><button type="button">Start New Session</button></a>
      <a href="logout.php">
  <button type="button">Logout</button>
</a>

    </div>
  </div>
  <script>
const loginTime = <?php echo $_SESSION['login_time'] * 1000; ?>; // convert to ms

function updateTimer() {
  const now = Date.now();
  const elapsed = now - loginTime;
  const minutes = Math.floor(elapsed / 60000);
  const seconds = Math.floor((elapsed % 60000) / 1000);
  document.getElementById('live-timer').textContent = `Session Time: ${minutes}m ${seconds}s`;
}

setInterval(updateTimer, 1000);
updateTimer(); // run immediately
</script>

</body>

</html>
