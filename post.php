<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'];

// Check if user is admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_role);
$stmt->fetch();
$stmt->close();

// Redirect non-admin users
if ($user_role !== 'admin') {
    header("Location: index.php");
    exit();
}

$success = "";
$error = "";

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $image = null;

    // Check content
    if (empty($content)) {
        $error = "Post content cannot be empty.";
    } else {
        // Optional image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid("img_") . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
        }

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Failed to publish post.";
        }
    }
}

// Get notification count for navbar
$notification_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
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
  <title>New Post - Twittorant</title>
  <link rel="stylesheet" href="css/post.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
    <li><a href="post.php" class="navbar__link">New Post</a></li>
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

<!-- Main container -->
<div class="container" role="main">
  <h2>Create a New Post</h2>

  <?php if ($error): ?>
    <p style="color: #ff4655; font-weight: 700; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <textarea
      name="content"
      placeholder="What's on your mind, agent?"
      rows="4"
      required
      autocomplete="off"
    ></textarea>

    <label for="image-upload" style="margin-top: 15px; display: block; color: #eee;">
      Optional Image:
    </label>
    <input type="file" name="image" id="image-upload" accept="image/*" />

    <button type="submit">Post</button>
  </form>
</div>

<script>
  const burger = document.getElementById("burger");
  const menu = document.getElementById("navbarMenu");

  burger.addEventListener("click", function () {
    menu.classList.toggle("is-active");
  });
</script>
<script src="js/main.js"></script>

</body>
</html>