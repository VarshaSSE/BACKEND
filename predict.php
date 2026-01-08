<?php
// gdm_api/api/ai_risk/predict.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

// Get raw POST data
$data = json_decode(file_get_contents("php://input"));

// Validation
if (!isset($data->age) || !isset($data->weight) || !isset($data->systolic_bp)) {
    sendResponse("error", "Missing required fields: age, weight, systolic_bp", null, 400);
}

$user_id = isset($data->user_id) ? $data->user_id : null;
$age = intval($data->age);
$weight = floatval($data->weight);
$systolic_bp = intval($data->systolic_bp);
$is_sugar_history = isset($data->is_sugar_history) && $data->is_sugar_history == true;
$is_family_history = isset($data->is_family_history) && $data->is_family_history == true;

// --- Rule-Based Logic Implementation ---
$risk_score = 0;

// Age Factor
if ($age > 35) $risk_score += 2;
elseif ($age > 25) $risk_score += 1;

// BMI/Weight Factor (Approx)
if ($weight > 80) $risk_score += 2;
elseif ($weight > 70) $risk_score += 1;

// BP Factor
if ($systolic_bp > 140) $risk_score += 3;
elseif ($systolic_bp > 120) $risk_score += 1;

// History Factors
if ($is_sugar_history) $risk_score += 4;
if ($is_family_history) $risk_score += 3;

// Determine Risk Level & Recommendations
$risk_level = "Low";
$recommendations = "";

if ($risk_score >= 10) {
    $risk_level = "High";
    $recommendations = "Immediate consultation with a GDM specialist is required. Frequent blood glucose monitoring (4-6 times a day).";
} elseif ($risk_score >= 5) {
    $risk_level = "Medium";
    $recommendations = "Regular monitoring of blood sugar required. Maintain a strict diabetic diet (Low Carb, High Fiber).";
} else {
    $risk_level = "Low";
    $recommendations = "Continue with a healthy and balanced diet. Maintain regular follow-ups.";
}

// Log to Database if user_id is provided
if ($user_id) {
    $query = "INSERT INTO ai_risk_scores (user_id, risk_score, risk_level, recommendations) VALUES (:uid, :score, :level, :rec)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->bindParam(":score", $risk_score);
    $stmt->bindParam(":level", $risk_level);
    $stmt->bindParam(":rec", $recommendations);
    $stmt->execute();
}

// Response
$response_payload = [
    "user_id" => $user_id,
    "input" => [
        "age" => $age,
        "weight" => $weight,
        "bp" => $systolic_bp
    ],
    "prediction" => [
        "score" => $risk_score,
        "risk_level" => $risk_level,
        "color_code" => ($risk_level == 'High') ? '#FF0000' : (($risk_level == 'Medium') ? '#FFA500' : '#008000'),
        "recommendations" => $recommendations
    ]
];

sendResponse("success", "Risk prediction calculated successfully", $response_payload, 200);
?>
