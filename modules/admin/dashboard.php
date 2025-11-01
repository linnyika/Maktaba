<?php
session_start();
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

// --- Admin access guard ---
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /index.php');
    exit;
}

// --- Initialize counts ---
$counts = [
    'users' => 0,
    'books' => 0,
    'orders_total' => 0,
    'orders_pending' => 0,
    'shipping_pending' => 0,
];

// --- Fetch dashboard stats ---
$q = $conn->query("SELECT COUNT(*) AS c FROM users");
$counts['users'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM books");
$counts['books'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM orders");
$counts['orders_total'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE order_status IN ('Pending', 'Processing')");
$counts['orders_pending'] = (int)$q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) AS c FROM shipping WHERE status IN ('Pending', 'Processing', 'Shipped', 'Out for Delivery')");
$counts['shipping_pending'] = (int)$q->fetch_assoc()['c'];

// --- Recent Orders ---
$recent_orders = [];
$res = $conn->query("
    SELECT o.order_id, o.order_date, o.total_amount, o.order_status, o.payment_status, u.full_name
    FROM orders o
    LEFT JOIN users u ON u.user_id = o.user_id
    ORDER BY o.order_date DESC
    LIMIT 8
");
while ($r = $res->fetch_assoc()) {
    $recent_orders[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maktaba | Admin Dashboard</title>

  <!-- Bootswatch Minty -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">
      <img src="../../assets/img/sm.png" width="36" class="me-2"> Maktaba Admin
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="manage_users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_orders.php">Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_shipping.php">Shipping</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<main class="container my-5 flex-grow-1">
  <header class="mb-4">
    <h3 class="fw-bold">Admin Dashboard</h3>
    <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></p>
  </header>

  <!-- Dashboard Buttons -->
  <div class="row g-3 mb-5 text-center">
    <div class="col-md-3 col-6">
      <div class="btn btn-lg btn-outline-primary w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-people-fill fs-3 mb-1"></i><br>
        <strong><?php echo $counts['users']; ?></strong><br>
        <small>Users</small>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="btn btn-lg btn-outline-success w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-book-half fs-3 mb-1"></i><br>
        <strong><?php echo $counts['books']; ?></strong><br>
        <small>Books</small>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="btn btn-lg btn-outline-info w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-cart-check fs-3 mb-1"></i><br>
        <strong><?php echo $counts['orders_total']; ?></strong><br>
        <small>Orders (<?php echo $counts['orders_pending']; ?> pending)</small>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="btn btn-lg btn-outline-warning w-100 py-3 rounded-4 shadow-sm">
        <i class="bi bi-truck fs-3 mb-1"></i><br>
        <strong><?php echo $counts['shipping_pending']; ?></strong><br>
        <small>Shipments</small>
      </div>
    </div>
  </div>

  <!-- Recent Orders -->
  <section>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="fw-bold text-primary">Recent Orders</h5>
      <a href="/modules/admin/manage_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>

    <?php if (empty($recent_orders)): ?>
      <p class="text-muted">No recent orders.</p>
    <?php else: ?>
      <div class="table-responsive rounded-4 shadow-sm">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-primary">
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Total (KSh)</th>
              <th>Order Status</th>
              <th>Payment</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_orders as $r): ?>
              <tr>
                <td><?php echo (int)$r['order_id']; ?></td>
                <td><?php echo htmlspecialchars($r['full_name'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($r['order_date']))); ?></td>
                <td><?php echo number_format((float)$r['total_amount'], 2); ?></td>
                <td><span class="badge bg-<?php echo ($r['order_status'] === 'Pending') ? 'warning' : (($r['order_status'] === 'Completed') ? 'success' : 'info'); ?>">
                    <?php echo htmlspecialchars($r['order_status']); ?>
                </span></td>
                <td>
                  <span class="badge bg-<?php echo ($r['payment_status'] === 'Paid') ? 'success' : 'secondary'; ?>">
                    <?php echo htmlspecialchars($r['payment_status']); ?>
                  </span>
                </td>
                <td><a href="/modules/admin/manage_orders.php?order_id=<?php echo (int)$r['order_id']; ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- Footer -->
<footer class="bg-primary text-white text-center py-3 mt-auto">
  <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore | Admin Panel</small>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/admin_dashboard.js"></script>
</body>
</html>
