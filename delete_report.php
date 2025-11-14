<?php
session_start();
include('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if (isset($_GET['user']) && isset($_GET['date'])) {
    $user = $conn->real_escape_string($_GET['user']);
    $date = $conn->real_escape_string($_GET['date']);

    $userQuery = "SELECT user_id FROM jamii_system.users WHERE user_name = '$user'";
    $userResult = $conn->query($userQuery);

    if ($userResult && $userResult->num_rows > 0) {
        $userId = $userResult->fetch_assoc()['user_id'];

        $deleteQuery = "DELETE FROM jamii_system.logs WHERE user_id = '$userId' AND DATE(log_date) = '$date'";
        if ($conn->query($deleteQuery)) {
            header("Location: reports.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting logs: " . $conn->error;
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>
