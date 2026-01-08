<?php
session_start();
require_once '../db/connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['team_up'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing team_up value']);
    exit();
}

$team_up = $data['team_up'] ? 1 : 0;

$stmt = $conn->prepare("UPDATE users SET team_up = ? WHERE id = ?");
$stmt->bind_param("ii", $team_up, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'team_up' => $team_up]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update status']);
}
$stmt->close();
