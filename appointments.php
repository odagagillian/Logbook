<?php
include('connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all appointments for this user, including staff info
$query = "SELECT a.service_name, a.appointment_date, a.created_at,
                 s.staff_name, s.staff_phone, s.room_number
          FROM appointments a
          JOIN staff s ON a.staff_id = s.staff_id
          WHERE a.user_id = ?
          ORDER BY a.appointment_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Appointments - Jamii Resource Centre</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background:#000; color:#fff; }
    .container { max-width: 1000px; margin:auto; padding:20px; }
    h2 { color:#ff00ff; }
    table { width:100%; border-collapse: collapse; margin-top:20px; }
    th, td { padding:12px; border-bottom:1px solid #a818adff; text-align:left; }
    th { background:#1a1a1a; color:#ff00ff; }
    tr:hover { background:#222; }
    a { color:#ff00ff; text-decoration:none; }
    .back-btn { margin-top:20px; display:inline-block; padding:10px 15px; background:#ff00ff; color:#fff; border-radius:6px; }
    .back-btn:hover { background:#a64bf0; }
  </style>
</head>
<body>
  <div class="container">
    <h2>My Appointment History</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <table>
        <tr>
          <th>Service</th>
          <th>Appointment Date</th>
          <th>Booked On</th>
          <th>Staff</th>
          <th>Phone</th>
          <th>Room</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td><?= date("F j, Y, g:i a", strtotime($row['appointment_date'])) ?></td>
            <td><?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></td>
            <td><?= htmlspecialchars($row['staff_name']) ?></td>
            <td><?= htmlspecialchars($row['staff_phone']) ?></td>
            <td><?= htmlspecialchars($row['room_number']) ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>No appointments found.</p>
    <?php endif; ?>

    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
  </div>
</body>
</html>
