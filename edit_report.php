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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $services = $conn->real_escape_string($_POST['services']);
            $time = $conn->real_escape_string($_POST['time']);

            $updateQuery = "UPDATE jamii_system.logs 
                            SET service_name = '$services', time_spent = '$time'
                            WHERE user_id = '$userId' AND DATE(log_date) = '$date'";
            if ($conn->query($updateQuery)) {
                header("Location: reports.php?msg=updated");
                exit();
            } else {
                echo "Update failed: " . $conn->error;
            }
        }

        $logQuery = "SELECT service_name, time_spent FROM jamii_system.logs 
                     WHERE user_id = '$userId' AND DATE(log_date) = '$date' LIMIT 1";
        $logResult = $conn->query($logQuery);
        $log = $logResult->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Report</title>
</head>
<body>
    <h2>Edit Report for <?= htmlspecialchars($user) ?> on <?= htmlspecialchars($date) ?></h2>
    <form method="POST">
        <label>Services Used:</label><br>
        <input type="text" name="services" value="<?= htmlspecialchars($log['service_name']) ?>" required><br><br>

        <label>Time Spent (e.g., 10m 30s):</label><br>
        <input type="text" name="time" value="<?= htmlspecialchars($log['time_spent']) ?>" required><br><br>

        <button type="submit">Update</button>
        <a href="reports.php">Cancel</a>
    </form>
</body>
</html>
