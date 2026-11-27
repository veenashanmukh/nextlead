<?php
session_start();
include 'connection.php';

$message = "";

if (isset($_POST['submit'])) {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $tables = ['voters', 'candidates', 'admins'];
    $found = false;

    foreach ($tables as $table) {
        // Use backticks around table names to prevent reserved word conflicts (optional)
        $sql = "SELECT * FROM `$table` WHERE phone = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password securely
            if (!empty($row['password']) && password_verify($password, $row['password'])) {
                // Common session values
                $_SESSION['name'] = $row['name'];
                $_SESSION['phone'] = $row['phone'];
                $_SESSION['user_type'] = ucfirst(rtrim($table, 's')); // "voters" → "Voter"

                // Set user-specific session variables & redirect accordingly
                if ($table === 'voters') {
                    $_SESSION['voter_id'] = $row['voter_id'];
                    $_SESSION['gender'] = $row['gender'];
                    $_SESSION['constituency_id'] = $row['constituency_id'];
                    $_SESSION['age'] = $row['age'];
                    $_SESSION['election_id'] = $row['election_id'];
                    header("Location: voter.php");
                } elseif ($table === 'candidates') {
                    $_SESSION['candidate_id'] = $row['candidate_id'];
                    $_SESSION['gender'] = $row['gender'];
                    $_SESSION['constituency_id'] = $row['constituency_id'];
                    $_SESSION['age'] = $row['age'];
                    $_SESSION['party_name'] = $row['party_name'];
                    $_SESSION['election_id'] = $row['election_id'];
                    header("Location: candidate.php");
                } else {
                    $_SESSION['admin_id'] = $row['id'];
                    header("Location: admin.php");
                }
                exit;
            } else {
                $message = "Incorrect password.";
                $found = true;
                $stmt->close();
                break;
            }
        }
        $stmt->close();
    }

    if (!$found && empty($message)) {
        $message = "You are not registered. <a href='register.php'>Register here</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - Online Voting System</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-image: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding-top: 80px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .form {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .navbar-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .forgot-password-link {
            display: block;
            margin-top: 10px;
            text-align: right;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="navbar-container d-flex justify-content-between align-items-center p-3 bg-dark">
        <a class="navbar-brand text-white" href="#">Online Voting System</a>
        <a href="welcome.php" class="btn btn-light">Home</a>
    </div>

    <div class="form">
        <form method="POST" novalidate>
            <h2 class="text-center mb-4">Login</h2>
            <p class="text-center" style="color: <?= (isset($_GET['message']) && $_GET['message'] === 'loggedout') ? 'green' : 'red'; ?>">
                <?php
                if (isset($_GET['message']) && $_GET['message'] === 'loggedout') {
                    echo "You have been successfully logged out.";
                } elseif (!empty($message)) {
                    echo $message;
                }
                ?>
            </p>

            <div class="mb-3">
                <input type="tel" name="phone" placeholder="Enter your phone number" class="form-control" pattern="[0-9]{10}" maxlength="10" required />
            </div>

            <div class="mb-3">
                <input type="password" name="password" placeholder="Enter your password" class="form-control" required minlength="6" />
                <a href="forgot_password.php" class="forgot-password-link">Forgot Password?</a>
            </div>

            <button class="btn btn-primary w-100" name="submit" type="submit">Login Now</button>
            <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>

    <footer class="mt-auto bg-light text-center py-3">
        <small>&copy; 2025 Online Voting System. All rights reserved.</small>
    </footer>
</body>
</html>
