<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'] ?? null;
$old_password = $data['old_password'] ?? null;
$new_password = $data['new_password'] ?? null;

if (!$user_id || !$old_password || !$new_password) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($old_password, $user['password_hash'])) {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$new_hash, $user_id]);
        echo json_encode(["status" => "success", "message" => "Password changed successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect old password"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
