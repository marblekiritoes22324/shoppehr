<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$title = trim($data['title'] ?? '');
$department = trim($data['department'] ?? '');
$requirements = trim($data['requirements'] ?? '');
$status = trim($data['status'] ?? 'Open');

if (empty($title) || empty($department)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Job title and department are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO jobs (title, department, requirements, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $department, $requirements, $status]);
    
    $jobId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Job posted successfully.',
        'job' => [
            'id' => $jobId,
            'title' => $title,
            'department' => $department,
            'status' => $status
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
