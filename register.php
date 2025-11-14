<?php
include 'connect.php';
session_start();

$error = '';
$success = '';
$old_fullname = '';
$old_user_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $fullname   = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $user_name  = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $password   = isset($_POST['password']) ? $_POST['password'] : '';

    // Keep values to refill form on error (but never refill password)
    $old_fullname = htmlspecialchars($fullname, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $old_user_name = htmlspecialchars($user_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Basic validation
    if ($fullname === '' || $user_name === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        // Check username uniqueness
        $checkStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_name = ?");
        if (!$checkStmt) {
            $error = 'Server error. Please try again later.';
        } else {
            mysqli_stmt_bind_param($checkStmt, 's', $user_name);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $error = 'That username is already taken. Please choose another.';
                mysqli_stmt_close($checkStmt);
            } else {
                mysqli_stmt_close($checkStmt);

                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user with default role 'user'
                $stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, user_name, password_hash, role) VALUES (?, ?, ?, 'user')");
                if (!$stmt) {
                    $error = 'Server error. Please try again later.';
                } else {
                    mysqli_stmt_bind_param($stmt, 'sss', $fullname, $user_name, $password_hash);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);
                        header('Location: log_activity.php');
                        exit;
                    } else {
                        $error = 'Registration failed. Please try again.';
                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }

    if ($conn) {
        mysqli_close($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jamii Resource Centre — Register</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="container">
        <h1 class="panel-title"></h1>
        <div class= "left-panel">
            <h1>JAMII RESOURCE CENTER</h1>
            <p>Your trusted partner in community support and empowerment.</p>
        </div>
        <div class="right-panel">
        <h2>Create Account</h2>

        <form action="register.php" method="POST" autocomplete="off">
            <input type="text" name="fullname" placeholder="Full name" required value="<?php echo $old_fullname; ?>">
            <input type="text" name="user_name" placeholder="Username" required value="<?php echo $old_user_name; ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
<div class= "footer-text"> Have an account? <a href="index.php">Login Here</a>
    </div> </div>
</body>
</html>
