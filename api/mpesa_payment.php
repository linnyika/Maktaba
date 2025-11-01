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
    die("❌ Missing transaction reference (order_id or reservation_id).");
}

$user_id = $_SESSION['user_id'];
$amount = 0;
$description = "";

// --- Retrieve details depending on transaction type ---
if ($type === "order") {
    $stmt = $conn->prepare("
        SELECT o.*, b.title, c.full_name
        FROM orders o
        JOIN books b ON o.book_id = b.book_id
        JOIN customers c ON o.user_id = c.customer_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        die("❌ Order not found.");
    }

    $amount = $data['total_amount'];
    $description = "Payment for order: " . $data['title'];

} elseif ($type === "reservation") {
    $stmt = $conn->prepare("
        SELECT r.*, b.title, c.full_name
        FROM reservations r
        JOIN books b ON r.book_id = b.book_id
        JOIN customers c ON r.user_id = c.customer_id
        WHERE r.reservation_id = ? AND r.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        die("❌ Reservation not found.");
    }

    $amount = 200.00; // flat reservation fee
    $description = "Payment for reservation: " . $data['title'];
}

// --- Simulate M-Pesa payment ---
$mpesa_transaction_id = 'MPESA' . rand(10000, 99999);
$mpesa_receipt_number = 'RCP' . rand(1000, 9999);
$mpesa_phone = '2547XXXXXXXX';
$payment_status = 'Paid';

// --- Record payment ---
$insert = $conn->prepare("
    INSERT INTO payments (
        order_id, payment_method, payment_status, amount, 
        mpesa_phone, mpesa_transaction_id, mpesa_receipt_number, result_desc
    ) VALUES (?, 'Mpesa', ?, ?, ?, ?, ?, ?)
");
$insert->bind_param("isdssss", $reference_id, $payment_status, $amount, $mpesa_phone, $mpesa_transaction_id, $mpesa_receipt_number, $description);

if ($insert->execute()) {
    // Update related record
    if ($type === "order") {
        $conn->query("UPDATE orders SET payment_status='Paid', order_status='Confirmed' WHERE order_id=$reference_id");
    } elseif ($type === "reservation") {
        $conn->query("UPDATE reservations SET payment_status='Paid', status='Confirmed' WHERE reservation_id=$reference_id");
    }

    // Confirmation message
    echo "
    <html>
    <head>
        <title>M-Pesa Payment Success</title>
        <link rel='stylesheet' href='../assets/css/user.css'>
    </head>
    <body>
        <div class='container'>
            <h2>✅ M-Pesa Payment Successful</h2>
            <p><strong>Transaction ID:</strong> $mpesa_transaction_id</p>
            <p><strong>Receipt No:</strong> $mpesa_receipt_number</p>
            <p><strong>Amount:</strong> Ksh $amount</p>
            <p><strong>Type:</strong> " . ucfirst($type) . "</p>
            <p><strong>Description:</strong> $description</p>
            <br>
            <a href='../user/dashboard.php'>⬅ Back to Dashboard</a>
        </div>
    </body>
    </html>
    ";
} else {
    echo "<h3>❌ Payment failed. Please try again later.</h3>";
}
?>
