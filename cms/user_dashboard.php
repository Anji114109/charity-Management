<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch all charities along with total donations
$query = "
    SELECT c.id, c.charity_name, c.charity_address, 
           COALESCE(SUM(t.amount), 0) AS total_donations
    FROM charities c
    LEFT JOIN transactions t ON c.id = t.charity_id
    GROUP BY c.id
    ORDER BY c.charity_name ASC
";
$result = $conn->query($query);

// Extract username from email
$user_email = $_SESSION['user_email'];
$username = explode('@', $user_email)[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fdf1e6, #fde6d6);
            margin: 0;
            padding: 0;
        }
        /* ---------- NAVBAR ---------- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 0 0 15px 15px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar h1 {
            color: #e65100;
            font-size: 26px;
            margin: 0;
        }
        .navbar .nav-links {
            display: flex;
            gap: 15px;
        }
        .navbar .nav-links a {
            background: #ffb74d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .navbar .nav-links a:hover {
            background: #ff9800;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        /* ---------- CONTAINER ---------- */
        .container {
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }
        h3 {
            color: #e65100;
            text-align: center;
            margin-bottom: 20px;
        }

        /* ---------- CHARITY LIST ---------- */
        .charity-list {
            margin-top: 20px;
        }
        .charity-row {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .charity-row:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .charity-info {
            font-size: 18px;
            color: #00796b;
            font-weight: bold;
        }
        .total-donations {
            font-size: 16px;
            color: #333;
            margin-top: 5px;
        }

        /* ---------- DONATE BUTTON ---------- */
        .donate-btn {
            background-color: #00796b;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .donate-btn:hover {
            background-color: #004d40;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        /* ---------- LOGOUT BUTTON ---------- */
        .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .logout-btn:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        <div class="nav-links">
            <a class="logout-btn" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h3>Registered Charities</h3>
        <div class="charity-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="charity-row">
                        <div>
                            <div class="charity-info">
                                <?php echo htmlspecialchars($row['charity_name']); ?> ---- <?php echo htmlspecialchars($row['charity_address']); ?>
                            </div>
                            <div class="total-donations">
                                Total Donations Received: ₹<?php echo number_format($row['total_donations']); ?>
                            </div>
                        </div>
                        <a class="donate-btn" href="charity_detail.php?id=<?php echo $row['id']; ?>">Donate</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
