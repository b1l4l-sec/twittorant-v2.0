<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$user_role = 'user';

// Get current user role
if ($user_id) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_role);
    $stmt->fetch();
    $stmt->close();
}

$message = "";

// Handle toggle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_team_up'])) {
    $new_status = isset($_POST['team_up']) && $_POST['team_up'] === '1' ? 1 : 0;

    // If disabling team up, delete old comments
    if ($new_status === 0) {
        $delete_stmt = $conn->prepare("DELETE FROM teamup_comments WHERE player_id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    // Update team up status
    $stmt = $conn->prepare("UPDATE users SET team_up = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);
    $stmt->execute();
    $message = $stmt->affected_rows > 0
        ? ($new_status === 1 ? "You're now looking for a team!" : "Request deactivated. Old comments removed.")
        : "Failed to update status.";
    $stmt->close();
}

// Get current user status
$stmt = $conn->prepare("SELECT team_up FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_status);
$stmt->fetch();
$stmt->close();

// Fetch players currently looking to team up
$sql = "
  SELECT DISTINCT u.id, u.username, u.avatar, u.created_at
  FROM users u
  WHERE u.team_up = 1 
  ORDER BY u.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$players_result = $stmt->get_result();
$stmt->close();

// Get notification count for navbar
$notification_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($notification_count);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Team Up - Twittorant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/teamup.css" />
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
  <h2>Looking for Team</h2>

  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" class="teamup-form">
    <label>
      <input type="checkbox" name="team_up" value="1" <?= $current_status ? "checked" : "" ?>>
      I'm looking for a team
    </label>
    <button type="submit" name="toggle_team_up">Update Status</button>
  </form>

  <hr>

  <h3>Players Currently Looking to Team Up or With Requests:</h3>
  <?php if ($players_result->num_rows === 0): ?>
    <p>No players are currently looking to team up or have posted requests.</p>
  <?php else: ?>
    <?php while ($player = $players_result->fetch_assoc()): ?>
      <div class="player-card <?= ($player['id'] == $user_id) ? 'highlight' : '' ?>" data-player-id="<?= (int)$player['id'] ?>">
        <div class="player-info">
          <img src="img/<?= htmlspecialchars($player['avatar']) ?>" alt="avatar" />
          <strong><?= htmlspecialchars($player['username']) ?></strong> — Joined: <?= date('M Y', strtotime($player['created_at'])) ?>
        </div>

        <div class="comments" id="comments-<?= (int)$player['id'] ?>">Loading comments...</div>

        <form class="comment-form" data-player-id="<?= (int)$player['id'] ?>">
          <input type="text" name="comment_content" placeholder="Say hi or ask to team up..." required autocomplete="off" />
          <button type="submit">Send</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<script>
  async function fetchComments(playerId) {
    const container = document.getElementById('comments-' + playerId);
    container.textContent = 'Loading comments...';
    try {
      const response = await fetch(`api/fetch_teamup_comments.php?player_id=${playerId}`);
      if (!response.ok) throw new Error();
      const comments = await response.json();
      container.innerHTML = comments.length
        ? comments.map(c => `<div class="comment"><strong>${c.username}:</strong> ${c.content}</div>`).join('')
        : '<p>No comments yet.</p>';
    } catch {
      container.textContent = 'Error loading comments.';
    }
  }

  document.querySelectorAll('.player-card').forEach(card => fetchComments(card.dataset.playerId));

  document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const playerId = form.dataset.playerId;
      const input = form.querySelector('input[name="comment_content"]');
      const content = input.value.trim();
      if (!content) return;

      try {
        const response = await fetch('api/post_teamup_comment.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `player_id=${encodeURIComponent(playerId)}&content=${encodeURIComponent(content)}`
        });
        const data = await response.json();
        if (data.success) {
          input.value = '';
          fetchComments(playerId);
        } else {
          alert(data.error || 'Failed to post comment');
        }
      } catch {
        alert('Error posting comment');
      }
    });
  });

  // Navbar toggle
  const burger = document.getElementById("burger");
  const menu = document.getElementById("navbarMenu");

  burger.addEventListener("click", function () {
    menu.classList.toggle("is-active");
  });
</script>

<script src="js/main.js"></script>
</body>
</html>
