<?php
session_start();
include('connect.php');

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Filters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$service_filter = $_GET['service'] ?? '';
$status_filter = $_GET['status'] ?? '';

$searchCondition = $search ? "AND (u.user_name LIKE '%$search%' OR u.fullname LIKE '%$search%')" : '';
$dateCondition = ($start_date && $end_date) ? "AND DATE(l.log_date) BETWEEN '$start_date' AND '$end_date'" : '';
$serviceCondition = $service_filter ? "AND l.service_name = '$service_filter'" : '';

// Combined query for logs and appointments
$sql = "SELECT u.user_id, u.user_name, u.fullname, 
               DATE(l.log_date) AS log_day,
               GROUP_CONCAT(DISTINCT l.service_name SEPARATOR ', ') AS services_used,
               SUM(TIME_TO_SEC(SUBSTRING_INDEX(l.time_spent, 'm', 1)) * 60 + 
                   SUBSTRING_INDEX(SUBSTRING_INDEX(l.time_spent, 'm ', -1), 's', 1)) AS total_seconds,
               TIME(l.log_date) AS session_time,
               (SELECT GROUP_CONCAT(CONCAT(a.service_name, ' with ', s.staff_name) SEPARATOR ' | ')
                FROM appointments a 
                JOIN staff s ON a.staff_id = s.staff_id 
                WHERE a.user_id = u.user_id) AS appointments
        FROM logs AS l
        JOIN users AS u ON l.user_id = u.user_id
        WHERE 1=1 $searchCondition $dateCondition $serviceCondition
        GROUP BY u.user_id, log_day
        ORDER BY log_day DESC, session_time DESC";

$result = $conn->query($sql);

// Summary cards
$userCount = $conn->query("SELECT COUNT(DISTINCT user_id) AS total FROM logs")->fetch_assoc()['total'];
$apptCount = $conn->query("SELECT COUNT(*) AS total FROM appointments")->fetch_assoc()['total'];
$topServiceResult = $conn->query("SELECT service_name, COUNT(*) AS count FROM logs GROUP BY service_name ORDER BY count DESC LIMIT 1")->fetch_assoc();
$topService = $topServiceResult['service_name'] ?? 'N/A';
$totalTimeResult = $conn->query("SELECT SUM(TIME_TO_SEC(SUBSTRING_INDEX(time_spent, 'm', 1)) * 60) AS total FROM logs")->fetch_assoc();
$totalMinutes = floor(($totalTimeResult['total'] ?? 0) / 60);

