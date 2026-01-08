<?php
// gdm_api/api/medication/insulin_recommend.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->current_glucose)) {
    sendResponse("error", "Missing glucose level", null, 400);
}

$glucose = intval($data->current_glucose);
$meal_status = isset($data->meal_status) ? $data->meal_status : 'Random'; // Before Meal, After Meal, Bedtime

// --- Rule-Based Logic ---
// Simplified sliding scale logic for GDM
$units = "0 Units";
$advice = "Glucose level is within target range.";
$action_code = "NORMAL"; // NORMAL, CORRECTION, EMERGENCY

if ($glucose > 250) {
    $units = "6-8 Units";
    $advice = "Your glucose is very high. Please contact your doctor immediately.";
    $action_code = "EMERGENCY";
} elseif ($glucose > 200) {
    $units = "4-6 Units";
    $advice = "Consider a correction dose as per your sliding scale protocol.";
    $action_code = "CORRECTION";
} elseif ($glucose > 140 && $meal_status == 'Before Meal') {
    $units = "2-4 Units";
    $advice = "Glucose is slightly high before meal. Adjust pre-meal bolus.";
    $action_code = "CORRECTION";
} elseif ($glucose > 180 && $meal_status == 'After Meal') {
    $units = "2 Units";
    $advice = "Post-meal glucose is higher than target. Walk for 15 mins.";
    $action_code = "CORRECTION";
} elseif ($glucose < 70) {
    $units = "0 Units";
    $advice = "HYPOGLYCEMIA ALERT! Eat 15g of fast-acting carbs (sugar/juice) immediately.";
    $action_code = "HYPO";
}

$response_data = [
    "input" => [
        "glucose_level" => $glucose,
        "meal_status" => $meal_status
    ],
    "recommendation" => [
        "dosage" => $units,
        "advice" => $advice,
        "action_code" => $action_code
    ]
];

sendResponse("success", "Insulin recommendation generated", $response_data, 200);
?>
