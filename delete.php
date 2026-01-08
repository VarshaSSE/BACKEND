<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Missing contact id"]);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM emergency_contacts WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Contact deleted"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
