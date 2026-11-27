<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include 'connection.php';

$sql = "SELECT voter_id, name, phone, gender, constituency_id, age, election_id FROM voters";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg'); /* adjust path if needed */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }

        .blurred {
            color: transparent;
            text-shadow: 0 0 5px rgba(0,0,0,0.5);
            filter: blur(5px);
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
        }

        .navbar {
            border-radius: 0;
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
    <div class="navbar navbar-dark bg-dark px-3">
        <span class="navbar-brand">Manage Voters</span>
        <a href="logout.php" class="btn btn-light">Logout</a>
    </div>

    <div class="container mt-5">
        <h2>All Voters</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Voter ID</th>
                        <th>Name (Blurred)</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Constituency</th>
                        <th>Age</th>
                        <th>Election ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['voter_id']); ?></td>
                            <td><span class="blurred"><?php echo htmlspecialchars($row['name']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['constituency_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['age']); ?></td>
                            <td><?php echo htmlspecialchars($row['election_id']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No voters found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
