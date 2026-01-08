<?php
header("Content-Type: application/json");
include_once '../../config/db.php';

if (!isset($_GET['patient_id'])) {
    echo json_encode(["status" => "error", "message" => "Patient ID is required"]);
    exit();
}

$patient_id = $_GET['patient_id'];

try {
    // 1. Fetch Blood Sugar Records
    $sugarStmt = $conn->prepare("SELECT record_date, record_time, day_type, session_type, food_consumed, glucose_value 
                                FROM blood_sugar_records 
                                WHERE user_id = ? 
                                ORDER BY record_date DESC, record_time DESC");
    $sugarStmt->execute([$patient_id]);
    $sugarRecords = $sugarStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Pregnancy Tracking (Weight & Kicks)
    $trackingStmt = $conn->prepare("SELECT record_date, weight_kg, kick_count 
                                   FROM pregnancy_tracking 
                                   WHERE user_id = ? 
                                   ORDER BY record_date DESC");
    $trackingStmt->execute([$patient_id]);
    $trackingRecords = $trackingStmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        "status" => "success",
        "data" => [
            "bloodSugar" => array_map(function($r) {
                return [
                    "day" => date('l', strtotime($r['record_date'])),
                    "date" => $r['record_date'],
                    "time" => $r['record_time'],
                    "session" => $r['session_type'],
                    "food" => $r['food_consumed'],
                    "value" => (string)$r['glucose_value']
                ];
            }, $sugarRecords),
            "weightHistory" => array_filter(array_map(function($r) {
                if (!$r['weight_kg']) return null;
                return [
                    "day" => date('l', strtotime($r['record_date'])),
                    "date" => $r['record_date'],
                    "value" => (string)$r['weight_kg']
                ];
            }, $trackingRecords)),
            "kickHistory" => array_filter(array_map(function($r) {
                return [
                    "day" => date('l', strtotime($r['record_date'])),
                    "date" => $r['record_date'],
                    "value" => (string)$r['kick_count']
                ];
            }, $trackingRecords))
        ]
    ];

    // Re-index arrays after filtering nulls
    $response['data']['weightHistory'] = array_values($response['data']['weightHistory']);
    $response['data']['kickHistory'] = array_values($response['data']['kickHistory']);

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
