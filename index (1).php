<?php
// You can add PHP logic here later (e.g., session checking)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Next Lead</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body, html {
      height: 100%;
    }

    .hero {
      background: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQOG3UYTXb1yyTtdz-Xyws_xF-UKP-PHWIPHg&s') no-repeat center center / cover;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      position: relative;
    }

    .overlay {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1;
    }

    .content {
      z-index: 2;
    }

    h1 {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
    }

    .btn {
      background-color: #00b894;
      color: white;
      padding: 15px 30px;
      font-size: 1.2rem;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn:hover {
      background-color: #019875;
    }
  </style>
</head>
<body>
  <div class="hero">
    <div class="overlay"></div>
    <div class="content">
      <h1>Next Lead</h1>
      <p>Your gateway to a transparent voting system</p>
      <button class="btn" onclick="window.location.href='welcome.php'">Get Started</button>
    </div>
  </div>
</body>
</html>
