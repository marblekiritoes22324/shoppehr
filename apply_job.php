<?php
header('Content-Type: application/json');
require 'db.php';

// Get POST data
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$position = trim($_POST['position'] ?? '');
$coverLetter = trim($_POST['coverLetter'] ?? '');
$stage = 'Applied';
$appliedDate = date('Y-m-d');

if (empty($firstName) || empty($lastName) || empty($email) || empty($position)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

// Handle File Upload
$resumePath = null;
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileTmpPath = $_FILES['resume']['tmp_name'];
    $fileName = $_FILES['resume']['name'];
    $fileSize = $_FILES['resume']['size'];
    $fileType = $_FILES['resume']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    // Sanitize file name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $dest_path = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        $resumePath = $dest_path;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error moving the uploaded file.']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO applications (first_name, last_name, email, phone_number, address, position_applied, stage, applied_date, resume_path, cover_letter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $email, $phone, $address, $position, $stage, $appliedDate, $resumePath, $coverLetter]);
    
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'You have already applied with this email address.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
