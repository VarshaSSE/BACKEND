<?php
// Quick Setup and Test Script for GDMCare Backend
// Run this file to verify backend setup: http://YOUR_IP/gdm_api/setup_test.php

header('Content-Type: application/json');

echo "<h1>GDMCare Backend Setup Test</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    include_once 'config/db.php';
    echo "<p style='color:green'>✓ Database connection successful!</p>";
    echo "<p>Database: gdm_care_db</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check Tables
echo "<h2>2. Database Tables Check</h2>";
$tables = ['users', 'blood_sugar_records', 'pregnancy_tracking', 'chat_messages', 'reports'];
foreach ($tables as $table) {
    $query = "SHOW TABLES LIKE '$table'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color:red'>✗ Table '$table' missing</p>";
    }
}

// Test 3: Check Test User
echo "<h2>3. Test User Check</h2>";
$query = "SELECT * FROM users WHERE email = 'patient@test.com'";
$stmt = $conn->prepare($query);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    echo "<p style='color:green'>✓ Test user exists</p>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Email: " . $user['email'] . "</p>";
    echo "<p>Role: " . $user['role'] . "</p>";
} else {
    echo "<p style='color:orange'>⚠ Test user not found. Creating...</p>";
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $insertQuery = "INSERT INTO users (username, email, password_hash, role) VALUES ('patient_user', 'patient@test.com', :pass, 'patient')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bindParam(':pass', $password);
    if ($insertStmt->execute()) {
        echo "<p style='color:green'>✓ Test user created successfully!</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to create test user</p>";
    }
}

// Test 4: Check Uploads Directory
echo "<h2>4. Uploads Directory Check</h2>";
$uploadDir = 'uploads/reports/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "<p style='color:green'>✓ Created uploads directory: $uploadDir</p>";
} else {
    echo "<p style='color:green'>✓ Uploads directory exists: $uploadDir</p>";
}

if (is_writable($uploadDir)) {
    echo "<p style='color:green'>✓ Uploads directory is writable</p>";
} else {
    echo "<p style='color:red'>✗ Uploads directory is not writable</p>";
}

// Test 5: API Endpoints
echo "<h2>5. API Endpoints Test</h2>";
$endpoints = [
    'api/auth/login.php',
    'api/sugar/add.php',
    'api/sugar/history.php',
    'api/pregnancy/track.php',
    'api/pregnancy/history.php',
    'api/doctor/upload_report.php',
    'api/doctor/chat_send.php',
    'api/doctor/chat_history.php'
];

foreach ($endpoints as $endpoint) {
    if (file_exists($endpoint)) {
        echo "<p style='color:green'>✓ $endpoint exists</p>";
    } else {
        echo "<p style='color:red'>✗ $endpoint missing</p>";
    }
}

// Test 6: Chat Messages Table Structure
echo "<h2>6. Chat Messages Table Structure</h2>";
$query = "DESCRIBE chat_messages";
$stmt = $conn->prepare($query);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$requiredColumns = ['id', 'user_id', 'message', 'sender_type', 'sender_name', 'created_at'];
$existingColumns = array_column($columns, 'Field');

foreach ($requiredColumns as $col) {
    if (in_array($col, $existingColumns)) {
        echo "<p style='color:green'>✓ Column '$col' exists</p>";
    } else {
        echo "<p style='color:red'>✗ Column '$col' missing - Run update_chat_table.sql</p>";
    }
}

// Summary
echo "<hr>";
echo "<h2>Setup Summary</h2>";
echo "<p><strong>Backend URL:</strong> http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/</p>";
echo "<p><strong>Test User Credentials:</strong></p>";
echo "<ul>";
echo "<li>Email: patient@test.com</li>";
echo "<li>Password: password123</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Update RetrofitClient.kt with this URL: http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/</li>";
echo "<li>Build and install the Android app</li>";
echo "<li>Login with test credentials</li>";
echo "<li>Test all features using the TESTING_GUIDE.md</li>";
echo "</ol>";

echo "<p style='color:blue'><strong>If all tests passed, your backend is ready! ✓</strong></p>";
?>
