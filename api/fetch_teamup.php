<?php
require_once '../db/connect.php';

$query = "SELECT team_ups.*, users.username, users.avatar 
          FROM team_ups 
          JOIN users ON team_ups.user_id = users.id 
          ORDER BY team_ups.created_at DESC";

$result = $conn->query($query);

$teamups = [];
while ($row = $result->fetch_assoc()) {
    $teamups[] = $row;
}

header('Content-Type: application/json');
echo json_encode($teamups);
