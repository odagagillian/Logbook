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

// Handle booking form submission
if (isset($_POST['book_session'])) {
    $_SESSION['pending_appointment'] = [
        'service_name' => $service_name,
        'appointment_date' => $_POST['appointment_date']
    ];
    $insert = "INSERT INTO appointments (user_id, service_name, appointment_date) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, 'sss', $user_id, $service_name, $appointment_date);
    mysqli_stmt_execute($stmt);
    $appointment_id = mysqli_insert_id($conn);
    $_SESSION['appointment_id'] = $appointment_id;
    mysqli_stmt_close($stmt);

    $success_message = "Your session for '$service_name' has been booked on " . date("F j, Y, g:i a", strtotime($appointment_date));
}

// Handle cancel appointment
if (isset($_POST['cancel_session']) && isset($_SESSION['appointment_id'])) {
    $appointment_id = $_SESSION['appointment_id'];

    $delete = "DELETE FROM appointments WHERE appointment_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, 'ii', $appointment_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    unset($_SESSION['pending_appointment']);
    $appt_date = null; 
    $appt_service = null;
    $success_message = "Your appointment has been cancelled.";
}
// Handle approve appointment
if (isset($_POST['approve_session']) && isset($_SESSION['pending_appointment'])) {
    $data = $_SESSION['pending_appointment'];
    $insert = "INSERT INTO appointments (user_id, service_name, appointment_date) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, 'sss', $user_id, $data['service_name'], $data['appointment_date']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    unset($_SESSION['pending_appointment']);
    $appt_date = null;
    $appt_service = null;

    $success_message = "Your appointment has been approved.";
}


// If appointment exists, fetch it
$appt_query = "SELECT appointment_id, service_name, appointment_date
 FROM appointments
 WHERE user_id = ? AND appointment_date >= NOW() 
               ORDER BY appointment_date ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $appt_query);
mysqli_stmt_bind_param($stmt, 's', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $appt_id, $appt_service, $appt_date);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
if (!empty($appt_id)) {
    $_SESSION['appointment_id'] = $appt_id;
}
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
    h2 { color: #333; }
    .label { font-weight: bold; color: #555; }
    .cancel-btn {
      background: #e74c3c;
      color: #fff;
      border: none;
      padding: 10px 15px;
      border-radius: 6px;
      cursor: pointer;
    }
    .cancel-btn:hover {
      background: #c0392b;
    }
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

      <!-- Success / Cancel message -->
      <?php if (!empty($success_message)): ?>
        <div class="card" style="background:#dff0d8; color:#3c763d;">
          <p><?= $success_message ?></p>
        </div>
      <?php endif; ?>

   <?php if (isset($_SESSION['pending_appointment'])): ?>
  <?php $data = $_SESSION['pending_appointment']; ?>
  <div class="card" style="background:#dff0d8; color:#3c763d;">
    <h3>Your Upcoming Appointment</h3>
    <p>Service: <?= htmlspecialchars($data['service_name']) ?></p>
    <p>Date: <?= date("F j, Y, g:i a", strtotime($data['appointment_date'])) ?></p>
    <form method="POST" action="" style="display:flex; gap:10px;">
      <button type="submit" name="approve_session" class="cancel-btn" style="background:#2ecc71;">Approve Appointment</button>
      <button type="submit" name="cancel_session" class="cancel-btn">Cancel Appointment</button>
    </form>
  </div>
<?php else: ?>
  <!-- Show booking form only if no pending appointment -->
  <div class="card">
    <h3>Book a Session</h3>
    <form method="POST" action="">
      <label for="appointment_date">Choose Date & Time:</label><br>
      <input type="datetime-local" name="appointment_date" required>
      <br><br>
      <button type="submit" name="book_session">Book Session</button>
    </form>
  </div>
<?php endif; ?>

      <!-- Inquiry number always visible -->
      <div class="card">
        <p class="label">Need Help?</p>
        <p>📞 Call us: +254 712 345 678</p>
      </div>

      <a href="appointments.php"><button type="button">View Appointment History</button></a> <br>

      <a href="log_activity.php"><button type="button">Start New Session</button></a> <br>
      <a href="logout.php"><button type="button">Logout</button></a>
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
