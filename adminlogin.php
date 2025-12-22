<?php
session_start();
include 'connection.php';

$message = "";

if (isset($_POST['submit'])) {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Admin table login check
    $sql = "SELECT * FROM admins WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['user_type'] = 'Admin';
            $_SESSION['admin_id'] = $row['id'];

            header("Location: admin.php");
            exit;
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "Admin not found. <a href='register.php'>Register here</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Online Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            padding-top: 70px; /* Prevent content under the fixed navbar */
        }
    </style>
</head>
<body>
    <!-- Fixed Navbar -->
    <div class="navbar-container fixed-top bg-dark d-flex justify-content-between align-items-center px-3 py-2 shadow">
        <a class="navbar-brand text-white mb-0 h4" href="#">Online Voting System</a>
        <a href="welcome.php" class="btn btn-light">Home</a>
    </div>

    <!-- Admin Login Form -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h2 class="text-center mb-4">Admin Login</h2>
            <p class="text-center mb-3" style="color: <?php echo (isset($_GET['message']) && $_GET['message'] === 'loggedout') ? 'green' : 'red'; ?>">
                <?php 
                if (isset($_GET['message']) && $_GET['message'] === 'loggedout') {
                    echo "You have been successfully logged out.";
                } elseif (!empty($message)) {
                    echo $message;
                }
                ?>
            </p>
            <form action="" method="post">
                <div class="mb-3">
                    <input type="number" name="phone" class="form-control" placeholder="Enter your phone number" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary w-100">Login Now</button>
            </form>
            <p class="text-center mt-3">Don't have an account? <a href="adminregister.php">Register Now</a></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-light text-center py-3">
        <small>&copy; 2025 Online Voting System. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
