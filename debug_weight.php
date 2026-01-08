<?php
include_once 'config/db.php';
$stmt = $conn->query("SELECT * FROM pregnancy_tracking ORDER BY id DESC LIMIT 5");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
