<?php
// gdm_api/api/weight/add.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (!$data) {
    sendResponse("error", "Invalid JSON input", null, 400);
}

if (!isset($data->user_id) || !isset($data->record_date) || !isset($data->weight_kg)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$user_id = $data->user_id;
$record_date = $data->record_date;
$weight_kg = floatval($data->weight_kg);

try {
    $query = "INSERT INTO pregnancy_tracking (user_id, record_date, weight_kg) 
              VALUES (:uid, :rdate, :wt)";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->bindParam(":rdate", $record_date);
    $stmt->bindParam(":wt", $weight_kg);

    if ($stmt->execute()) {
        sendResponse("success", "Weight record added", ["id" => $conn->lastInsertId()], 201);
    } else {
        $error = $stmt->errorInfo();
        sendResponse("error", "Failed to add record: " . $error[2], null, 500);
    }
} catch (PDOException $e) {
    sendResponse("error", "Database Error: " . $e->getMessage(), null, 500);
}
?>
