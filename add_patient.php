<?php
// gdm_api/api/doctor/add_patient.php
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

if (!isset($data->doctor_id) || !isset($data->full_name) || !isset($data->patient_id) || !isset($data->password)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$doctor_id = (int)$data->doctor_id;
$full_name = htmlspecialchars(strip_tags($data->full_name));
$username = htmlspecialchars(strip_tags($data->patient_id));
$password = $data->password;
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 1. Check if patient_id (username) already exists
    $check_query = "SELECT id FROM users WHERE username = :username";
    $stmt = $conn->prepare($check_query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        sendResponse("error", "Patient ID already exists", null, 409);
    }

    // 2. Fetch Doctor's full name for the profile
    $doctor_query = "SELECT full_name FROM user_profiles WHERE user_id = :doctor_id";
    $stmt = $conn->prepare($doctor_query);
    $stmt->bindParam(":doctor_id", $doctor_id);
    $stmt->execute();
    $doctor = $stmt->fetch();
    $doctor_name = $doctor ? $doctor['full_name'] : "Unknown Doctor";

    // 3. Insert into users table
    $user_query = "INSERT INTO users (username, password_hash, role) VALUES (:username, :password, 'patient')";
    $stmt = $conn->prepare($user_query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $password_hash);
    $stmt->execute();
    $patient_user_id = $conn->lastInsertId();

    // 4. Insert into user_profiles table
    $profile_query = "INSERT INTO user_profiles (user_id, full_name, doctor_name, age, pregnancy_week, height_cm, weight_kg, medical_history, prev_gdm, family_history) 
                      VALUES (:user_id, :full_name, :doctor_name, 0, 0, 0, 0, '', '', '')";
    $stmt = $conn->prepare($profile_query);
    $stmt->bindParam(":user_id", $patient_user_id);
    $stmt->bindParam(":full_name", $full_name);
    $stmt->bindParam(":doctor_name", $doctor_name);
    $stmt->execute();

    sendResponse("success", "Patient added successfully and can now login");

} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
