<?php
require_once(__DIR__ . '/../database/config.php');

function logActivity($user_id, $action, $module, $description) {
    global $conn; // ✅ Give access to DB connection

    if (!$conn) {
        error_log("Database connection not found in logger.php");
        return;
    }

    $stmt = $conn->prepare("
        INSERT INTO logs (user_id, action, module, description, timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $module, $description);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Failed to prepare log statement: " . $conn->error);
    }
}
?>
