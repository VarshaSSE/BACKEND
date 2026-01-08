<?php
// gdm_api/api/doctor/chat_history.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['user_id'])) {
    sendResponse("error", "Missing user_id parameter", null, 400);
}

$user_id = $_GET['user_id'];

try {
    $query = "SELECT id, user_id, message, sender_type, sender_name, created_at, is_read 
              FROM chat_messages 
              WHERE user_id = :uid 
              ORDER BY created_at ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->execute();

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse("success", "Chat history retrieved successfully", $messages, 200);
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
