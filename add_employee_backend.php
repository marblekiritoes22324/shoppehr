<?php
header('Content-Type: application/json');
require 'db.php';

// Ensure tables exist (specifically employees table for the new feature)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        employee_id VARCHAR(20) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        mobile VARCHAR(20),
        dob DATE,
        gender VARCHAR(10),
        address VARCHAR(255),
        city VARCHAR(100),
        state VARCHAR(100),
        zip VARCHAR(20),
        marital_status VARCHAR(20),
        nationality VARCHAR(50),
        position VARCHAR(100),
        department VARCHAR(100),
        status VARCHAR(20) DEFAULT 'Active',
        start_date DATE,
        manager VARCHAR(100),
        location VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {
    // Ignore error, table might exist
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$position = trim($data['position'] ?? '');
$department = trim($data['department'] ?? '');
$status = trim($data['status'] ?? 'Active');
$empId = trim($data['empId'] ?? '');

if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($position)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'First Name, Last Name, Email, Password, and Position are required.']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
        exit;
    }

    $pdo->beginTransaction();

    // 1. Create User
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $fullName = $firstName . ' ' . $lastName;
    
    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'employee')");
    $stmtUser->execute([$fullName, $email, $hashedPassword]);
    
    $userId = $pdo->lastInsertId();

    // 2. Create Employee Profile
    $stmtEmp = $pdo->prepare("INSERT INTO employees (
        user_id, employee_id, first_name, last_name, mobile, dob, gender, 
        address, city, state, zip, marital_status, nationality, 
        position, department, status, start_date, manager, location
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?
    )");
    
    $stmtEmp->execute([
        $userId,
        $empId,
        $firstName,
        $lastName,
        $data['mobile'] ?? null,
        !empty($data['dob']) ? $data['dob'] : null,
        $data['gender'] ?? null,
        $data['address'] ?? null,
        $data['city'] ?? null,
        $data['state'] ?? null,
        $data['zip'] ?? null,
        $data['marital'] ?? null,
        $data['nationality'] ?? null,
        $position,
        $department,
        $status,
        !empty($data['start']) ? $data['start'] : null,
        $data['manager'] ?? null,
        $data['location'] ?? null
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Employee added successfully and login account created.',
        'employee' => [
            'id' => $empId,
            'name' => $fullName,
            'user_id' => $userId
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
