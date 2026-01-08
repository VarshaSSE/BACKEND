<?php
// gdm_api/api/auth/login.php
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

if (!isset($data->email) || !isset($data->password)) {
    sendResponse("error", "Missing email or password", null, 400);
}

$email = htmlspecialchars(strip_tags($data->email));
$password = $data->password;

$query = "SELECT id, username, email, password_hash, role FROM users WHERE email = :email OR username = :email";
$stmt = $conn->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($password, $user['password_hash'])) {
        // Generate Token (Mock Token for XAMPP without JWT library complexity, or simple base64)
        // In production, use Firebase JWT or php-jwt
        $token_payload = json_encode([
            "id" => $user['id'],
            "role" => $user['role'],
            "exp" => time() + (60 * 60 * 24) // 24 hours
        ]);
        $token = base64_encode($token_payload); // Simplified for this environment
        
        $response_data = [
            "token" => $token,
            "user" => [
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ];
        
        sendResponse("success", "Login successful", $response_data, 200);
    } else {
        sendResponse("error", "Invalid credentials", null, 401);
    }
} else {
    sendResponse("error", "User not found", null, 401);
}
?>
