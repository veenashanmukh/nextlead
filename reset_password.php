<?php
session_start();
require 'connection.php';

$message = "";

if (!isset($_GET['token']) || !isset($_SESSION['reset_token']) || $_GET['token'] !== $_SESSION['reset_token']) {
    $message = "Invalid or expired token.";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['password'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        $table = $_SESSION['reset_table'];

        $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        if ($stmt->execute()) {
            $message = "Password has been successfully updated. <a href='login.php'>Login here</a>.";
            session_unset(); // clear session token
        } else {
            $message = "Error updating password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container">
        <div class="mx-auto p-4 bg-white shadow rounded" style="max-width: 500px;">
            <h2 class="text-center mb-4">Reset Password</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php elseif (isset($_GET['token'])): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="password">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
