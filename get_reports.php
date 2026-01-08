<?php
// gdm_api/api/doctor/get_reports.php
header("Content-Type: application/json");
include_once '../../config/db.php';
include_once '../../utils/response.php';

if (!isset($_GET['patient_id'])) {
    sendResponse("error", "Patient ID is required", null, 400);
}

$patient_id = $_GET['patient_id'];

try {
    $stmt = $conn->prepare("SELECT id, report_type, file_path, uploaded_at, description 
                            FROM reports 
                            WHERE user_id = ? 
                            ORDER BY uploaded_at DESC");
    $stmt->execute([$patient_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($reports as $row) {
        $fileUrl = "http://" . $_SERVER['HTTP_HOST'] . "/gdm_api/" . $row['file_path'];
        $data[] = [
            "id" => $row['id'],
            "type" => $row['report_type'],
            "fileUrl" => $fileUrl,
            "date" => date('Y-m-d H:i:s', strtotime($row['uploaded_at'])),
            "description" => $row['description'] ?? ""
        ];
    }

    sendResponse("success", "Reports fetched", $data);
} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
