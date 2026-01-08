<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../../config/db.php";
require_once "../../utils/response.php";

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->glucose_level)) {
    sendResponse("error", "User ID and Glucose Level are required");
    exit();
}

$user_id = $data->user_id;

try {
    // Fetch profile for additional features - Correcting column names
    $query = "SELECT age, weight_kg as weight, height_cm as height, pregnancy_week FROM user_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Default values if profile missing or columns null
    $age = $profile['age'] ?? 25;
    $weight = $profile['weight'] ?? 65;
    $height = $profile['height'] ?? 160;
    $preg_week = $profile['pregnancy_week'] ?? 24;

    // Calculate BMI
    if ($height > 0) {
        $height_m = ($height > 3) ? $height / 100 : $height;
        $bmi = $weight / ($height_m ** 2);
    } else {
        $bmi = 22; // default
    }

    $trimester = 1;
    if ($preg_week > 28) $trimester = 3;
    elseif ($preg_week > 13) $trimester = 2;

    $glucose = $data->glucose_level;
    $meal_type = $data->meal_type ?? "Before Meal"; 

    // Map features for the model
    $fasting_glucose = ($meal_type == "Before Meal") ? $glucose : 95; 
    $postprandial_glucose = ($meal_type == "After Meal") ? $glucose : 120; 
    if ($meal_type == "Bedtime") {
        $fasting_glucose = $glucose; 
    }

    $gest_age = $preg_week;
    $current_dose = 0; 
    $ins_type = 0;
    $diet = 1; 
    $activity = 1; 
    $hypo = 0;
    $ketone = 0;
    $ins_action = 2; // default

    // Call Python
    $python_path = "py";
    $script_path = "C:\\xampp\\htdocs\\gdm_api\\ai\\predict_insulin.py";

    $args = [
        $fasting_glucose, $postprandial_glucose, $gest_age, $trimester, $weight, $bmi,
        $current_dose, $ins_type, $diet, $activity, $hypo, $ketone, $ins_action
    ];

    $command = "$python_path \"$script_path\" " . implode(" ", array_map('escapeshellarg', $args)) . " 2>&1";
    $output = shell_exec($command);
    
    // Parse output
    $start_pos = strpos($output, '{');
    $end_pos = strrpos($output, '}');
    
    if ($start_pos !== false && $end_pos !== false) {
        $json_content = substr($output, $start_pos, $end_pos - $start_pos + 1);
        $result = json_decode($json_content, true);
        
        if ($result && !isset($result['error'])) {
            sendResponse("success", "Insulin recommendation generated", $result);
        } else {
            sendResponse("error", "AI Error: " . ($result['error'] ?? $output));
        }
    } else {
        sendResponse("error", "Invalid AI output: " . $output);
    }

} catch (Exception $e) {
    sendResponse("error", "System error: " . $e->getMessage());
}
?>
