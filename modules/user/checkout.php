<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';
require_once __DIR__ . '/../../includes/audit_helper.php';
require_once __DIR__ . '/../../includes/notification_helper.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$user_id = $_SESSION['user_id'];
$total_price = 0;

// Calculate total cart amount
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

$shipping_fee = 200;
$tax = $total_price * 0.16;
$grand_total = $total_price + $shipping_fee + $tax;

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Insert order into the database
    $insertOrder = $conn->prepare("
        INSERT INTO orders (user_id, shipping_address, total_amount, order_status, payment_status)
        VALUES (?, '', ?, 'Pending', 'Paid')
    ");
    $insertOrder->bind_param('id', $user_id, $grand_total);
    $insertOrder->execute();
    $order_id = $conn->insert_id;

    // 2️⃣ Insert all items from the cart
    $insertItem = $conn->prepare("
        INSERT INTO order_items (order_id, book_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($_SESSION['cart'] as $book_id => $item) {
        $insertItem->bind_param('iiid', $order_id, $book_id, $item['quantity'], $item['price']);
        $insertItem->execute();

        // Reduce stock in books table
        $conn->query("
            UPDATE books 
            SET stock_quantity = GREATEST(stock_quantity - {$item['quantity']}, 0)
            WHERE book_id = $book_id
        ");
    }

    // 3️⃣ Log activity & notify
    logActivity($user_id, 'Checkout', 'User', 'Placed Order #' . $order_id . ' (KSh ' . number_format($grand_total, 2) . ')');
    addNotification($user_id, 'Your order #' . $order_id . ' has been placed successfully!');

    // 4️⃣ Clear cart
    $_SESSION['cart'] = [];

    // 5️⃣ Redirect
    header("Location: confirmation.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<div class="container my-5">
    <h3 class="text-primary fw-bold mb-4"><i class="bi bi-credit-card"></i> Checkout</h3>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Order Items</h5></div>
                <div class="card-body">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?php echo htmlspecialchars($item['title']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span>KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white"><h5 class="mb-0">Payment Summary</h5></div>
                <div class="card-body">
                    <p class="d-flex justify-content-between"><span>Subtotal:</span><strong>KSh <?php echo number_format($total_price, 2); ?></strong></p>
                    <p class="d-flex justify-content-between"><span>Shipping:</span><strong>KSh 200.00</strong></p>
                    <p class="d-flex justify-content-between"><span>Tax (16%):</span><strong>KSh <?php echo number_format($tax, 2); ?></strong></p>
                    <hr>
                    <h5 class="d-flex justify-content-between">
                        <span>Total:</span>
                        <strong>KSh <?php echo number_format($grand_total, 2); ?></strong>
                    </h5>

                    <form method="POST" class="mt-4">
                        <button type="submit" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-check-circle"></i> Confirm and Pay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>
</body>
</html>
