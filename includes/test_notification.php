<?php
require_once __DIR__ . '/../database/config.php';
session_start();

// Simulate being logged in as admin for testing
$_SESSION['user_id'] = 1; // Admin user ID

// Test: Add sample notifications
function addSampleNotifications() {
    global $conn;
    
    // Clear existing test notifications
    $conn->query("DELETE FROM notifications WHERE message LIKE '%Test%' OR message LIKE '%New user%' OR message LIKE '%Shipping%'");
    
    // Add sample notifications
    $samples = [
        "New user 'John Doe' registered",
        "Shipping reminder: 11 pending orders need to be shipped",
        "New book 'The Great Gatsby' added to catalog",
        "Test notification - System is working"
    ];
    
    foreach ($samples as $message) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (1, ?, 'unread', NOW())");
        $stmt->bind_param("s", $message);
        $stmt->execute();
        $stmt->close();
    }
    
    echo "Sample notifications added!";
}

addSampleNotifications();