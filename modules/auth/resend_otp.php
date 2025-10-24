<?php
require_once __DIR__ . '/../../includes/otp_helper.php';
require_once __DIR__ . '/../../mailer/send_otp.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $result = resendOTP($email);

    if ($result['success']) {
        $otp = $result['otp'];
        // Send new OTP email
        $sent = sendOtpEmail($email, 'User', $otp);
        if ($sent) {
            $message = "<div class='alert alert-success text-center'>New OTP sent to your email.</div>";
        } else {
            $message = "<div class='alert alert-danger text-center'>Could not send new OTP. Try again later.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger text-center'>Error updating OTP. Please check your email and try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend OTP - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="card p-4 border-0 shadow-lg text-center">
      <img src="../../assets/img/bg.png" alt="Maktaba Logo" width="70" class="mb-3">
      <h3 class="fw-bold text-primary mb-3">Resend OTP</h3>

      <?php echo $message; ?>

      <form method="POST" action="">
        <div class="mb-3 text-start">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mb-3">Resend OTP</button>
      </form>

      <p class="mb-0">
        <a href="verify_otp.php" class="text-primary text-decoration-none fw-semibold">Back to Verify OTP</a>
      </p>
    </div>
  </div>
</body>
</html>