<?php
include_once('../config/config.php');
include_once('../includes/otp_helper.php');

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

    if (!$row) {
        $message = "Email not found.";
    } elseif ($row['otp_code'] === $otp && strtotime($row['otp_expiry']) > time()) {
        // OTP valid → update password
        $update = $conn->prepare("UPDATE customers SET password_hash = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update->bind_param("ss", $new_password, $email);
        if ($update->execute()) {
            $message = "✅ Password reset successful! You can now <a href='../login.php'>login</a>.";
        } else {
            $message = "Error updating password.";
        }
    } else {
        $message = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Maktaba</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ffff, #00ddffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 { text-align: center; color:  #2ba0e8f9; }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #00a2ffff;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background: #180a0117; }
        .msg { color: red; text-align: center; margin-top: 10px; }
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
