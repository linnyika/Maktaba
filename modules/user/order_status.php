<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php';
require_once("../../database/config.php");
require_once __DIR__ . '/../mailer/order_notifications.php';
$mail->Body = orderEmailTemplate($new_status, $order);


// --- Fetch order info ---
$order_id = 123; // Replace dynamically
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_status, o.total_amount, u.full_name, u.email, b.title AS book_title
    FROM orders o
    JOIN users u ON u.user_id = o.user_id
    JOIN books b ON b.book_id = o.book_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// --- Update order status ---
$new_status = "Shipped"; // Example status
$update = $conn->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
$update->bind_param("si", $new_status, $order_id);

if ($update->execute()) {
    // --- Send email via PHPMailer ---
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // Your SMTP email
        $mail->Password   = 'your_app_password';     // Use app password for Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@maktaba.com', 'Maktaba Bookstore');
        $mail->addAddress($order['email'], $order['full_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Order #{$order['order_id']} Status Update";
        $mail->Body    = "
        <h3>Hi {$order['full_name']},</h3>
        <p>Your order for the book <strong>{$order['book_title']}</strong> (Order ID: {$order['order_id']}) status has been updated to: <strong>{$new_status}</strong>.</p>
        <p>Total Amount: KSh " . number_format($order['total_amount'], 2) . "</p>
        <p>Thank you for shopping with us at <strong>Maktaba Bookstore</strong>!</p>
        ";

        $mail->send();
        echo "Order status updated and email sent successfully.";
    } catch (Exception $e) {
        echo "Order status updated but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Failed to update order status.";
}
?>
