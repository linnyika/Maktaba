<?php
require_once("../../database/config.php");
require_once("../../mailer/send_otp.php");

$message = '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if ($password !== $confirm) {
        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
    } else {
        // Verify OTP
        $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['otp_code'] == $otp && strtotime($user['otp_expiry']) > time()) {
            // Update password
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE users SET password_hash=?, otp_code=NULL, otp_expiry=NULL WHERE email=?");
            $update->bind_param("ss", $hash, $email);
            $update->execute();

            $message = "<div class='alert alert-success'>Password updated! <a href='login.php'>Login</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Invalid or expired OTP.</div>";
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="card p-4 shadow" style="width: 400px;">
<h3 class="text-center mb-3">Reset Password</h3>
<?php echo $message; ?>
<form method="POST" action="">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
    <div class="mb-3">
        <label>OTP</label>
        <input type="text" name="otp" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button class="btn btn-success w-100">Update Password</button>
</form>
</div>
</body>
</html>
