<?php
// gdm_api/api/doctor/chat_send.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->message) || !isset($data->sender_type)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$user_id = $data->user_id;
$message = $data->message;
$sender_type = $data->sender_type; // 'patient' or 'doctor'

try {
    // Get sender name from users table
    $userQuery = "SELECT username FROM users WHERE id = :uid";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(":uid", $user_id);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $sender_name = $user ? $user['username'] : 'Unknown';

    $query = "INSERT INTO chat_messages (user_id, message, sender_type, sender_name, created_at) 
              VALUES (:uid, :msg, :stype, :sname, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->bindParam(":msg", $message);
    $stmt->bindParam(":stype", $sender_type);
    $stmt->bindParam(":sname", $sender_name);

    if ($stmt->execute()) {
        $chatId = $conn->lastInsertId();
        sendResponse("success", "Message sent successfully", [
            "chat_id" => $chatId,
            "timestamp" => date("Y-m-d H:i:s")
        ], 201);
    } else {
        $errorInfo = $stmt->errorInfo();
        sendResponse("error", "Failed to send message: " . $errorInfo[2], null, 500);
    }
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
