<?php
// backend/post_announcement.php — FINAL (No Anonymous Mode)
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/connection.php';
if (!isset($conn)) {
  echo json_encode(['status'=>'error','message'=>'Database connection missing']);
  exit;
}

session_start();

function respond($data) {
  echo json_encode($data);
  exit;
}

if (!isset($_SESSION['user_id'])) {
  respond(['status'=>'error','message'=>'Not authenticated']);
}

$user_id = intval($_SESSION['user_id']);
$username = trim($_SESSION['username'] ?? '');

if ($username === '') {
  respond(['status'=>'error','message'=>'Invalid username in session']);
}

// ✅ Allowed categories
$allowed = ['general','events','academics','chillhub','confession','emergency'];

/* --------------------------------
   GET — Fetch all or category posts
-----------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $category = strtolower(trim($_GET['category'] ?? 'general'));
  if ($category !== 'general' && !in_array($category, $allowed)) {
    respond(['status'=>'error','message'=>'Invalid category']);
  }

  try {
    if ($category === 'general') {
      $sql = "SELECT id, user_id, username, content, category, created_at 
              FROM announcements ORDER BY created_at DESC";
      $stmt = $conn->prepare($sql);
    } else {
      $sql = "SELECT id, user_id, username, content, category, created_at 
              FROM announcements WHERE LOWER(TRIM(category)) = ? ORDER BY created_at DESC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $category);
    }

    if (!$stmt) respond(['status'=>'error','message'=>'DB prepare failed']);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    respond(['status'=>'success','data'=>$data]);

  } catch (Exception $e) {
    respond(['status'=>'error','message'=>'Fetch failed']);
  }
}

/* --------------------------------
   POST — Add or Delete announcement
-----------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // DELETE post
  if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    if ($delete_id <= 0) respond(['status'=>'error','message'=>'Invalid post ID']);

    // Verify post ownership
    $check = $conn->prepare("SELECT user_id FROM announcements WHERE id = ?");
    $check->bind_param("i", $delete_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
      respond(['status'=>'error','message'=>'Post not found']);
    }

    $row = $res->fetch_assoc();
    if (intval($row['user_id']) !== $user_id) {
      respond(['status'=>'error','message'=>'You can delete only your own posts']);
    }

    $del = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $del->bind_param("i", $delete_id);
    if ($del->execute()) {
      respond(['status'=>'success','message'=>'Post deleted successfully']);
    } else {
      respond(['status'=>'error','message'=>'Delete failed']);
    }
  }

  // ADD new post
  $content = trim($_POST['content'] ?? '');
  $category = strtolower(trim($_POST['category'] ?? 'general'));

  if ($content === '') respond(['status'=>'error','message'=>'Content cannot be empty']);
  if (!in_array($category, $allowed)) $category = 'general';

  $stmt = $conn->prepare("INSERT INTO announcements (user_id, username, content, category) VALUES (?, ?, ?, ?)");
  if (!$stmt) respond(['status'=>'error','message'=>'DB prepare failed']);

  $stmt->bind_param("isss", $user_id, $username, $content, $category);
  $ok = $stmt->execute();

  if ($ok) respond(['status'=>'success','message'=>'Posted successfully']);
  else respond(['status'=>'error','message'=>'Insert failed']);
}

respond(['status'=>'error','message'=>'Unsupported method']);
?>
