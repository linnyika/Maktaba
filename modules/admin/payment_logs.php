<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

$message = "";

// Handle table fixes if requested
if (isset($_GET['fix_table'])) {
    include 'fix_payments_table.php';
    exit;
}

// Handle sample data if requested
if (isset($_GET['add_samples'])) {
    include 'add_sample_payments.php';
    exit;
}

// --- Search & Filter Logic ---
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';

// Check if payments table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'payments'")->num_rows > 0;

if (!$table_exists) {
    $message = "Payments table doesn't exist. <a href='?fix_table=1' class='alert-link'>Create it now</a>";
    $result = false;
} else {
    // Check total payments
    $count_result = $conn->query("SELECT COUNT(*) as total FROM payments");
    $count_row = $count_result->fetch_assoc();
    $total_payments = $count_row['total'];
    
    if ($total_payments == 0) {
        $message = "No payment records found. <a href='?add_samples=1' class='alert-link'>Generate sample data</a>";
    }

    // SIMPLE QUERY: Just get payments data without complex joins
    $query = "
        SELECT 
            p.*,
            o.user_id,
            o.order_date,
            u.full_name as customer_name
        FROM payments p
        LEFT JOIN orders o ON p.order_id = o.order_id
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE 1
    ";

    $params = [];
    $types = '';

    if ($search) {
        $query .= " AND (p.mpesa_transaction_id LIKE ? OR p.payment_method LIKE ? OR u.full_name LIKE ? OR p.result_desc LIKE ?)";
        $searchParam = "%$search%";
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $types .= 'ssss';
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

    $query .= " ORDER BY p.payment_date DESC LIMIT 100";

    // Execute query
    if ($params) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $message = "Error preparing query: " . $conn->error;
            $result = false;
        }
    } else {
        $result = $conn->query($query);
        if (!$result) {
            $message = "Error executing query: " . $conn->error;
        }
    }
}
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
      <?php if (isset($total_payments) && $total_payments > 0): ?>
        <span class="badge bg-primary fs-6">Total: <?= $total_payments ?> payments</span>
      <?php endif; ?>
    </div>

    <!-- Status Messages -->
    <?php if ($message): ?>
      <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle"></i> <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Search and Filter Form -->
    <?php if ($table_exists && $total_payments > 0): ?>
    <form method="get" class="row g-2 mb-4">
      <div class="col-md-4">
        <input 
          type="text" 
          name="search" 
          value="<?= htmlspecialchars($search) ?>" 
          placeholder="Search transaction ID, customer, or description..." 
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

      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-funnel"></i> Filter
        </button>
      </div>
    </form>
    <?php endif; ?>

    <!-- Payment Logs Table -->
    <div class="table-wrapper shadow-sm rounded bg-white p-3">
      <?php if (!$table_exists): ?>
        <div class="text-center py-5">
          <i class="bi bi-credit-card display-1 text-muted"></i>
          <h4 class="text-muted mt-3">Payments Table Not Setup</h4>
          <p class="text-muted">You need to create the payments table first.</p>
          <a href="?fix_table=1" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Payments Table
          </a>
        </div>
      <?php elseif ($total_payments == 0): ?>
        <div class="text-center py-5">
          <i class="bi bi-credit-card-2-front display-1 text-muted"></i>
          <h4 class="text-muted mt-3">No Payment Records Yet</h4>
          <p class="text-muted">No payment records found in the database.</p>
          <a href="?add_samples=1" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Generate Sample Data
          </a>
        </div>
      <?php elseif ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Order ID</th>
                <th>Method</th>
                <th>Status</th>
                <th>Amount (KES)</th>
                <th>Phone</th>
                <th>Transaction ID</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['payment_id'] ?></td>
                  <td>
                    <small><?= date('d M Y', strtotime($row['payment_date'])) ?></small><br>
                    <small class="text-muted"><?= date('h:i A', strtotime($row['payment_date'])) ?></small>
                  </td>
                  <td>
                    <?php if (!empty($row['customer_name'])): ?>
                      <?= htmlspecialchars($row['customer_name']) ?>
                    <?php else: ?>
                      <span class="text-muted">Customer #<?= $row['user_id'] ?? 'N/A' ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($row['order_id']): ?>
                      <a href="manage_orders.php?order_id=<?= $row['order_id'] ?>" class="badge bg-info text-decoration-none">
                        #<?= $row['order_id'] ?>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-secondary"><?= $row['payment_method'] ?></span>
                  </td>
                  <td>
                    <span class="badge 
                      <?= match($row['payment_status']) {
                        'Completed' => 'bg-success',
                        'Pending' => 'bg-warning text-dark',
                        'Failed' => 'bg-danger',
                        'Cancelled' => 'bg-secondary',
                        default => 'bg-info'
                      } ?>">
                      <?= $row['payment_status'] ?>
                    </span>
                  </td>
                  <td><strong>KES <?= number_format($row['amount'], 2) ?></strong></td>
                  <td><?= htmlspecialchars($row['mpesa_phone'] ?? 'N/A') ?></td>
                  <td>
                    <?php if ($row['mpesa_transaction_id']): ?>
                      <code class="small"><?= $row['mpesa_transaction_id'] ?></code>
                    <?php else: ?>
                      <span class="text-muted">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td><small><?= htmlspecialchars($row['result_desc'] ?? 'No description') ?></small></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="bi bi-search display-1 text-muted"></i>
          <h4 class="text-muted mt-3">No Payments Match Your Search</h4>
          <p class="text-muted">Try adjusting your filters or search terms.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?= date('Y') ?> Maktaba Bookstore | Admin Panel</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>