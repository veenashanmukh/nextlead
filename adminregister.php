<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $created_at = date("Y-m-d H:i:s");

    if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $check = $conn->prepare("SELECT * FROM admins WHERE username = ? OR phone = ? OR email = ?");
        $check->bind_param("sss", $username, $phone, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "Username, phone number, or email already registered.";
        } else {
            $sql = $conn->prepare("INSERT INTO admins (name, username, email, phone, password, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $sql->bind_param("ssssss", $name, $username, $email, $phone, $hashed_password, $created_at);

            if ($sql->execute()) {
                $_SESSION['name'] = $name;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $_SESSION['user_type'] = 'Admin';
                $_SESSION['admin_id'] = $conn->insert_id;

                header("Location: admin.php");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
            background-size: cover;
            padding-top: 70px;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
        }
        .navbar-container {
            z-index: 1050;
        }
    </style>
</head>
<body>

<!-- Fixed Navbar -->
<div class="navbar-container fixed-top bg-dark d-flex justify-content-between align-items-center px-3 py-2 shadow">
    <a class="navbar-brand text-white mb-0 h4" href="#">Online Voting System</a>
    <a href="welcome.php" class="btn btn-light">Home</a>
</div>

<!-- Registration Form -->
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-4">Admin Register</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Register</button>
            <p class="text-center mt-3">Already an admin? <a href="adminlogin.php">Login here</a></p>
        </form>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-3 mt-5 bg-light">
    <small>&copy; 2025 Online Voting System. All rights reserved.</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
