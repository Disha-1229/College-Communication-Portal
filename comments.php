<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json; charset=utf-8');

// Catch PHP warnings/errors as JSON
function safe_json($arr) {
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // Ensure user is logged in
  if (!isset($_SESSION['user_id'])) {
    safe_json(['status' => 'error', 'message' => 'User not logged in']);
  }

  $user_id = $_SESSION['user_id'];
  $method = $_SERVER['REQUEST_METHOD'];

  /* ------------------------------
      ADD NEW COMMENT
  ------------------------------ */
  if ($method === 'POST') {
    $announcement_id = $_POST['announcement_id'] ?? null;
    $content = trim($_POST['content'] ?? '');

    if (empty($announcement_id) || empty($content)) {
      safe_json(['status' => 'error', 'message' => 'Missing data']);
    }

    $stmt = $pdo->prepare("INSERT INTO comments (announcement_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$announcement_id, $user_id, $content]);

    safe_json(['status' => 'success', 'message' => 'Comment added successfully']);
  }

  /* ------------------------------
      FETCH COMMENTS
  ------------------------------ */
  if ($method === 'GET') {
    $announcement_id = $_GET['announcement_id'] ?? null;
    if (!$announcement_id) {
      safe_json(['status' => 'error', 'message' => 'Missing announcement_id']);
    }

    $stmt = $pdo->prepare("
      SELECT c.id, c.content, c.created_at, u.username 
      FROM comments c
      LEFT JOIN users u ON c.user_id = u.id
      WHERE c.announcement_id = ?
      ORDER BY c.created_at ASC
    ");
    $stmt->execute([$announcement_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    safe_json(['status' => 'success', 'data' => $comments]);
  }

  /* ------------------------------
      DELETE COMMENT
  ------------------------------ */
  if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $comment_id = $_DELETE['id'] ?? null;

    if (!$comment_id) {
      safe_json(['status' => 'error', 'message' => 'Missing comment ID']);
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]);

    safe_json(['status' => 'success', 'message' => 'Comment deleted']);
  }

  safe_json(['status' => 'error', 'message' => 'Invalid request method']);

} catch (Exception $e) {
  safe_json(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
