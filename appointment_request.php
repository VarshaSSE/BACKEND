<?php
// gdm_api/api/doctor/appointment_request.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->patient_id) || !isset($data->appt_date) || !isset($data->appt_time) || !isset($data->reason)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$patient_id = $data->patient_id;
$appt_date = $data->appt_date;
$appt_time = $data->appt_time;
$reason = $data->reason;

try {
    $query = "INSERT INTO appointments (patient_id, appt_date, appt_time, reason, status) 
              VALUES (:pid, :bdate, :btime, :reason, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":pid", $patient_id);
    $stmt->bindParam(":bdate", $appt_date);
    $stmt->bindParam(":btime", $appt_time);
    $stmt->bindParam(":reason", $reason);

    if ($stmt->execute()) {
        $apptId = $conn->lastInsertId();
        sendResponse("success", "Appointment requested successfully", [
            "id" => $apptId,
            "status" => "Pending"
        ], 201);
    } else {
        $errorInfo = $stmt->errorInfo();
        sendResponse("error", "Failed to book appointment: " . $errorInfo[2], null, 500);
    }
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
