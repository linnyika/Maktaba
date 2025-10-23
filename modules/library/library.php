<?php
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: /Maktaba/modules/auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5 text-center">
    <h2 class="text-primary fw-bold">Welcome to the Library Section</h2>
    <p class="lead">Here you’ll be able to view and borrow books soon!</p>
    <a href="/Maktaba/modules/dashboard/dashboard.php" class="btn btn-outline-primary mt-3">&larr; Back to Dashboard</a>
  </div>
</body>
</html>
