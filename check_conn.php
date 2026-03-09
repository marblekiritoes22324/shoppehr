<?php
require 'db.php';
header('Content-Type: application/json');

try {
    // Basic connection test
    $pdo->query("SELECT 1");
    
    // Check if table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    $tableStatus = $tableCheck > 0 ? "✅ Table 'users' exists." : "❌ Table 'users' is MISSING.";
    
    // Check columns if table exists
    $columnStatus = "";
    if ($tableCheck > 0) {
        $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['id', 'name', 'email', 'password', 'role'];
        $missing = array_diff($required, $columns);
        if (empty($missing)) {
            $columnStatus = "✅ All required columns are present.";
        } else {
            $columnStatus = "❌ Missing columns: " . implode(', ', $missing);
        }
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Database connection successful!',
        'details' => [
            'table_status' => $tableStatus,
            'column_status' => $columnStatus
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
}
?>
