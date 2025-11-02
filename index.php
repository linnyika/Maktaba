<?php
// Start the session
session_start();

// Include configuration and helper functions
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maktaba</title>

    <!-- Bootswatch Minty Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">

    <!-- Optional Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <img src="assets/img/sm.png" alt="Maktaba Logo" width="40" class="me-2">
      Maktaba
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="modules/auth/login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="modules/auth/signup.php">Sign Up</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Floating Card -->
<main class="flex-grow-1 d-flex align-items-center justify-content-center">
  <div class="card shadow-lg border-0 p-4" style="max-width: 450px; border-radius: 20px;">
    <div class="card-body text-center">
      <img src="assets/img/bg.png" alt="Maktaba Logo" width="70" class="mb-3">
      <h3 class="fw-bold text-primary mb-2">Welcome to Maktaba</h3>
      <p class="text-muted mb-4">Your digital bookstore explore, shop, learn, and grow.</p>
      <a href="modules/auth/signup.php" class="btn btn-success btn-lg w-100">Get Started</a>
    </div>
  </div>
</main>

<!-- Footer -->
<footer class="bg-primary text-white text-center py-3 mt-auto">
  <p class="mb-0">&copy; <?php echo date('Y'); ?> Maktaba Library Store. All Rights Reserved.</p>
</footer>

<!-- JS (Bootstrap Bundle) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
