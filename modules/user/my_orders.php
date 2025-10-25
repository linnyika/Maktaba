<?php
require_once("../../includes/session_check.php");
require_once("../../database/config.php");

// get the current user's ID
$user_id = $_SESSION['user_id'];

// fetch this user's orders
$stmt = $conn->prepare("SELECT order_id, order_date, total_amount, order_status, payment_status 
                        FROM orders 
                        WHERE user_id = ? 
                        ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders | Maktaba</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="text-center text-primary mb-4">My Orders</h2>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-striped shadow-sm">
      <thead class="table-primary">
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Total (₵)</th>
          <th>Order Status</th>
          <th>Payment Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo htmlspecialchars($row['order_id']); ?></td>
            <td><?php echo htmlspecialchars($row['order_date']); ?></td>
            <td><?php echo number_format($row['total_amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['order_status']); ?></td>
            <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info text-center">
      You haven’t placed any orders yet.
    </div>
  <?php endif; ?>
</div>

</body>
</html>
