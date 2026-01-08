<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

try {
    // Join users and user_profiles to get all patient information
    $query = "SELECT u.id, u.username, p.full_name, p.age, p.pregnancy_week, p.height_cm, p.weight_kg, 
                     p.medical_history, p.prev_gdm, p.family_history, p.doctor_name, p.avatar_url
              FROM users u 
              JOIN user_profiles p ON u.id = p.user_id 
              WHERE u.role = 'patient' 
              ORDER BY p.full_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response and include full avatar URL
    $data = [];
    foreach ($patients as $row) {
        $avatarUrl = null;
        if (!empty($row['avatar_url'])) {
            $avatarUrl = "http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/" . $row['avatar_url'];
        }
        
        $data[] = [
            "id" => $row['id'],
            "username" => $row['username'],
            "fullName" => $row['full_name'] ?? "Unknown",
            "age" => (string)$row['age'],
            "pregnancyWeek" => (string)$row['pregnancy_week'],
            "height" => (string)$row['height_cm'],
            "weight" => (string)$row['weight_kg'],
            "medHistory" => $row['medical_history'] ?? "None",
            "prevGDM" => $row['prev_gdm'] ?? "No",
            "familyHistory" => $row['family_history'] ?? "No",
            "avatarUrl" => $avatarUrl
        ];
    }

    echo json_encode(["status" => "success", "data" => $data]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
