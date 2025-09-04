<?php
session_start();
require 'db.php'; // Include InfinityFree DB connection

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepared statement for security
    $query = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_email'] = $email;
            header("Location: user_dashboard.php");
            exit();
        } else {
            $error = "Invalid Email or Password";
        }
    } else {
        $error = "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #FFF8F0 0%, #FFF8F0 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-box {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
            padding: 40px 30px;
            width: 360px;
            text-align: center;
            box-sizing: border-box;
        }

        .login-box h2 {
            margin-bottom: 25px;
            font-size: 28px;
            color:#333;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            padding-right: 40px; /* space for eye toggle */
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #555;
            user-select: none;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background-color: rgb(246, 213, 163);
            color: black;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .login-box button:hover {
            background-color: rgb(247, 222, 185);
            transform: scale(1.05);
        }

        .register-line {
            margin: 15px 0;
            font-size: 14px;
            color: #333;
        }

        .register-line a {
            color: rgb(246, 168, 85);
            font-weight: bold;
            text-decoration: none;
        }

        .register-line a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>User Login</h2>

        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Enter Email" required>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Enter Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>

            <div class="register-line">
                You haven't registered? <a href="user_register.php">Register</a>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>

    <script>
    function togglePassword(id, element) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            element.textContent = "🙈";
        } else {
            input.type = "password";
            element.textContent = "👁️";
        }
    }
    </script>
</body>
</html>
