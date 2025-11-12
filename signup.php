<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json; charset=utf-8');

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'All fields required']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check duplicate email
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['username'] = $username;
    echo json_encode(['status' => 'success', 'message' => 'Signup successful']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
