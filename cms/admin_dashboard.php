<?php
session_start();
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

require 'db.php';

// ------------------- HANDLE DELETION -------------------

if(isset($_POST['action'])){
    if($_POST['action']=='delete_user'){
        $email=$_POST['email'] ?? '';
        if($email){
            $stmt=$conn->prepare("DELETE FROM users WHERE email=?");
            $stmt->bind_param("s",$email);
            $stmt->execute();
        }
        exit;
    } elseif($_POST['action']=='delete_volunteer'){
        $charity_name=$_POST['charity_name'] ?? '';
        if($charity_name){
            $stmt=$conn->prepare("DELETE FROM charities WHERE charity_name=?");
            $stmt->bind_param("s",$charity_name);
            $stmt->execute();
        }
        exit;
    }
}

// ------------------- AJAX SECTION -------------------
if(isset($_GET['section'])){
    $section=$_GET['section'];
    echo '<div class="cards-row">';
    
    if($section=='users'){
        $result=$conn->query("SELECT * FROM users");
        if($result->num_rows>0){
            while($row=$result->fetch_assoc()){
                $username=htmlspecialchars($row['username'] ?? 'User');
                $email=htmlspecialchars($row['email'] ?? '');
                echo "<div class='card'>
                        <h3>$username</h3>
                        <p>Email: $email</p>
                        <button onclick=\"deleteUser('$email', this)\">Delete</button>
                      </div>";
            }
        } else {
            echo "<div class='no-data'>No Users Found</div>";
        }
    } elseif($section=='volunteers'){
        $result=$conn->query("SELECT DISTINCT charity_name,charity_address FROM charities");
        if($result->num_rows>0){
            while($row=$result->fetch_assoc()){
                $charity_name=htmlspecialchars($row['charity_name'] ?? '');
                $charity_address=htmlspecialchars($row['charity_address'] ?? '');
                echo "<div class='card'>
                        <h3>$charity_name</h3>
                        <p>Address: $charity_address</p>
                        <button onclick=\"deleteVolunteer('$charity_name', this)\">Delete</button>
                      </div>";
            }
        } else {
            echo "<div class='no-data'>No Volunteers Found</div>";
        }
    } elseif($section=='transactions'){
        $sql="SELECT t.amount,t.date,t.user_email,c.charity_name FROM transactions t LEFT JOIN charities c ON t.charity_id=c.id ORDER BY t.date DESC";
        $result=$conn->query($sql);
        if($result->num_rows>0){
            while($row=$result->fetch_assoc()){
                $amount=htmlspecialchars($row['amount'] ?? '0');
                $date=htmlspecialchars(date('d M Y, h:i A', strtotime($row['date'] ?? '')));
                $user_email=htmlspecialchars($row['user_email'] ?? '');
                $charity_name=htmlspecialchars($row['charity_name'] ?? 'Unknown Charity');
                echo "<div class='card'>
                        <h3>₹$amount</h3>
                        <p>Date: $date</p>
                        <p>From: $user_email</p>
                        <p>To Charity: $charity_name</p>
                      </div>";
            }
        } else {
            echo "<div class='no-data'>No Transactions Found</div>";
        }
    }
    echo '</div>';
    exit;
}

