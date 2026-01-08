<?php
include_once 'config/db.php';
try {
    $conn->exec("ALTER TABLE pregnancy_tracking ADD COLUMN record_time TIME AFTER record_date");
    echo "Added record_time column. ";
} catch (Exception $e) { echo "record_time error: " . $e->getMessage() . ". "; }

try {
    $conn->exec("ALTER TABLE pregnancy_tracking ADD COLUMN day_type VARCHAR(20) AFTER record_time");
    echo "Added day_type column. ";
} catch (Exception $e) { echo "day_type error: " . $e->getMessage() . ". "; }
?>
