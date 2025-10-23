<?php
ini_set('session.cookie_path', '/');
session_start();
include '../../database/config.php';

// Restrict access to logged-in users only
if (!isset($_SESSION['customer_id'])) {
    header("Location: /Maktaba/modules/auth/login.php");
    exit;
}

// Get user details
$stmt = $conn->prepare("SELECT email, full_name FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">Maktaba</a>
      <div class="d-flex">
        <a href="/Maktaba/modules/auth/logout.php" class="btn btn-light btn-sm text-primary fw-semibold">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="card shadow-lg border-0 p-4">
      <h3 class="text-center text-primary fw-bold mb-3">Welcome, <?php echo htmlspecialchars($user['full_name']); ?> </h3>
      <p class="text-center">Your email: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>

      <div class="text-center mt-4 d-flex justify-content-center gap-3">
        <a href="/Maktaba/modules/library/library.php" class="btn btn-success px-4">View Library</a>
        <a href="/Maktaba/modules/auth/logout.php" class="btn btn-danger px-4">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>
