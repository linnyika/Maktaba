<?php
require_once __DIR__ . '/../../includes/session_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Handle cart actions
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['update_quantity'])) {
    $update_id = (int)$_GET['update_quantity'];
    $new_quantity = (int)$_GET['quantity'];
    
    if (isset($_SESSION['cart'][$update_id]) && $new_quantity > 0) {
        $_SESSION['cart'][$update_id]['quantity'] = $new_quantity;
    } elseif (isset($_SESSION['cart'][$update_id]) && $new_quantity <= 0) {
        unset($_SESSION['cart'][$update_id]);
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Maktaba Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("../../includes/user_nav.php"); ?>


<!-- Shopping Cart Content -->
<main class="container my-5 flex-grow-1">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-primary">
                    <i class="bi bi-cart"></i> Shopping Cart
                </h3>
                <?php if (!empty($_SESSION['cart'])): ?>
                <a href="?clear_cart=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to clear your cart?')">
                    <i class="bi bi-trash"></i> Clear Cart
                </a>
                <?php endif; ?>
            </div>

            <?php if (empty($_SESSION['cart'])): ?>
            <!-- Empty Cart -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">Your cart is empty</h4>
                    <p class="text-muted mb-4">Start adding some books to your cart!</p>
                    <a href="browse_books.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-book"></i> Browse Books
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Cart Items -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Cart Items (<?php echo count($_SESSION['cart']); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $total_price = 0;
                            $total_items = 0;
                            
                            foreach ($_SESSION['cart'] as $book_id => $item):
                                $item_total = $item['price'] * $item['quantity'];
                                $total_price += $item_total;
                                $total_items += $item['quantity'];
                            ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-md-2">
                                    <img src="<?php echo $item['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <p class="text-muted small mb-1">by <?php echo htmlspecialchars($item['author']); ?></p>
                                    <p class="text-success small mb-0">In stock: <?php echo $item['stock']; ?></p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <p class="mb-0 fw-bold">KSh <?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" 
                                               onchange="updateQuantity(<?php echo $book_id; ?>, this.value)">
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <p class="mb-1 fw-bold">KSh <?php echo number_format($item_total, 2); ?></p>
                                    <a href="?remove=<?php echo $book_id; ?>" class="btn btn-outline-danger btn-sm" 
                                       onclick="return confirm('Remove this item from cart?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Items (<?php echo $total_items; ?>):</span>
                                <span>KSh <?php echo number_format($total_price, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>KSh 200.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax:</span>
                                <span>KSh <?php echo number_format($total_price * 0.16, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong>KSh <?php echo number_format($total_price + 200 + ($total_price * 0.16), 2); ?></strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-success btn-lg">
                                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                                </a>
                                <a href="browse_books.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateQuantity(bookId, quantity) {
    if (quantity > 0) {
        window.location.href = `cart.php?update_quantity=${bookId}&quantity=${quantity}`;
    }
}
</script>
</body>
</html>