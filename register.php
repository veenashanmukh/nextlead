<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connection.php';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $user_type = $_POST['user_type'];
    $gender = $_POST['gender'];
    $constituency_id = intval($_POST['constituency_id']);
    $age = intval($_POST['age']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $election_id = intval($_POST['election_id']);

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    if ($age < 18) {
        die("You must be at least 18 years old to register.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if phone or email already registered
    $check_sql = "
        SELECT phone FROM voters WHERE phone = ? OR email = ?
        UNION 
        SELECT phone FROM candidates WHERE phone = ? OR email = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ssss", $phone, $email, $phone, $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows > 0) {
        die("This phone or email is already registered.");
    }

    // Validate election ID exists
    $stmt_check_election = $conn->prepare("SELECT election_id FROM election WHERE election_id = ?");
    $stmt_check_election->bind_param("i", $election_id);
    $stmt_check_election->execute();
    $result_election = $stmt_check_election->get_result();
    if ($result_election->num_rows === 0) {
        die("Invalid election ID.");
    }

    // Insert into appropriate table
    if ($user_type === "Voter") {
        $sql = "INSERT INTO voters (name, email, phone, gender, constituency_id, age, password, election_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiisi", $name, $email, $phone, $gender, $constituency_id, $age, $hashed_password, $election_id);
    } elseif ($user_type === "Candidate") {
    if (!isset($_POST['party_name']) || empty(trim($_POST['party_name']))) {
        die("Party Name is required for candidates.");
    }
    $party_name = trim($_POST['party_name']);
    $sql = "INSERT INTO candidates (name, email, phone, gender, party_name, constituency_id, age, password, election_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssiisi", $name, $email, $phone, $gender, $party_name, $constituency_id, $age, $hashed_password, $election_id);

}
 else {
        die("Invalid user type.");
    }

    // Execute insert
    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;

        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['phone'] = $phone;
        $_SESSION['election_id'] = $election_id;

        if ($user_type === "Candidate") {
            $_SESSION['candidate_id'] = $last_id;
            header("Location: candidate.php");
        } else {
            $_SESSION['voter_id'] = $last_id;
            header("Location: voter.php");
        }
        exit;
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $stmt_check->close();
    $stmt_check_election->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registration</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-image: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
        }

        .navbar-container {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
        }

        .form.container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            margin-top: 100px;
        }

        footer {
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
    <div class="navbar-container d-flex justify-content-between align-items-center p-3 bg-dark text-white">
        <a class="navbar-brand text-white" href="#">NextLead</a>
        <a href="welcome.php" class="btn btn-light">Home</a>
    </div>

    <div class="form container mt-5" style="max-width: 500px;">
        <form action="" method="post" novalidate>
            <h2 class="text-center mb-4">Registration</h2>

            <div class="form-group mb-3">
                <input type="text" name="name" placeholder="Enter your name" class="form-control" required />
            </div>
            <div class="form-group mb-3">
                <input type="email" name="email" placeholder="Enter your email" class="form-control" required />
            </div>
            <div class="form-group mb-3">
                <input type="tel" name="phone" placeholder="Enter your phone number" class="form-control" pattern="[0-9]{10}" maxlength="10" required />
            </div>
            <div class="form-group mb-3">
                <select name="user_type" id="user_type" class="form-control" required>
                    <option value="">Select user type</option>
                    <option value="Voter">Voter</option>
                    <option value="Candidate">Candidate</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <select name="gender" class="form-control" required>
                    <option value="" disabled selected>Select your gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                    <option value="Prefer not to say">Prefer not to say</option>
                </select>
            </div>
            <div class="form-group mb-3" id="partyNameGroup" style="display: none;">
                <input type="text" name="party_name" placeholder="Enter your Party Name" class="form-control" />
            </div>
            <div class="form-group mb-3">
                <input type="number" name="constituency_id" placeholder="Enter your Constituency ID" class="form-control" min="1" required />
            </div>
            <div class="form-group mb-3">
                <input type="number" name="age" id="age" placeholder="Enter your age" class="form-control" min="18" required />
                <small id="ageWarning" style="color: red; display: none;">You must be 18 or older to register.</small>
            </div>
            <div class="form-group mb-3">
                <input type="number" name="election_id" placeholder="Enter Election ID" class="form-control" min="1" required />
            </div>
            <div class="form-group mb-3">
                <input type="password" name="password" placeholder="Enter your password" class="form-control" required minlength="6" />
            </div>
            <div class="form-group mb-4">
                <input type="password" name="confirm_password" placeholder="Confirm your password" class="form-control" required minlength="6" />
            </div>
            <button class="btn btn-primary w-100 font-weight-bold" name="submit" type="submit">Register Now</button>
            <p class="mt-3 text-center">Already have an account? <a href="login.php">Login Now</a></p>
        </form>
    </div>

    <footer class="text-center py-3">
        <small>&copy; <?= date('Y') ?> NextLead. All rights reserved.</small>
    </footer>

    <script>
        const userTypeSelect = document.querySelector('select[name="user_type"]');
        const partyNameGroup = document.getElementById('partyNameGroup');
        const partyInput = partyNameGroup.querySelector('input');

        userTypeSelect.addEventListener('change', function () {
            if (this.value === 'Candidate') {
                partyNameGroup.style.display = 'block';
                partyInput.required = true;
            } else {
                partyNameGroup.style.display = 'none';
                partyInput.required = false;
            }
        });

        const ageInput = document.getElementById('age');
        const ageWarning = document.getElementById('ageWarning');
        const submitBtn = document.querySelector('button[name="submit"]');

        ageInput.addEventListener('input', function () {
            const age = parseInt(this.value);
            if (age < 18) {
                ageWarning.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                ageWarning.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
