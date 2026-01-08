<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit();
}

try {
    // Check if profile exists
    $stmt = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $sql = "UPDATE user_profiles SET full_name = ?, age = ?, pregnancy_week = ?, height_cm = ?, weight_kg = ?, doctor_name = ?, medical_history = ?, prev_gdm = ?, family_history = ? WHERE user_id = ?";
    } else {
        $sql = "INSERT INTO user_profiles (full_name, age, pregnancy_week, height_cm, weight_kg, doctor_name, medical_history, prev_gdm, family_history, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $data['fullName'],
        $data['age'],
        $data['pregnancyWeek'],
        $data['height'],
        $data['weight'],
        $data['doctorsName'],
        $data['medHistory'],
        $data['prevGDM'],
        $data['familyHistory'],
        $user_id
    ]);

    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
