<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$user_id = $_GET['user_id'] ?? null;

try {
    $stmt = $conn->prepare("SELECT id, user_id, contact_name as name, relationship as relation, phone_number as phone FROM emergency_contacts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "data" => $contacts]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
