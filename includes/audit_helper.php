<?php
require_once(__DIR__ . '/../database/config.php');

function logActivity($user_id, $action, $description, $ip_address = 'SYSTEM') {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
    $stmt->execute();
}
?>
