<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/admin_check.php';
require_once '../../database/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);

    // ✅ Allowed ENUM values from DB
    $allowed_status = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];

    // ✅ Check if valid
    if (!in_array($status, $allowed_status)) {
        die("Invalid order status value: " . htmlspecialchars($status));
    }

    // ✅ Update safely
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        header("Location: manage_orders.php?success=1");
        exit;
    } else {
        echo "❌ Error updating order: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
