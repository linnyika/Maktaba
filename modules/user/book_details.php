<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// Get book ID from URL
$book_id = (int)($_GET['book_id'] ?? 0);

if (!$book_id) {
    header("Location: browse_books.php");
    exit;
}

// Get book details
$stmt = $conn->prepare("
    SELECT b.*, p.name as publisher_name 
    FROM books b 
    LEFT JOIN publishers p ON b.publisher_id = p.publisher_id 
    WHERE b.book_id = ?
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    header("Location: browse_books.php");
    exit;
}

// Format book data
$title = htmlspecialchars($book['title'] ?? 'Unknown Title');
$author = htmlspecialchars($book['author'] ?? 'Unknown Author');
$publisher_name = htmlspecialchars($book['publisher_name'] ?? 'Unknown Publisher');
$price = number_format($book['price'] ?? 0, 2);
$stock = $book['stock_quantity'] ?? 0;
$description = htmlspecialchars($book['description'] ?? 'No description available.');
$year = $book['year_of_publication'] ?? 'Unknown';
$status = $book['is_available'] ?? 0;

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Maktaba Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("../../includes/user_nav.php"); ?>


<!-- Book Details Content -->
<main class="container my-5 flex-grow-1">
    <div class="row">
        <div class="col-md-4">
            <!-- Book Cover -->
            <div class="card">
                <img src="../../assets/img/shout.jpg" class="card-img-top" alt="<?php echo $title; ?>">
                <div class="card-body text-center">
                    <h4 class="text-primary">KSh <?php echo $price; ?></h4>
                    <div class="mb-3">
                        <span class="badge bg-<?php echo $status ? 'success' : 'secondary'; ?>">
                            <?php echo $status ? 'Available' : 'Unavailable'; ?>
                        </span>
                        <span class="badge bg-<?php echo $stock > 0 ? 'info' : 'danger'; ?> ms-1">
                            <?php echo $stock > 0 ? $stock . ' in stock' : 'Out of stock'; ?>
                        </span>
                    </div>
                    
                    <?php if ($stock > 0 && $status): ?>
                        <a href="add_to_cart.php?book_id=<?php echo $book_id; ?>" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </a>
                        <a href="checkout.php?book_id=<?php echo $book_id; ?>" class="btn btn-primary btn-lg w-100 mb-2">
                            <i class="bi bi-lightning"></i> Buy Now
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                            <i class="bi bi-x-circle"></i> Not Available
                        </button>
                    <?php endif; ?>
                    
                    <a href="browse_books.php" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-left"></i> Back to Browse
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Book Information -->
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo $title; ?></h1>
                    <h4 class="card-subtitle mb-3 text-muted">by <?php echo $author; ?></h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Publisher:</strong> <?php echo $publisher_name; ?></p>
                            <p><strong>Publication Year:</strong> <?php echo $year; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Stock Available:</strong> <?php echo $stock; ?> copies</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo $status ? 'success' : 'secondary'; ?>">
                                    <?php echo $status ? 'Available for Purchase' : 'Currently Unavailable'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <h5>Description</h5>
                    <p class="card-text"><?php echo nl2br($description); ?></p>
                </div>
            </div>
            
            <!-- Reviews Section (Placeholder for Phase 2) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Customer Reviews</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Reviews feature coming soon in Phase 2.</p>
                    <button class="btn btn-outline-primary" disabled>
                        <i class="bi bi-star"></i> Write a Review
                    </button>
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