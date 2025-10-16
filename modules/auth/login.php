<?php
session_start();
require_once("../../database/config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['is_verified'] == 1) {
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['customer_id'] = $user['customer_id'];
                    $_SESSION['fullname'] = $user['full_name'];
                    header("Location: ../dashboard/dashboard.php");
                    exit;
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Please verify your account first (check your email).";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Minty Bootswatch Theme -->
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg border-0 p-4" style="width: 400px; border-radius: 15px;">
    <div class="card-body text-center">
      <img src="../../assets/img/logo.jpg" alt="Maktaba Logo" width="60" class="mb-3">
      <h3 class="fw-bold text-primary mb-3">Login to Maktaba</h3>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3 text-start">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <a href="dashboard/dashboard.php" class="btn btn-success btn-lg w-100 mb-3">Submit</a>
      </form>

      <p class="mb-0">Don’t have an account? 
        <a href="signup.php" class="text-primary text-decoration-none fw-semibold">Sign up here</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
