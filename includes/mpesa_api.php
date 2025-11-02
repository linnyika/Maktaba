<?php
// includes/mpesa_api.php
include('config.php');
include('session_check.php');

$type = "";
$reference_id = 0;

// Detect whether this is a reservation or normal order
if (isset($_GET['reservation_id'])) {
    $reference_id = intval($_GET['reservation_id']);
    $type = "reservation";
} elseif (isset($_GET['order_id'])) {
    $reference_id = intval($_GET['order_id']);
    $type = "order";
} else {
    die("Missing transaction reference (reservation_id or order_id).");
}

// Common user info
$user_id = $_SESSION['user_id'];
$amount = 0;
$description = "";

// --- Get details depending on transaction type ---
if ($type === "reservation") {
    $stmt = $conn->prepare("
        SELECT r.*, b.title, c.full_name 
        FROM reservations r
        JOIN books b ON r.book_id = b.book_id
        JOIN customers c ON r.user_id = c.customer_id
        WHERE r.reservation_id = ? AND r.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        die("Reservation not found.");
    }

    $amount = 200.00; // static for now; you can base it on book price
    $description = "Reservation payment for book: " . $data['title'];

} elseif ($type === "order") {
    $stmt = $conn->prepare("
        SELECT o.*, c.full_name 
        FROM orders o
        JOIN customers c ON o.user_id = c.customer_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $reference_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        die("Order not found.");
    }

    $amount = $data['total_amount'] ?? 500.00; // placeholder fallback
    $description = "Payment for order #" . $reference_id;
}

// --- Simulate M-Pesa payment ---
$mpesa_transaction_id = 'MPESA' . rand(10000, 99999);
$mpesa_phone = '2547XXXXXXXX'; // replace with input later
$payment_status = 'Paid';

// --- Save payment record ---
$insert = $conn->prepare("
    INSERT INTO payments (order_id, payment_method, payment_status, amount, mpesa_phone, mpesa_transaction_id, result_desc)
    VALUES (?, 'Mpesa', ?, ?, ?, ?, ?)
");
$insert->bind_param("isdsss", $reference_id, $payment_status, $amount, $mpesa_phone, $mpesa_transaction_id, $description);

if ($insert->execute()) {
    if ($type === "reservation") {
        $conn->query("UPDATE reservations SET payment_status='Paid', status='Confirmed' WHERE reservation_id=$reference_id");
    } elseif ($type === "order") {
        $conn->query("UPDATE orders SET payment_status='Paid' WHERE order_id=$reference_id");
    }

    echo "<h2>M-Pesa Payment Successful!</h2>";
    echo "<p><strong>Transaction ID:</strong> $mpesa_transaction_id</p>";
    echo "<p><strong>Amount:</strong> Ksh $amount</p>";
    echo "<p><strong>Type:</strong> " . ucfirst($type) . "</p>";
    echo "<p><strong>Description:</strong> $description</p>";
    echo "<a href='../user/dashboard.php'>Back to Dashboard</a>";
} else {
    echo "<h3>Payment failed. Try again later.</h3>";
}
?>
