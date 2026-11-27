<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

// Fetch elections ordered by start_date ascending
$sql = "SELECT * FROM election ORDER BY start_date ASC";
$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}

$currentElections = [];
$upcomingElections = [];

$today = date('Y-m-d');

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start_date = $row['start_date'] ?? null;
        $end_date = $row['end_date'] ?? null;

        if ($start_date && $end_date) {
            $start_date_fmt = date('Y-m-d', strtotime($start_date));
            $end_date_fmt = date('Y-m-d', strtotime($end_date));

            if ($today >= $start_date_fmt && $today <= $end_date_fmt) {
                $currentElections[] = $row;
            } elseif ($today < $start_date_fmt) {
                $upcomingElections[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Elections | NextLead</title>
  <style>
    body > nav { display: flex; justify-content: space-between; align-items: center; }
    nav ul { list-style: none; display: flex; gap: 1.4rem; margin: 0; padding: 0; align-items: center; }
    nav ul li { position: relative; }
    nav ul li a { color: var(--slate); font-weight: 700; text-decoration: none; }
    nav ul li a:hover { color: var(--ink); }
    nav ul li .dropdown-toggle::after { content: " \25BE"; color: var(--muted); }
    .dropdown-menu {
      display: none; position: absolute; right: 0; top: calc(100% + .6rem);
      background: #fff; border: 1px solid var(--line); border-radius: 12px;
      list-style: none; padding: .4rem; min-width: 12rem; z-index: 10;
    }
    .dropdown-menu li a { display: block; padding: .6rem .8rem; border-radius: 8px; font-weight: 400; }
    .dropdown-menu li a:hover { background: var(--ink-wash); }
    nav ul li.show > .dropdown-menu { display: block; }

    .container { max-width: 1100px; margin: 0 auto; padding: clamp(2rem, 6vw, 4rem) clamp(1.25rem, 5vw, 2.5rem); }
    .container > h1 { font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.03em; margin: 0 0 .6rem; }
    .intro { color: var(--muted); font-size: 1.15rem; max-width: 38rem; margin: 0 0 2.8rem; }
    .election-list { display: grid; gap: 3rem; }
    .election-section h2 { font-size: 1.5rem; margin: 0 0 1rem; display: flex; align-items: center; gap: .6rem; }
    .election-section.live h2::before {
      content: ""; width: .7rem; height: .7rem; border-radius: 50%; background: var(--lamp);
      box-shadow: 0 0 0 4px rgba(215,38,61,.18);
    }
    ul.election-items { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 20rem), 1fr)); gap: 1rem; }
    ul.election-items li {
      background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 1.3rem 1.4rem;
      display: flex; flex-direction: column; gap: .35rem;
    }
    .election-section.live ul.election-items li { border-left: 6px solid var(--ink); }
    ul.election-items li > strong { font-family: var(--display); font-size: 1.35rem; line-height: 1.2; }
    ul.election-items li small { color: var(--muted); }
    ul.election-items li .date { font-weight: 700; color: var(--slate); margin-top: .2rem; }
    ul.election-items li .buttons { display: flex; gap: .6rem; margin-top: .9rem; flex-wrap: wrap; }
    a.btn { text-decoration: none; padding: .65rem 1.1rem; border-radius: 10px; font-weight: 700; border: 1.5px solid var(--ink); color: var(--ink); background: #fff; }
    a.btn:hover { background: var(--ink-wash); }
    a.btn.primary { background: var(--ink); color: #fff; }
    a.btn.primary:hover { background: var(--ink-deep); }
    .empty { color: var(--muted); background: #fff; border: 1px dashed var(--line); border-radius: 16px; padding: 1.3rem 1.4rem; margin: 0; }
    body > footer p { margin: .2rem 0; }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
</head>
<body>

<nav>
  <a href="index.php" class="brand">NextLead</a>
  <ul>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" tabindex="0" id="adminDropdown">Admin Panel</a>
      <ul class="dropdown-menu" aria-labelledby="adminDropdown">
        <li><a href="adminregister.php">Admin Register</a></li>
        <li><a href="adminlogin.php">Admin Login</a></li>
      </ul>
    </li>
    <li><a href="welcome.php">Home</a></li>
  </ul>
</nav>

<div class="container">
  <h1>Elections</h1>
  <p class="intro">Vote in an election that is open now, or register early for one that starts soon. Your election ID is shown on each card.</p>

  <div class="election-list">

    <section class="election-section live">
      <h2>Polling now</h2>
      <?php if (!empty($currentElections)): ?>
      <ul class="election-items">
        <?php foreach ($currentElections as $election): ?>
          <?php
            // Safely get values or fallback
            $election_id = htmlspecialchars($election['election_id'] ?? 'N/A');
            $name = htmlspecialchars($election['name'] ?? 'No Name');
            $constituency_id = htmlspecialchars($election['constituency_id'] ?? 'N/A');
            $start = !empty($election['start_date']) ? date('F d, Y', strtotime($election['start_date'])) : 'N/A';
            $end = !empty($election['end_date']) ? date('F d, Y', strtotime($election['end_date'])) : 'N/A';
          ?>
          <li>
            <strong><?= $name ?></strong>
            <small>Election ID <?= $election_id ?></small>
            <div class="date">
              <?= $start ?> to <?= $end ?>
            </div>
            <div class="buttons">
              <a href="login.php?election_id=<?= urlencode($election_id) ?>" class="btn primary">Vote now</a>
              <a href="register.php?election_id=<?= urlencode($election_id) ?>" class="btn">Register</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p class="empty">No election is open right now. Check the list below for what's coming up.</p>
      <?php endif; ?>
    </section>

    <section class="election-section">
      <h2>Coming up</h2>
      <?php if (!empty($upcomingElections)): ?>
      <ul class="election-items">
        <?php foreach ($upcomingElections as $election): ?>
          <?php
            // Safely get values or fallback
            $election_id = htmlspecialchars($election['election_id'] ?? 'N/A');
            $name = htmlspecialchars($election['name'] ?? 'No Name');
            $constituency_id = htmlspecialchars($election['constituency_id'] ?? 'N/A');
            $start = !empty($election['start_date']) ? date('F d, Y', strtotime($election['start_date'])) : 'N/A';
          ?>
          <li>
            <strong><?= $name ?></strong>
            <small>Election ID <?= $election_id ?></small>
            <div class="date">
              Opens <?= $start ?>
            </div>
            <div class="buttons">
              <a href="register.php?election_id=<?= urlencode($election_id) ?>" class="btn">Register</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p class="empty">No elections are scheduled yet. An admin can create one from the admin panel.</p>
      <?php endif; ?>
    </section>

  </div>
</div>

<footer>
  <h3>NextLead</h3>
  <p>Your trusted platform for secure and transparent elections.</p>
  <p class="contact-info">
    Contact us: <a href="mailto:veenasb2006@gmail.com">veenasb2006@gmail.com</a> | Phone: +91 8618035570
    Address: bengaluru, karnataka, india
  </p>
  <p>&copy; <?= date('Y') ?> NextLead. All rights reserved.</p>
</footer>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownParent = dropdownToggle.parentElement;

    dropdownToggle.addEventListener('click', e => {
      e.preventDefault();
      dropdownParent.classList.toggle('show');
    });

    document.addEventListener('click', e => {
      if (!dropdownParent.contains(e.target)) {
        dropdownParent.classList.remove('show');
      }
    });

    dropdownToggle.addEventListener('keydown', e => {
      if (e.key === "Escape") {
        dropdownParent.classList.remove('show');
        dropdownToggle.focus();
      }
    });
  });
</script>

</body>
</html>
