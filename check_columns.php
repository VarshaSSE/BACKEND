<?php
require_once "config/db.php";
try {
    $stmt = $conn->query("DESCRIBE user_profiles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
