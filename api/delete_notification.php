<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../db/connect.php';

$user_id = $_SESSION['user_id'];
$notification_id = intval($_GET['id'] ?? 0);

if ($notification_id) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
