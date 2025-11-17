<?php
// Include PHPMailer manually
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOtpEmail($toEmail, $fullName, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';           // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'linda.nyika@strathmore.edu';     // your Gmail email
        $mail->Password   = 'rvgi nvlg imnb voeg';       // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('linda.nyika@strathmore.edu', 'Maktaba OTP');
        $mail->addAddress($toEmail, $fullName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code for Maktaba';
        $mail->Body    = "
            <p>Hi <strong>{$fullName}</strong>,</p>
            <p>Your OTP code is: <strong>{$otp}</strong></p>
            <p>This code will expire in 10 minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Show exact error for debugging
        echo "Mailer Error: " . $mail->ErrorInfo;
        error_log("OTP email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
