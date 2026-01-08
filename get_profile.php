<?php
// gdm_api/api/doctor/get_profile.php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

include_once '../../config/db.php';
include_once '../../utils/response.php';

$doctor_id = $_GET['doctor_id'] ?? null;

if (!$doctor_id) {
    sendResponse("error", "Missing doctor_id", null, 400);
}

try {
    $query = "SELECT p.full_name, p.medical_history as specialty, p.avatar_url 
              FROM user_profiles p 
              WHERE p.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$doctor_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        if (!empty($profile['avatar_url'])) {
            $profile['avatar_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/" . $profile['avatar_url'];
        }
        sendResponse("success", "Profile fetched", $profile);
    } else {
        sendResponse("error", "Profile not found", null, 404);
    }
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
