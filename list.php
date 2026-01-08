<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

try {
    $stmt = $conn->query("SELECT id, title as question, content_body as answer FROM education_content ORDER BY id ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $items]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
