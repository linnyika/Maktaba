<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Corrected paths (absolute, to avoid confusion)
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';
require_once __DIR__ . '/Exception.php';

function sendOtpEmail($email, $full_name, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barasaderrick44@gmail.com'; // ✅ fix your email here
        $mail->Password = 'dzejkijuaqrevhjx';         // ✅ your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('barasaderrick44@gmail.com', 'Maktaba');
        $mail->addAddress($email, $full_name);

        $mail->isHTML(true);
        $mail->Subject = 'Maktaba Account Verification OTP';
        $mail->Body = "
            <h3>Hello $full_name,</h3>
            <p>Your Maktaba verification OTP is:</p>
            <h2>$otp</h2>
            <p>This code expires in 10 minutes.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
