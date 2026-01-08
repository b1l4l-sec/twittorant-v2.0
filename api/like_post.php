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

if ($post_id <= 0) {
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();
$already_liked = $result->num_rows > 0;
$stmt->close();

if ($already_liked) {
    // Unlike
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();
    $liked = false;
} else {
    // Like
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->close();
    $liked = true;

    // Create notification for post author
    $stmt = $conn->prepare("SELECT user_id, username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($post_author_id, $post_author_username);
    $stmt->fetch();
    $stmt->close();

    if ($post_author_id && $post_author_id != $user_id) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($liker_username);
        $stmt->fetch();
        $stmt->close();

        $message = "@{$liker_username} liked your post";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'like')");
        $stmt->bind_param("is", $post_author_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Get updated like count
$stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($like_count);
$stmt->fetch();
$stmt->close();

echo json_encode([
    'liked' => $liked,
    'like_count' => $like_count
]);
?>