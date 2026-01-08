<?php
session_start();
require_once '../db/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$player_id = intval($_POST['player_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($player_id <= 0 || $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO teamup_comments (player_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $player_id, $user_id, $content);
$success = $stmt->execute();
$stmt->close();

if (!$success) {
    echo json_encode(['error' => 'Failed to post comment']);
    exit;
}

// Get commenter's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($commenter_username);
$stmt->fetch();
$stmt->close();

// Send notification if player is not commenting on their own profile
if ($player_id !== $user_id) {
    $message = "@$commenter_username commented on your team-up request.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $player_id, $message);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true]);
