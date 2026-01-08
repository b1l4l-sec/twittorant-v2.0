<?php
session_start();
require_once '../db/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($post_id <= 0 || empty($content)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $content);

if ($stmt->execute()) {
    $comment_id = $stmt->insert_id;
    $stmt->close();

    // Get the new comment with user info
    $stmt = $conn->prepare("
        SELECT c.id, c.content, c.created_at, u.username, u.avatar 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $new_comment = $result->fetch_assoc();
    $stmt->close();

    // Create notification for post author
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_author_id);
    $stmt->fetch();
    $stmt->close();

    if ($post_author_id && $post_author_id != $user_id) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($commenter_username);
        $stmt->fetch();
        $stmt->close();

        $message = "@{$commenter_username} commented on your post";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'comment')");
        $stmt->bind_param("is", $post_author_id, $message);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode([
        'success' => true,
        'comment' => $new_comment
    ]);
} else {
    echo json_encode(['error' => 'Failed to add comment']);
}
?>