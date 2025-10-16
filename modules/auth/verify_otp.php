<?php
require_once __DIR__ . '/../../includes/otp_helper.php';
require_once __DIR__ . '/../../mailer/send_otp.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);

    $result = verifyOTP($email, $otp);

    if ($result['success']) {
        $message = "<div class='alert alert-success text-center'>{$result['message']}</div>";
        $redirect = "<div class='text-center mt-2'><a href='login.php' class='text-decoration-none text-success fw-bold'>Go to Login</a></div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>{$result['message']}</div>";
        $redirect = "<div class='text-center mt-2'><a href='resend_otp.php' class='text-decoration-none text-danger fw-bold'>Resend OTP</a></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Maktaba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-lg border-0 p-4" style="width: 400px; border-radius: 15px;">
    <div class="card-body">
        <h3 class="text-center text-success mb-4">Verify OTP</h3>

        <?= $message ?? '' ?>
        <?= $redirect ?? '' ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Enter OTP</label>
                <input type="text" name="otp" maxlength="6" class="form-control" placeholder="Enter the 6-digit code" required>
            </div>

            <a href="modules/auth/login.php" class="btn btn-success btn-lg w-100 mb-3">Submit</a>
        </form>
    </div>
</div>

</body>
</html>