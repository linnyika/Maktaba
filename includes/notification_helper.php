<?php
require_once __DIR__ . '/../database/config.php';

if (!function_exists('addNotification')) {
    function addNotification($user_id, $message, $status = 'unread') {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt === false) {
            error_log('addNotification prepare failed: ' . $conn->error);
            return false;
        }
        $stmt->bind_param("iss", $user_id, $message, $status);
        $exec = $stmt->execute();
        if ($stmt->error) {
            error_log('addNotification execute failed: ' . $stmt->error);
        }
        $stmt->close();
        return $exec;
    }
}

// OPTIONAL: sometimes you want a helper that logs + notifies together:
if (!function_exists('logAndNotify')) {
    function logAndNotify($user_id, $action, $details, $notify_message = null) {
        // avoid circular includes — assume audit_helper already loaded elsewhere
        if (function_exists('logActivity')) {
            logActivity($user_id, $action, null, $details);
        }

        if ($notify_message !== null && function_exists('addNotification')) {
            addNotification($user_id, $notify_message);
        }
    }
}
