<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password) || !isset($data->full_name)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. Create User
    $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'doctor')");
    // Using username as email if email not provided by admin, or just use as username
    // Usually admin gives a login ID (username)
    $stmt->execute([$data->username, $data->username . "@gdmcare.doctor", $password_hash]);
    $user_id = $conn->lastInsertId();

    // 2. Create Doctor Profile
    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, full_name, doctor_name) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $data->full_name, $data->full_name]);

    // 3. Create Doctor Details (Specialization/Degree)
    $stmt = $conn->prepare("INSERT INTO doctor_details (user_id, specialization) VALUES (?, ?)");
    $stmt->execute([$user_id, $data->specialization . " (" . $data->degree . ")"]);

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Doctor added successfully"]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
