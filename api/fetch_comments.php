<?php
session_start();
require_once '../db/connect.php';

header('Content-Type: application/json');

$post_id = (int)($_GET['post_id'] ?? 0);
$limit = min((int)($_GET['limit'] ?? 10), 50);
$offset = max((int)($_GET['offset'] ?? 0), 0);

if ($post_id <= 0) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// Get total comment count
$stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

// Get comments with pagination
$stmt = $conn->prepare("
    SELECT c.content, c.created_at, u.username, u.avatar 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $post_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

echo json_encode([
    'comments' => $comments,
    'total' => $total,
    'has_more' => ($offset + $limit) < $total
]);
?>