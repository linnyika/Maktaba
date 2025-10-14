<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

function sendOtpEmail($email, $full_name, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'juliet.nyakiamo@strathmore.edu';  // your Gmail
        $mail->Password = 'aoqb xtmj pqgy utlo';           // your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('jnyakiamovictoria@gmail.com', 'Maktaba');
        $mail->addAddress($email, $full_name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Maktaba Account Verification OTP';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; background-color:#f5f9ff; padding:20px; border-radius:8px;'>
                <h2 style='color:#0056b3;'>Hello $full_name,</h2>
                <p>Your <strong>Maktaba</strong> verification OTP is:</p>
                <h1 style='color:#007bff;'>$otp</h1>
                <p>This code will expire in 10 minutes.</p>
                <br>
                <p style='font-size:12px; color:#555;'>If you didn’t request this, please ignore this email.</p>
            </div>
        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
