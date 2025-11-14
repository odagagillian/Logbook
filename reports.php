<?php
session_start();
include('connect.php');

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Search filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$searchCondition = $search ? "AND (u.user_name LIKE '%$search%' OR u.fullname LIKE '%$search%')" : '';

// Date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$dateCondition = ($start_date && $end_date) ? "AND DATE(l.log_date) BETWEEN '$start_date' AND '$end_date'" : '';

// Fetch logs
$sql = "SELECT u.user_name, u.fullname, 
               DATE(l.log_date) AS log_day,
               GROUP_CONCAT(DISTINCT l.service_name SEPARATOR ', ') AS services_used,
               SUM(TIME_TO_SEC(SUBSTRING_INDEX(l.time_spent, 'm', 1)) * 60 + 
                   SUBSTRING_INDEX(SUBSTRING_INDEX(l.time_spent, 'm ', -1), 's', 1)) AS total_seconds,
               GROUP_CONCAT(TIME(l.log_date) ORDER BY l.log_date SEPARATOR ', ') AS times
        FROM jamii_system.logs AS l
        JOIN jamii_system.users AS u ON l.user_id = u.user_id
        WHERE 1=1 $searchCondition $dateCondition
        GROUP BY u.user_id, log_day
        ORDER BY log_day DESC";

$result = $conn->query($sql);

// Count unique users
$userCountSql = "SELECT COUNT(DISTINCT user_id) AS total_users FROM jamii_system.logs";
$userCountResult = $conn->query($userCountSql);
$totalUsers = ($userCountResult && $userCountResult->num_rows > 0)
              ? $userCountResult->fetch_assoc()['total_users']
              : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Activity Records</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #000;
      color: #fff;
      font-family: 'Poppins', sans-serif;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 30px;
    }

    header {
      text-align: center;
      margin-bottom: 30px;
    }

    header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #ff00ff;
      margin: 0;
    }

    .controls {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 15px;
      margin-bottom: 20px;
    }

    .controls form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .controls input,
    .controls button,
    .controls a {
      padding: 10px 15px;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }

    .controls button,
    .controls a {
      background: linear-gradient(to right, #8a2be2, #ff007f);
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
    }

    .controls a.clear {
      background: #555;
    }

    .table-wrapper {
      overflow-x: auto;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 0, 255, 0.2);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 900px;
    }

    table th, table td {
      border: 1px solid #444;
      padding: 12px;
      text-align: center;
    }

    table th {
      background: #8a2be2;
      color: #fff;
    }

    table tr:nth-child(even) {
      background: #1a1a1a;
    }

    table tr:nth-child(odd) {
      background: #2c2c2c;
    }

    footer {
      margin-top: 40px;
      text-align: center;
      font-size: 1.2rem;
      color: #ffb6c1;
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1>Activity Records</h1>
  </header>

  <div class="controls">
    <form method="GET">
      <input type="text" name="search" placeholder="Search by username or full name" value="<?= htmlspecialchars($search) ?>" />
      <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" />
      <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" />
      <button type="submit">Filter</button>
      <a href="reports.php" class="clear">Clear Filters</a>
    </form>

    <form method="POST" action="export.php">
      <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
      <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
      <button type="submit">Export Report</button>
    </form>

    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Username</th>
          <th>Services Used</th>
          <th>Total Time Spent</th>
          <th>Log Date</th>
          <th>Session Times</th>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <th>Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $minutes = floor($row['total_seconds'] / 60);
                $seconds = $row['total_seconds'] % 60;
                $formatted_time = "{$minutes}m {$seconds}s";

                echo "<tr>
                  <td>{$row['fullname']}</td>
                  <td>{$row['user_name']}</td>
                  <td>{$row['services_used']}</td>
                  <td>{$formatted_time}</td>
                  <td>{$row['log_day']}</td>
                  <td>{$row['times']}</td>";

                if ($_SESSION['role'] === 'admin') {
                    echo "<td>
                      <a href='edit_report.php?user={$row['user_name']}&date={$row['log_day']}' class='btn'>Edit</a>
                      <a href='delete_report.php?user={$row['user_name']}&date={$row['log_day']}' class='btn' onclick=\"return confirm('Are you sure you want to delete this report?');\">Delete</a>
                    </td>";
                }

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>NO LOG RECORDS FOUND!</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <footer>
    <h3>Total Users Logged: <?= $totalUsers ?></h3>
  </footer>
</div>
</body>
</html>
