<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

$search = $_GET['search'] ?? '';
$publisher = $_GET['publisher'] ?? '';
$availability = $_GET['availability'] ?? '';
$sort = $_GET['sort'] ?? 'title_asc';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// --- Fetch Publishers ---
$publishers_result = $conn->query("SELECT publisher_id, name FROM publishers ORDER BY name ASC");

// --- Build Dynamic Query ---
$query = "SELECT b.*, p.name AS publisher_name,
          COALESCE(AVG(r.rating), 0) AS avg_rating
          FROM books b
          LEFT JOIN publishers p ON b.publisher_id = p.publisher_id
          LEFT JOIN reviews r ON b.book_id = r.book_id AND r.is_approved = 1
          WHERE 1=1";

$params = [];
$types = "";

// Search filter
if (!empty($search)) {
    $query .= " AND (b.title LIKE ? OR b.author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Publisher filter
if (!empty($publisher)) {
    $query .= " AND b.publisher_id = ?";
    $params[] = $publisher;
    $types .= "i";
}

// Availability filter
if ($availability === 'in_stock') {
    $query .= " AND b.stock_quantity > 0";
} elseif ($availability === 'out_stock') {
    $query .= " AND b.stock_quantity = 0";
}

$query .= " GROUP BY b.book_id";

// Sorting
switch ($sort) {
    case 'title_desc': $query .= " ORDER BY b.title DESC"; break;
    case 'price_asc': $query .= " ORDER BY b.price ASC"; break;
    case 'price_desc': $query .= " ORDER BY b.price DESC"; break;
    case 'rating_desc': $query .= " ORDER BY avg_rating DESC"; break;
    default: $query .= " ORDER BY b.title ASC"; break;
}

$query .= " LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// --- Handle AJAX Partial Requests ---
if (isset($_GET['ajax'])) {
    include __DIR__ . '/partials/book_cards.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/user.css" rel="stylesheet">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<div class="container my-5">
    <h3 class="mb-4 text-primary fw-bold">Browse Our Collection</h3>

    <!-- Filter Controls -->
    <form id="filterForm" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="publisher" class="form-select">
                <option value="">All Publishers</option>
                <?php while ($pub = $publishers_result->fetch_assoc()): ?>
                    <option value="<?php echo $pub['publisher_id']; ?>" <?php echo ($publisher == $pub['publisher_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pub['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="availability" class="form-select">
                <option value="">All</option>
                <option value="in_stock" <?php echo $availability === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                <option value="out_stock" <?php echo $availability === 'out_stock' ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price Low-High</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price High-Low</option>
                <option value="rating_desc" <?php echo $sort === 'rating_desc' ? 'selected' : ''; ?>>Rating High-Low</option>
            </select>
        </div>
    </form>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center d-none mb-3">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Book Cards Container -->
    <div id="bookContainer">
        <?php include __DIR__ . '/partials/book_cards.php'; ?>
    </div>
</div>

<script src="../../assets/js/data_filter.js"></script>
<script>
    // Auto-submit for instant filtering
    const form = document.getElementById('filterForm');
    form.addEventListener('change', () => form.dispatchEvent(new Event('submit')));
</script>
</body>
</html>
