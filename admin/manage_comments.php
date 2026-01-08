<?php
require_once '../includes/admin_check.php';
require_once '../db/connect.php';

if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_comments.php");
    exit;
}

$sql = "SELECT comments.id, comments.content, comments.created_at, users.username, comments.post_id 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        ORDER BY comments.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Comments - Admin Panel</title>
  <link rel="stylesheet" href="../css/manage_comments.css" />
</head>
<body>
  <div class="container">
    <h1>Manage Comments</h1>

    <a href="index.php" class="btn-back">← Back to Admin Panel</a>

    <table class="comments-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Author</th>
          <th>Content</th>
          <th>Post ID</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($comment = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $comment['id'] ?></td>
            <td><?= htmlspecialchars($comment['username']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($comment['content'], 0, 50, '...')) ?></td>
            <td><?= $comment['post_id'] ?></td>
            <td><?= $comment['created_at'] ?></td>
            <td>
              <a href="?delete_comment=<?= $comment['id'] ?>" class="btn-delete" onclick="return confirm('Delete this comment?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
