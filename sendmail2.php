<?php
// Include PHPMailer classes manually
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = trim((string) getenv('SMTP_USER'));
    $mail->Password = str_replace(' ', '', trim((string) getenv('SMTP_PASS')));
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true
    )
);

    $mail->setFrom(trim((string) getenv('SMTP_USER')), 'veena sb');
    $mail->addAddress('gireeshpatil2005@gmail.com', 'gireesh patil');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email sent using PHPMailer without Composer.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>
