<?php
session_start();
require_once '../../database/config.php';
require_once '../../includes/session_check.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'User';

// --- Total Orders ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total_orders'] ?? 0;
$stmt->close();

// --- Total Reviews ---
$stmt = $conn->prepare("SELECT COUNT(*) AS total_reviews FROM reviews WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total_reviews'] ?? 0;
$stmt->close();

// --- Total Amount Spent ---
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(p.amount), 0) AS total_spent
    FROM payments p
    INNER JOIN orders o ON p.order_id = o.order_id
    WHERE o.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'] ?? 0;
$stmt->close();

// --- Featured Books ---
$featured = [];
$limit = 10;
$query = "
    SELECT b.book_id, b.title, b.genre, b.price, b.book_cover, p.name AS publisher
    FROM books b
    LEFT JOIN publishers p ON b.publisher_id = p.publisher_id
    WHERE b.is_available = 1
    ORDER BY RAND()
    LIMIT ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $featured[] = $row;
}
$stmt->close();

function cover_url($cover) {
    if (empty($cover)) return "../../assets/img/shout.jpg";
    if (file_exists("../../uploads/" . $cover)) {
        return "../../uploads/" . $cover;
    }
    return "../../assets/img/shout.jpg";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($full_name); ?> — Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/user.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("../../includes/user_nav.php"); ?>

<!-- Content -->
<main class="container my-5 flex-grow-1">
  <h3 class="fw-bold mb-3">Hey <?php echo htmlspecialchars($full_name); ?> </h3>
  <p class="text-muted mb-4">Here’s what’s happening with your account</p>

  <div class="row g-3 mb-4 text-center">
    <div class="col-md-4 col-12">
      <div class="btn btn-lg btn-outline-primary w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-cart-check mb-1 fs-3"></i><br>
        <strong><?php echo (int)$total_orders; ?></strong><br>
        <small>Total Orders</small>
      </div>
    </div>
    <div class="col-md-4 col-12">
      <div class="btn btn-lg btn-outline-success w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-star-fill mb-1 fs-3"></i><br>
        <strong><?php echo (int)$total_reviews; ?></strong><br>
        <small>Reviews Written</small>
      </div>
    </div>
    <div class="col-md-4 col-12">
      <div class="btn btn-lg btn-outline-info w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-currency-exchange mb-1 fs-3"></i><br>
        <strong>KSh <?php echo number_format((float)$total_spent, 2); ?></strong><br>
        <small>Total Spent</small>
      </div>
    </div>
  </div>

  <section>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="fw-bold text-primary">Featured Books</h5>
      <div class="scroll-controls">
        <button class="btn btn-sm btn-outline-secondary scroll-btn" data-action="left">‹</button>
        <button class="btn btn-sm btn-outline-secondary scroll-btn" data-action="right">›</button>
      </div>
    </div>

    <div id="bookScroller" class="h-scroll">
      <?php if (empty($featured)): ?>
        <div class="text-muted">No featured books right now.</div>
      <?php else: ?>
        <?php foreach ($featured as $b): ?>
          <div class="book-card">
            <img src="<?php echo htmlspecialchars(cover_url($b['book_cover'])); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
            <div class="body">
              <div class="title"><?php echo htmlspecialchars($b['title']); ?></div>
              <div class="meta"><?php echo htmlspecialchars($b['publisher'] ?? 'Unknown'); ?></div>
              <div class="meta"><?php echo htmlspecialchars($b['genre'] ?? '—'); ?></div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="price badge bg-primary">KSh <?php echo number_format($b['price'], 2); ?></span>
                <a href="browse_books.php?book_id=<?php echo (int)$b['book_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer class="bg-primary text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/dashboard.js"></script>
</body>
</html>