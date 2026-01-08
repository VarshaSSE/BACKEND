<?php
// gdm_api/api/pregnancy/weight_history.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['user_id'])) {
    sendResponse("error", "Missing user_id", null, 400);
}

$user_id = (int)$_GET['user_id'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

try {
    $query = "SELECT * FROM pregnancy_tracking WHERE user_id = :uid AND weight_kg IS NOT NULL ORDER BY record_date DESC LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse("success", "Weight history fetched successfully", $records, 200);
} catch (PDOException $e) {
    sendResponse("error", "Database Error: " . $e->getMessage(), null, 500);
}
?>
