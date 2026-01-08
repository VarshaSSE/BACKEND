<?php
// check_users.php
include_once 'config/db.php';

try {
    $stmt = $conn->query("SELECT id, username, email, role FROM users");
    $users = $stmt->fetchAll();

    echo "<h1>Database User List</h1>";
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<br><b>Try logging in with the 'Username' shown above.</b>";
    } else {
        echo "<p style='color:red;'>No users found in the database. Please run setup_backend.php first.</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
