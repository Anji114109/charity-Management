
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Charity Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(to right, #FFF8F0 0%, #FFF8F0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
        }

        .container {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .box {
            background: #fff;
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .box img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .box p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        .box a {
            text-decoration: none;
            width: 100%;
            
        }

        .box button {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            background:rgb(235, 194, 153);
            color: white;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .box button:hover {
            background:rgb(247, 230, 213);
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <h1>WELCOME TO DIGITAL CHARITY PLATFORM</h1>

    <div class="container">
        
        <!-- User Box -->
        <div class="box">
            <img src="user.png" alt="User Image">
            <p>Your giving lights a thousand lives. Thank you for making a difference.</p>
            <a href="user_login.php"><button>User</button></a>
        </div>

        <!-- Admin Box -->
        <div class="box">
            <img src="admin.png" alt="Admin Image">
            <p>With every transaction you manage, trust and hope grow stronger.</p>
            <a href="admin_login.php"><button>Admin</button></a>
        </div>

        <!-- Volunteer Box -->
        <div class="box">
            <img src="volunteer.png" alt="Volunteer Image">
            <p>Your hands turn kindness into action, making dreams come true.</p>
            <a href="volunteer_login.php"><button>Volunteer</button></a>
        </div>

    </div>

</body>
</html>
