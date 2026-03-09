<?php
// get_online_admins.php
require 'db.php';
header('Content-Type: application/json');

// Admins active in the last 5 minutes
$stmt = $pdo->prepare("SELECT name FROM users WHERE role = 'admin' AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['success' => true, 'admins' => $admins]);
?>
