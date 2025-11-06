<?php
// api/export_api.php
include('../database/config.php');
include('../includes/session_check.php');
include('../includes/export_helper.php');

// Ensure only admin can export
if ($_SESSION['user_role'] !== "admin") {
    die("Unauthorized access.");
}

// Determine export type
$type = isset($_GET['type']) ? $_GET['type'] : 'payments';

// Export Payment Logs
if ($type === "payments") {

    $query = "
        SELECT 
            p.payment_id, 
            p.order_id, 
            p.payment_method,
            p.payment_status, 
            p.amount, 
            p.mpesa_transaction_id,
            p.mpesa_receipt_number,
            p.mpesa_phone,
            p.result_desc,
            p.created_at
        FROM payments p
        ORDER BY p.created_at DESC
    ";

    $result = $conn->query($query);

    $header = [
        "Payment ID",
        "Order/Reservation ID",
        "Method",
        "Status",
        "Amount (Ksh)",
        "Transaction ID",
        "Receipt No",
        "Phone",
        "Description",
        "Recorded On"
    ];

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['payment_id'],
            $row['order_id'],
            $row['payment_method'],
            $row['payment_status'],
            $row['amount'],
            $row['mpesa_transaction_id'],
            $row['mpesa_receipt_number'],
            $row['mpesa_phone'],
            $row['result_desc'],
            $row['created_at']
        ];
    }

    exportToCSV("payment_logs.csv", $header, $data);
}

// If type invalid
echo "Invalid export request.";
exit();
