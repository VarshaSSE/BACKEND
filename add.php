<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;
$name = $data['name'] ?? null;
$relation = $data['relation'] ?? null;
$phone = $data['phone'] ?? null;

if (!$user_id || !$name || !$phone) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, relationship, phone_number) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $name, $relation, $phone])) {
        echo json_encode(["status" => "success", "message" => "Contact added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add contact"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
