<?php
session_start();
include("connect.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Jamii Resource Center</title>
<link rel="stylesheet" href="style.css">

</head>
<body>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Admin Dashboard</h1>
      <p>Welcome, Admin Gillian!</p>
    </header>

    <section class="dashboard-actions">
      <a href="reports.php" class="btn-admin">View Reports</a>
      <a href="logout.php" class="btn-admin"> Logout</a>
    </section>
  </div>
</body>


</html>
