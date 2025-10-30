<?php
// includes/mpesa_api.php
include('config.php');

function initiateMpesaPayment($reservation_id, $amount, $phone)
{
    global $conn;

    $transaction_id = 'TX' . time();
    $receipt_number = 'RCP' . rand(1000, 9999);
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO payments
        (order_id, amount, mpesa_phone, mpesa_transaction_id, mpesa_receipt_number, payment_status)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param("idssss", $reservation_id, $amount, $phone, $transaction_id, $receipt_number, $status);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'transaction_id' => $transaction_id,
            'receipt_number' => $receipt_number,
            'message' => 'Payment initiated successfully.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Database error: ' . $stmt->error
        ];
    }
}
?>
