<?php
// password_reset/reset_request.php
include('../database/config.php');
include('../includes/otp_helper.php');
include('../mailer/send_request.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = generateOTP();
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $update = $conn->prepare("UPDATE customers SET otp_code = ?, otp_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $expiry, $email);
        $update->execute();

        include('../mailer/send_reset_otp.php'); 

        $message = "An OTP has been sent to your email.";
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Request Password Reset</title></head>
<body>
    <h2>Reset Your Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send OTP</button>
    </form>
    <p><?= $message ?></p>
</body>
</html>
