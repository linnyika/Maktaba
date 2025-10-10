<?php
require_once __DIR__ . '/../../includes/otp_helper.php';
require_once __DIR__ . '/../../mailer/send_otp.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $result = resendOTP($email);

    if ($result['success']) {
        $otp = $result['otp'];
        // send new OTP email
        $sent = sendOtpEmail($email, 'User', $otp);
        if ($sent) {
            $message = "<p style='color:green; text-align:center;'>New OTP sent to your email.</p>";
        } else {
            $message = "<p style='color:red; text-align:center;'>Could not send new OTP. Try again later.</p>";
        }
    } else {
        $message = "<p style='color:red; text-align:center;'>Error updating OTP. Please check your email and try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend OTP - Maktaba</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="font-family: Arial; background-color:#f8f9fa;">
<div style="width:400px; margin:50px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 8px rgba(0,0,0,0.1);">
    <h2 style="text-align:center; color:#0056b3;">Resend OTP</h2>
    <?= $message ?? '' ?>
    <form method="POST" action="">
        <label>Email:</label><br>
        <input type="email" name="email" required style="width:100%; padding:8px;"><br><br>

        <button type="submit" style="width:100%; background-color:#007bff; color:white; padding:10px; border:none; border-radius:5px;">Resend OTP</button>
    </form>
</div>
</body>
</html>
