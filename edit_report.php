<?php
session_start();
include('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if (isset($_GET['log_id'])) {
    $log_id = intval($_GET['log_id']); // safer than user/date combo

    // Fetch log entry securely
    $logQuery = "SELECT l.service_name, l.time_spent, u.user_name, DATE(l.log_date) AS log_day
                 FROM logs l
                 JOIN users u ON l.user_id = u.user_id
                 WHERE l.log_id = ?";
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $logResult = $stmt->get_result();
    $log = $logResult->fetch_assoc();
    $stmt->close();

    if (!$log) {
        echo "Log not found.";
        exit();
    }

    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $services = $_POST['services'];
        $time = $_POST['time'];

        $updateQuery = "UPDATE logs SET service_name = ?, time_spent = ? WHERE log_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $services, $time, $log_id);

        if ($stmt->execute()) {
            header("Location: reports.php?msg=updated");
            exit();
        } else {
            header("Location: reports.php?msg=error");
            exit();
        }
        $stmt->close();
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
    <h2>Edit Report for <?= htmlspecialchars($log['user_name']) ?> on <?= htmlspecialchars($log['log_day']) ?></h2>
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
