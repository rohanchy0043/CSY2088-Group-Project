<?php include __DIR__ . '/../app/views/includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About DormSync</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/landing.css">
  <style>
    .info-page { background:#f7f8f6; min-height:100vh; padding:9rem 0 5rem; }
    .info-hero { max-width:780px; margin:0 auto 3rem; text-align:center; }
    .info-kicker { color:#2563eb; font-size:.75rem; font-weight:700; letter-spacing:3px; }
    .info-hero h1 { color:#0a1a2b; font-family:'Instrument Serif',serif; font-size:clamp(3rem,7vw,6rem); font-weight:400; line-height:.95; margin:1rem 0 1.25rem; }
    .info-hero p { color:#475569; font-size:1.1rem; margin:0 auto; max-width:650px; }
    .info-grid { display:grid; gap:1.25rem; grid-template-columns:repeat(3,1fr); margin:0 auto; max-width:1100px; }
    .info-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.75rem; transition:transform .3s ease, box-shadow .3s ease; }
    .info-card:hover { box-shadow:0 10px 24px rgba(10,26,43,.08); transform:translateY(-4px); }
    .info-card i { color:#2563eb; font-size:1.5rem; margin-bottom:1rem; }
    .info-card h2 { color:#0a1a2b; font-size:1.15rem; margin:.5rem 0; }
    .info-card p { color:#64748b; font-size:.95rem; margin:0; }
    @media (max-width:760px) { .info-page { padding-top:7rem; } .info-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<main class="info-page">
  <div class="container">
    <header class="info-hero">
      <span class="info-kicker">ABOUT DORMSYNC</span>
      <h1>A clearer way to run hostel life.</h1>
      <p>DormSync brings students, wardens, and administrators into one calm, connected system for the everyday work of hostel management.</p>
    </header>
    <section class="info-grid" aria-label="About DormSync">
      <article class="info-card"><i class="fas fa-users" aria-hidden="true"></i><h2>One shared system</h2><p>Everyone works from the same current information, from room assignments to notices and visitor requests.</p></article>
      <article class="info-card"><i class="fas fa-shield-alt" aria-hidden="true"></i><h2>Clear permissions</h2><p>Students, wardens, and administrators see the tools and records that belong to their role.</p></article>
      <article class="info-card"><i class="fas fa-chart-line" aria-hidden="true"></i><h2>Better decisions</h2><p>Practical dashboards and reports turn hostel activity into useful, timely insight.</p></article>
    </section>
  </div>
</main>
</body>
</html>