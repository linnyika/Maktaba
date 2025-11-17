<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

// Fetch all orders with user info
$query = "
    SELECT 
        o.order_id, 
        o.user_id, 
        u.full_name, 
        o.total_amount, 
        o.order_status, 
        o.order_date 
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders - Maktaba Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">

  <?php include("../../includes/admin_nav.php"); ?>

  <main class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold text-primary">Manage Orders</h3>
    </div>

    <!--  Success message -->
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        Order status updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="table-wrapper shadow-sm rounded bg-white p-3">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total (KES)</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['order_id']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><strong><?= number_format($row['total_amount'], 2) ?></strong></td>
                <td>
                  <span class="badge 
                    <?php 
                      echo match($row['order_status']) {
                        'Pending' => 'bg-warning text-dark',
                        'Shipped' => 'bg-info text-dark',
                        'Delivered' => 'bg-success',
                        'Cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                      };
                    ?>">
                    <?= htmlspecialchars($row['order_status']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($row['order_date']))) ?></td>
                <td>
                  <form action="../../modules/admin/update_order_status.php" method="POST" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                    <select name="status" class="form-select form-select-sm" style="width:auto;">
                      <?php
                        $statuses = ['Pending','Shipped','Delivered','Cancelled'];
                        foreach ($statuses as $status) {
                          $selected = ($row['order_status'] === $status) ? 'selected' : '';
                          echo "<option value='$status' $selected>$status</option>";
                        }
                      ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-success">
                      <i class="bi bi-check-circle"></i> Update
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No orders found.</td>
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
