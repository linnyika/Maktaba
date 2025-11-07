<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch the order info
$order_stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status, o.payment_status
    FROM orders o
    WHERE o.order_id = ? AND o.user_id = ?
");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='alert alert-danger text-center m-5'>Order not found.</div>";
    exit;
}

// Fetch order items
$item_stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, b.title, b.author, b.book_cover
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items = $item_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="bg-light">
<?php include("../../includes/user_nav.php"); ?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order #<?php echo htmlspecialchars($order['order_id']); ?></h5>
            <span><?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <p><strong>Order Status:</strong> 
                        <span class="badge bg-<?php echo ($order['order_status'] == 'Delivered') ? 'success' : (($order['order_status'] == 'Pending') ? 'warning text-dark' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Payment:</strong> 
                        <span class="badge bg-<?php echo ($order['payment_status'] == 'Paid') ? 'success' : (($order['payment_status'] == 'Pending') ? 'warning text-dark' : 'danger'); ?>">
                            <?php echo htmlspecialchars($order['payment_status']); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total:</strong> KSh <?php echo number_format($order['total_amount'], 2); ?></p>
                </div>
            </div>

            <h5 class="mb-3">Books in this Order</h5>
            <?php if ($items->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Book</th>
                                <th>Author</th>
                                <th>Quantity</th>
                                <th>Price (KSh)</th>
                                <th>Subtotal (KSh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $items->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($row['book_cover']) ? '../../assets/img/'.$row['book_cover'] : '../../assets/img/shout.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                             width="50" class="rounded me-2">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo number_format($row['quantity'] * $row['price'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No books found for this order.</div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <a href="my_orders.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to My Orders
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>
</body>
</html>
