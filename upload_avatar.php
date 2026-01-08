<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

$user_id = $_POST['user_id'] ?? null;

if (!$user_id || !isset($_FILES['avatar'])) {
    echo json_encode(["status" => "error", "message" => "Missing user_id or avatar file"]);
    exit();
}

$target_dir = "../../uploads/avatars/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
$file_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
$target_file = $target_dir . $file_name;

// Basic image check
$check = getimagesize($_FILES["avatar"]["tmp_name"]);
if ($check === false) {
    echo json_encode(["status" => "error", "message" => "File is not an image"]);
    exit();
}

if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
    try {
        // Save relative path to DB
        $db_path = "uploads/avatars/" . $file_name;
        $stmt = $conn->prepare("UPDATE user_profiles SET avatar_url = ? WHERE user_id = ?");
        $stmt->execute([$db_path, $user_id]);
        
        // Return JSON response including the full URL
        $full_url = "http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/" . $db_path;
        echo json_encode([
            "status" => "success", 
            "message" => "Avatar uploaded successfully",
            "avatar_url" => $full_url
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to move uploaded file"]);
}
?>
