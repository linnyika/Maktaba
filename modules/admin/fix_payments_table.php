<?php
require_once __DIR__ . '/../../database/config.php';

header('Content-Type: application/json');

try {
    // First, let's drop the existing payments table if it exists
    $conn->query("DROP TABLE IF EXISTS payments");
    
    // Create a new proper payments table WITH user_id column
    $conn->query("
        CREATE TABLE payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            user_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('M-Pesa', 'Cash', 'Card') NOT NULL,
            payment_status ENUM('Pending', 'Completed', 'Failed', 'Cancelled') DEFAULT 'Pending',
            mpesa_phone VARCHAR(20),
            mpesa_transaction_id VARCHAR(100),
            result_desc TEXT,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_user_id (user_id),
            INDEX idx_payment_date (payment_date)
        )
    ");
    
    echo json_encode(['success' => true, 'message' => 'Payments table recreated successfully with proper structure including user_id']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>