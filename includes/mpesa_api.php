<?php
// includes/mpesa_api.php
// Basic M-Pesa setup (mock for Phase 1)

function initiatePayment($phone, $amount)
{
    // Generate a mock transaction ID
    $transaction_id = 'TXN' . rand(100000, 999999);

    // Simulate response
    $response = [
        'TransactionID' => $transaction_id,
        'Phone' => $phone,
        'Amount' => $amount,
        'Status' => 'Pending',
        'Message' => 'Mock payment initiated successfully.'
    ];

    // Log transaction (to file or DB)
    logPayment($response);

    return $response;
}

function checkPaymentStatus($transaction_id)
{
    // For Phase 1, simulate random success/failure
    $status = (rand(1, 2) == 1) ? 'Success' : 'Failed';

    return [
        'TransactionID' => $transaction_id,
        'Status' => $status,
        'Message' => "Payment $status (simulated)"
    ];
}

function logPayment($data)
{
    $logFile = __DIR__ . '/../logs/payment_logs.txt';
    $logEntry = date('Y-m-d H:i:s') . " | " . json_encode($data) . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
