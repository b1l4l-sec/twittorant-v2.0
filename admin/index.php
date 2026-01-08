<?php
require_once '../includes/admin_check.php';
require_once '../db/connect.php';

// Fetch user stats
$sql = "SELECT 
          (SELECT COUNT(*) FROM users) as total_users,
          (SELECT COUNT(*) FROM posts) as total_posts,
          (SELECT COUNT(*) FROM comments) as total_comments
        ";
$result = $conn->query($sql);
$stats = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Panel - Twittorant</title>
  <link rel="stylesheet" href="../css/admin.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Admin Panel</h1>
      <p class="admin-subtitle">Monitor and manage platform activity</p>
    </header>

    <nav class="admin-nav">
      <a href="manage_users.php">Manage Users</a>
      <a href="manage_posts.php">Manage Posts</a>
      <a href="manage_comments.php">Manage Comments</a>
    </nav>

    <section class="admin-stats">
      <h2>Site Statistics</h2>
      <ul>
        <li><strong>Total Users:</strong> <?= htmlspecialchars($stats['total_users']) ?></li>
        <li><strong>Total Posts:</strong> <?= htmlspecialchars($stats['total_posts']) ?></li>
        <li><strong>Total Comments:</strong> <?= htmlspecialchars($stats['total_comments']) ?></li>
      </ul>
    </section>

    <div class="admin-buttons">
      <a href="../profile.php">Back to Profile</a>
      <a href="../index.php">Back to Home</a>
    </div>
  </div>
</body>
</html>
