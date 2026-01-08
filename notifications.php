<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'];

// Get current user role for navbar
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_role);
$stmt->fetch();
$stmt->close();

// Mark all as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Get notifications
$stmt = $conn->prepare("SELECT id, message, type, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Notification count (always 0 after marking read)
$notification_count = 0;
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Notifications - Twittorant</title>
  <link rel="stylesheet" href="css/notifications.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar__top">
    <a href="index.php" class="navbar__logo">Twittorant</a>
    <button class="navbar__burger" id="burger" aria-label="Toggle menu" aria-expanded="false">☰</button>
  </div>
  <ul class="navbar__menu" id="navbarMenu">
    <li><a href="index.php" class="navbar__link">Home</a></li>
    <?php if ($user_role === 'admin'): ?>
      <li><a href="post.php" class="navbar__link">New Post</a></li>
    <?php endif; ?>
    <li><a href="team-up.php" class="navbar__link">Team Up</a></li>
    <li><a href="profile.php" class="navbar__link">My Profile</a></li>
    <li class="navbar__notification">
      <a href="notifications.php" class="navbar__link navbar__link--notification">
        Notifications
        <?php if ($notification_count > 0): ?>
          <span class="navbar__badge"><?= $notification_count ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li><a href="logout.php" class="navbar__link navbar__link--logout">Logout</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Notifications</h2>
  <?php if ($result->num_rows === 0): ?>
    <div class="empty-state">
      <h3>No notifications yet</h3>
      <p>When someone likes your posts or comments, you'll see notifications here.</p>
    </div>
  <?php else: ?>
    <ul>
      <?php while ($row = $result->fetch_assoc()):
        $icon = '<i class="fa-regular fa-bell"></i>';
        if ($row['type'] === 'like') $icon = '<i class="fa-solid fa-heart" style="color:#ff6b6b;"></i>';
        if ($row['type'] === 'comment') $icon = '<i class="fa-solid fa-comment" style="color:#4ecdc4;"></i>';
        if ($row['type'] === 'team') $icon = '<i class="fa-solid fa-users" style="color: #000;;"></i>';
      ?>
        <li class="notification-<?= htmlspecialchars($row['type']) ?>" data-id="<?= $row['id'] ?>">
          <div class="left">
            <span class="icon"><?= $icon ?></span>
            <span class="message"><?= htmlspecialchars($row['message']) ?></span>
            <small><?= date('F j, Y, g:i a', strtotime($row['created_at'])) ?></small>
          </div>
          <button class="delete-btn" onclick="deleteNotification(<?= $row['id'] ?>)">✖</button>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>
</div>

<script>
function deleteNotification(id) {
  fetch('api/delete_notification.php?id=' + id, { method: 'GET' })
    .then(res => res.text())
    .then(() => {
      const el = document.querySelector(`li[data-id='${id}']`);
      if (el) el.remove();
    });
}

document.getElementById("burger").addEventListener("click", function () {
  document.getElementById("navbarMenu").classList.toggle("is-active");
});
</script>

<script src="js/main.js"></script>
</body>
</html>
