<?php
include('connect.php');
session_start();

// If already logged in, redirect based on role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password  = isset($_POST['password']) ? $_POST['password'] : '';

    if ($user_name === '' || $password === '') {
        $error_message = "Please fill in all fields.";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT user_id, user_name, password_hash, role FROM users WHERE user_name = ?");
        if (!$stmt) {
            $error_message = "Server error. Please try again later.";
        } else {
            mysqli_stmt_bind_param($stmt, 's', $user_name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);

                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id']   = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['login_time'] = time();

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();
                } else {
                    $error_message = "Incorrect password!";
                }
            } else {
                $error_message = "No user found with that username.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jamii Resource Centre - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Inline styles for login page */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .login-page {
            margin: 0;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4b0082 0%, #0d0213ff 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 900px;
            max-width: 95%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #2c0040 0%, #4b0082 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #ffb6c1;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 182, 193, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .left-panel h1 {
            font-size: 2.2em;
            text-align: center;
            font-weight: 700;
            position: relative;
            z-index: 1;
            line-height: 1.3;
        }

        .right-panel {
            flex: 1;
            background: #1a1a1a;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h2 {
            font-size: 2em;
            margin-bottom: 30px;
            text-align: center;
            color: #fff;
            font-weight: 700;
        }

        .error-message {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            font-size: 0.9em;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .right-panel form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .right-panel input {
            padding: 14px 18px;
            border-radius: 10px;
            border: 2px solid rgba(162, 89, 255, 0.3);
            outline: none;
            font-size: 1em;
            background: rgba(51, 51, 51, 0.8);
            color: #fff;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .right-panel input::placeholder {
            color: #999;
        }

        .right-panel input:focus {
            border-color: #a259ff;
            box-shadow: 0 0 0 3px rgba(162, 89, 255, 0.2);
            background: rgba(51, 51, 51, 1);
        }

        .right-panel button {
            background: linear-gradient(135deg, #ff007f 0%, #8a2be2 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 14px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.05em;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(255, 0, 127, 0.3);
            font-family: 'Poppins', sans-serif;
        }

        .right-panel button:hover {
            background: linear-gradient(135deg, #ff4da6 0%, #a64bf0 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(255, 0, 127, 0.4);
        }

        .right-panel button:active {
            transform: translateY(0);
        }

        .footer-text {
            margin-top: 25px;
            text-align: center;
            font-size: 0.95em;
            color: #ccc;
        }

        .footer-text a {
            color: #ff00ff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .footer-text a:hover {
            color: #ff4da6;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-page {
                padding: 10px;
            }

            .container {
                flex-direction: column;
                width: 100%;
                max-width: 100%;
            }

            .left-panel {
                padding: 30px 20px;
            }

            .left-panel h1 {
                font-size: 1.6em;
            }

            .right-panel {
                padding: 30px 20px;
            }

            .right-panel h2 {
                font-size: 1.6em;
                margin-bottom: 20px;
            }

            .right-panel input,
            .right-panel button {
                padding: 12px 15px;
                font-size: 0.95em;
            }

            .footer-text {
                font-size: 0.85em;
            }
        }
    </style>
</head>

<body class="login-page">
    <div class="container">
        <!-- Left abstract panel -->
        <div class="left-panel">
            <h1>WELCOME TO JAMII RESOURCE CENTER!</h1>
        </div>

        <!-- Right login form panel -->
        <div class="right-panel">
            <h2>LOGIN</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <div class="footer-text">
                New User? <a href="register.php">Register Here</a> <br>
                HOME <a href="index.php">Go back</a>
            </div>
        </div>
    </div>
</body>
</html>