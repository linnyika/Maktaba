<?php
session_start();
require_once __DIR__ . '/../../database/config.php';

header('Content-Type: application/json');

// Debug: Log the request
error_log("get_notifications.php called - Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in for notifications");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

try {
    // Check if notifications table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows == 0) {
        // Create notifications table
        $conn->query("
            CREATE TABLE notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                message TEXT NOT NULL,
                status ENUM('unread', 'read') DEFAULT 'unread',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX user_status_idx (user_id, status)
            )
        ");
        
        // Add some sample notifications
        $samples = [
            "Welcome to Maktaba Admin Panel!",
            "System is running smoothly",
            "Check your recent orders for pending actions"
        ];
        
        foreach ($samples as $message) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $message);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Get notifications
    $query = "SELECT id, user_id, message, status, created_at 
              FROM notifications 
              WHERE user_id = ? 
              ORDER BY created_at DESC 
              LIMIT 15";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => (int)$row['id'],
            'message' => $row['message'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }

    $stmt->close();

    error_log("Returning " . count($notifications) . " notifications for user $user_id");

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);

} catch (Exception $e) {
    error_log("Error in get_notifications: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>