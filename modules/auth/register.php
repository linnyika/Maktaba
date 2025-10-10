<?php
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../mailer/send_otp.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Generate OTP (6 digits)
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Check if email exists
    $check = $conn->prepare("SELECT email FROM customers WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>Email already registered. Please log in.</p>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("
            INSERT INTO customers (full_name, email, password_hash, otp_code, otp_expiry, is_verified)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmt->bind_param("sssss", $full_name, $email, $password_hash, $otp, $otp_expiry);

        if ($stmt->execute()) {
            // Send OTP email
            if (sendOtpEmail($email, $full_name, $otp)) {
                echo "<p style='color:green;'>Registration successful! OTP sent to your email.</p>";
                echo "<a href='verify_otp.php'>Click here to verify</a>";
            } else {
                echo "<p style='color:red;'>Registration saved but OTP could not be sent. Please contact admin.</p>";
            }
        } else {
            echo "<p style='color:red;'>Registration failed. Try again later.</p>";
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Maktaba</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="font-family: Arial, sans-serif; background-color:#f8f9fa;">
    <div style="width: 400px; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
        <h2 style="text-align:center; color:#0056b3;">Register</h2>
        <form method="POST" action="">
            <label>Full Name:</label><br>
            <input type="text" name="full_name" required style="width:100%; padding:8px;"><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" required style="width:100%; padding:8px;"><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required style="width:100%; padding:8px;"><br><br>

            <button type="submit" style="width:100%; background-color:#007bff; color:white; padding:10px; border:none; border-radius:5px;">Register</button>
        </form>
    </div>
</body>
</html>
