<?php
require_once("../../database/config.php");
require_once("../../mailer/send_otp.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Save OTP in DB
        $update = $conn->prepare("UPDATE users SET otp_code=?, otp_expiry=? WHERE email=?");
        $update->bind_param("sss", $otp, $otp_expiry, $email);
        $update->execute();

        // Send OTP email
        if (sendOtpEmail($email, $user['full_name'], $otp)) {
            header("Location: reset_password.php?email=" . urlencode($email));
            exit();
        } else {
            $message = "<div class='alert alert-danger'>OTP email failed. Contact admin.</div>";
        }

    } else {
        $message = "<div class='alert alert-danger'>No account found with that email.</div>";
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="card p-4 shadow" style="width: 400px;">
<h3 class="text-center mb-3">Forgot Password</h3>
<?php echo $message; ?>
<form method="POST" action="">
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <button class="btn btn-success w-100">Send OTP</button>
</form>
</div>
</body>
</html>
