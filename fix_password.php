<?php
// gdm_api/fix_password.php
include_once 'config/db.php';

$new_password = "password123";
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$username = "varshuu";

$query = "UPDATE users SET password_hash = :hash WHERE username = :user";
$stmt = $conn->prepare($query);
$stmt->bindParam(":hash", $hash);
$stmt->bindParam(":user", $username);

if ($stmt->execute()) {
    echo "Password for '$username' updated to '$new_password'. You can now login.";
} else {
    echo "Failed to update password.";
}
?>
