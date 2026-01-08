<?php
// gdm_api/api/pregnancy/history.php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['user_id'])) {
    sendResponse("error", "Missing user_id", null, 400);
}

$user_id = $_GET['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // 'weight', 'kicks', or 'all'

$query = "SELECT * FROM pregnancy_tracking WHERE user_id = :uid ";

if ($type === 'weight') {
    $query .= "AND weight_kg IS NOT NULL ";
} elseif ($type === 'kicks') {
    $query .= "AND kick_count > 0 ";
}

$query .= "ORDER BY record_date DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(":uid", $user_id);
$stmt->execute();

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse("success", "Pregnancy history fetched successfully", $records, 200);
?>
