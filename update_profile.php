<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json; charset=utf-8');

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Not authenticated']);
    exit;
}

$uid = intval($_SESSION['user_id']);
$newname = trim($_POST['username'] ?? '');
if ($newname === '' || strlen($newname) < 2) {
    echo json_encode(['status'=>'error','message'=>'Invalid name']);
    exit;
}

// sanitize name length
if (strlen($newname) > 100) $newname = substr($newname,0,100);

// update users table
$stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
if (!$stmt) { echo json_encode(['status'=>'error','message'=>'DB error']); exit; }
$stmt->bind_param("si", $newname, $uid);
$ok = $stmt->execute();
if (!$ok) { echo json_encode(['status'=>'error','message'=>'DB update failed']); exit; }

// update announcements.username for this user
$u = $conn->prepare("UPDATE announcements SET username = ? WHERE user_id = ?");
if ($u) {
  $u->bind_param("si", $newname, $uid);
  $u->execute();
  $u->close();
}

// update session
$_SESSION['username'] = $newname;

// if the form posted normally, redirect back
if (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') === false) {
  header("Location: ../announce.php");
  exit;
}

// otherwise return JSON (for ajax)
echo json_encode(['status'=>'success','message'=>'Name updated','username'=>$newname]);
exit;
