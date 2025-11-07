<?php
require_once("../../includes/session_check.php");
require_once("../../database/config.php");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT order_id, order_date, total_amount, order_status, payment_status 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include("../../includes/user_nav.php"); ?>

<div class="container mt-5">
  <h2 class="text-center text-primary mb-4">My Orders</h2>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-striped shadow-sm">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Total (KSh)</th>
          <th>Order Status</th>
          <th>Payment</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $row['order_id']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td><?php echo number_format($row['total_amount'], 2); ?></td>
            <td>
              <span class="badge bg-<?php echo ($row['order_status']=='Delivered')?'success':(($row['order_status']=='Pending')?'warning text-dark':'secondary'); ?>">
                <?php echo htmlspecialchars($row['order_status']); ?>
              </span>
            </td>
            <td>
              <span class="badge bg-<?php echo ($row['payment_status']=='Paid')?'success':(($row['payment_status']=='Pending')?'warning text-dark':'danger'); ?>">
                <?php echo htmlspecialchars($row['payment_status']); ?>
              </span>
            </td>
            <td>
              <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye"></i> View
              </a>
            </td>
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
