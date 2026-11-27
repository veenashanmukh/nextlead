<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include 'connection.php';

// Handle the delete request
if (isset($_GET['delete_id'])) {
    $candidate_id = $_GET['delete_id'];

    // Prepare and execute delete query
    $sql = "DELETE FROM candidates WHERE candidate_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $candidate_id);

    if ($stmt->execute()) {
        $message = "Candidate deleted successfully.";
    } else {
        $message = "Error deleting candidate: " . $conn->error;
    }

    $stmt->close();
}

// Fetch candidates from the database
$sql = "SELECT * FROM candidates";  // Make sure the table name is 'candidates'
$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}

$candidates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
} else {
    $message = "No candidates found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Blue Header */
        .navbar {
            background-color: #007bff;
        }

        /* Card Style */
        .card {
            background-color: #ffffff;  /* White background for contrast */
            color: #333; /* Dark text for better readability */
            border: 1px solid #ddd; /* Light border */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for card effect */
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
        }

        /* Hover Effect on Card */
        .card:hover {
            transform: translateY(-5px); /* Slight lift effect */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2); /* Darker shadow on hover */
        }

        /* Card Title */
        .card-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #007bff; /* Blue color for the title for contrast */
        }

        /* Card Text */
        .card-text {
            font-size: 1rem;
        }

        /* Button Styling */
        .btn-primary {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #bd2130;
        }

        /* Consistent spacing in cards */
        .dashboard-card {
            min-height: 180px;
        }

        /* Container for consistent card spacing */
        .row-cols-md-3 {
            margin-top: 30px;
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
<div class="navbar navbar-dark bg-primary px-3">
    <span class="navbar-brand text-white">Candidates</span>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

<div class="container mt-5">
    <h2>Manage Candidates</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-md-3 g-4 mt-4">
        <?php foreach ($candidates as $candidate): ?>
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                        <p class="card-text"><strong>Gender:</strong> <?php echo htmlspecialchars($candidate['gender']); ?></p>
                        <p class="card-text"><strong>Party:</strong> <?php echo htmlspecialchars($candidate['party_name']); ?></p>
                        <p class="card-text"><strong>Constituency:</strong> <?php echo htmlspecialchars($candidate['constituency_id']); ?></p>
                        <p class="card-text"><strong>Votes:</strong> <?php echo htmlspecialchars($candidate['votes']); ?></p>
                        <div class="d-flex justify-content-between">
                            <a href="edit_candidate.php?id=<?php echo $candidate['candidate_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $candidate['candidate_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this candidate?');">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
