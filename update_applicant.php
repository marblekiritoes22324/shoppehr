<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('debug_log.txt', date('[Y-m-d H:i:s] ') . "Incoming update: " . json_encode($data) . PHP_EOL, FILE_APPEND);

if (!$data || empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input or missing ID']);
    exit;
}

$id = $data['id'];
$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$position = trim($data['position'] ?? '');
$stage = trim($data['stage'] ?? 'Applied');
$interviewDate = !empty($data['interviewDate']) ? $data['interviewDate'] : null;
$interviewTime = !empty($data['interviewTime']) ? $data['interviewTime'] : null;
$interviewType = !empty($data['interviewType']) ? $data['interviewType'] : null;
$appliedDate = !empty($data['appliedDate']) ? $data['appliedDate'] : date('Y-m-d');

try {
    if (empty($firstName) && empty($lastName)) {
        // partial update (e.g. from schedule interview)
        $stmt = $pdo->prepare("UPDATE applications SET stage=?, interview_date=?, interview_time=?, interview_type=? WHERE id=?");
        $stmt->execute([$stage, $interviewDate, $interviewTime, $interviewType, $id]);
    } else {
        // full update - but we only update interview info if it's provided in the JSON
        $sql = "UPDATE applications SET first_name=?, last_name=?, email=?, phone_number=?, address=?, position_applied=?, stage=?, applied_date=?";
        $params = [$firstName, $lastName, $email, $phone, $address, $position, $stage, $appliedDate];
        
        if (isset($data['interviewDate'])) {
            $sql .= ", interview_date=?";
            $params[] = $interviewDate;
        }
        if (isset($data['interviewTime'])) {
            $sql .= ", interview_time=?";
            $params[] = $interviewTime;
        }
        if (isset($data['interviewType'])) {
            $sql .= ", interview_type=?";
            $params[] = $interviewType;
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Applicant updated successfully.',
        'applicant' => [
            'id' => $id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'position' => $position,
            'stage' => $stage,
            'interviewDate' => $interviewDate,
            'interviewTime' => $interviewTime,
            'interviewType' => $interviewType,
            'appliedDate' => $appliedDate
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
