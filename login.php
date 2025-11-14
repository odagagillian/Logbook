<?php
include('connect.php');
session_start();

// If already logged in, redirect based on role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: log_activity.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password  = isset($_POST['password']) ? $_POST['password'] : '';

    if ($user_name === '' || $password === '') {
        echo "<script>alert('Please fill in all fields.'); window.location.href='index.php';</script>";
        exit;
    }

    // Use prepared statements to prevent SQL injection
    $stmt = mysqli_prepare($conn, "SELECT user_id, user_name, password_hash, role FROM users WHERE user_name = ?");
    if (!$stmt) {
        echo "<script>alert('Server error. Please try again later.'); window.location.href='index.php';</script>";
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role']      = $user['role'];   // ✅ use 'role'
            $_SESSION['login_time'] = time();         // Start session timer

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: log_activity.php");
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('No user found with that name.'); window.location.href='index.php';</script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jamii Resource Centre</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
  <div class="container">
    <!-- Left abstract panel -->
    <div class="left-panel">
      <h1>WELCOME TO JAMII RESOURCE CENTER!</h1>
    </div>

    <!-- Right login form panel -->
    <div class="right-panel">
      <h2>LOGIN</h2>
      <form id="loginForm" action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
      <div class="footer-text">
        New User? <a href="register.php" style="color:#ff00ff; text-decoration:underline;">Register Here</a>
      </div>
    </div>
  </div>
</body>
</html>
