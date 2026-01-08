<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'db/connect.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user info
$stmt = $conn->prepare("SELECT username, avatar, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $current_avatar, $current_bio);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');

    // Get last avatar update
    $last_update = null;
    $stmt = $conn->prepare("SELECT last_avatar_update FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($last_update);
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

    // Handle avatar upload
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
                $message = "Only JPG, PNG, GIF files allowed for avatar.";
            } elseif ($fileSize > 2 * 1024 * 1024) {
                $message = "Avatar file size should not exceed 2MB.";
            } else {
                // Delete the old avatar if not default
                if ($current_avatar && $current_avatar !== 'default_avatar.png') {
                    $oldAvatarPath = __DIR__ . '/img/' . $current_avatar;
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

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
                    $message = "Error uploading avatar.";
                }
            }
        }
    }

    if (!$message) {
        $stmt = $conn->prepare("UPDATE users SET bio = ?, avatar = ? WHERE id = ?");
        $stmt->bind_param("ssi", $bio, $current_avatar, $user_id);
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Failed to update profile.";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Profile - Twittorant</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .profile-edit {
      max-width: 600px;
      margin: 20px auto;
    }
    .profile-edit label {
      display: block;
      margin-top: 15px;
    }
    .profile-edit input[type="text"],
    .profile-edit textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
    }
    .profile-edit img {
      display: block;
      margin-top: 10px;
      max-width: 120px;
      border-radius: 50%;
      object-fit: cover;
    }
    .message {
      margin-top: 10px;
      color: green;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container profile-edit">
  <h2>Edit Profile for <?= htmlspecialchars($username) ?></h2>

  <?php if ($message): ?>
    <p class="<?= strpos($message, 'failed') !== false || strpos($message, 'error') !== false ? 'error' : 'message' ?>">
      <?= htmlspecialchars($message) ?>
    </p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label for="bio">Bio:</label>
    <textarea name="bio" id="bio" rows="4"><?= htmlspecialchars($current_bio) ?></textarea>

    <label for="avatar">Avatar:</label>
    <input type="file" name="avatar" id="avatar" accept="image/*">
    <?php if ($current_avatar): ?>
      <img src="img/<?= htmlspecialchars($current_avatar) ?>" alt="Current Avatar">
    <?php endif; ?>

    <button type="submit" style="margin-top:20px;">Save Changes</button>
  </form>
</div>
</body>
</html>
