<?php
<<<<<<< HEAD
include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../includes/otp_helper.php';
require_once __DIR__ . '/../mailer/send_request.php';
=======
// password_reset/reset_request.php
include('../database/config.php');
include('../includes/otp_helper.php');
include('../mailer/send_request.php');
>>>>>>> UI_Interface

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT full_name FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $update = $conn->prepare("UPDATE customers SET otp_code=?, otp_expiry=? WHERE email=?");
            $update->bind_param("sss", $otp, $expiry, $email);
            $update->execute();

            if (sendResetOTP($email, $otp)) {
                $message = "✅ An OTP has been sent to <strong>$email</strong>.";
            } else {
                $message = "❌ Failed to send OTP. Try again later.";
            }
        } else {
            $message = "⚠️ No account found with that email.";
        }
    } else {
        $message = "Please enter your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Password Reset</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #0066cc, #33ccff);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background: #fff;
      padding: 40px 50px;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      width: 350px;
      text-align: center;
    }
    h2 {
      margin-bottom: 15px;
      color: #003366;
    }
    input[type=email] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 20px;
    }
    button {
      background-color: #0066cc;
      color: #fff;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
    }
    button:hover {
      background-color: #004c99;
    }
    p {
      margin-top: 15px;
      color: #444;
    }
    .message {
      font-size: 14px;
      color: #333;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Password Reset</h2>
    <p>Enter your email to receive an OTP.</p>
    <form method="POST">
      <input type="email" name="email" placeholder="Enter your email" required>
      <button type="submit">Send OTP</button>
    </form>
    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
