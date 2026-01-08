<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../../config/db.php";
require_once "../../utils/response.php";

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id)) {
    sendResponse("error", "User ID is required");
    exit();
}

try {
    // Collect all required data
    $age = $data->age ?? 0;
    $weight = $data->weight ?? 0;
    $height = $data->height ?? 0;
    $sugar_history = $data->sugar_history ?? "No";
    $sys_bp = $data->sys_bp ?? 120;
    $dia_bp = $data->dia_bp ?? 80;
    $family_history = $data->family_history ?? "No";

    // Call Python script
    $python_path = "py"; 
    $script_path = "C:\\xampp\\htdocs\\gdm_api\\ai\\predict.py";
    
    // Construct command
    $command = "$python_path \"$script_path\" " . 
               escapeshellarg($age) . " " . 
               escapeshellarg($weight) . " " . 
               escapeshellarg($height) . " " . 
               escapeshellarg($sugar_history) . " " . 
               escapeshellarg($sys_bp) . " " . 
               escapeshellarg($dia_bp) . " " . 
               escapeshellarg($family_history) . " 2>&1";
    
    $output = shell_exec($command);
    
    if (empty($output)) {
        sendResponse("error", "No output from AI model script.");
        exit();
    }

    // Try to find the JSON start
    $start_pos = strpos($output, '{');
    $end_pos = strrpos($output, '}');
    
    if ($start_pos === false || $end_pos === false) {
        sendResponse("error", "AI model returned non-JSON output: " . $output);
        exit();
    }
    
    $json_content = substr($output, $start_pos, $end_pos - $start_pos + 1);
    $result = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse("error", "Failed to parse AI response: " . $json_content);
        exit();
    }

    if (isset($result['error'])) {
        sendResponse("error", "AI Error: " . $result['error']);
        exit();
    }

    sendResponse("success", "Prediction generated", $result);

} catch (Exception $e) {
    sendResponse("error", "System error: " . $e->getMessage());
}
?>