// Get all services for filter
$servicesQuery = $conn->query("SELECT DISTINCT service_name FROM logs ORDER BY service_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Reports - Jamii Resource Centre</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
      font-family: 'Inter', 'Segoe UI', sans-serif; 
      background: linear-gradient(135deg, #0f0c1d 0%, #1a1530 100%);
      color: #fff;
      min-height: 100vh;
      padding: 20px;
    }
    .container { max-width: 1400px; margin: auto; }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 20px 0;
    }
    .header h1 { 
      font-size: 2rem; 
      color: #a259ff; 
      font-weight: 700;
    }
    .header .actions a {
      background: linear-gradient(135deg, #7c3aed 0%, #a259ff 100%);
      color: #fff;
      padding: 12px 24px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
    }
    .header .actions a:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(162, 89, 255, 0.4);
    }
    
    .summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .summary-card {
      background: rgba(26, 21, 48, 0.8);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: 16px;
      border: 1px solid rgba(162, 89, 255, 0.2);
      transition: all 0.3s ease;
    }
    .summary-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(162, 89, 255, 0.2);
    }
    .summary-card h3 {
      font-size: 0.9rem;
      color: #c8c8d8;
      font-weight: 500;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .summary-card p {
      font-size: 2rem;
      font-weight: 700;
      color: #a259ff;
    }
    
    .filters-section {
      background: rgba(26, 21, 48, 0.8);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: 16px;
      border: 1px solid rgba(162, 89, 255, 0.2);
      margin-bottom: 30px;
    }
    .filters-section h3 {
      color: #a259ff;
      margin-bottom: 15px;
      font-size: 1.1rem;
    }
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }
    .filters input, .filters select, .filters button {
      padding: 12px 15px;
      border-radius: 10px;
      border: 2px solid rgba(162, 89, 255, 0.3);
      background: rgba(162, 89, 255, 0.1);
      color: #fff;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }
    .filters input::placeholder {
      color: #999;
    }
    .filters input:focus, .filters select:focus {
      outline: none;
      border-color: #a259ff;
      box-shadow: 0 0 0 3px rgba(162, 89, 255, 0.2);
    }
    .filters button {
      background: linear-gradient(135deg, #7c3aed 0%, #a259ff 100%);
      border: none;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .filters button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(162, 89, 255, 0.4);
    }
    .filters .clear-btn {
      background: rgba(255, 255, 255, 0.1);
    }
    .filters .export-btn {
      background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    }
    
    .table-container {
      background: rgba(26, 21, 48, 0.8);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      border: 1px solid rgba(162, 89, 255, 0.2);
      overflow: hidden;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    thead {
      background: rgba(162, 89, 255, 0.2);
    }
    th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #a259ff;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid rgba(162, 89, 255, 0.3);
    }
    td {
      padding: 15px;
      border-bottom: 1px solid rgba(162, 89, 255, 0.1);
      color: #c8c8d8;
      font-size: 0.9rem;
    }
    tr:hover {
      background: rgba(162, 89, 255, 0.05);
    }
    .badge {
      display: inline-block;
      background: rgba(162, 89, 255, 0.2);
      color: #a259ff;
      padding: 5px 12px;
      border-radius: 20px;
      margin: 3px;
      font-size: 0.8rem;
      font-weight: 500;
      border: 1px solid rgba(162, 89, 255, 0.3);
    }
    .status-open {
      background: rgba(46, 204, 113, 0.2);
      color: #2ecc71;
      border: 1px solid rgba(46, 204, 113, 0.3);
    }
    .actions-cell {
      display: flex;
      gap: 10px;
    }
    .action-btn {
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
    }
    .edit-btn {
      background: rgba(52, 152, 219, 0.2);
      color: #3498db;
      border: 1px solid rgba(52, 152, 219, 0.3);
    }
    .edit-btn:hover {
      background: rgba(52, 152, 219, 0.3);
      transform: translateY(-2px);
    }
    .delete-btn {
      background: rgba(231, 76, 60, 0.2);
      color: #e74c3c;
      border: 1px solid rgba(231, 76, 60, 0.3);
    }
    .delete-btn:hover {
      background: rgba(231, 76, 60, 0.3);
      transform: translateY(-2px);
    }
    .no-data {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 1rem;
    }
    footer {
      margin-top: 40px;
      text-align: center;
      color: #999;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>📊 Admin Reports Dashboard</h1>
    <div class="actions">
      <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
  </div>

  <div class="summary">
    <div class="summary-card">
      <h3>Total Users</h3>
      <p><?= $userCount ?></p>
    </div>
    <div class="summary-card">
      <h3>Total Appointments</h3>
      <p><?= $apptCount ?></p>
    </div>
    <div class="summary-card">
      <h3>Most Used Service</h3>
      <p style="font-size: 1.2rem;"><?= htmlspecialchars($topService) ?></p>
    </div>
    <div class="summary-card">
      <h3>Total Time Logged</h3>
      <p><?= number_format($totalMinutes) ?> <span style="font-size: 1rem;">mins</span></p>
    </div>
  </div>

  <div class="filters-section">
    <h3>🔍 Filter Reports</h3>
    <form method="GET" class="filters">
      <input type="text" name="search" placeholder="Search by name or username..." value="<?= htmlspecialchars($search) ?>" style="min-width: 250px;" />
      <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" />
      <span style="color: #999;">to</span>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" />
      <select name="service">
        <option value="">All Services</option>
        <?php while($service = $servicesQuery->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($service['service_name']) ?>" <?= $service_filter === $service['service_name'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($service['service_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Apply Filters</button>
      <a href="reports.php" class="clear-btn" style="padding: 12px 15px; border-radius: 10px; text-decoration: none; color: #fff; display: inline-block;">Clear</a>
    </form>
  </div>

  <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
    <h3 style="color: #a259ff;">User Activity & Appointments</h3>
    <form method="POST" action="export.php" style="display: inline;">
      <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
      <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
      <button type="submit" class="export-btn" style="padding: 10px 20px; border-radius: 10px; border: none; color: #fff; font-weight: 600; cursor: pointer;">📥 Export Report</button>
    </form>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Username</th>
          <th>Date</th>
          <th>Session Time</th>
          <th>Services Used</th>
          <th>Total Time Spent</th>
          <th>Booked Appointments</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $minutes = floor($row['total_seconds'] / 60);
                $seconds = $row['total_seconds'] % 60;
                $formatted_time = "{$minutes}m {$seconds}s";
                $services = explode(', ', $row['services_used']);
                
                echo "<tr>
                  <td><strong style='color: #fff;'>{$row['fullname']}</strong></td>
                  <td>{$row['user_name']}</td>
                  <td>{$row['log_day']}</td>
                  <td>{$row['session_time']}</td>
                  <td>";
                foreach ($services as $s) {
                    echo "<span class='badge'>" . htmlspecialchars($s) . "</span>";
                }
                echo "</td>
                  <td><strong style='color: #a259ff;'>$formatted_time</strong></td>
                  <td>";
                if ($row['appointments']) {
                    $appointments = explode(' | ', $row['appointments']);
                    foreach ($appointments as $appt) {
                        echo "<span class='badge status-open'>" . htmlspecialchars($appt) . "</span>";
                    }
                } else {
                    echo "<span style='color: #666;'>No appointments</span>";
                }
                echo "</td>
                  <td class='actions-cell'>
                    <a href='edit_report.php?user={$row['user_name']}&date={$row['log_day']}' class='action-btn edit-btn'>Edit</a>
                    <a href='delete_report.php?user={$row['user_name']}&date={$row['log_day']}' class='action-btn delete-btn' onclick=\"return confirm('Delete this report?');\">Delete</a>
                  </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='no-data'>No records found. Try adjusting your filters.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <footer>
    Jamii Resource Centre Admin Panel © <?= date('Y') ?>
  </footer>
</div>
</body>
</html>
