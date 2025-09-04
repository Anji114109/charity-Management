<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: user_login.php');
    exit();
}

// Check if 'id' exists in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM charities WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $charity = $result->fetch_assoc();

    if (!$charity) {
        die("Charity not found.");
    }
} else {
    die("Invalid or missing charity ID.");
}

// Fetch total donations
$stmtTotal = $conn->prepare("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE charity_id=?");
$stmtTotal->bind_param("i",$id);
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result()->fetch_assoc();
$totalDonations = $resTotal['total'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $user_email = $_SESSION['user_email'];

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
        echo "<script>alert('Please select a valid file.');</script>";
    } else {
        $uploadDir = __DIR__ . '/uploads/'; 
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $fileType = $_FILES['photo']['type'];
        $fileSize = $_FILES['photo']['size'];

        $allowedTypes = ['image/jpeg','image/png','application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type! Only JPG, PNG, PDF allowed.');</script>";
        } elseif ($fileSize > $maxSize) {
            echo "<script>alert('File size exceeds 5MB!');</script>";
        } elseif (!preg_match('/\d+/', $fileName)) {
            echo "<script>alert('File must contain UTR number in the filename!');</script>";
        } else {
            $uniqueFileName = uniqid() . "_" . $fileName;
            $targetPath = $uploadDir . $uniqueFileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO transactions (charity_id, user_email, amount, date, screenshot) VALUES (?, ?, ?, NOW(), ?)");
                $stmt->bind_param("isss", $id, $user_email, $amount, $uniqueFileName);
                $stmt->execute();

                echo "<script>alert('Transaction successful!'); window.location.href='user_dashboard.php';</script>";
                exit();
            } else {
                echo "<script>alert('File upload failed. Please try again.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Charity Details</title>
<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(to right, #e7dacE, #e7dacE);
    color: #333;
}

/* ---------- NAVBAR ---------- */
.header {
    background: linear-gradient(135deg,#ffecb3,#ffe0b2);
    padding: 20px 0;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    position: relative;
    color: #e65100;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
}

/* Logout Button */
.logout {
    position: absolute;
    right: 20px;
    top: 12px;
    background: #fff;
    color: #e65100;
    padding: 5px 12px; /* smaller */
    border-radius: 10px;
    font-weight: bold;
    font-size: 13px;
    text-decoration: none;
    transition: 0.3s;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}
.logout:hover {
    background: #ffe0b2;
    color: #000;
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.25);
}

/* ---------- CONTAINER ---------- */
.container {
    max-width: 700px;
    margin: 50px auto;
    background: linear-gradient(135deg,#fff3e0,#ffe0b2);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    text-align: center;
    transition: transform 0.3s;
}
.container:hover { transform: translateY(-5px); }

/* Charity Title */
h2 { color: #e65100; margin-bottom: 20px; }

/* Donation Summary */
.summary-card {
    background: linear-gradient(135deg,#fff9c4,#fff59d);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    margin-bottom: 30px;
    font-size: 22px;
    font-weight: bold;
}

/* Form Inputs */
input[type="file"], input[type="number"] {
    margin: 15px 0;
    padding: 10px;
    font-size: 16px;
    width: 100%;
}

/* Submit Button */
button {
    background-color: #f9b779;
    color: white;
    padding: 12px 20px;
    border: none;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
    margin-top: 15px;
}
button:hover { background-color: #e7dacE; color:black; }

/* Back to dashboard */
.back-dashboard {
    display:inline-block;
    margin-top: 20px;
    padding:10px 20px;
    background: #4b79a1;
    color:white;
    text-decoration:none;
    border-radius:8px;
}
.back-dashboard:hover { background:#283e51; }

.thankyou-msg { font-size: 18px; margin-bottom: 20px; color: #555; }
</style>
</head>
<body>

<div class="header">
    Charity Details
    <a href="user_dashboard.php" class="logout">Logout</a>
</div>

<div class="container">
    <h2><?php echo htmlspecialchars($charity['charity_name']); ?></h2>

    <div class="summary-card">
        Total Donations Received: <span id="donationCount">₹0</span>
    </div>

    <div class="thankyou-msg">
        Thank you for your generosity. Your donation brings hope and change.
    </div>

    <form method="POST" enctype="multipart/form-data" id="donationForm">
        <label for="amount"><strong>Enter Amount (₹):</strong></label>
        <input type="number" name="amount" id="amount" required min="1">

        <p><strong>Pay to:</strong> <?php echo htmlspecialchars($charity['charity_phone_number']); ?></p>
        <p><strong>Bank Account No:</strong> <?php echo htmlspecialchars($charity['charity_bank_number']); ?></p>

        <h3>Upload Payment Screenshot (UTR number in filename)</h3>
        <input type="file" name="photo" id="photo" required>

        <button type="submit">Submit</button>
    </form>

    <a href="user_dashboard.php" class="back-dashboard">Back to Dashboard</a>
</div>

<script>
// Counter animation
let count = 0;
const total = <?php echo $totalDonations ?: 0; ?>;
const donationElem = document.getElementById('donationCount');

function animateCount() {
    const increment = total / 100;
    if (count < total) {
        count += increment;
        if(count > total) count = total;
        donationElem.innerText = "₹" + Math.floor(count).toLocaleString();
        requestAnimationFrame(animateCount);
    }
}
animateCount();

// Validate UTR in filename
document.getElementById('donationForm').addEventListener('submit', function() {
    var fileInput = document.getElementById('photo');
    var fileName = fileInput.value;
    if (!/\d+/.test(fileName)) {
        alert('Filename must contain UTR number!');
        return false;
    }
});
</script>

</body>
</html>
