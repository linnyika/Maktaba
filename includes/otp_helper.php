<?php
require_once __DIR__ . '/../database/config.php';

function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function verifyOTP($email, $otp) {
    global $conn;

    // FIX: Changed 'customers' to 'users' table
    $stmt = $conn->prepare("SELECT user_id, otp_code, otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return ['success' => false, 'message' => 'Email not found.'];
    }

    $stored_otp = $row['otp_code'];
    $expiry = $row['otp_expiry'];

    // Check if OTP exists and is not expired
    if ($stored_otp && $otp === $stored_otp && strtotime($expiry) > time()) {
        // Mark as verified - FIX: Changed to 'users' table
        $update = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        
        if ($update->execute()) {
            return ['success' => true, 'message' => 'Account verified successfully! You can now login.'];
        } else {
            return ['success' => false, 'message' => 'Verification failed. Please try again.'];
        }
    } else {
        return ['success' => false, 'message' => 'Invalid or expired OTP.'];
    }
}

function resendOTP($email) {
    global $conn;

    $new_otp = rand(100000, 999999);
    $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // FIX: Changed 'customers' to 'users' table
    $stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $new_otp, $new_expiry, $email);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        return ['success' => true, 'otp' => $new_otp];
    } else {
        return ['success' => false, 'message' => 'Email not found or OTP update failed.'];
    }
}

// New function to check if user is verified
function isUserVerified($email) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row && $row['is_verified'] == 1;
}

// New function to get OTP expiry time (for frontend timer)
function getOTPExpiryTime($email) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row ? $row['otp_expiry'] : null;
}
?>