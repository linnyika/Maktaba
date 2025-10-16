<?php
include('../database/config.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);

    // Check OTP
    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($result['success']) {
        header("Location: reset_password.php?email=" . urlencode($email));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maktaba | Verify OTP</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #28a745, #7ee787);
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
      color: #155724;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #28a745;
      color: #fff;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #218838;
    }
    .message {
      margin-top: 15px;
      font-size: 14px;
      color: #333;
    }
  </style>
</head>
<body>
<div class="reset-box">
    <h2>Enter OTP & New Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Your email" required>
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <input type="password" name="new_password" placeholder="New password" required>
        <button type="submit">Reset Password</button>
    </form>
    <div class="msg"><?= $message ?></div>
</div>
</body>
</html>
