<?php
// gdm_api/api/diet/log.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->log_date) || !isset($data->meal_type)) {
    sendResponse("error", "Missing required fields", null, 400);
}

$user_id = $data->user_id;
$log_date = $data->log_date;
$meal_type = $data->meal_type;
$food_items = isset($data->food_items) ? $data->food_items : "";
$carbs = isset($data->carbs) ? floatval($data->carbs) : 0;
$calories = isset($data->calories) ? floatval($data->calories) : 0;
$water = isset($data->water_intake_ml) ? intval($data->water_intake_ml) : 0;

$query = "INSERT INTO diet_logs (user_id, log_date, meal_type, food_items, carbs_intake, calories, water_intake_ml)
          VALUES (:uid, :ldate, :mtype, :food, :carbs, :cal, :water)";

$stmt = $conn->prepare($query);
$stmt->bindParam(":uid", $user_id);
$stmt->bindParam(":ldate", $log_date);
$stmt->bindParam(":mtype", $meal_type);
$stmt->bindParam(":food", $food_items);
$stmt->bindParam(":carbs", $carbs);
$stmt->bindParam(":cal", $calories);
$stmt->bindParam(":water", $water);

if ($stmt->execute()) {
    // Carb Warning Logic
    $warning = null;
    if ($carbs > 60) {
        $warning = "High carb intake detected for this meal!";
    }
    
    sendResponse("success", "Diet log added", ["id" => $conn->lastInsertId(), "warning" => $warning], 201);
} else {
    sendResponse("error", "Failed to log diet", null, 500);
}
?>
