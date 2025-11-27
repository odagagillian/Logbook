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

// Set headers for CSV export (cleaner than .xls)
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Jamii_Reports.csv");
header("Pragma: no-cache");
header("Expires: 0");

// Title and timestamp
$date = date("Y-m-d H:i:s");
echo "Jamii Resource Centre - System Usage Report\n";
echo "Generated on: $date\n\n";

// Column headers
echo "Fullname,Username,Service Used,Time Spent,Log Date,Appointment Date,Staff,Staff Phone,Room\n";

// Fetch data with joins
$sql = "SELECT u.fullname, u.user_name, l.service_name, l.time_spent, l.log_date,
               a.appointment_date, s.staff_name, s.staff_phone, s.room_number
        FROM logs AS l
        JOIN users AS u ON l.user_id = u.user_id
        LEFT JOIN appointments a ON l.user_id = a.user_id
        LEFT JOIN staff s ON a.staff_id = s.staff_id
        WHERE 1=1 $searchCondition $dateCondition
        ORDER BY l.log_date DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Escape commas by wrapping values in quotes
        echo "\"{$row['fullname']}\",\"{$row['user_name']}\",\"{$row['service_name']}\",\"{$row['time_spent']}\",\"{$row['log_date']}\",";
        echo "\"".($row['appointment_date'] ? date("F j, Y, g:i a", strtotime($row['appointment_date'])) : "")."\",";
        echo "\"{$row['staff_name']}\",\"{$row['staff_phone']}\",\"{$row['room_number']}\"\n";
    }
} else {
    echo "No records found.\n";
}

// Count unique users (respecting filters)
$userCountSql = "SELECT COUNT(DISTINCT u.user_id) AS total_users
                 FROM logs AS l
                 JOIN users AS u ON l.user_id = u.user_id
                 WHERE 1=1 $searchCondition $dateCondition";
$userCountResult = $conn->query($userCountSql);
$totalUsers = ($userCountResult && $userCountResult->num_rows > 0)
              ? $userCountResult->fetch_assoc()['total_users']
              : 0;

echo "\nTotal Users Logged:,$totalUsers\n";
?>
