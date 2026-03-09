<?php
require 'db.php';

try {
    // 1. jobs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        department VARCHAR(100) NOT NULL,
        requirements TEXT,
        status ENUM('Open', 'Closed') DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. applications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_id INT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100),
        position_applied VARCHAR(150),
        stage ENUM('Applied', 'Screening', 'Interview', 'Final Interview', 'Hired', 'Rejected') DEFAULT 'Applied',
        interview_date DATETIME DEFAULT NULL,
        applied_date DATE NOT NULL,
        resume_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL
    )");

    echo "Recruitment tables created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
