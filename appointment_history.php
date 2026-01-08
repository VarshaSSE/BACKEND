<?php
// gdm_api/api/doctor/appointment_history.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['patient_id'])) {
    sendResponse("error", "Missing patient_id parameter", null, 400);
}

$patient_id = $_GET['patient_id'];

try {
    $query = "SELECT * FROM appointments 
              WHERE patient_id = :pid 
              ORDER BY appt_date DESC, appt_time DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":pid", $patient_id);
    $stmt->execute();

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse("success", "Appointment history retrieved successfully", $appointments, 200);
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
