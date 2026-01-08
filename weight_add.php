<?php
// gdm_api/api/pregnancy/weight_add.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (!$data) {
    sendResponse("error", "Invalid JSON input", null, 400);
}

if (!isset($data->user_id) || !isset($data->record_date) || !isset($data->weight_kg)) {
    sendResponse("error", "Missing required fields (user_id, record_date, weight_kg)", null, 400);
}

$user_id = (int)$data->user_id;
$record_date = $data->record_date;
$weight_kg = floatval($data->weight_kg);

try {
    $query = "INSERT INTO pregnancy_tracking (user_id, record_date, weight_kg)
              VALUES (:uid, :rdate, :wt)";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id, PDO::PARAM_INT);
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
