<?php
require_once __DIR__ . '/../../includes/otp_helper.php';

$message = '';
$redirect = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $result = verifyOTP($email, $otp);

    if ($result['success']) {
        $message = "<div class='alert alert-success text-center'>{$result['message']}</div>";
        $redirect = "<div class='text-center mt-3'><a href='login.php' class='btn btn-success'>Go to Login</a></div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>{$result['message']}</div>";
        $redirect = "<div class='text-center mt-3'><a href='resend_otp.php' class='btn btn-outline-primary'>Resend OTP</a></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-lg border-0 p-4" style="width: 400px; border-radius: 15px;">
    <div class="card-body">
        <h3 class="text-center text-success mb-4">Verify OTP</h3>

      <?php echo $message; ?>

      <form method="POST" action="">
        <div class="mb-3 text-start">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>

        <div class="mb-3 text-start">
          <label class="form-label">Enter OTP</label>
          <input type="text" name="otp" maxlength="6" class="form-control" placeholder="Enter the 6-digit code" required>
        </div>

        <button type="submit" class="btn btn-success w-100 mb-3">Verify OTP</button>
      </form>

      <?php echo $redirect; ?>

    </div>
  </div>
</body>
</html>