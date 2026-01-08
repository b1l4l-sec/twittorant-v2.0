<?php
session_start();
require_once '../db/connect.php';

$player_id = intval($_GET['player_id'] ?? 0);
if ($player_id <= 0) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT c.content, u.username 
    FROM teamup_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.player_id = ?
    ORDER BY c.created_at ASC
");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    // Optionally sanitize output here if needed
    $comments[] = [
      'content' => $row['content'],
      'username' => $row['username'],
    ];
}

echo json_encode($comments);
$stmt->close();
