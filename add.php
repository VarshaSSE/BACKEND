<?php
header("Content-Type: application/json");
error_reporting(0); // Suppress all visual errors

// Include database config
$config_path = '../../config/db.php';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database config not found"]);
    exit();
}

include_once $config_path;

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

$db = $conn;

// 1. Get Inputs (Support both POST and JSON)
$user_id = $_POST['user_id'] ?? null;
$name = $_POST['name'] ?? null;
$dosage = $_POST['dosage'] ?? null;
$type = $_POST['type'] ?? null;
$is_enabled = $_POST['is_enabled'] ?? 0;

if (empty($user_id)) {
    $json = json_decode(file_get_contents("php://input"), true);
    if (!empty($json['user_id'])) {
        $user_id = $json['user_id'];
        $name = $json['name'];
        $dosage = $json['dosage'];
        $type = $json['type'];
        $is_enabled = $json['is_enabled'] ?? 0;
    }
}

// 2. Validate Inputs
if (empty($user_id) || empty($name) || empty($dosage) || empty($type)) {
    http_response_code(200); // Return 200 so app parses JSON error
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

try {
    // 3. Insert Data
    $query = "INSERT INTO medications (user_id, name, dosage, type, is_enabled) VALUES (:user_id, :name, :dosage, :type, :is_enabled)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":dosage", $dosage);
    $stmt->bindParam(":type", $type);
    $stmt->bindParam(":is_enabled", $is_enabled);

    if ($stmt->execute()) {
        $new_id = $db->lastInsertId();
        echo json_encode([
            "status" => "success", 
            "message" => "Medication added", 
            "id" => $new_id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database insert failed"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
}
?>
