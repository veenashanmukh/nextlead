<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NextLead: online constituency elections</title>
  <meta name="description" content="NextLead runs constituency elections online: register, vote once, and see the results when polls close.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="theme.css">
  <style>
    .top {
      display: flex; justify-content: space-between; align-items: center;
      padding: 1.1rem clamp(1.25rem, 5vw, 4rem);
    }
    .wordmark {
      font-family: var(--display); font-weight: 800; font-size: 1.35rem;
      color: var(--slate); text-decoration: none;
      display: inline-flex; align-items: center; gap: .55rem;
    }
    .wordmark::before {
      content: ""; width: .85rem; height: 1.1rem; background: var(--ink);
      border-radius: 50% 50% 45% 45% / 60% 60% 40% 40%;
    }
    .top nav a { margin-left: 1.4rem; font-weight: 700; text-decoration: none; color: var(--slate); }
    .top nav a:hover { color: var(--ink); }

    .hero {
      display: grid;
      grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
      gap: clamp(2rem, 5vw, 5rem);
      align-items: center;
      padding: clamp(2rem, 7vw, 6rem) clamp(1.25rem, 5vw, 4rem) clamp(3rem, 7vw, 6rem);
      max-width: 1240px; margin: 0 auto;
    }
    .hero h1 {
      font-size: clamp(3rem, 8.5vw, 6.6rem);
      line-height: .95;
      letter-spacing: -0.035em;
      margin: 0 0 1.6rem;
      font-variation-settings: "opsz" 96;
    }
    .mark-line { position: relative; display: inline-block; white-space: nowrap; }
    .mark-line svg {
      position: absolute; left: -2%; bottom: -.18em; width: 104%; height: .42em;
      overflow: visible; pointer-events: none;
    }
    .mark-line path {
      fill: none; stroke: var(--ink); stroke-width: 14; stroke-linecap: round;
      stroke-dasharray: 620; stroke-dashoffset: 620;
      animation: draw 1.1s cubic-bezier(.6,.05,.25,1) .35s forwards;
    }
    @keyframes draw { to { stroke-dashoffset: 0; } }
    .lede { font-size: 1.2rem; color: var(--muted); max-width: 34rem; margin: 0 0 2rem; }
    .actions { display: flex; gap: .8rem; flex-wrap: wrap; }
    .actions a { text-decoration: none; }
    .btn-ink { background: var(--ink); color: #fff; padding: .95rem 1.6rem; border-radius: 12px; font-weight: 700; font-size: 1.05rem; }
    .btn-ink:hover { background: var(--ink-deep); color: #fff; }
    .btn-ghost { padding: .95rem 1.3rem; border-radius: 12px; font-weight: 700; color: var(--slate); border: 1.5px solid var(--line); background: #fff; }
    .btn-ghost:hover { border-color: var(--ink); color: var(--ink); }

    /* sample ballot unit, purely illustrative */
    .unit {
      background: #E9E9EF; border-radius: 22px; padding: 1.1rem 1.1rem 1.4rem;
      border: 2px solid var(--slate); transform: rotate(1.5deg);
      max-width: 26rem; justify-self: center; width: 100%;
    }
    .unit-head { display: flex; justify-content: space-between; align-items: center; padding: .2rem .4rem .9rem; font-family: var(--display); font-weight: 700; }
    .unit-head span:last-child { width: .7rem; height: .7rem; border-radius: 50%; background: var(--ok); }
    .unit .ballot-row { cursor: default; }
    .unit .lit .ballot-lamp { background: var(--lamp); border-color: var(--lamp); box-shadow: 0 0 0 4px rgba(215,38,61,.18); }
    .unit .lit { background: var(--ink-wash); }

    .how { max-width: 1240px; margin: 0 auto; padding: 0 clamp(1.25rem, 5vw, 4rem) clamp(3rem, 7vw, 5.5rem); }
    .how h2 { font-size: clamp(1.7rem, 3vw, 2.3rem); margin-bottom: 1.8rem; }
    .steps { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; counter-reset: s; }
    .steps li { counter-increment: s; border-top: 2px solid var(--slate); padding-top: 1rem; }
    .steps li::before { content: counter(s); font-family: var(--display); font-weight: 800; font-size: 2.6rem; color: var(--ink); line-height: 1; display: block; margin-bottom: .6rem; }
    .steps h3 { font-size: 1.25rem; margin: 0 0 .35rem; }
    .steps p { color: var(--muted); margin: 0; max-width: 22rem; }

    @media (max-width: 860px) {
      .hero { grid-template-columns: 1fr; }
      .unit { transform: none; }
      .steps { grid-template-columns: 1fr; gap: 1.6rem; }
      .top nav a:first-child { display: none; }
    }
  </style>
</head>
<body>
  <header class="top">
    <a class="wordmark" href="index.php">NextLead</a>
    <nav>
      <a href="welcome.php">Elections</a>
      <a href="login.php">Log in</a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div>
        <h1>Every vote<br>leaves a <span class="mark-line">mark.<svg viewBox="0 0 300 40" preserveAspectRatio="none" aria-hidden="true"><path d="M6 26 C 70 12, 150 8, 294 20"/></svg></span></h1>
        <p class="lede">NextLead runs constituency elections online. Register with your phone number, cast one vote per election, and see the count as soon as polls close.</p>
        <div class="actions">
          <a class="btn-ink" href="welcome.php">See current elections</a>
          <a class="btn-ghost" href="register.php">Register to vote</a>
        </div>
      </div>

      <div class="unit" aria-hidden="true">
        <div class="unit-head"><span>Ballot unit</span><span></span></div>
        <div class="ballot">
          <div class="ballot-row"><span class="ballot-no">1</span><span><span class="ballot-name">Ananya Rao</span><span class="ballot-meta">Civic Front</span></span><span class="ballot-lamp"></span><span class="ballot-btn"></span></div>
          <div class="ballot-row lit"><span class="ballot-no">2</span><span><span class="ballot-name">Mohammed Iqbal</span><span class="ballot-meta">People's Alliance</span></span><span class="ballot-lamp"></span><span class="ballot-btn"></span></div>
          <div class="ballot-row"><span class="ballot-no">3</span><span><span class="ballot-name">Kavya Shetty</span><span class="ballot-meta">Independent</span></span><span class="ballot-lamp"></span><span class="ballot-btn"></span></div>
        </div>
      </div>
    </section>

    <section class="how" aria-labelledby="how-title">
      <h2 id="how-title">How voting works</h2>
      <ol class="steps">
        <li><h3>Register</h3><p>Sign up as a voter or candidate with your phone number, constituency and election ID.</p></li>
        <li><h3>Log in on polling day</h3><p>Your ballot shows only the candidates standing in your constituency.</p></li>
        <li><h3>Press the blue button</h3><p>One vote per election. A confirmation email arrives, and results open when polling ends.</p></li>
      </ol>
    </section>
  </main>

  <footer>
    <small>&copy; <?= date('Y') ?> NextLead, Bengaluru, Karnataka</small>
  </footer>
</body>
</html>
