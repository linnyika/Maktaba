<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

$message = "";

// --- Search & Filter Logic ---
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';
$type_filter = $_GET['type'] ?? '';

$query = "
    SELECT 
        p.payment_id,
        p.payment_date,
        p.payment_method,
        p.payment_status,
        p.amount,
        p.mpesa_phone,
        p.mpesa_transaction_id,
        p.result_desc,
        CASE
            WHEN o.order_id IS NOT NULL THEN 'Order'
            WHEN r.reservation_id IS NOT NULL THEN 'Reservation'
            ELSE 'Unknown'
        END AS payment_type
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.order_id
    LEFT JOIN reservations r ON p.order_id = r.reservation_id
    WHERE 1
";

$params = [];
$types = '';

if ($search) {
    $query .= " AND (p.mpesa_transaction_id LIKE ? OR p.payment_method LIKE ? OR p.payment_status LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= 'sss';
}

if ($status_filter) {
    $query .= " AND p.payment_status = ?";
    $params[] = &$status_filter;
    $types .= 's';
}

if ($method_filter) {
    $query .= " AND p.payment_method = ?";
    $params[] = &$method_filter;
    $types .= 's';
}

if ($type_filter) {
    $query .= " AND (
        (o.order_id IS NOT NULL AND ? = 'Order')
        OR (r.reservation_id IS NOT NULL AND ? = 'Reservation')
    )";
    $params[] = &$type_filter;
    $params[] = &$type_filter;
    $types .= 'ss';
}

$query .= " ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Logs - Maktaba Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include("../../includes/admin_nav.php"); ?>

  <main class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold text-primary">Payment Logs</h3>
    </div>

    <!-- ✅ Search and Filter Form -->
    <form method="get" class="row g-2 mb-4">
      <div class="col-md-3">
        <input 
          type="text" 
          name="search" 
          value="<?= htmlspecialchars($search) ?>" 
          placeholder="Search transaction ID..." 
          class="form-control"
        >
      </div>

      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
          <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Failed" <?= $status_filter === 'Failed' ? 'selected' : '' ?>>Failed</option>
          <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </div>

      <div class="col-md-3">
        <select name="method" class="form-select">
          <option value="">All Methods</option>
          <option value="M-Pesa" <?= $method_filter === 'M-Pesa' ? 'selected' : '' ?>>M-Pesa</option>
          <option value="Cash" <?= $method_filter === 'Cash' ? 'selected' : '' ?>>Cash</option>
          <option value="Card" <?= $method_filter === 'Card' ? 'selected' : '' ?>>Card</option>
        </select>
      </div>

      <div class="col-md-2">
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <option value="Order" <?= $type_filter === 'Order' ? 'selected' : '' ?>>Order</option>
          <option value="Reservation" <?= $type_filter === 'Reservation' ? 'selected' : '' ?>>Reservation</option>
        </select>
      </div>

      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-funnel"></i>
        </button>
      </div>
    </form>

    <!-- ✅ Payment Logs Table -->
    <div class="table-wrapper shadow-sm rounded bg-white p-3">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Type</th>
            <th>Method</th>
            <th>Status</th>
            <th>Amount (KES)</th>
            <th>Phone</th>
            <th>Transaction ID</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['payment_id']) ?></td>
                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($row['payment_date']))) ?></td>
                <td><?= htmlspecialchars($row['payment_type']) ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td>
                  <span class="badge 
                    <?php 
                      echo match(strtolower($row['payment_status'])) {
                        'completed' => 'bg-success',
                        'pending' => 'bg-warning text-dark',
                        'failed' => 'bg-danger',
                        'cancelled' => 'bg-secondary',
                        default => 'bg-info text-dark'
                      };
                    ?>">
                    <?= htmlspecialchars(ucfirst($row['payment_status'])) ?>
                  </span>
                </td>
                <td><strong><?= number_format($row['amount'], 2) ?></strong></td>
                <td><?= htmlspecialchars($row['mpesa_phone']) ?></td>
                <td><?= htmlspecialchars($row['mpesa_transaction_id']) ?></td>
                <td><?= htmlspecialchars($row['result_desc']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-4">No payments found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | Admin Panel</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
