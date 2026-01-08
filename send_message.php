<?php
// gdm_api/api/doctor/send_message.php
header("Content-Type: application/json");
include_once '../../config/db.php';
include_once '../../utils/response.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['sender_id']) || !isset($input['receiver_id']) || !isset($input['message'])) {
    sendResponse("error", "Missing required fields", null, 400);
}

$doctor_id = $input['sender_id'];
$patient_id = $input['receiver_id'];
$message = $input['message'];

try {
    // Get doctor name for sender_name
    $docQuery = "SELECT username FROM users WHERE id = ?";
    $docStmt = $conn->prepare($docQuery);
    $docStmt->execute([$doctor_id]);
    $doctor = $docStmt->fetch(PDO::FETCH_ASSOC);
    $sender_name = $doctor ? $doctor['username'] : 'Doctor';

    // Insert into chat_messages table using the verified structure
    $query = "INSERT INTO chat_messages (user_id, message, sender_type, sender_name, created_at) 
              VALUES (?, ?, 'doctor', ?, NOW())";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$patient_id, $message, $sender_name])) {
        sendResponse("success", "Message sent", ["id" => $conn->lastInsertId()]);
    } else {
        sendResponse("error", "Failed to send message");
    }
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
