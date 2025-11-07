<?php
require_once __DIR__ . '/../../includes/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation - Maktaba</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../../assets/css/user.css" rel="stylesheet">
</head>
<body>
  <?php include("../../includes/user_nav.php"); ?>

  <div class="container my-5 text-center">
    <div class="card p-5 shadow-sm">
      <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
      <h3 class="text-success mb-3">Order Confirmed!</h3>
      <p class="lead text-muted">Your order has been successfully placed and is being processed.</p>
      <p class="text-muted mb-4">You will receive a confirmation email or SMS shortly.</p>

      <a href="browse_books.php" class="btn btn-outline-primary">
        <i class="bi bi-book"></i> Continue Shopping
      </a>
      <a href="my_orders.php" class="btn btn-success ms-2">
        <i class="bi bi-bag-check"></i> View My Orders
      </a>
    </div>
  </div>

  <footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore</small>
  </footer>
</body>
</html>
