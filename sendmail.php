<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Ensure this path is correct

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                            // Use SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Gmail SMTP server
    $mail->SMTPAuth   = true;                                   // Enable SMTP auth
    $mail->Username = trim((string) getenv('SMTP_USER'));                  // Your Gmail address
    $mail->Password = str_replace(' ', '', trim((string) getenv('SMTP_PASS')));               // The 16-char App Password
    $mail->SMTPSecure = 'tls';                                  // Use TLS encryption
    $mail->Port       = 587;                                    // TLS port

    // Recipients
    $mail->setFrom(trim((string) getenv('SMTP_USER')), 'veena sb');
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
