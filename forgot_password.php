<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require 'connection.php';
require __DIR__ . '/vendor/autoload.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $tables = ['voters', 'candidates', 'admins'];
    $user = null;
    $tableFound = null;

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $tableFound = $table;
            break;
        }
    }

    if ($user) {
        $token = bin2hex(random_bytes(16));
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_table'] = $tableFound;

        $resetLink = "http://localhost/project/reset_password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = trim((string) getenv('SMTP_USER'));        // change to your email
            $mail->Password = str_replace(' ', '', trim((string) getenv('SMTP_PASS')));           // use app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom(trim((string) getenv('SMTP_USER')), 'Online Voting System');
            $mail->addAddress($email, $user['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi {$user['name']},<br><br>
                Click the link below to reset your password:<br>
                <a href='$resetLink'>$resetLink</a><br><br>
                If you did not request this, please ignore this email.";

            $mail->send();
            $message = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $message = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $message = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body class="bg-light py-5">
    <div class="container">
        <div class="mx-auto p-4 bg-white shadow rounded" style="max-width: 500px;">
            <h2 class="text-center mb-4">Forgot Password</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email">Enter your email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html>
