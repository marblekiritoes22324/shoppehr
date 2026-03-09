<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$position = trim($data['position'] ?? '');
$stage = trim($data['stage'] ?? 'Applied');
$interviewDate = !empty($data['interviewDate']) ? $data['interviewDate'] : null;
$appliedDate = !empty($data['appliedDate']) ? $data['appliedDate'] : date('Y-m-d');

if (empty($firstName) || empty($lastName) || empty($position)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'First Name, Last Name, and Position are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO applications (first_name, last_name, email, phone_number, address, position_applied, stage, interview_date, applied_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $email, $phone, $address, $position, $stage, $interviewDate, $appliedDate]);
    
    $appId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Applicant added successfully.',
        'applicant' => [
            'id' => $appId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'position' => $position,
            'stage' => $stage,
            'interviewDate' => $interviewDate,
            'appliedDate' => $appliedDate
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
