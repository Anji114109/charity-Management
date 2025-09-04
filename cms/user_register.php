<?php
session_start();
require 'db.php'; // Include InfinityFree DB connection

$emailError = $passwordError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $passwordError = "Passwords do not match!";
    } else {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $emailError = "Email already registered!";
        } else {
            $passHashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, phone, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $phone, $passHashed);

            if ($stmt->execute()) {
                echo "<script>
                        alert('User Registered Successfully!');
                        window.location.href='user_login.php';
                      </script>";
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #FFF8F0, #FFF8F0);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 450px;
            box-sizing: border-box;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }

        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            padding-right: 40px; /* space for eye icon */
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box; /* prevents overflow */
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

        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #4b79a1;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .form-container button:hover {
            background-color: #283e51;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>User Registration</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <?php if (!empty($emailError)) echo "<div class='error'>$emailError</div>"; ?>

        <input type="text" name="phone" placeholder="Phone Number" required>

        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
        </div>

        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>
        <?php if (!empty($passwordError)) echo "<div class='error'>$passwordError</div>"; ?>

        <button type="submit">Register</button>
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
