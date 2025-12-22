<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                            // Use SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Gmail SMTP server
    $mail->SMTPAuth   = true;                                   // Enable SMTP auth
    $mail->Username   = 'veenasb2006@gmail.com';                  // Your Gmail address
    $mail->Password   = 'pttfbxmmcgjwoumh';               // The 16-char App Password
    $mail->SMTPSecure = 'tls';                                  // Use TLS encryption
    $mail->Port       = 587;                                    // TLS port

    // Recipients
    $mail->setFrom('veenasb2006@gmail.com', 'veena sb');
    $mail->addAddress('gireeshpatil2005@gmail.com', 'gireesh patil');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body    = 'This is a test email sent using <b>PHPMailer</b> and Gmail SMTP.';

    $mail->send();
    echo 'Message has been sent successfully.';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
