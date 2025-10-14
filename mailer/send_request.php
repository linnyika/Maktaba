<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // your SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email@gmail.com'; // your email
    $mail->Password = 'your_app_password';    // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your_email@gmail.com', 'Maktaba');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Maktaba Password Reset OTP';
    $mail->Body    = "Your OTP for password reset is: <b>$otp</b><br>Valid for 10 minutes.";

    $mail->send();
} catch (Exception $e) {
    error_log("Mailer Error: {$mail->ErrorInfo}");
}
?>
