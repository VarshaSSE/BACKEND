<?php
// gdm_api/api/emergency/contacts.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if (!isset($_GET['user_id'])) {
        sendResponse("error", "Missing user_id", null, 400);
    }
    $user_id = $_GET['user_id'];
    $query = "SELECT * FROM emergency_contacts WHERE user_id = :uid";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->execute();
    sendResponse("success", "Contacts fetched", $stmt->fetchAll(PDO::FETCH_ASSOC), 200);
    
} elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->user_id) || !isset($data->name) || !isset($data->phone)) {
        sendResponse("error", "Missing fields", null, 400);
    }
    
    $query = "INSERT INTO emergency_contacts (user_id, contact_name, relationship, phone_number) VALUES (:uid, :name, :rel, :phone)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $data->user_id);
    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":rel", $data->relationship);
    $stmt->bindParam(":phone", $data->phone);
    
    if ($stmt->execute()) {
        sendResponse("success", "Contact added", ["id" => $conn->lastInsertId()], 201);
    } else {
        sendResponse("error", "DB Error", null, 500);
    }
    
} else {
    sendResponse("error", "Method not allowed", null, 405);
}
?>
