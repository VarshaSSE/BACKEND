<?php
// gdm_api/api/doctor/update_profile.php
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

if (!isset($data->doctor_id) || !isset($data->full_name) || !isset($data->specialty)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$doctor_id = (int)$data->doctor_id;
$full_name = htmlspecialchars(strip_tags($data->full_name));
$specialty = htmlspecialchars(strip_tags($data->specialty));

try {
    $query = "UPDATE user_profiles SET full_name = ?, medical_history = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$full_name, $specialty, $doctor_id]);

    sendResponse("success", "Profile updated successfully");
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
