<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$message = "";

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_bio = trim($_POST['bio'] ?? '');

    if ($new_username === '') {
        $message = "Username cannot be empty.";
    } else {
        $current_avatar = null;
        $last_update = null;

        // Get current avatar and last update
        $stmt = $conn->prepare("SELECT avatar, last_avatar_update FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_avatar, $last_update);
        $stmt->fetch();
        $stmt->close();

        $canUpdateAvatar = true;
        if ($last_update !== null) {
            $lastTime = new DateTime($last_update);
            $now = new DateTime();
            $interval = $lastTime->diff($now);
            if ($interval->m < 1 && $interval->y === 0) {
                $canUpdateAvatar = false;
            }
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            if (!$canUpdateAvatar) {
                $message = "You can only update your avatar once per month.";
            } else {
                $fileTmpPath = $_FILES['avatar']['tmp_name'];
                $fileName = basename($_FILES['avatar']['name']);
                $fileSize = $_FILES['avatar']['size'];
                $fileType = mime_content_type($fileTmpPath);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($fileType, $allowedTypes)) {
                    $message = "Only JPG, PNG, and GIF files are allowed for avatar.";
                } elseif ($fileSize > 2 * 1024 * 1024) {
                    $message = "Avatar file size must not exceed 2MB.";
                } else {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = 'avatar_' . $user_id . '.' . $ext;
                    $destPath = __DIR__ . '/img/' . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $current_avatar = $newFileName;
                        $stmt = $conn->prepare("UPDATE users SET last_avatar_update = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $message = "Error uploading avatar image.";
                    }
                }
            }
        }

        if (!$message) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, bio = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_username, $new_bio, $current_avatar, $user_id);
            if ($stmt->execute()) {
                $message = "Profile updated successfully.";
            } else {
                $message = "Failed to update profile.";
            }
            $stmt->close();
        }
    }
}

// Fetch updated user info including role
$stmt = $conn->prepare("SELECT username, email, avatar, team_up, created_at, COALESCE(bio, '') as bio, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $avatar, $team_up, $created_at, $bio, $role);
$stmt->fetch();
$stmt->close();

// Fetch posts based on user role
if ($role === 'admin') {
    // Admin: show posts they published
    $stmt = $conn->prepare("SELECT id, content, image, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
} else {
    // Regular user: show posts they liked
    $stmt = $conn->prepare("
        SELECT p.id, p.content, p.image, p.created_at, u.username as post_author
        FROM posts p 
        JOIN likes l ON p.id = l.post_id 
        JOIN users u ON p.user_id = u.id
        WHERE l.user_id = ? 
        ORDER BY l.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$posts = $stmt->get_result();
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($username) ?>'s Profile - Twittorant</title>
  <link rel="stylesheet" href="css/profile.css" />
</head>
<body>

<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar__top">
    <a href="index.php" class="navbar__logo">Twittorant</a>
    <button class="navbar__burger" id="burger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navbarMenu">
      ☰
    </button>
  </div>

  <ul class="navbar__menu" id="navbarMenu">
    <li><a href="index.php" class="navbar__link">Home</a></li>
    <?php if ($role === 'admin'): ?>
      <li><a href="post.php" class="navbar__link">New Post</a></li>
    <?php endif; ?>
    <li><a href="team-up.php" class="navbar__link">Team Up</a></li>
    <li><a href="profile.php" class="navbar__link active" aria-current="page">My Profile</a></li>
    <li class="navbar__notification">
      <a href="notifications.php" class="navbar__link navbar__link--notification" aria-label="Notifications">
        Notifications
        <?php if ($notification_count > 0): ?>
          <span class="navbar__badge" aria-live="polite" aria-atomic="true"><?= $notification_count ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li><a href="logout.php" class="navbar__link navbar__link--logout">Logout</a></li>
  </ul>
</nav>

<div class="container profile-container">
  <section class="profile-header" aria-label="User profile details">
    <img src="img/<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($username) ?>'s avatar" class="profile-avatar" />
    <div class="profile-info">
      <h2 class="profile-username"><?= htmlspecialchars($username) ?></h2>
      <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
      <p><strong>Joined:</strong> <?= date('F j, Y', strtotime($created_at)) ?></p>
      <p><strong>Looking to team up:</strong>
        <span class="status-indicator <?= $team_up ? 'active' : 'inactive' ?>"><?= $team_up ? 'Yes' : 'No' ?></span>
      </p>

      <?php if ($role === 'admin'): ?>
        <a href="admin/index.php" class="admin-btn" role="button" aria-label="Go to Admin Panel">Go to Admin Panel</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="profile-edit-section" aria-label="Edit your profile">
    <h3>Edit Profile</h3>
    <?php if ($message): ?>
      <p class="message" role="alert"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form id="profile-form" method="POST" enctype="multipart/form-data" action="" class="profile-form" novalidate>
      <label for="username"><strong>Username:</strong></label>
      <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required autocomplete="username" />

      <label for="bio"><strong>Bio:</strong></label>
      <textarea name="bio" id="bio" rows="4" placeholder="Tell us about yourself"><?= htmlspecialchars($bio) ?></textarea>

      <label for="avatar"><strong>Change Avatar:</strong></label>
      <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/gif" />

      <button type="submit" class="btn-primary">Save Changes</button>
    </form>
  </section>

  <section class="profile-posts-section" aria-label="Your posts">
    <h3><?= $role === 'admin' ? 'Your Published Posts:' : 'Posts You Liked:' ?></h3>
    <?php if ($posts->num_rows === 0): ?>
      <div class="no-posts-message" aria-live="polite">
        <?= $role === 'admin' ? "You haven't published anything yet." : "You haven't liked any posts yet." ?>
      </div>
    <?php else: ?>
      <?php while ($post = $posts->fetch_assoc()): ?>
        <article class="post-card" aria-label="Post from <?= date('F j, Y', strtotime($post['created_at'])) ?>">
          <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if ($post['image']): ?>
            <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="Post image" class="post-image" loading="lazy" />
          <?php endif; ?>
          <div class="post-meta">
            <?php if ($role !== 'admin' && isset($post['post_author'])): ?>
              <small class="post-author">By @<?= htmlspecialchars($post['post_author']) ?></small>
            <?php endif; ?>
            <small class="post-date">
              <?= $role === 'admin' ? 'Published' : 'Liked' ?> on <?= date('M d, Y H:i', strtotime($post['created_at'])) ?>
            </small>
          </div>
        </article>
      <?php endwhile; ?>
    <?php endif; ?>
  </section>
</div>

<script>
  // Burger menu toggle for mobile
  const burger = document.getElementById('burger');
  const menu = document.getElementById('navbarMenu');

  burger.addEventListener('click', () => {
    const expanded = burger.getAttribute('aria-expanded') === 'true' || false;
    burger.setAttribute('aria-expanded', !expanded);
    menu.classList.toggle('is-active');
  });
</script>

</body>
</html>