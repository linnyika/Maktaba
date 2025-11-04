<?php
function orderEmailTemplate($status, $orderData) {
    $customer = htmlspecialchars($orderData['full_name'] ?? 'Valued Customer');
    $book = htmlspecialchars($orderData['book_title'] ?? 'Unknown Book');
    $amount = number_format($orderData['total_amount'] ?? 0, 2);
    $orderId = (int)($orderData['order_id'] ?? 0);

    // Dynamic status color
    $statusColors = [
        'Pending' => '#f4c542',
        'Processing' => '#3a86ff',
        'Shipped' => '#ff7b00',
        'Delivered' => '#2d8a34',
        'Cancelled' => '#d90429'
    ];
    $color = $statusColors[$status] ?? '#2d8a34';

    $baseTemplate = '
        <div style="font-family: Poppins, Arial, sans-serif; background: #f9fff9; padding: 25px; border-radius: 12px; 
                    border: 1px solid #d0eed0; max-width: 600px; margin: auto; color: #333;">
            <h2 style="color: %s; text-align:center;">📚 Maktaba Bookstore</h2>
            <p>Hi <strong>%s</strong>,</p>
            <p style="font-size: 15px;">%s</p>
            <div style="background: #ffffff; border-radius: 10px; padding: 15px; border: 1px solid #e3f2e3;">
                <p><b>Order ID:</b> #%d<br>
                   <b>Book Title:</b> %s<br>
                   <b>Total Amount:</b> KSh %s<br>
                   <b>Status:</b> <span style="color:%s; font-weight:bold;">%s</span></p>
            </div>
            <p style="margin-top:20px;">Thank you for shopping with <strong>Maktaba Bookstore</strong>!<br>
            <small>© %d Maktaba Bookstore. All rights reserved.</small></p>
        </div>
    ';

    switch ($status) {
        case 'Pending':
            $body = 'Your order has been received and is currently pending processing.';
            break;
        case 'Processing':
            $body = 'Your order is now being processed. You will receive another update soon.';
            break;
        case 'Shipped':
            $body = 'Great news! Your book order has been shipped and is on its way to you.';
            break;
        case 'Delivered':
            $body = 'Your order has been delivered successfully. We hope you enjoy reading your new book!';
            break;
        case 'Cancelled':
            $body = 'Your order has been cancelled. Please contact support if this was unintentional.';
            break;
        default:
            $body = 'Your order status has been updated.';
            break;
    }

    return sprintf($baseTemplate, $color, $customer, $body, $orderId, $book, $amount, $color, $status, date('Y'));
}
?>
