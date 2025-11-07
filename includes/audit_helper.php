<?php
require_once __DIR__ . '/../database/config.php';

if (!function_exists('logActivity')) {
    function logActivity($user_id, $action, $role = null, $details = null) {
        global $conn;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $stmt = $conn->prepare("
            INSERT INTO audit_trail (user_id, action, description, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        if ($stmt === false) {
            // optional: handle prepare error
            error_log('logActivity prepare failed: ' . $conn->error);
            return false;
        }
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $exec = $stmt->execute();
        if ($stmt->error) {
            error_log('logActivity execute failed: ' . $stmt->error);
        }
        $stmt->close();
        return $exec;
    }
}
