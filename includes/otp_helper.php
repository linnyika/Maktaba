<?php
require_once __DIR__ . '/../database/config.php';

function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}


function verifyOTP($email, $otp) {
    global $conn;

    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return ['success' => false, 'message' => 'Email not found.'];
    }

    $stored_otp = $row['otp_code'];
    $expiry = $row['otp_expiry'];

    if ($otp === $stored_otp && strtotime($expiry) > time()) {
        // Mark as verified
        $update = $conn->prepare("UPDATE customers SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();
        return ['success' => true, 'message' => 'Account verified successfully!'];
    } else {
        return ['success' => false, 'message' => 'Invalid or expired OTP.'];
    }
}

function resendOTP($email) {
    global $conn;

    $new_otp = rand(100000, 999999);
    $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $stmt = $conn->prepare("UPDATE customers SET otp_code = ?, otp_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $new_otp, $new_expiry, $email);

    if ($stmt->execute()) {
        return ['success' => true, 'otp' => $new_otp];
    } else {
        return ['success' => false];
    }
}
?>
