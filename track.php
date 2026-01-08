<?php
// gdm_api/api/pregnancy/track.php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->record_date)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$user_id = $data->user_id;
$date = $data->record_date;
$time = isset($data->record_time) ? $data->record_time : date('H:i:s');
$day = isset($data->day_type) ? $data->day_type : '';
$weight = isset($data->weight_kg) ? floatval($data->weight_kg) : null;
$kicks = isset($data->kick_count) ? intval($data->kick_count) : 0;
$bp_sys = isset($data->systolic_bp) ? intval($data->systolic_bp) : null;
$bp_dia = isset($data->diastolic_bp) ? intval($data->diastolic_bp) : null;
$notes = isset($data->notes) ? $data->notes : '';

$query = "INSERT INTO pregnancy_tracking (user_id, record_date, record_time, day_type, weight_kg, kick_count, systolic_bp, diastolic_bp, notes) 
          VALUES (:uid, :rdate, :rtime, :day, :wt, :kicks, :bps, :bpd, :notes)";

$stmt = $conn->prepare($query);
$stmt->bindParam(":uid", $user_id);
$stmt->bindParam(":rdate", $date);
$stmt->bindParam(":rtime", $time);
$stmt->bindParam(":day", $day);
$stmt->bindParam(":wt", $weight);
$stmt->bindParam(":kicks", $kicks);
$stmt->bindParam(":bps", $bp_sys);
$stmt->bindParam(":bpd", $bp_dia);
$stmt->bindParam(":notes", $notes);

if ($stmt->execute()) {
    $alert = null;
    if ($kicks > 0 && $kicks < 10) {
        $alert = "Warning: Fetal movement is lower than 10 kicks/day.";
    }
    sendResponse("success", "Pregnancy data recorded", ["id" => $conn->lastInsertId(), "alert" => $alert], 201);
} else {
    sendResponse("error", "Failed to record data", null, 500);
}
?>
