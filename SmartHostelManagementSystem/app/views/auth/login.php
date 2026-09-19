<?php
$errors = sessionFlash('auth_errors') ?? [];
$error = sessionFlash('auth_error');
sessionFlash('success');
$success = sessionFlash('auth_success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - DormSync</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:opsz,wght@14..32,400;500;600&display=swap" rel="stylesheet">
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #061426; color: #1e293b; font-family: 'Inter', sans-serif; }
		.auth-page { min-height: 100dvh; display: grid; place-items: center; padding: clamp(1rem, 3vw, 3rem); }
		.auth-shell { width: min(1120px, 100%); min-height: 680px; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); background: #fff; box-shadow: 0 24px 70px rgba(0, 0, 0, .28); }
		.auth-card { padding: clamp(2rem, 5vw, 5.5rem); display: flex; flex-direction: column; justify-content: center; }
		h1 { margin: 0 0 8px; color: #061426; font-family: 'Instrument Serif', serif; font-size: clamp(2.8rem, 5vw, 4.3rem); font-weight: 400; letter-spacing: -.04em; line-height: .95; white-space: nowrap; }
		.subtitle { margin: 0 0 28px; color: #64748b; font-size: .98rem; }
		label { display: block; margin: 16px 0 7px; color: #061426; font-size: .82rem; font-weight: 600; }
		input { width: 100%; padding: 13px 14px; border: 1px solid #cbd5e1; border-radius: 4px; color: #061426; font: inherit; }
		input:focus { outline: 2px solid #94a3b8; border-color: #061426; }
		button { width: 100%; margin-top: 24px; padding: 14px; border: 0; border-radius: 4px; background: #061426; color: #fff; cursor: pointer; font: inherit; font-weight: 600; transition: background .2s ease, transform .2s ease; }
		button:hover { background: #1e3a5f; transform: translateY(-1px); }
		.message { padding: 10px 12px; border-radius: 4px; margin-bottom: 12px; background: #fee2e2; color: #991b1b; font-size: .88rem; }
		.success { background: #dcfce7; color: #166534; }
		.back { display: block; margin-top: 20px; text-align: center; color: #64748b; text-decoration: none; font-size: .86rem; }
		.back:hover, .muted a:hover { color: #64748b; }
		.muted { color: #64748b; text-align: center; margin: 20px 0 0; font-size: .9rem; }
		.muted a { color: #061426; font-weight: 600; text-decoration: none; }
		.divider { display: flex; align-items: center; gap: 12px; color: #94a3b8; margin: 22px 0; font-size: 13px; }
		.divider::before, .divider::after { content: ''; height: 1px; background: #e2e8f0; flex: 1; }
		.password-wrap { position: relative; }
		.password-wrap input { padding-right: 44px; }
		.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: auto; margin: 0; padding: 4px; background: transparent; color: #64748b; }
		.password-toggle:hover { background: transparent; color: #061426; transform: translateY(-50%); }
		.auth-visual { position: relative; min-height: 100%; background: radial-gradient(120% 90% at 30% 20%, #12335c 0%, #061426 70%); color: #fff; display: flex; align-items: flex-end; padding: clamp(2rem, 5vw, 4rem); overflow: hidden; }
		.auth-visual::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(6,20,38,0) 40%, rgba(6,20,38,.88)); }
		.auth-visual-content { position: relative; max-width: 420px; }
		.auth-visual-kicker { color: rgba(255,255,255,.72); font-size: .72rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; }
		.auth-visual h2 { margin: .7rem 0 .8rem; font-family: 'Instrument Serif', serif; font-size: clamp(2.5rem, 4vw, 4.2rem); font-weight: 400; line-height: .95; letter-spacing: -.04em; }
		.auth-visual p { color: rgba(255,255,255,.78); line-height: 1.65; margin: 0; }

		/* ===== Boxy hostel characters (move + track cursor) ===== */
		.art { position: absolute; inset: 0 0 120px 0; pointer-events: none; }
		.boxy { position: absolute; transition: transform .12s ease-out; will-change: transform; }
		.face { position: absolute; display: flex; gap: 18px; }
		.eye { width: 14px; height: 18px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
		.eye.white { background: #dbeafe; }
		.pupil { width: 7px; height: 9px; background: #17171c; border-radius: 50%; will-change: transform; }

		/* purple dorm tower — a box with windows */
		.tower { width: 100px; height: 200px; background: #6a3df0; border-radius: 14px 14px 6px 6px; left: 16%; top: 22%; box-shadow: inset -10px -10px 0 rgba(0,0,0,.18); animation: floaty 6s ease-in-out 1.6s infinite; }
		.tower .face { top: 26px; left: 30px; }
		.win { position: absolute; width: 16px; height: 16px; background: rgba(255,255,255,.9); border-radius: 4px; }
		.win.r1c1 { top: 78px; left: 20px; }  .win.r1c2 { top: 78px; right: 20px; }
		.win.r2c1 { top: 112px; left: 20px; } .win.r2c2 { top: 112px; right: 20px; }
		.win.r3c1 { top: 146px; left: 20px; } .win.r3c2 { top: 146px; right: 20px; }

		/* orange box */
		.orange { width: 130px; height: 100px; background: #ff7b33; border-radius: 16px; left: 6%; top: 52%; box-shadow: inset -10px -10px 0 rgba(0,0,0,.12); animation: floaty 5s ease-in-out 2s infinite; }
		.orange .face { top: 32px; left: 34px; }
		.orange .smile { position: absolute; width: 40px; height: 20px; border: 5px solid #17171c; border-top: none; border-radius: 0 0 40px 40px; top: 56px; left: 42px; }

		/* slate box (navy-tinted so it pops on dark bg) */
		.slate { width: 66px; height: 150px; background: #33507a; border-radius: 12px; left: 46%; top: 34%; box-shadow: inset -8px -8px 0 rgba(0,0,0,.25); animation: floaty 6.5s ease-in-out 2.3s infinite; }
		.slate .face { top: 26px; left: 14px; gap: 12px; }

		/* yellow box */
		.yellow { width: 80px; height: 110px; background: #ffc632; border-radius: 18px 18px 12px 12px; left: 56%; top: 56%; box-shadow: inset -9px -9px 0 rgba(0,0,0,.1); animation: floaty 4.6s ease-in-out 2.6s infinite; }
		.yellow .face { top: 34px; left: 26px; gap: 0; }
		.yellow .beak { position: absolute; width: 36px; height: 6px; background: #17171c; border-radius: 4px; top: 62px; left: 38px; }

		@keyframes floaty {
			0%,100% { margin-top: 0; }
			50%     { margin-top: -12px; }
		}

		@media (max-width: 1024px) {
			.auth-shell { min-height: 620px; }
			.auth-card { padding: clamp(2rem, 4vw, 3.5rem); }
			.auth-visual { padding: clamp(2rem, 4vw, 3.5rem); }
			.tower { left: 20%; } .orange { left: 4%; }
		}
		@media (max-height: 760px) and (min-width: 761px) {
			.auth-page { padding-top: 1rem; padding-bottom: 1rem; }
			.auth-shell { min-height: 0; }
			.auth-card { padding-top: 2rem; padding-bottom: 2rem; }
		}
		@media (max-width: 760px) {
			.auth-page { padding: 0; }
			.auth-shell { width: 100%; min-height: 100dvh; grid-template-columns: 1fr; }
			.auth-visual { min-height: 280px; padding: 2rem; }
			.auth-visual h2 { font-size: 2.8rem; }
			.auth-card { padding: 2rem 1.5rem 3rem; }
			.art { display: none; }
		}
		@media (max-width: 420px) {
			.auth-visual { min-height: 230px; padding: 1.5rem; }
			.auth-visual h2 { font-size: 2.35rem; }
			.auth-visual p { font-size: .9rem; }
			.auth-card { padding: 1.75rem 1.25rem 2.5rem; }
			h1 { font-size: clamp(2.35rem, 11vw, 2.8rem); }
		}
	</style>
</head>
<body>
	<main class="auth-page">
		<section class="auth-shell">
			<aside class="auth-visual" aria-label="DormSync hostel interior">

				<!-- animated box characters -->
				<div class="art" id="art">
					<div class="boxy tower" data-depth="14">
						<div class="win r1c1"></div><div class="win r1c2"></div>
						<div class="win r2c1"></div><div class="win r2c2"></div>
						<div class="win r3c1"></div><div class="win r3c2"></div>
						<div class="face">
							<div class="eye"><div class="pupil"></div></div>
							<div class="eye"><div class="pupil"></div></div>
						</div>
					</div>
					<div class="boxy orange" data-depth="22">
						<div class="face">
							<div class="eye"><div class="pupil"></div></div>
							<div class="eye"><div class="pupil"></div></div>
						</div>
						<div class="smile"></div>
					</div>
					<div class="boxy slate" data-depth="10">
						<div class="face">
							<div class="eye white"><div class="pupil"></div></div>
							<div class="eye white"><div class="pupil"></div></div>
						</div>
					</div>
					<div class="boxy yellow" data-depth="18">
						<div class="face">
							<div class="eye"><div class="pupil"></div></div>
						</div>
						<div class="beak"></div>
					</div>
				</div>

				<div class="auth-visual-content">
					<span class="auth-visual-kicker">SMART HOSTEL MANAGEMENT</span>
					<h2>A better way to manage hostel life.</h2>
					<p>Stay connected to rooms, fees, complaints and daily hostel operations with DormSync.</p>
				</div>
			</aside>
			<div class="auth-card">
			<h1>Welcome Back</h1>
			<p class="subtitle">Sign in to your DormSync account.</p>
			<?php if ($success): ?><div class="message success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
			<?php if ($error): ?><div class="message"><?= htmlspecialchars($error) ?></div><?php endif; ?>
			<?php foreach ($errors as $error): ?>
				<?php foreach ((array) $error as $message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endforeach; ?>
			<?php endforeach; ?>
			<form id="loginForm" method="post" action="/login.php">
				<label for="email">Email</label>
				<input id="email" name="email" type="email" placeholder="Enter your email" value="<?= htmlspecialchars(old('email')) ?>" required>
				<label for="password">Password</label>
				<div class="password-wrap">
					<input id="password" name="password" type="password" placeholder="Enter your password" required>
					<button class="password-toggle" type="button" onclick="togglePassword('password', this)" aria-label="Show password">◉</button>
				</div>
				<button type="submit">Login →</button>
			</form>
			<p class="muted">Don't have an account? <a href="/register.php">Create Account</a></p>
			<div class="divider">or</div>
			<a class="back" href="/">← Back to DormSync</a>
			</div>
		</section>
	</main>
	<script>
		function togglePassword(id, button) {
			const input = document.getElementById(id);
			input.type = input.type === 'password' ? 'text' : 'password';
			button.setAttribute('aria-label', input.type === 'password' ? 'Show password' : 'Hide password');
		}
	</script>
	<script src="/js/login.js"></script>
</body>
</html>