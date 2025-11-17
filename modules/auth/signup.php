<?php
require_once("../../database/config.php");
require_once("../../mailer/send_otp.php"); // make sure this is configured correctly

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $message = "<div class='alert alert-warning'>Passwords do not match.</div>";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Check if email already exists
        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "<div class='alert alert-warning'>Email already registered. Please log in.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, user_role, otp_code, otp_expiry, is_verified) 
                                    VALUES (?, ?, ?, 'customer', ?, ?, 0)");
            $stmt->bind_param("sssss", $full_name, $email, $password_hash, $otp, $otp_expiry);

            if ($stmt->execute()) {
                // Send OTP email
                if (sendOtpEmail($email, $full_name, $otp)) {
                    header("Location: verify_otp.php?email=" . urlencode($email));
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>Account created but OTP email failed to send. Contact admin.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Something went wrong: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Sign Up</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg border-0 p-4" style="width: 400px; border-radius: 15px;">
    <div class="card-body text-center">
      <img src="../../assets/img/bg.png" alt="Maktaba Logo" width="60" class="mb-3">
      <h3 class="fw-bold text-primary mb-3">Create Account</h3>

      <?php echo $message; ?>

      <form method="POST" action="">
        <div class="mb-3 text-start">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Your full name" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Choose a strong password" required>
        </div>
        <div class="mb-3 text-start">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mb-3">Sign Up</button>
      </form>

      <p class="mb-0">Already have an account? 
        <a href="login.php" class="text-primary text-decoration-none fw-semibold">Login here</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
