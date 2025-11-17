<?php
session_start();
require_once '../../database/config.php';
require_once '../../includes/session_check.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['total_spent' => 0]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch total spent
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(p.amount), 0) AS total_spent
    FROM payments p
    INNER JOIN orders o ON p.order_id = o.order_id
    WHERE o.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'] ?? 0;
$stmt->close();

echo json_encode(['total_spent' => (float)$total_spent]);
