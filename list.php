<?php
header("Content-Type: application/json");
error_reporting(0);

$config_path = '../../config/db.php';
if (!file_exists($config_path)) {
    echo json_encode(["status" => "error", "message" => "Config missing"]);
    exit();
}
include_once $config_path;
$db = $conn;

$user_id = $_GET['user_id'] ?? null;
$type = $_GET['type'] ?? null;

if (empty($user_id)) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit();
}

try {
    $query = "SELECT * FROM medications WHERE user_id = :user_id";
    if (!empty($type)) {
        $query .= " AND type = :type";
    }
    $query .= " ORDER BY created_at ASC"; 

    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    if (!empty($type)) {
        $stmt->bindParam(":type", $type);
    }
    
    $stmt->execute();
    
    $meds = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure enabled is integer for consistent handling
        $row['is_enabled'] = (int)$row['is_enabled'];
        $meds[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "data" => $meds
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
