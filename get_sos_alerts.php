<?php
// gdm_api/api/doctor/get_sos_alerts.php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

include_once '../../config/db.php';
include_once '../../utils/response.php';

try {
    // In this app structure, we assume a doctor sees alerts from all patients.
    // In a more complex mult-doctor system, we would filter by doctor_name from user_profiles.
    
    $query = "SELECT l.id, l.created_at as timestamp, p.full_name as patientName, u.username as patientId 
              FROM sos_logs l
              JOIN users u ON l.user_id = u.id
              JOIN user_profiles p ON u.id = p.user_id
              ORDER BY l.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format timestamp for display
    foreach ($alerts as &$alert) {
        $alert['timestamp'] = date('M d, g:i A', strtotime($alert['timestamp']));
    }

    sendResponse("success", "Alerts fetched", $alerts);

} catch (PDOException $e) {
    sendResponse("error", "Database error: " . $e->getMessage(), null, 500);
}
?>
