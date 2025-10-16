<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function sendResetOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'juliet.nyakiamo@strathmore.edu';  // your Gmail
        $mail->Password = 'aoqb xtmj pqgy utlo';           // your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('jnyakiamovictoria@gmail.com', 'Maktaba');
        $mail->addAddress($email);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Maktaba Password Reset OTP';
        $mail->Body    = "Your OTP for password reset is: <b>$otp</b><br>Valid for 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
