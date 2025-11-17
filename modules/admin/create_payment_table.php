<?php
require_once __DIR__ . '/../../database/config.php';

header('Content-Type: application/json');

try {
    // Create payments table
    $conn->query("
        CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            user_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_status VARCHAR(50) DEFAULT 'Pending',
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
    
    echo json_encode(['success' => true, 'message' => 'Payments table created successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>