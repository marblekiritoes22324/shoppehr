<?php
require 'db.php';

try {
    $email = 'admin@shoppehr.com';
    $password = 'admin123';
    $name = 'System Administrator';

    // Check if the admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Admin account already exists! Login with: admin@shoppehr.com / admin123";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmtUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmtUser->execute([$name, $email, $hashedPassword]);
    
    echo "Default Admin created successfully! Login with: admin@shoppehr.com / admin123";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
