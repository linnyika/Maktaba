<?php
include('../database/config.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT otp_expiry FROM customers WHERE email = ? AND otp_code = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['otp_expiry']) > time()) {
            // OTP is valid
            header("Location: reset_password.php?email=" . urlencode($email));
            exit;
        } else {
            $message = "OTP expired.";
        }
    } else {
        $message = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify OTP</title></head>
<body>
    <h2>Enter OTP</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify OTP</button>
    </form>
    <p><?= $message ?></p>
</body>
</html>
