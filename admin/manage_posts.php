<?php
require_once '../includes/admin_check.php';
require_once '../db/connect.php';

if (isset($_GET['delete_post'])) {
    $id = intval($_GET['delete_post']);
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_posts.php");
    exit;
}

$sql = "SELECT posts.id, posts.content, posts.created_at, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Posts - Admin Panel</title>
  <link rel="stylesheet" href="../css/manage_posts.css" />
</head>
<body>
  <div class="container">
    <h1>Manage Posts</h1>

    <a href="index.php" class="btn-back">← Back to Admin Panel</a>

    <table class="posts-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Author</th>
          <th>Content</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($post = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $post['id'] ?></td>
            <td><?= htmlspecialchars($post['username']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($post['content'], 0, 50, '...')) ?></td>
            <td><?= $post['created_at'] ?></td>
            <td>
              <a href="?delete_post=<?= $post['id'] ?>" class="btn-delete" onclick="return confirm('Delete this post?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
