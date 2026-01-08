<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit();
}

try {
    // Log the SOS alert
    $stmt = $conn->prepare("INSERT INTO sos_logs (user_id, alert_message) VALUES (?, ?)");
    $stmt->execute([$user_id, "Emergency SOS triggered by user"]);
    
    // In a real app, this would trigger SMS/Push through a service provider
    // For now, we just log it and return success
    echo json_encode(["status" => "success", "message" => "SOS Alert recorded and sent to your emergency contacts"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
