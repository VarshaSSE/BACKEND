<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        $data = [
            "id" => (int)$profile['user_id'],
            "fullName" => $profile['full_name'] ?? "",
            "age" => (string)$profile['age'] ?? "",
            "pregnancyWeek" => (string)$profile['pregnancy_week'] ?? "",
            "height" => (string)$profile['height_cm'] ?? "",
            "weight" => (string)$profile['weight_kg'] ?? "",
            "doctorsName" => $profile['doctor_name'] ?? "",
            "medHistory" => $profile['medical_history'] ?? "No",
            "prevGDM" => $profile['prev_gdm'] ?? "No",
            "familyHistory" => $profile['family_history'] ?? "No",
            "avatarUrl" => $profile['avatar_url'] ? "http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/" . $profile['avatar_url'] : null
        ];
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        // Create empty profile if not found
        echo json_encode(["status" => "success", "data" => ["id" => (int)$user_id, "fullName" => "New User"]]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
