<?php
// gdm_api/api/weight/history.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['user_id'])) {
    sendResponse("error", "Missing user_id", null, 400);
}

$user_id = $_GET['user_id'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

try {
    $query = "SELECT * FROM pregnancy_tracking WHERE user_id = :uid AND weight_kg IS NOT NULL ORDER BY record_date DESC LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse("success", "Weight history fetched successfully", $records, 200);
} catch (PDOException $e) {
    sendResponse("error", "Database Error: " . $e->getMessage(), null, 500);
}
?>
