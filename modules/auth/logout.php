<?php
session_start();
$_SESSION = array();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Logout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Minty Bootstrap Theme -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg border-0 p-4 text-center" style="width: 400px; border-radius: 15px;">
    <div class="card-body">
      <img src="assets/img/logobig.png" alt="Maktaba Logo" width="60" class="mb-3">
      <h3 class="fw-bold text-success mb-3">You’ve Logged Out</h3>
      <p class="text-muted mb-4">
        We hope you enjoyed reading with us at <strong>Maktaba</strong>.<br>
        Come back soon for more great books and learning!
      </p>
      <a href="modules/auth/login.php" class="btn btn-success w-100">Login Again</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

