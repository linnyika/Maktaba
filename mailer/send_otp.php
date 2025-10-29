<?php
// Correct paths - PHPMailer is in a subfolder
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function sendOtpEmail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings - USING YOUR ACTUAL CREDENTIALS
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barasaderrick44@gmail.com'; // Your email
        $mail->Password = 'dzej kiju aqre vhjx'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 30;

        // Enable verbose debug output
        $mail->SMTPDebug = 0; // Set to 2 for detailed debugging

        // Recipients
        $mail->setFrom('barasaderrick44@gmail.com', 'Maktaba Bookstore');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('barasaderrick44@gmail.com', 'Maktaba Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Maktaba Verification Code';
        
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
                    .header { text-align: center; color: #2c5aa0; }
                    .otp-code { font-size: 32px; font-weight: bold; text-align: center; color: #2c5aa0; background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Maktaba Bookstore</h2>
                        <h3>Email Verification</h3>
                    </div>
                    
                    <p>Hello <strong>$name</strong>,</p>
                    
                    <p>Thank you for registering with Maktaba Bookstore. Use the following OTP code to verify your email address:</p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <p>This code will expire in <strong>10 minutes</strong>.</p>
                    
                    <p>If you didn't create an account with Maktaba, please ignore this email.</p>
                    
                    <div class='footer'>
                        <p>&copy; 2024 Maktaba Bookstore. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Alternative plain text version
        $mail->AltBody = "Maktaba Verification Code\n\nHello $name,\n\nYour OTP code is: $otp\n\nThis code expires in 10 minutes.\n\nIf you didn't create an account, please ignore this email.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordResetOtp($email, $name, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barasaderrick44@gmail.com';
        $mail->Password = 'dzej kiju aqre vhjx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 30;

        // Recipients
        $mail->setFrom('barasaderrick44@gmail.com', 'Maktaba Bookstore');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('barasaderrick44@gmail.com', 'Maktaba Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Maktaba Bookstore';
        
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
                    .header { text-align: center; color: #2c5aa0; }
                    .otp-code { font-size: 32px; font-weight: bold; text-align: center; color: #2c5aa0; background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .warning { background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Maktaba Bookstore</h2>
                        <h3>Password Reset Request</h3>
                    </div>
                    
                    <p>Hello <strong>$name</strong>,</p>
                    
                    <p>We received a request to reset your password. Use the following OTP code to proceed:</p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <p>This code will expire in <strong>10 minutes</strong>.</p>
                    
                    <div class='warning'>
                        <p><strong>Note:</strong> If you didn't request a password reset, please ignore this email and ensure your account is secure.</p>
                    </div>
                    
                    <p>Best regards,<br>Maktaba Bookstore Team</p>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Maktaba Password Reset\n\nHello $name,\n\nYour password reset OTP is: $otp\n\nThis code expires in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Test function to verify email configuration
function testEmailConfig() {
    $test_email = "linda.nyika@strathmore.edu"; // Send test to yourself
    $test_name = "Linda Nyika";
    $test_otp = "999999";
    
    return sendOtpEmail($test_email, $test_name, $test_otp);
}
?>