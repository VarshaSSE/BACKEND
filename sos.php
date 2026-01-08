<?php
// gdm_api/api/emergency/sos.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id)) {
    sendResponse("error", "Missing user_id", null, 400);
}

$user_id = $data->user_id;
$lat = isset($data->latitude) ? $data->latitude : 0.0;
$long = isset($data->longitude) ? $data->longitude : 0.0;
$msg = "SOS Triggered by user!";

// Log the SOS
$query = "INSERT INTO sos_logs (user_id, location_lat, location_long, alert_message) VALUES (:uid, :lat, :long, :msg)";
$stmt = $conn->prepare($query);
$stmt->bindParam(":uid", $user_id);
$stmt->bindParam(":lat", $lat);
$stmt->bindParam(":long", $long);
$stmt->bindParam(":msg", $msg);
$stmt->execute();

// Fetch Emergency Contacts
$c_query = "SELECT * FROM emergency_contacts WHERE user_id = :uid";
$c_stmt = $conn->prepare($c_query);
$c_stmt->bindParam(":uid", $user_id);
$c_stmt->execute();
$contacts = $c_stmt->fetchAll(PDO::FETCH_ASSOC);

// In a real app, integrate SMS Gateway (Twilio/Msg91) here to send SMS to $contacts

sendResponse("success", "SOS Alert Logged", ["contacts_notified" => count($contacts)], 200);
?>
