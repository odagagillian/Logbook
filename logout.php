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

    $stmt = $conn->prepare("
        INSERT INTO logs (user_id, service_name, time_spent, log_date)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("iss", $user_id, $service_name, $timeSpent);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Clear session
$_SESSION = [];
session_destroy();

// Redirect to login
header('Location: index.php?msg=logged_out');
exit();
?>
