<?php
include '../../includes/admin_check.php';
include '../../database/config.php';

// check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // update the order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        // redirect back to manage orders
        header("Location: manage_orders.php?success=1");
        exit;
    } else {
        echo "Error updating order: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>
