<?php
require_once 'notification_helper.php';

class NotificationTriggers {
    
    public static function onNewUserRegistration($user_id, $username) {
        $message = "New user '$username' registered";
        addNotification(1, $message); // Send to admin (user_id 1)
        error_log("Notification: $message");
    }
    
    public static function onPendingOrdersReminder() {
        global $conn;
        
        // Count pending orders
        $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $row = $result->fetch_assoc();
        $pending_count = $row['count'];
        
        if ($pending_count > 0) {
            $message = "Shipping reminder: $pending_count pending orders need to be shipped";
            addNotification(1, $message); // Send to admin
            error_log("Notification: $message");
        }
    }
    
    public static function onNewBookAdded($book_title) {
        $message = "New book '$book_title' added to catalog";
        addNotification(1, $message);
    }
    
    public static function onLowStock($book_title, $stock_count) {
        $message = "Low stock alert: '$book_title' has only $stock_count copies left";
        addNotification(1, $message);
    }
}

// Auto-run shipping reminder every time this file is included
NotificationTriggers::onPendingOrdersReminder();