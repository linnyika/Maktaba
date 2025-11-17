<?php
session_start();
require_once '../../database/config.php';
require_once '../../includes/session_check.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['total_orders'=>0,'total_reviews'=>0,'total_spent'=>0]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Total Orders
$stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total_orders'] ?? 0;
$stmt->close();

// Total Reviews
$stmt = $conn->prepare("SELECT COUNT(*) AS total_reviews FROM reviews WHERE user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total_reviews'] ?? 0;
$stmt->close();

// Total Spent (only Paid payments)
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(p.amount),0) AS total_spent
    FROM orders o
    INNER JOIN payments p ON o.order_id=p.order_id
    WHERE o.user_id=? AND p.payment_status='Paid'
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'] ?? 0;
$stmt->close();

echo json_encode([
    'total_orders' => (int)$total_orders,
    'total_reviews' => (int)$total_reviews,
    'total_spent' => (float)$total_spent
]);
