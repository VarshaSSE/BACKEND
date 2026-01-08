<?php
include_once 'config/db.php';

try {
    $conn->exec("ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) DEFAULT NULL");
    
    // Create directory for avatars
    $dir = 'uploads/avatars';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    echo "<h1>Avatar Support Setup</h1><p>Table updated and directory created.</p>";
} catch(PDOException $e) {
    echo "<h1>Error</h1>" . $e->getMessage();
}
?>
