<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connection.php';

$election_id = isset($_GET['election_id']) ? intval($_GET['election_id']) : null;

date_default_timezone_set('UTC');
$current_time = date('Y-m-d H:i:s');

if ($election_id) {
    $sql_results = "SELECT candidates.candidate_id, candidates.name, candidates.party_name, candidates.election_id, 
                    COUNT(votes.id) AS vote_count 
                    FROM candidates 
                    LEFT JOIN votes ON candidates.candidate_id = votes.candidate_id 
                    WHERE candidates.election_id = ? 
                    GROUP BY candidates.candidate_id";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param("i", $election_id);
    $stmt_results->execute();
    $results = $stmt_results->get_result();
} else {
    $sql_results = "SELECT candidates.candidate_id, candidates.name, candidates.party_name, candidates.election_id, 
                    COUNT(votes.id) AS vote_count
                    FROM candidates 
                    LEFT JOIN votes ON candidates.candidate_id = votes.candidate_id 
                    GROUP BY candidates.election_id, candidates.candidate_id
                    ORDER BY candidates.election_id";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->execute();
    $results = $stmt_results->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Election Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
    background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
    background-size: cover;
    color: var(--bs-body-color);
    font-family: Arial, sans-serif;
}

    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
<div class="container mt-4">
    <h3>Election Results</h3>

    <?php if ($election_id): ?>
        <div class="alert alert-info">
            <strong>Results for Election ID: <?= htmlspecialchars($election_id) ?></strong>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php
        $candidates_data = [];

        while ($row = $results->fetch_assoc()) {
            $candidates_data[$row['election_id']][] = $row;
        }

        foreach ($candidates_data as $election_id_key => $candidates) {
            $election_query = "SELECT name, start_date, end_date FROM election WHERE election_id = ?";
            $stmt_election = $conn->prepare($election_query);
            $stmt_election->bind_param("i", $election_id_key);
            $stmt_election->execute();
            $election_result = $stmt_election->get_result();
            $election_info = $election_result->fetch_assoc();

            if ($election_info) {
                $election_name = $election_info['name'];
                $start_date = $election_info['start_date'];
                $end_date = $election_info['end_date'];
                $has_ended = $end_date < $current_time;
            } else {
                $election_name = "Unknown Election (ID: $election_id_key)";
                $start_date = $end_date = "Unknown";
                $has_ended = false;
            }
        ?>
            <div class="col-md-12 mt-4">
                <div class="card p-3 shadow">
                    <h4>Election: <?= htmlspecialchars($election_name) ?> <?= $has_ended ? '(Ended)' : '(Ongoing)' ?></h4>
                    <p><strong>Start Date:</strong> <?= htmlspecialchars($start_date) ?></p>
                    <p><strong>End Date:</strong> <?= htmlspecialchars($end_date) ?></p>

                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Party</th>
                                <th>Votes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($candidates as $candidate): ?>
                            <tr>
                                <td><?= htmlspecialchars($candidate['name']) ?></td>
                                <td><?= htmlspecialchars($candidate['party_name']) ?></td>
                                <td><?= $candidate['vote_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
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
                                <?= htmlspecialchars($winner['name']) ?> (<?= htmlspecialchars($winner['party_name']) ?>) with <?= $winner['vote_count'] ?> votes<br>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Results will be declared after the election ends.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>

<?php
$stmt_results->close();
$conn->close();
?>
