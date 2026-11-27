<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include 'connection.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $election_date = $_POST['election_date'];
    $status = $_POST['status'];

    if (empty($name) || empty($start_date) || empty($end_date) || empty($election_date) || empty($status)) {
        $message = "<div class='alert alert-danger'>Please fill in all fields.</div>";
    } else {
        $sql = "INSERT INTO election (name, start_date, end_date, election_date, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $start_date, $end_date, $election_date, $status);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Election created successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Election</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 80px; /* To offset the fixed header */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #222;
            min-height: 100vh;
        }

        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 100px);
            padding: 20px;
        }

        .form-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 30px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            background-color: #007bff;
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>

    <nav class="navbar navbar-dark bg-primary fixed-top px-3">
        <span class="navbar-brand text-white">Create Election</span>
        <a href="logout.php" class="btn btn-light">Logout</a>
    </nav>

    <div class="form-container">
        <div class="form-card">
            <h3 class="mb-4 text-center">Create New Election</h3>

            <?php if (isset($message)) echo $message; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Election Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="election_date" class="form-label">Election Day</label>
                    <input type="date" name="election_date" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100">Create Election</button>
            </form>
        </div>
    </div>

</body>
</html>