// ------------------- AJAX ENDPOINT FOR COUNTS -------------------
if(isset($_GET['count'])){
    $type=$_GET['count'];
    if($type=='users'){
        $res=$conn->query("SELECT COUNT(*) as cnt FROM users");
        $row=$res->fetch_assoc();
        echo $row['cnt'];
    } elseif($type=='charities'){
        $res=$conn->query("SELECT COUNT(*) as cnt FROM charities");
        $row=$res->fetch_assoc();
        echo $row['cnt'];
    } elseif($type=='transactions'){
        $res=$conn->query("SELECT COUNT(*) as cnt FROM transactions");
        $row=$res->fetch_assoc();
        echo $row['cnt'];
    } elseif($type=='chart'){
        $data=[];
        $res=$conn->query("SELECT MONTH(date) as month, SUM(amount) as total FROM transactions GROUP BY MONTH(date) ORDER BY MONTH(date)");
        while($row=$res->fetch_assoc()){
            $data[(int)$row['month']] = (float)$row['total'];
        }
        echo json_encode($data);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
*{box-sizing:border-box;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
body{margin:0; padding:0; background:linear-gradient(135deg,#EDE3C7,#CFC0A3); height:100vh;}
h1,h2,h3,p{margin:0;}
a{text-decoration:none;}
button{cursor:pointer; transition:0.3s;}

/* ---------- NAVBAR ---------- */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:rgba(255,255,255,0.2);
    backdrop-filter:blur(10px);
    padding:20px 40px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    border-radius:0 0 15px 15px;
}
.navbar h1{color:#e65100;font-size:26px;}
.navbar a{
    background:#f44336;
    color:white;
    padding:10px 18px;
    border-radius:8px;
    font-weight:bold;
}
.navbar a:hover{background:#d32f2f;}

/* ---------- LAYOUT ---------- */
.container{display:flex;height:calc(100vh-80px);}
.sidebar{
    width:220px;
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(10px);
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:15px;
    box-shadow:2px 0 10px rgba(0,0,0,0.1);
    border-radius:0 15px 15px 0;
}
.sidebar button{
    padding:12px;
    font-size:16px;
    border:none;
    border-radius:12px;
    background:#ffe0b2;
    color:#333;
    font-weight:bold;
}
.sidebar button:hover{
    background:#ffcc80;
    transform:scale(1.05);
}

/* ---------- CONTENT ---------- */
#content{
    flex:1;
    padding:30px;
    overflow-y:auto;
}
#content h2{
    text-align:center;
    color:#e65100;
    font-size:28px;
}

/* ---------- CARDS ROW ---------- */
.cards-row{
    margin-top:40px;
    display:flex;
    gap:20px;
    flex-wrap:wrap;
    justify-content:center;
}
.card{
    flex:1;
    min-width:220px;
    background:rgba(255,255,255,0.3);
    backdrop-filter:blur(8px);
    border-radius:15px;
    padding:20px;
    text-align:center;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
    transition:transform 0.3s;
    cursor:pointer;
}
.card:hover{transform:translateY(-5px);}
.card h3{color:#e65100;margin-bottom:10px;}
.card p{color:#333;font-size:16px;margin:5px 0;}
.card button{
    margin-top:10px;
    padding:6px 12px;
    font-size:14px;
    background:#f44336;
    color:white;
    border:none;
    border-radius:6px;
}
.card button:hover{background:#d32f2f;}

/* ---------- TRANSACTION GRAPH ---------- */
#chart-container{
    margin-top:40px;
    background:rgba(255,255,255,0.3);
    padding:20px;
    border-radius:15px;
    backdrop-filter:blur(8px);
}

/* ---------- NO DATA STYLE ---------- */
.no-data{
    text-align:center;
    font-size:22px;
    color:#e65100;
    font-weight:bold;
    padding:40px;
}

/* ---------- RESPONSIVE ---------- */
@media(max-width:768px){
    .container{flex-direction:column;}
    .sidebar{width:100%; flex-direction:row; justify-content:space-around; border-radius:0 0 15px 15px;}
    .cards-row{justify-content:center;}
}
</style>
</head>
<body>

<div class="navbar">
    <h1>Charity Admin Dashboard</h1>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <div class="sidebar">
        <button onclick="loadDashboard()">Dashboard</button>
        <button onclick="loadSection('users')">Users</button>
        <button onclick="loadSection('volunteers')">Volunteers</button>
        <button onclick="loadSection('transactions')">Transactions</button>
    </div>

    <div id="content">
        <h2>Dashboard Overview</h2>
        <div class="cards-row">
            <div class="card" id="total-users" onclick="loadSection('users')">Users: 0</div>
            <div class="card" id="total-charities" onclick="loadSection('volunteers')">Charities: 0</div>
            <div class="card" id="total-transactions" onclick="loadSection('transactions')">Transactions: 0</div>
        </div>
        <div id="chart-container">
            <canvas id="transactionChart" width="400" height="150"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function fetchCounts(){
    const u=await fetch("?count=users"); 
    const c=await fetch("?count=charities"); 
    const t=await fetch("?count=transactions");
    document.getElementById('total-users').innerText="Users: "+await u.text();
    document.getElementById('total-charities').innerText="Charities: "+await c.text();
    document.getElementById('total-transactions').innerText="Transactions: "+await t.text();
}

async function renderChart(){
    const res=await fetch("?count=chart");
    const data=await res.json();
    const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const chartData=[];
    for(let i=1;i<=12;i++){ chartData.push(data[i]||0); }

    const ctx=document.getElementById('transactionChart').getContext('2d');
    new Chart(ctx,{
        type:'bar',
        data:{
            labels:months,
            datasets:[{
                label:'Monthly Donations',
                data:chartData,
                backgroundColor:'#FFB74D'
            }]
        },
        options:{
            responsive:true,
            plugins:{legend:{display:false}},
            scales:{
                y:{
                    beginAtZero:true,
                    max:3000,
                    ticks:{
                        stepSize:500
                    }
                }
            }
        }
    });
}

// Load dashboard initially
fetchCounts();
renderChart();

// ---------- LOAD SECTION ----------
function loadSection(section){
    fetch("?section="+section)
        .then(res=>res.text())
        .then(data=>{ document.getElementById('content').innerHTML=data; })
        .catch(err=>console.error(err));
}

// ---------- DASHBOARD BUTTON ----------
function loadDashboard(){
    document.getElementById('content').innerHTML=`
        <h2>Dashboard Overview</h2>
        <div class="cards-row">
            <div class="card" id="total-users" onclick="loadSection('users')">Users: 0</div>
            <div class="card" id="total-charities" onclick="loadSection('volunteers')">Charities: 0</div>
            <div class="card" id="total-transactions" onclick="loadSection('transactions')">Transactions: 0</div>
        </div>
        <div id="chart-container">
            <canvas id="transactionChart" width="400" height="150"></canvas>
        </div>
    `;
    fetchCounts();
    renderChart();
}

// ---------- DELETE FUNCTIONS ----------
function deleteUser(email, btn){
    if(confirm("Delete this user?")){
        fetch("",{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:"action=delete_user&email="+encodeURIComponent(email)
        }).then(()=>btn.parentElement.remove());
    }
}
function deleteVolunteer(charity_name, btn){
    if(confirm("Delete this charity?")){
        fetch("",{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:"action=delete_volunteer&charity_name="+encodeURIComponent(charity_name)
        }).then(()=>btn.parentElement.remove());
    }
}
</script>

</body>
</html>
