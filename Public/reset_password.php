<?php
include('../database/config.php');

$email = $_GET['email'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE customers SET password_hash = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $new_pass, $email);

    if ($stmt->execute()) {
        $message = "Password updated successfully! You can now log in.";
    } else {
        $message = "Error updating password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <h2>Set New Password</h2>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="password" name="password" placeholder="New Password" required>
        <button type="submit">Update Password</button>
    </form>
    <p><?= $message ?></p>
</body>
</html>
