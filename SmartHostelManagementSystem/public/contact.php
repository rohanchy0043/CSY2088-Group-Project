<?php include __DIR__ . '/../app/views/includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact DormSync</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/landing.css">
  <style>
    .contact-page { background:#f7f8f6; min-height:100vh; padding:9rem 0 5rem; }
    .contact-layout { align-items:start; display:grid; gap:4rem; grid-template-columns:.85fr 1.15fr; margin:0 auto; max-width:1050px; }
    .contact-copy .kicker { color:#2563eb; font-size:.75rem; font-weight:700; letter-spacing:3px; }
    .contact-copy h1 { color:#0a1a2b; font-family:'Instrument Serif',serif; font-size:clamp(3rem,6vw,5.5rem); font-weight:400; line-height:.95; margin:1rem 0 1.25rem; }
    .contact-copy p { color:#64748b; font-size:1.05rem; }
    .contact-details { border-top:1px solid #cbd5e1; margin-top:2rem; padding-top:1.25rem; }
    .contact-details p { margin:.6rem 0; }
    .contact-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 2px rgba(10,26,43,.04); padding:2rem; }
    .contact-card label { color:#334155; display:block; font-size:.85rem; font-weight:600; margin:1rem 0 .4rem; }
    .contact-card label:first-child { margin-top:0; }
    .contact-card input,.contact-card textarea { border:1px solid #cbd5e1; border-radius:6px; font:inherit; padding:.8rem; width:100%; }
    .contact-card textarea { min-height:150px; resize:vertical; }
    .contact-card button { background:#0a1a2b; border:0; border-radius:999px; color:#fff; cursor:pointer; font-weight:600; margin-top:1.25rem; padding:.8rem 1.5rem; }
    .contact-card button:hover { background:#1e3a5f; }
    @media (max-width:760px) { .contact-page { padding-top:7rem; } .contact-layout { grid-template-columns:1fr; gap:2rem; } }
  </style>
</head>
<body>
<main class="contact-page">
  <div class="container contact-layout">
    <section class="contact-copy">
      <span class="kicker">CONTACT</span>
      <h1>Let’s talk about your hostel.</h1>
      <p>Have a question about DormSync or want to improve the way your hostel works? Send us a message and our team will get back to you.</p>
      <div class="contact-details">
        <p><strong>Email</strong><br>hello@dormsync.local</p>
        <p><strong>Office hours</strong><br>Sunday–Friday, 9:00–17:00</p>
      </div>
    </section>
    <form class="contact-card" method="post" action="mailto:hello@dormsync.local" enctype="text/plain">
      <label for="name">Your name</label>
      <input id="name" name="name" required>
      <label for="email">Email address</label>
      <input id="email" name="email" type="email" required>
      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>
      <button type="submit">Send message</button>
    </form>
  </div>
</main>
</body>
</html>