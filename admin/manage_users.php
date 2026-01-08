<?php
require_once '../includes/admin_check.php';
require_once '../db/connect.php';

// Delete user if requested
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    // Prevent deleting yourself
    if ($id !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_users.php");
        exit;
    } else {
        echo "<p style='color: red; text-align:center; margin: 20px;'>You cannot delete yourself.</p>";
    }
}

// Fetch users list
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Users - Admin Panel</title>
  <link rel="stylesheet" href="../css/manage_users.css" />
</head>
<body>
  <div class="container">
    <h1>Manage Users</h1>

    <a href="index.php" class="btn-back">← Back to Admin Panel</a>

    <table class="users-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
              <?php if ($user['id'] != $_SESSION['user_id']): ?>
                <a href="?delete_user=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
              <?php else: ?>
                <span class="self-label">(You)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
