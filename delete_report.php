<?php
session_start();
include('connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if (isset($_GET['user']) && isset($_GET['date'])) {
    $user = $_GET['user'];
    $date = $_GET['date'];

    // Find user_id securely
    $userQuery = "SELECT user_id FROM users WHERE user_name = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $stmt->close();

    if ($userResult && $userResult->num_rows > 0) {
        $userId = $userResult->fetch_assoc()['user_id'];

        // Delete logs securely
        $deleteQuery = "DELETE FROM logs WHERE user_id = ? AND DATE(log_date) = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("is", $userId, $date);

        if ($stmt->execute()) {
            header("Location: reports.php?msg=deleted");
            exit();
        } else {
            header("Location: reports.php?msg=error");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: reports.php?msg=user_not_found");
        exit();
    }
} else {
    header("Location: reports.php?msg=invalid_request");
    exit();
}
?>
