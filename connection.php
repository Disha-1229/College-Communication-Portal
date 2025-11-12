<?php
$servername = "localhost";
$username = "root";
$password = ""; // keep blank if using XAMPP default
$dbname = "campverse_db"; // ✅ FIXED name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
