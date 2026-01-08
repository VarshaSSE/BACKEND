<?php
header("Content-Type: application/json");
error_reporting(0);

include_once '../../config/db.php';
$db = $conn;

$json = json_decode(file_get_contents("php://input"), true);
$id = $json['id'] ?? null;
// Use array key check for is_enabled as it can be 0 or 1
if (empty($id) || !array_key_exists('is_enabled', $json)) {
    echo json_encode(["status" => "error", "message" => "Missing id or is_enabled"]);
    exit();
}

try {
    $query = "UPDATE medications SET is_enabled = :val WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":val", $json['is_enabled']);
    $stmt->bindParam(":id", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
