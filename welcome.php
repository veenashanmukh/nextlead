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
  <meta charset="UTF-8" />
  <title>Online Voting System</title>
  <style>
    /* Your CSS here (same as you provided) */
    body {
      background: url('https://t3.ftcdn.net/jpg/03/89/61/88/360_F_389618887_PImTbyGB4vTQOoGKxjhOKp2w7hZmcNAs.jpg') no-repeat center center fixed;
      background-size: cover;
      background-color: #f8f9fa;
      margin: 0;
      padding-top: 72px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #222;
      min-height: 100vh;
      overflow-x: hidden;
    }

    nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background-color: rgba(13, 110, 253, 0.95);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.25);
      z-index: 1000;
      height: 72px;
      box-sizing: border-box;
    }

    nav .brand {
      color: white;
      font-weight: 700;
      font-size: 1.75rem;
      text-decoration: none;
      white-space: nowrap;
    }

    nav ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    nav ul li {
      position: relative;
    }

    nav ul li a {
      color: rgba(255,255,255,0.9);
      font-weight: 600;
      text-decoration: none;
      line-height: 1.5;
      padding: 8px 0;
      white-space: nowrap;
      font-size: 1rem;
    }

    nav ul li a:hover,
    nav ul li a:focus {
      color: #ffd43b;
    }

    nav ul li .dropdown-toggle::after {
      content: " ▼";
      font-size: 0.6em;
      margin-left: 6px;
      color: rgba(255,255,255,0.7);
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      background-color: #0b5ed7;
      top: 100%;
      left: 0;
      min-width: 180px;
      border-radius: 6px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.3);
      z-index: 2000;
    }

    .dropdown-menu li {
      padding: 10px 16px;
    }

    .dropdown-menu li a {
      color: white;
      display: block;
      font-weight: 500;
    }

    .dropdown-menu li a:hover,
    .dropdown-menu li a:focus {
      background-color: #ffd43b;
      color: #0d6efd;
    }

    nav ul li:hover > .dropdown-menu,
    nav ul li:focus-within > .dropdown-menu,
    nav ul li.show > .dropdown-menu {
      display: block;
    }

    .container {
      max-width: 900px;
      margin: 40px auto 60px;
      background: rgba(255,255,255,0.85);
      border-radius: 12px;
      padding: 30px 20px;
      color: #222;
      text-align: center;
    }

    h1, h2 {
      color: #111;
      margin-bottom: 20px;
    }

    .election-list {
      display: flex;
      gap: 40px;
      justify-content: center;
      flex-wrap: wrap;
      background: transparent;
    }

    .election-section {
      flex: 1 1 350px;
      background-color: transparent;
      border-radius: 12px;
      padding: 20px;
    }

    ul.election-items {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    ul.election-items li {
      background-color: white;
      margin: 12px 0;
      padding: 15px 18px;
      border-radius: 10px;
      box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
    }

    ul.election-items li strong {
      font-weight: 700;
    }

    ul.election-items li .date {
      color: #555;
      font-size: 0.9rem;
      margin-top: 6px;
    }

    ul.election-items li .buttons {
      margin-top: 10px;
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
    }

    a.btn {
      display: inline-block;
      padding: 8px 18px;
      background-color: #0d6efd;
      color: white;
      font-weight: 600;
      border-radius: 30px;
      text-decoration: none;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }

    a.btn:hover,
    a.btn:focus {
      background-color: #0b5ed7;
      transform: scale(1.05);
    }

    footer {
      text-align: center;
      padding: 15px 10px;
      font-size: 0.9rem;
      color: #333;
    }
  </style>
</head>
<body>

<nav>
  <a href="#" class="brand">Online Voting</a>
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
  <h1>Welcome to the Online Voting System</h1>

  <div class="election-list">

    <section class="election-section">
      <h2>🗳️ Current Elections</h2>
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
            <strong><?= $name ?></strong><br>
            <small><strong>Election ID:</strong> <?= $election_id ?> | 
            <strong>Constituency ID:</strong> <?= $constituency_id ?></small>
            <div class="date">
              From <?= $start ?> to <?= $end ?>
            </div>
            <div class="buttons">
              <a href="login.php?election_id=<?= urlencode($election_id) ?>" class="btn">Vote Now</a>
              <a href="register.php?election_id=<?= urlencode($election_id) ?>" class="btn">Register</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p>No current elections.</p>
      <?php endif; ?>
    </section>

    <section class="election-section">
      <h2>📅 Upcoming Elections</h2>
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
            <strong><?= $name ?></strong><br>
            <small><strong>Election ID:</strong> <?= $election_id ?> | 
            <strong>Constituency ID:</strong> <?= $constituency_id ?></small>
            <div class="date">
              <?= $start ?>
            </div>
            <div class="buttons">
              <a href="register.php?election_id=<?= urlencode($election_id) ?>" class="btn">Register</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p>No upcoming elections.</p>
      <?php endif; ?>
    </section>

  </div>
</div>

<footer>
  <h3>Online Voting System</h3>
  <p>Your trusted platform for secure and transparent elections.</p>
  <p class="contact-info">
    Contact us: <a href="mailto:support@onlinevoting.com" style="color:#ffd43b;">veena@onlinevoting.com</a> | Phone: +91 8618035570
    Address: bengaluru, karnataka, india
  </p>
  <p>&copy; 2025 Online Voting System. All rights reserved.</p>
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
