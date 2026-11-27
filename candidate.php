<?php
session_start();
include 'connection.php';

// Check if user is logged in and is a candidate
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Candidate') {
    header("Location: login.php");
    exit;
}

$candidate_id = $_SESSION['candidate_id'] ?? null;

if (!$candidate_id) {
    echo "Candidate not found in session. Please log in.";
    exit;
}

// Fetch logged-in candidate info (including election_id and constituency_id)
$stmt = $conn->prepare("SELECT name, phone, gender, age, constituency_id, party_name, election_id FROM candidates WHERE candidate_id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$stmt->bind_result($name, $phone, $gender, $age, $constituency_id, $party_name, $election_id);
$stmt->fetch();
$stmt->close();

date_default_timezone_set('UTC');
$current_time = date('Y-m-d H:i:s');

// Fetch all candidates in the SAME ELECTION and SAME CONSTITUENCY
$sql_results = "SELECT c.candidate_id, c.name, c.party_name, COUNT(v.id) AS vote_count
                FROM candidates c
                LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                WHERE c.election_id = ? AND c.constituency_id = ?
                GROUP BY c.candidate_id
                ORDER BY vote_count DESC, c.name ASC";

$stmt_results = $conn->prepare($sql_results);
$stmt_results->bind_param("ii", $election_id, $constituency_id);
$stmt_results->execute();
$results = $stmt_results->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <title>Candidate Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
            background-size: cover;
            padding-top: 100px;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .fixed-header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .info-box {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
        }
        .message {
            margin-top: 10px;
            text-align: center;
            color: #fff;
            font-weight: 500;
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>

<div class="navbar px-3">
    <span class="navbar-brand">Candidate Dashboard</span>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>
<p class="message">To vote yourself, log in with your voter account.</p>

<div class="container mt-4">
    <!-- Logged-in candidate info -->
    <div class="info-box text-center mb-4">
        <h5 class="mb-3">Your Candidate Information</h5>
        <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($age) ?></p>
        <p><strong>Constituency ID:</strong> <?= htmlspecialchars($constituency_id) ?></p>
        <p><strong>Party Name:</strong> <?= htmlspecialchars($party_name) ?></p>
    </div>

    <!-- Election Results for candidates in the same election & constituency -->
    <div class="card p-4 shadow">
        <h4>Election Results</h4>
        <?php
        // Fetch election details
        $election_query = "SELECT name, start_date, end_date FROM election WHERE election_id = ?";
        $stmt_election = $conn->prepare($election_query);
        $stmt_election->bind_param("i", $election_id);
        $stmt_election->execute();
        $election_result = $stmt_election->get_result();
        $election_info = $election_result->fetch_assoc();
        $stmt_election->close();

        if ($election_info):
            $election_name = $election_info['name'];
            $start_date = $election_info['start_date'];
            $end_date = $election_info['end_date'];
            $has_ended = $end_date < $current_time;
        ?>
            <div class="mt-3">
                <h5><?= htmlspecialchars($election_name) ?> <?= $has_ended ? '(Ended)' : '(Ongoing)' ?></h5>
                <p><strong>Start:</strong> <?= htmlspecialchars($start_date) ?></p>
                <p><strong>End:</strong> <?= htmlspecialchars($end_date) ?></p>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Party</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $candidates = [];
                        while ($row = $results->fetch_assoc()) {
                            $candidates[] = $row;
                            // Highlight logged-in candidate row
                            $highlight = ($row['candidate_id'] == $candidate_id) ? 'table-primary' : '';
                            echo "<tr class='$highlight'>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['party_name']) . "</td>
                                    <td>" . $row['vote_count'] . "</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <?php if ($has_ended): ?>
                    <?php
                    $max_votes = max(array_column($candidates, 'vote_count'));
                    $winners = array_filter($candidates, fn($c) => $c['vote_count'] == $max_votes);
                    ?>
                    <div class="alert alert-success">
                        <strong>Winner<?= count($winners) > 1 ? 's' : '' ?>:</strong><br>
                        <?php foreach ($winners as $winner): ?>
                            <?= htmlspecialchars($winner['name']) ?> (<?= htmlspecialchars($winner['party_name']) ?>) - <?= $winner['vote_count'] ?> votes<br>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Results will be declared after the election ends.
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-3">Election information not found.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
$stmt_results->close();
$conn->close();
?>
