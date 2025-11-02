<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// Handle search, filters, and sorting
$search = $_GET['search'] ?? '';
$publisher = $_GET['publisher'] ?? '';
$availability = $_GET['availability'] ?? '';
$sort = $_GET['sort'] ?? 'title_asc';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get list of publishers for filter dropdown
$publishers_result = $conn->query("SELECT publisher_id, name FROM publishers ORDER BY name ASC");

// Build query dynamically
$query = "SELECT b.*, p.name as publisher_name, 
          COALESCE(AVG(r.rating), 0) as avg_rating
          FROM books b 
          LEFT JOIN publishers p ON b.publisher_id = p.publisher_id
          LEFT JOIN reviews r ON b.book_id = r.book_id AND r.is_approved = 1
          WHERE 1=1";

$params = [];
$types = "";

// Search
if(!empty($search)){
    $query .= " AND (b.title LIKE ? OR b.author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Publisher filter
if(!empty($publisher)){
    $query .= " AND b.publisher_id = ?";
    $params[] = $publisher;
    $types .= "i";
}

// Availability filter
if($availability === 'in_stock'){
    $query .= " AND b.stock_quantity > 0";
} elseif($availability === 'out_stock'){
    $query .= " AND b.stock_quantity = 0";
}

// Group for AVG
$query .= " GROUP BY b.book_id";

// Sorting
switch($sort){
    case 'title_desc': $query .= " ORDER BY b.title DESC"; break;
    case 'price_asc': $query .= " ORDER BY b.price ASC"; break;
    case 'price_desc': $query .= " ORDER BY b.price DESC"; break;
    case 'rating_desc': $query .= " ORDER BY avg_rating DESC"; break;
    default: $query .= " ORDER BY b.title ASC"; break;
}

// Pagination
$query .= " LIMIT $limit OFFSET $offset";

// Prepare statement
$stmt = $conn->prepare($query);
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books - Maktaba Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<div class="container my-5">

    <h3 class="mb-4 text-primary">Browse Our Collection</h3>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="publisher" class="form-select">
                <option value="">All Publishers</option>
                <?php while($pub = $publishers_result->fetch_assoc()): ?>
                    <option value="<?php echo $pub['publisher_id']; ?>" <?php if($pub['publisher_id']==$publisher) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($pub['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="availability" class="form-select">
                <option value="">All</option>
                <option value="in_stock" <?php if($availability=='in_stock') echo 'selected'; ?>>In Stock</option>
                <option value="out_stock" <?php if($availability=='out_stock') echo 'selected'; ?>>Out of Stock</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="title_asc" <?php if($sort=='title_asc') echo 'selected'; ?>>Title A-Z</option>
                <option value="title_desc" <?php if($sort=='title_desc') echo 'selected'; ?>>Title Z-A</option>
                <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>>Price Low-High</option>
                <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>>Price High-Low</option>
                <option value="rating_desc" <?php if($sort=='rating_desc') echo 'selected'; ?>>Rating High-Low</option>
            </select>
        </div>
        <div class="col-md-12 text-end">
            <button class="btn btn-primary">Apply Filters</button>
        </div>
    </form>

    <!-- Book Grid -->
    <div class="row g-4">
        <?php if($result->num_rows > 0): ?>
            <?php while($book = $result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100">
                        <img src="../../assets/img/shout.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text mb-1">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($book['publisher_name']); ?></p>
                            <p class="mb-1">
                                <small class="<?php echo $book['stock_quantity']>0?'text-success':'text-danger'; ?>">
                                    <?php echo $book['stock_quantity']>0?$book['stock_quantity'].' in stock':'Out of stock'; ?>
                                </small>
                            </p>
                            <p class="mb-2"><strong>KSh <?php echo number_format($book['price'],2); ?></strong></p>
                            <p class="mb-2">Rating: <?php echo number_format($book['avg_rating'],1); ?> / 5</p>
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="book_details.php?book_id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php if($book['stock_quantity']>0): ?>
                                    <a href="add_to_cart.php?book_id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-cart-plus"></i> Add</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info">No books found.</div></div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
