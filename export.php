<?php
session_start();
include('connect.php');

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get filters from POST
$search      = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
$start_date  = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date    = isset($_POST['end_date']) ? $_POST['end_date'] : '';

$searchCondition = $search ? "AND (u.user_name LIKE '%$search%' OR u.fullname LIKE '%$search%')" : '';
$dateCondition   = ($start_date && $end_date) ? "AND DATE(l.log_date) BETWEEN '$start_date' AND '$end_date'" : '';

// Set headers for Excel export
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Jamii_Reports.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Title and timestamp
$date = date("Y-m-d H:i:s");
echo "Jamii Resource Centre - System Usage Report\t\t\t\n";
echo "Generated on: $date\t\t\t\n\n";

// Column headers
echo "Fullname\tUser_name\tService_name\tTime Spent\tLog Date\n";

// Fetch data from database with filters
$sql = "SELECT u.fullname, u.user_name, l.service_name, l.time_spent, l.log_date
        FROM jamii_system.logs AS l
        JOIN jamii_system.users AS u ON l.user_id = u.user_id
        WHERE 1=1 $searchCondition $dateCondition
        ORDER BY l.log_date DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['fullname']}\t{$row['user_name']}\t{$row['service_name']}\t{$row['time_spent']}\t{$row['log_date']}\n";
    }
} else {
    echo "No records found.\n";
}

// Count unique users (respecting filters)
$userCountSql = "SELECT COUNT(DISTINCT u.user_id) AS total_users
                 FROM jamii_system.logs AS l
                 JOIN jamii_system.users AS u ON l.user_id = u.user_id
                 WHERE 1=1 $searchCondition $dateCondition";
$userCountResult = $conn->query($userCountSql);
$totalUsers = ($userCountResult && $userCountResult->num_rows > 0)
              ? $userCountResult->fetch_assoc()['total_users']
              : 0;

echo "\nTotal Users Logged:\t$totalUsers\n";
?>
