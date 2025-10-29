<?php
require_once __DIR__ . '/../../includes/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Maktaba Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../../index.php">
      <img src="../../assets/img/sm.png" width="36" class="me-2"> Maktaba Bookstore
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="browse_books.php">Browse</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart 
            <?php if (!empty($_SESSION['cart'])): ?>
            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </a></li>
        <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="../../modules/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Your Book Catalog Content -->
<main class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">Browse Our Collection</h3>
        <div class="search-box">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search books by title or author...">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Book Grid -->
    <div class="row g-4">
        <?php
        require_once __DIR__ . '/../../database/config.php';
        
        // Handle search functionality
        $search = $_GET['search'] ?? '';
        
        $query = "SELECT b.*, p.name as publisher_name 
                  FROM books b 
                  LEFT JOIN publishers p ON b.publisher_id = p.publisher_id 
                  WHERE b.is_available = 1";
        
        if (!empty($search)) {
            $query .= " AND (b.title LIKE ? OR b.author LIKE ?)";
            $search_term = "%$search%";
        }
        
        $query .= " ORDER BY b.title LIMIT 12";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($search)) {
            $stmt->bind_param("ss", $search_term, $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($book = $result->fetch_assoc()) {
                $book_id = $book['book_id'] ?? $book['id'] ?? 0;
                $title = htmlspecialchars($book['title'] ?? 'Unknown Title');
                $author = htmlspecialchars($book['author'] ?? 'Unknown Author');
                $publisher_name = htmlspecialchars($book['publisher_name'] ?? 'Unknown Publisher');
                $price = number_format($book['price'] ?? 0, 2);
                $stock = $book['stock_quantity'] ?? 0;
                
                echo '
                <div class="col-md-4 col-lg-3">
                    <div class="book-card">
                        <img src="../../assets/img/shout.jpg" alt="'.$title.'">
                        <div class="body">
                            <div class="title">'.$title.'</div>
                            <div class="meta">by '.$author.'</div>
                            <div class="meta text-muted">'.$publisher_name.'</div>
                            <div class="meta">
                                <small class="'.($stock > 0 ? 'text-success' : 'text-danger').'">
                                    '.($stock > 0 ? $stock . ' in stock' : 'Out of stock').'
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="price badge bg-primary">KSh '.$price.'</span>
                                <div>
                                    <a href="book_details.php?book_id='.$book_id.'" class="btn btn-sm btn-outline-primary me-1">View</a>
                                    ';
                                    
                // Only show add to cart button if book is in stock
                if ($stock > 0) {
                    echo '<a href="add_to_cart.php?book_id='.$book_id.'" class="btn btn-sm btn-success">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                          </a>';
                } else {
                    echo '<button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>';
                }
                
                echo '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-info text-center">No books found. '.($search ? 'Try a different search term.' : 'Please check back later.').'</div></div>';
        }
        
        $stmt->close();
        $conn->close();
        ?>
    </div>
</main>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; ' . date('Y') . ' Maktaba Bookstore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>