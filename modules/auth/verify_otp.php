<?php
require_once __DIR__ . '/../../includes/otp_helper.php';
require_once __DIR__ . '/../../mailer/send_otp.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $result = verifyOTP($email, $otp);

    if ($result['success']) {
        $message = "<p style='color:green; text-align:center;'>{$result['message']}</p>";
        $redirect = "<p style='text-align:center;'><a href='login.php'>Go to Login</a></p>";
    } else {
        $message = "<p style='color:red; text-align:center;'>{$result['message']}</p>";
        $redirect = "<p style='text-align:center;'><a href='resend_otp.php'>Resend OTP</a></p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Maktaba</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="font-family: Arial; background-color:#f8f9fa;">
<div style="width:400px; margin:50px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 8px rgba(0,0,0,0.1);">
    <h2 style="text-align:center; color:#0056b3;">Verify OTP</h2>
    <?= $message ?? '' ?>
    <?= $redirect ?? '' ?>
    <form method="POST" action="">
        <label>Email:</label><br>
        <input type="email" name="email" required style="width:100%; padding:8px;"><br><br>

        <label>Enter OTP:</label><br>
        <input type="text" name="otp" maxlength="6" required style="width:100%; padding:8px;"><br><br>

        <button type="submit" style="width:100%; background-color:#007bff; color:white; padding:10px; border:none; border-radius:5px;">Verify</button>
    </form>
</div>
</body>
</html>
