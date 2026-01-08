<?php
// setup_backend.php
// This script will automatically set up your database and create a test user.

$host = 'localhost';
$username = 'root';
$password = ''; 

try {
    // 1. Connect without DB to create it
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>GDM Care Backend Setup</h1>";
    
    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS gdm_care_db");
    echo "✅ Database 'gdm_care_db' created/exists.<br>";
    
    // 3. Connect to the DB
    $pdo->exec("USE gdm_care_db");
    
    // 4. Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Users table created/exists.<br>";
    
    // 5. Create / Update Test User
    $test_username = 'varshuu';
    $test_email = 'varshuu@example.com';
    $test_pass = 'password123';
    $hash = password_hash($test_pass, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) 
                           VALUES (?, ?, ?, 'patient') 
                           ON DUPLICATE KEY UPDATE password_hash = ?");
    $stmt->execute([$test_username, $test_email, $hash, $hash]);
    
    echo "✅ Test user created!<br>";
    echo "<b>Username:</b> $test_username<br>";
    echo "<b>Password:</b> $test_pass<br>";
    echo "<br><span style='color:green; font-weight:bold;'>SUCCESS: Everything is ready for login!</span>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
