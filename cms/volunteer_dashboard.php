<?php
session_start();
require 'db.php'; // InfinityFree DB connection

if (!isset($_SESSION['volunteer_email'])) {
    header('Location: volunteer_login.php');
    exit();
}

$volunteer_email = $_SESSION['volunteer_email'];

// Fetch charity info and total donations using JOIN
$stmt = $conn->prepare("
    SELECT c.*, COALESCE(SUM(t.amount), 0) AS total_donations
    FROM charities c
    LEFT JOIN transactions t ON c.id = t.charity_id
    WHERE c.volunteer_email = ?
    GROUP BY c.id
");
$stmt->bind_param("s", $volunteer_email);
$stmt->execute();
$charity_result = $stmt->get_result();
$charity = $charity_result->fetch_assoc();

$transactions = false;
$total_transactions = 0;
$total_donors = 0;
if ($charity) {
    $charity_id = $charity['id'];
    $stmt2 = $conn->prepare("SELECT * FROM transactions WHERE charity_id = ? ORDER BY date DESC");
    $stmt2->bind_param("i", $charity_id);
    $stmt2->execute();
    $transactions = $stmt2->get_result();

    // Fetch summary stats
    $stmt3 = $conn->prepare("
        SELECT COUNT(*) as cnt, COUNT(DISTINCT user_email) as donors
        FROM transactions WHERE charity_id = ?
    ");
    $stmt3->bind_param("i", $charity_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result()->fetch_assoc();
    $total_transactions = $res3['cnt'];
    $total_donors = $res3['donors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Volunteer Dashboard</title>
<style>
    body {
        margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background:linear-gradient(to right, #fdf1e6, #fde6d6); color:#333;
    }

    /* Navbar */
    .navbar {
        display:flex; justify-content:space-between; align-items:center;
        padding: 20px 40px;
        background: linear-gradient(135deg, #FFB74D, #FF9800);
        color:#fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-radius:0 0 20px 20px;
        position: sticky; top:0; z-index:100;
    }
    .navbar h1 { font-size:28px; font-weight:bold; text-shadow:1px 1px 3px rgba(0,0,0,0.3); margin:0; }
    .navbar .logout-btn {
        background:#fff; color:#ff9800; font-weight:bold; padding:10px 20px; border-radius:12px;
        text-decoration:none; transition:0.3s ease; box-shadow:0 4px 10px rgba(0,0,0,0.2);
    }
    .navbar .logout-btn:hover { background:#ffe0b2; transform:translateY(-2px); box-shadow:0 6px 12px rgba(0,0,0,0.25); }

    /* Container */
    .container {
        max-width:900px; margin:60px auto; padding:40px 30px;
        background:linear-gradient(145deg, #ffffff, #fff7ed);
        border-radius:25px; box-shadow:0 10px 30px rgba(0,0,0,0.15);
    }

    /* Summary Cards */
    .summary {
        display:flex; justify-content:space-between; gap:20px; margin-bottom:40px;
    }
    .summary .card {
        flex:1; background:#fff3e0; padding:20px; border-radius:15px;
        text-align:center; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s;
    }
    .summary .card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,0.15);}
    .summary .card h3 { font-size:22px; color:#e65100; margin-bottom:10px; }
    .summary .card p { font-size:18px; color:#00796b; font-weight:bold; }

    h3, h4 {text-align:center; margin-bottom:25px; color:#e65100;}

    /* Charity Info */
    .charity-info {
        background:#fff3e0; padding:25px; border-radius:20px; margin-bottom:35px;
        text-align:left; box-shadow:0 4px 15px rgba(0,0,0,0.1);
    }
    .charity-info:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,0.15);}
    .charity-info p { font-size:18px; margin:10px 0; color:#00796b; font-weight:bold;}

    /* Transactions */
    .transactions-container { max-height:350px; overflow-y:auto; padding-right:10px; }
    .transaction {
        background:#fff8e1; padding:20px; border-radius:15px; margin-bottom:20px;
        box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s;
    }
    .transaction:hover { transform:translateY(-4px); box-shadow:0 8px 25px rgba(0,0,0,0.15);}
    .amount { color:#ff9800; font-size:22px; font-weight:bold; }
    .date, .email { font-size:16px; color:#555; margin-top:5px; }

    .no-data { text-align:center; font-size:22px; color:#999; margin-top:30px; font-style:italic; letter-spacing:1px; }
</style>
</head>
<body>

<div class="navbar">
    <h1>Volunteer Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="container">
    <?php if ($charity): ?>
        <!-- Summary Cards -->
        <div class="summary">
            <div class="card">
                <h3>Total Donations</h3>
                <p>₹<?php echo number_format($charity['total_donations']); ?></p>
            </div>
            <div class="card">
                <h3>Transactions</h3>
                <p><?php echo $total_transactions; ?></p>
            </div>
            <div class="card">
                <h3>Total Donors</h3>
                <p><?php echo $total_donors; ?></p>
            </div>
        </div>

        <h3><?php echo htmlspecialchars($charity['charity_name']); ?></h3>
        <div class="charity-info">
            <p><strong>Address:</strong> <?php echo htmlspecialchars($charity['charity_address']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($charity['charity_phone_number']); ?></p>
        </div>

        <h4>Donations Received:</h4>
        <div class="transactions-container">
        <?php if ($transactions && $transactions->num_rows > 0): ?>
            <?php while ($row = $transactions->fetch_assoc()): ?>
                <div class="transaction">
                    <div class="amount">₹<?php echo htmlspecialchars($row['amount']); ?></div>
                    <div class="date">Date: <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['date']))); ?></div>
                    <div class="email">From: <?php echo htmlspecialchars($row['user_email']); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">No transactions found for your charity yet.</div>
        <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-data">You have not registered a charity yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
