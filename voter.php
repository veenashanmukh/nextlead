<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connection.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Voter') {
    header("Location: login.php");
    exit;
}

$phone = $_SESSION['phone'];
$sql = "SELECT * FROM voters WHERE phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Voter not found.";
    exit;
}

$voter = $result->fetch_assoc();
$voter_id = $voter['voter_id'];
$constituency_id = $voter['constituency_id'];
$election_id = $voter['election_id'];
$now = date("Y-m-d H:i:s");

// Check if election has ended
$stmt_election_end = $conn->prepare("SELECT end_date FROM election WHERE election_id = ?");
$stmt_election_end->bind_param("i", $election_id);
$stmt_election_end->execute();
$election_end_result = $stmt_election_end->get_result();
$election_ended = false;

if ($election_end_result->num_rows === 1) {
    $election_data = $election_end_result->fetch_assoc();
    $election_ended = ($election_data['end_date'] <= $now);
}
$stmt_election_end->close();

// Check if voter has already voted
$stmt_vote_check = $conn->prepare("SELECT * FROM votes WHERE voter_id = ? AND election_id = ?");
$stmt_vote_check->bind_param("ii", $voter_id, $election_id);
$stmt_vote_check->execute();
$vote_result = $stmt_vote_check->get_result();
$has_voted = $vote_result->num_rows > 0;

// Fetch candidates for this voter's constituency and election
$stmt_candidate = $conn->prepare("SELECT * FROM candidates WHERE constituency_id = ? AND election_id = ?");
$stmt_candidate->bind_param("ii", $constituency_id, $election_id);
$stmt_candidate->execute();
$candidates_result = $stmt_candidate->get_result();

// Fetch upcoming elections
$stmt_elections = $conn->prepare("SELECT * FROM election WHERE start_date > ? ORDER BY start_date ASC");
$stmt_elections->bind_param("s", $now);
$stmt_elections->execute();
$election_result = $stmt_elections->get_result();

// Fetch results if election ended
$results = null;
if ($election_ended) {
    $stmt_results = $conn->prepare("
        SELECT c.name AS candidate_name, c.party_name, COUNT(v.id) AS vote_count
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.candidate_id
        WHERE c.election_id = ?
        GROUP BY c.candidate_id
        ORDER BY vote_count DESC
    ");
    $stmt_results->bind_param("i", $election_id);
    $stmt_results->execute();
    $results = $stmt_results->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Voter Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
            background-size: cover;
            padding-top: 70px;
            padding-bottom: 60px;
        }
        .navbar, footer {
            position: fixed;
            width: 100%;
            z-index: 1030;
        }
        .navbar {
            top: 0;
        }
        footer {
            bottom: 0;
            background-color: #f8f9fa;
        }
        .container {
            margin-bottom: 60px;
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>

<div class="navbar navbar-dark bg-dark px-3">
    <span class="navbar-brand text-white">NextLead</span>
    <a href="logout.php" class="btn btn-light">Logout</a>
</div>

<div class="container mt-4">
    <div class="row">
        <!-- Voter Info -->
        <div class="col-md-4">
            <div class="card shadow p-3 mb-4">
                <h4>Voter Information</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($voter['name']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($voter['phone']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($voter['gender']) ?></p>
                <p><strong>Constituency ID:</strong> <?= htmlspecialchars($voter['constituency_id']) ?></p>
                <p><strong>Age:</strong> <?= htmlspecialchars($voter['age']) ?></p>
                <p><strong>Election ID:</strong> <?= htmlspecialchars($voter['election_id']) ?></p>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Election Results or Ongoing -->
            <div class="card shadow p-3 mb-4">
                <h4>Election Results</h4>
                <?php if ($election_ended): ?>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($row['candidate_name']) ?></strong> (<?= htmlspecialchars($row['party_name']) ?>):
                                    <?= $row['vote_count'] ?> votes
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info">No votes have been cast yet.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">The election is ongoing. Results will be available after it ends.</div>
                <?php endif; ?>
            </div>

            <!-- Voting Form or Status -->
            <?php if (!$election_ended): ?>
                <div class="card shadow p-3 mb-4">
                    <h4>Vote for Your Candidate</h4>
                    <?php if ($has_voted): ?>
                        <div class="alert alert-info">You have already voted.</div>
                    <?php else: ?>
                        <?php if ($candidates_result->num_rows > 0): ?>
                            <form action="vote.php" method="POST">
                                <input type="hidden" name="voter_id" value="<?= $voter_id ?>">
                                <input type="hidden" name="election_id" value="<?= $election_id ?>">
                                <div class="form-group">
                                    <p class="text-muted mb-3">Press the blue button next to your candidate. The red lamp shows your choice.</p>
                                    <div class="ballot" role="radiogroup" aria-label="Candidates">
                                    <?php $serial = 0; while ($candidate = $candidates_result->fetch_assoc()): $serial++; ?>
                                        <label class="ballot-row">
                                            <input type="radio" name="candidate_id" value="<?= $candidate['candidate_id'] ?>" required>
                                            <span class="ballot-no"><?= $serial ?></span>
                                            <span>
                                                <span class="ballot-name"><?= htmlspecialchars($candidate['name']) ?></span>
                                                <span class="ballot-meta"><?= htmlspecialchars($candidate['party_name']) ?>, <?= htmlspecialchars($candidate['gender']) ?>, <?= htmlspecialchars($candidate['age']) ?></span>
                                            </span>
                                            <span class="ballot-lamp" aria-hidden="true"></span>
                                            <span class="ballot-btn" aria-hidden="true"></span>
                                        </label>
                                    <?php endwhile; ?>
                                    </div>
                                </div>
                                <button type="submit" name="vote" class="btn btn-primary ballot-cast">Cast my vote</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">No candidates found for your constituency and election.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Upcoming Elections -->
            <div class="card shadow p-3">
                <h4>Upcoming Elections</h4>
                <?php if ($election_result->num_rows > 0): ?>
                    <ul class="list-group">
                        <?php while ($election = $election_result->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($election['name']) ?></strong><br>
                                Opens <?= date('j M Y', strtotime($election['start_date'])) ?><br>
                                Closes <?= date('j M Y', strtotime($election['end_date'])) ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-warning">No upcoming elections at this time.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-3">
    <small>&copy; <?= date('Y') ?> NextLead. All rights reserved.</small>
</footer>

</body>
</html>

<?php
$stmt->close();
$stmt_vote_check->close();
$stmt_candidate->close();
$stmt_elections->close();
if (isset($stmt_results)) {
    $stmt_results->close();
}
$conn->close();
?>
