<?php
// gdm_api/api/sugar/add.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->record_date) || !isset($data->glucose_value)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$user_id = $data->user_id;
$record_date = $data->record_date;
$record_time = isset($data->record_time) ? $data->record_time : date("H:i:s");
$session_type = isset($data->session_type) ? $data->session_type : 'Random';
$food_consumed = isset($data->food_consumed) ? $data->food_consumed : '';
$glucose_value = floatval($data->glucose_value);
$day_type = date('l', strtotime($record_date));

// Determine Risk (Auto-Classification)
$risk_level = 'Normal';
if ($session_type == 'Fasting') {
    if ($glucose_value > 126) $risk_level = 'Critical';
    elseif ($glucose_value > 100) $risk_level = 'Warning';
} else {
    // Post prandial / Random
    if ($glucose_value > 200) $risk_level = 'Critical';
    elseif ($glucose_value > 140) $risk_level = 'Warning';
}

$query = "INSERT INTO blood_sugar_records (user_id, record_date, record_time, day_type, session_type, food_consumed, glucose_value, risk_level) 
          VALUES (:uid, :rdate, :rtime, :day, :sess, :food, :val, :risk)";

$stmt = $conn->prepare($query);
$stmt->bindParam(":uid", $user_id);
$stmt->bindParam(":rdate", $record_date);
$stmt->bindParam(":rtime", $record_time);
$stmt->bindParam(":day", $day_type);
$stmt->bindParam(":sess", $session_type);
$stmt->bindParam(":food", $food_consumed);
$stmt->bindParam(":val", $glucose_value);
$stmt->bindParam(":risk", $risk_level);

if ($stmt->execute()) {
    $last_id = $conn->lastInsertId();
    
    // Check if critical, maybe trigger logic for SOS/Alerts here if needed
    
    sendResponse("success", "Glucose record added", ["id" => $last_id, "risk_level" => $risk_level], 201);
} else {
    sendResponse("error", "Failed to add record", null, 500);
}
?>
