<?php
// Setup script to ensure table exists with CORRECT columns
include_once 'config/db.php';

try {
    // We drop the table and recreate it to ensure the structure matches the current app requirements
    // This resolves the "Unknown column" error caused by an old or incorrect table structure.
    $conn->exec("DROP TABLE IF EXISTS medications");
    
    $sql = "CREATE TABLE medications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        dosage VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL, -- 'tablet' or 'insulin'
        is_enabled TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    )";
    
    $conn->exec($sql);
    echo "<h1>Database Setup Re-Initialized</h1>";
    echo "<p>Table 'medications' has been recreated with the correct structure.</p>";
} catch(PDOException $e) {
    echo "<h1>Error</h1>" . $e->getMessage();
}
?>
