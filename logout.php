<?php
session_start();

if (isset($_SESSION['login_time']) && isset($_SESSION['user_id'])) {

    $loginTime = $_SESSION['login_time'];
    $logoutTime = time();
    $duration = $logoutTime - $loginTime;

    $minutes = floor($duration / 60);
    $seconds = $duration % 60;
    $timeSpent = "{$minutes}m {$seconds}s";

    include("connect.php");

    $user_id = $_SESSION['user_id'];
    $service_name = $_SESSION['selected_service'] ?? 'Unknown';
    $appointment_id = $_SESSION['appointment_id'] ?? null;

   if ($appointment_id) {
    // Your table does NOT have appointment_id, so ignore it
}

$stmt = $conn->prepare("
    INSERT INTO logs (user_id, service_name, time_spent, log_date)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param("sss", $user_id, $service_name, $timeSpent);
$stmt->execute();
$stmt->close();
$conn->close();

}

session_destroy();
header('Location: index.php');
exit();
?>
