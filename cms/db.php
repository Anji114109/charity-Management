<?php
$host = "sql300.infinityfree.com";       // InfinityFree host
$user = "if0_39864162";                  // Your MySQL username
$pass = "Charity114109";          // Your MySQL password
$db   = "if0_39864162_cmsdb";            // Your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
