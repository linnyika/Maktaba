<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

$user_id = $_SESSION['user_id'];
$book_id = (int)($_GET['book_id'] ?? 0);

if (!$book_id) {
    header("Location: browse_books.php");
    exit;
}

// Get book details
$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ? AND is_available = 1 AND stock_quantity > 0");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    header("Location: browse_books.php?error=book_not_available");
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add book to cart
$added = false;
$message = '';
$message_type = '';

if (isset($_SESSION['cart'][$book_id])) {
    // Check if we can add more (stock limit)
    $current_quantity = $_SESSION['cart'][$book_id]['quantity'];
    if ($current_quantity < $book['stock_quantity']) {
        $_SESSION['cart'][$book_id]['quantity'] += 1;
        $added = true;
        $message = "Quantity increased for '" . htmlspecialchars($book['title']) . "' in your cart!";
        $message_type = "success";
    } else {
        $message = "Cannot add more copies of '" . htmlspecialchars($book['title']) . "'. Only " . $book['stock_quantity'] . " available in stock.";
        $message_type = "warning";
    }
} else {
    // Add new item to cart
    $_SESSION['cart'][$book_id] = [
        'title' => $book['title'],
        'author' => $book['author'],
        'price' => $book['price'],
        'quantity' => 1,
        'stock' => $book['stock_quantity'],
        'image' => '../../assets/img/shout.jpg'
    ];
    $added = true;
    $message = "'" . htmlspecialchars($book['title']) . "' added to your cart!";
    $message_type = "success";
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Cart - Maktaba Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navigation -->
<?php include("../../includes/user_nav.php"); ?>


<!-- Add to Cart Content -->
<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-cart-check"></i> Shopping Cart</h4>
                </div>
                <div class="card-body text-center">
                    
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <h5>
                            <?php if ($message_type === 'success'): ?>
                                <i class="bi bi-check-circle-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php endif; ?>
                            <?php echo $message; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Book Info -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="../../assets/img/shout.jpg" class="img-fluid rounded" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>
                        <div class="col-md-8 text-start">
                            <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p><strong>Price:</strong> KSh <?php echo number_format($book['price'], 2); ?></p>
                            <p><strong>In Stock:</strong> <?php echo $book['stock_quantity']; ?> copies</p>
                            <?php if (isset($_SESSION['cart'][$book_id])): ?>
                            <p><strong>In Your Cart:</strong> <?php echo $_SESSION['cart'][$book_id]['quantity']; ?> copies</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="cart.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart"></i> View Cart & Checkout
                        </a>
                        <a href="browse_books.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="book_details.php?book_id=<?php echo $book_id; ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-counterclockwise"></i> Back to Book
                        </a>
                    </div>
                    
                    <!-- Cart Summary -->
                    <?php if (!empty($_SESSION['cart'])): ?>
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Cart Summary</h6>
                        <p class="mb-1">Items in cart: <strong><?php echo count($_SESSION['cart']); ?></strong></p>
                        <?php
                        $total_items = 0;
                        $total_price = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $total_items += $item['quantity'];
                            $total_price += $item['price'] * $item['quantity'];
                        }
                        ?>
                        <p class="mb-1">Total items: <strong><?php echo $total_items; ?></strong></p>
                        <p class="mb-0">Total price: <strong>KSh <?php echo number_format($total_price, 2); ?></strong></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>