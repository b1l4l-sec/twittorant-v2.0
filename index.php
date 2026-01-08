<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

// Get current user info for header and role check
$current_user = null;
$user_role = 'user';
if ($user_id) {
    $stmt = $conn->prepare("SELECT username, avatar, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_username, $current_avatar, $user_role);
    $stmt->fetch();
    $current_user = ['username' => $current_username, 'avatar' => $current_avatar, 'role' => $user_role];
    $stmt->close();
}

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    // Search only for users by username or bio
    $search_param = "%$search%";
    $stmt = $conn->prepare("
        SELECT id, username, avatar, bio, created_at
        FROM users
        WHERE username LIKE ? OR bio LIKE ?
        ORDER BY username ASC
    ");
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $search_result = $stmt->get_result();
    $stmt->close();
} else {
    $query = "SELECT posts.*, users.username, users.avatar 
              FROM posts 
              JOIN users ON posts.user_id = users.id 
              ORDER BY posts.created_at DESC";
    $result = $conn->query($query);
}

// Fetch all liked posts by current user
$liked_posts = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT post_id FROM likes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $liked_posts[$row['post_id']] = true;
    }
    $stmt->close();
}

// Fetch like counts per post
$likes_counts = [];
$res = $conn->query("SELECT post_id, COUNT(*) as cnt FROM likes GROUP BY post_id");
while ($row = $res->fetch_assoc()) {
    $likes_counts[$row['post_id']] = $row['cnt'];
}

// Get notification count for badge
$notification_count = 0;
if ($user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($notification_count);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Twittorant - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/index.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar__top">
    <a href="index.php" class="navbar__logo">Twittorant</a>
    <button class="navbar__burger" id="burger" aria-label="Toggle menu" aria-expanded="false">
      ☰
    </button>
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
  <!-- User Header -->
  <?php if ($current_user): ?>
    <div class="user-header">
      <img src="img/<?= htmlspecialchars($current_user['avatar']) ?>" alt="Your avatar" class="user-header-avatar">
      <div class="user-header-info">
        <h3>Welcome back, <span class="username-highlight">@<?= htmlspecialchars($current_user['username']) ?></span></h3>
        <p>What's happening in the Valorant community today?</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Search Form -->
  <div class="search-section">
    <form method="GET" action="" class="search-form">
      <input type="text" name="search" placeholder="Search for players by username or bio..." value="<?= htmlspecialchars($search) ?>" class="search-input">
      <button type="submit" class="search-btn">Search</button>
      <?php if ($search): ?>
        <a href="index.php" class="clear-search">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if ($search !== ''): ?>
    <!-- Search Results -->
    <h2 class="page-title">Search Results for "<?= htmlspecialchars($search) ?>"</h2>
    
    <?php if ($search_result->num_rows === 0): ?>
      <div class="no-results">
        <p>No players found matching your search.</p>
      </div>
    <?php else: ?>
      <?php while ($user = $search_result->fetch_assoc()): ?>
        <div class="user-card">
          <div class="user-card-header">
            <img src="img/<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" class="user-card-avatar">
            <div class="user-card-info">
              <strong class="user-card-username">@<?= htmlspecialchars($user['username']) ?></strong>
              <span class="user-card-joined">Joined: <?= date('M Y', strtotime($user['created_at'])) ?></span>
            </div>
          </div>
          <?php if (!empty($user['bio'])): ?>
            <div class="user-card-bio">
              <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  <?php else: ?>
    <!-- Regular Posts Feed -->
    <h2 class="page-title">Latest Posts</h2>

    <?php while ($row = $result->fetch_assoc()):
      $post_id = $row['id'];
      $liked = isset($liked_posts[$post_id]);
      $like_count = $likes_counts[$post_id] ?? 0;
    ?>
      <div class="post-card">
        <div class="post-header">
          <img src="img/<?= htmlspecialchars($row['avatar']) ?>" alt="avatar" class="avatar">
          <div>
            <strong class="username">@<?= htmlspecialchars($row['username']) ?></strong><br>
            <span class="timestamp"><?= htmlspecialchars($row['created_at']) ?></span>
          </div>
        </div>

        <div class="post-content">
          <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
          <?php if ($row['image']): ?>
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="post image" class="post-image" />
          <?php endif; ?>
        </div>

        <div class="post-actions">
          <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-post-id="<?= $post_id ?>">
            <span class="like-icon">❤</span> <span class="like-count"><?= $like_count ?></span>
          </button>
          <button class="comment-toggle" data-post-id="<?= $post_id ?>">💬 Comments</button>
        </div>

        <div class="comment-section" id="comments-<?= $post_id ?>">
          <!-- JavaScript will inject comments here -->
        </div>

        <form class="comment-form" data-post-id="<?= $post_id ?>">
          <input type="text" name="content" placeholder="Add a comment..." required autocomplete="off" />
          <button type="submit">Send</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <!-- Server Info Section -->
  <div class="server-info-section">
    <div class="server-info-card">
      <div class="server-header">
        <img src="https://media.giphy.com/media/3o7TKwmnDgQb5jemjK/giphy.gif" alt="Valorant Server" class="server-avatar">
        <div class="server-details">
          <h3 class="server-name">Valorant Official Server</h3>
          <p class="server-status">🟢 Online</p>
          <p class="server-players">👥 Always players online</p>
          <p class="server-region">🌍 Global Server - Low Latency</p>
        </div>
      </div>
      <div class="server-description">
        <p>Join the official Valorant community server for competitive matches, team formations, and exclusive events. Connect with players worldwide and climb the ranks together!</p>
      </div>
      <div class="server-actions">
        <button class="join-server-btn" onclick="joinServer()">
          🎮 Join Server
        </button>
        <div class="server-stats">
          <span class="stat">⚡ Comps</span>
          <span class="stat">🏆 Ranked matches available</span>
          <span class="stat">🎯 24/7 active</span>
        </div>
      </div>
    </div>
  </div>

  <footer class="footer">
  <div class="footer-content">
    <p>&copy; <?= date("Y") ?> Twittorant. All rights reserved.</p>
    <p>Made for Valorant players. ❤️</p>
  </div>
  </footer>

</div>

<script>
  const burger = document.getElementById("burger");
  const menu = document.getElementById("navbarMenu");

  burger.addEventListener("click", function () {
    menu.classList.toggle("is-active");
  });

  function joinServer() {
    alert("Redirecting to Discord server... Make sure you have the game installed!");
    // In a real implementation, this would open the game or Discord server
    window.open("https://discord.gg/ER4x5PxA", "_blank");
  }
</script>
<script src="js/main.js"></script>

</body>
</html>