<?php
// gdm_api/api/reports/summary.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse("error", "Method not allowed", null, 405);
}

if (!isset($_GET['user_id'])) {
    sendResponse("error", "Missing user_id", null, 400);
}

$user_id = $_GET['user_id'];
$days = isset($_GET['days']) ? intval($_GET['days']) : 7; // Default weekly report

// 1. Get Sugar Avg
$sugar_query = "SELECT AVG(glucose_value) as avg_sugar, MIN(glucose_value) as min_sugar, MAX(glucose_value) as max_sugar 
                FROM blood_sugar_records 
                WHERE user_id = :uid AND record_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
$stmt = $conn->prepare($sugar_query);
$stmt->bindParam(":uid", $user_id);
$stmt->bindParam(":days", $days, PDO::PARAM_INT);
$stmt->execute();
$sugar_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Get Weight Gain
$weight_query = "SELECT weight_kg, record_date FROM pregnancy_tracking 
                 WHERE user_id = :uid AND record_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY) 
                 ORDER BY record_date ASC";
$w_stmt = $conn->prepare($weight_query);
$w_stmt->bindParam(":uid", $user_id);
$w_stmt->bindParam(":days", $days, PDO::PARAM_INT);
$w_stmt->execute();
$weights = $w_stmt->fetchAll(PDO::FETCH_ASSOC);

$weight_change = 0;
if (count($weights) >= 2) {
    $first = $weights[0]['weight_kg'];
    $last = $weights[count($weights) - 1]['weight_kg'];
    $weight_change = $last - $first;
}

// 3. Count High Risks
$risk_query = "SELECT COUNT(*) as high_risk_count FROM blood_sugar_records 
               WHERE user_id = :uid AND risk_level = 'Critical' AND record_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
$r_stmt = $conn->prepare($risk_query);
$r_stmt->bindParam(":uid", $user_id);
$r_stmt->bindParam(":days", $days, PDO::PARAM_INT);
$r_stmt->execute();
$risk_data = $r_stmt->fetch(PDO::FETCH_ASSOC);

$report_data = [
    "period" => "Last $days days",
    "sugar_statistics" => [
        "average" => round($sugar_stats['avg_sugar'], 1),
        "min" => $sugar_stats['min_sugar'],
        "max" => $sugar_stats['max_sugar'],
        "critical_readings" => $risk_data['high_risk_count']
    ],
    "weight_change_kg" => $weight_change,
    "generated_at" => date("Y-m-d H:i:s")
];

sendResponse("success", "Report generated", $report_data, 200);
?>
