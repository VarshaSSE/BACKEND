<?php
// gdm_api/api/doctor/upload_report.php
include_once '../../config/db.php';
include_once '../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse("error", "Method not allowed", null, 405);
}

// Check for file upload
if (!isset($_FILES['report_file']) || !isset($_POST['user_id']) || !isset($_POST['report_type'])) {
    sendResponse("error", "Missing file or metadata", null, 400);
}

$user_id = $_POST['user_id'];
$type = $_POST['report_type'];
$desc = isset($_POST['description']) ? $_POST['description'] : '';

$upload_dir = '../../uploads/reports/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_name = time() . "_" . basename($_FILES['report_file']['name']);
$target_file = $upload_dir . $file_name;
$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Allow certain file formats
if($file_type != "jpg" && $file_type != "png" && $file_type != "pdf") {
    sendResponse("error", "Sorry, only JPG, PNG & PDF files are allowed.", null, 400);
}

if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
    // Save to DB
    $web_path = "uploads/reports/" . $file_name;
    $query = "INSERT INTO reports (user_id, report_type, file_path, description) VALUES (:uid, :type, :path, :desc)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->bindParam(":type", $type);
    $stmt->bindParam(":path", $web_path);
    $stmt->bindParam(":desc", $desc);
    
    if ($stmt->execute()) {
        sendResponse("success", "File uploaded successfully", ["file_path" => $web_path], 201);
    } else {
        sendResponse("error", "Database error", null, 500);
    }
} else {
    sendResponse("error", "Sorry, there was an error uploading your file.", null, 500);
}
?>
