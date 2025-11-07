<?php
function logActivity($user_id, $action, $details = null, $timestamp = null)
{
    // Database connection
    require_once(__DIR__ . '/../database/config.php');

    // Default timestamp if not provided
    if ($timestamp === null) {
        $timestamp = date('Y-m-d H:i:s');
    }

    // Default details if not provided
    if ($details === null) {
        $details = $action;
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, details, timestamp) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("LogActivity prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("isss", $user_id, $action, $details, $timestamp);

    if (!$stmt->execute()) {
        error_log("LogActivity execute failed: " . $stmt->error);
        return false;
    }

    $stmt->close();
    $conn->close();

    return true;
}
?>
