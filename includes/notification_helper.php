<?php
require_once __DIR__ . '/../database/config.php';

if (!function_exists('addNotification')) {
    function addNotification($user_id, $message, $status = 'unread') {
        global $conn;
        
        // Check database connection
        if (!$conn || $conn->connect_error) {
            error_log('Database connection failed: ' . $conn->connect_error);
            return false;
        }
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt === false) {
            error_log('addNotification prepare failed: ' . $conn->error);
            return false;
        }
        
        $stmt->bind_param("iss", $user_id, $message, $status);
        $exec = $stmt->execute();
        
        if (!$exec) {
            error_log('addNotification execute failed: ' . $stmt->error);
        }
        
        $stmt->close();
        return $exec;
    }
}

// Test function to verify it's working
if (!function_exists('testNotification')) {
    function testNotification($user_id = 1) {
        $test_message = "Test notification at " . date('Y-m-d H:i:s');
        $result = addNotification($user_id, $test_message);
        
        if ($result) {
            error_log("✅ Notification test SUCCESS for user $user_id");
        } else {
            error_log("❌ Notification test FAILED for user $user_id");
        }
        
        return $result;
    }
}