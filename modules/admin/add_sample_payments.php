<?php
require_once __DIR__ . '/../../database/config.php';

header('Content-Type: application/json');

try {
    // Get existing orders that don't have payment records
    $orders_result = $conn->query("
        SELECT o.order_id, o.user_id, o.total_amount, o.order_date, u.full_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        WHERE p.payment_id IS NULL 
        ORDER BY o.order_date DESC 
        LIMIT 50
    ");
    
    $added_count = 0;
    
    while ($order = $orders_result->fetch_assoc()) {
        $payment_methods = ['M-Pesa', 'Cash', 'Card'];
        $statuses = ['Completed', 'Pending', 'Failed'];
        
        $payment_method = $payment_methods[array_rand($payment_methods)];
        $status = $statuses[array_rand($statuses)];
        
        // Generate random MPesa details for demonstration
        $mpesa_phone = '2547' . rand(10000000, 99999999);
        $mpesa_transaction_id = 'MPE' . rand(1000000, 9999999);
        
        $result_desc = match($status) {
            'Completed' => 'Payment completed successfully',
            'Pending' => 'Payment pending confirmation',
            'Failed' => 'Payment failed - insufficient funds',
            default => 'Payment processed'
        };
        
        $stmt = $conn->prepare("
            INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, mpesa_phone, mpesa_transaction_id, result_desc, payment_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iidssssss", 
            $order['order_id'], 
            $order['user_id'], 
            $order['total_amount'], 
            $payment_method, 
            $status,
            $mpesa_phone,
            $mpesa_transaction_id,
            $result_desc,
            $order['order_date']
        );
        
        if ($stmt->execute()) {
            $added_count++;
        }
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => "Added $added_count sample payment records from existing orders",
        'count' => $added_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>