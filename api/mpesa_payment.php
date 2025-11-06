<?php
// api/mpesa_payment.php
include('../../database/config.php');
include('../../includes/session_check.php');

// Identify transaction type
$type = "";
$reference_id = 0;

if (isset($_GET['order_id'])) {
    $reference_id = intval($_GET['order_id']);
    $type = "order";
} elseif (isset($_GET['reservation_id'])) {
    $reference_id = intval($_GET['reservation_id']);
    $type = "reservation";
} else {
    die(" Missing transaction reference (order_id or reservation_id).");
}

$user_id = $_SESSION['user_id'];
$amount = 0;
$description = "";

// --- Retrieve order or reservation ---
if ($type === "order") {
    $stmt = $conn->prepare("
        SELECT o.*, b.title 
        FROM orders o
        JOIN books b ON o.book_id = b.book_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) die("Order not found.");

    $amount = $data['total_amount'];
    $description = "Payment for order: " . $data['title'];

} elseif ($type === "reservation") {
    $stmt = $conn->prepare("
        SELECT r.*, b.title 
        FROM reservations r
        JOIN books b ON r.book_id = b.book_id
        WHERE r.reservation_id = ? AND r.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) die("Reservation not found.");

    $amount = 200.00; // flat reservation fee
    $description = "Payment for reservation: " . $data['title'];
}

// --- Simulated M-Pesa Response ---
$mpesa_transaction_id = 'MPESA' . rand(10000, 99999);
$mpesa_receipt_number = 'RCP' . rand(1000, 9999);
$mpesa_phone = '2547XXXXXXXX';
$payment_status = 'Paid';

// --- Record into payments table ---
$insert = $conn->prepare("
    INSERT INTO payments (
        order_id, payment_method, payment_status, amount, 
        mpesa_phone, mpesa_transaction_id, mpesa_receipt_number, result_desc
    ) VALUES (?, 'Mpesa', ?, ?, ?, ?, ?, ?)
");
$insert->bind_param("isdssss", $reference_id, $payment_status, $amount, $mpesa_phone, $mpesa_transaction_id, $mpesa_receipt_number, $description);
$insert->execute();

// ✅ NEW PHASE 4: Log Payment for Analytics
$log = $conn->prepare("
    INSERT INTO payment_logs (user_id, reference_type, reference_id, amount, mpesa_transaction_id, mpesa_phone, status, description)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$log->bind_param("issdssss", $user_id, $type, $reference_id, $amount, $mpesa_transaction_id, $mpesa_phone, $payment_status, $description);
$log->execute();

// Update status based on type
if ($type === "order") {
    $conn->query("UPDATE orders SET payment_status='Paid', order_status='Confirmed' WHERE order_id=$reference_id");
} else {
    $conn->query("UPDATE reservations SET payment_status='Paid', status='Confirmed' WHERE reservation_id=$reference_id");
}

// --- Success Display ---
echo "
<html>
<head>
    <title>Payment Success</title>
    <link rel='stylesheet' href='../../assets/css/user.css'>
</head>
<body>
<div class='container'>
    <h2>✅ M-Pesa Payment Successful</h2>
    <p><strong>Transaction ID:</strong> $mpesa_transaction_id</p>
    <p><strong>Receipt No:</strong> $mpesa_receipt_number</p>
    <p><strong>Amount:</strong> Ksh $amount</p>
    <p><strong>Description:</strong> $description</p>
    <a href='../user/dashboard.php' class='btn'>Back to Dashboard</a>
</div>
</body>
</html>
";
?>
