<?php
$errors = sessionErrors();
$success = sessionSuccess();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - DormSync</title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #f7f8f6; color: #1e293b; font-family: Arial, sans-serif; }
		.auth-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
		.auth-card { width: min(100%, 420px); background: #fff; padding: 36px; border-radius: 12px; box-shadow: 0 16px 45px rgba(10, 26, 43, .12); }
		h1 { margin: 0 0 8px; color: #0a1a2b; font-family: Georgia, serif; font-size: 36px; }
		.subtitle { margin: 0 0 28px; color: #64748b; }
		label { display: block; margin: 16px 0 7px; font-weight: 600; }
		input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; }
		input:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
		button { width: 100%; margin-top: 24px; padding: 13px; border: 0; border-radius: 6px; background: #0a1a2b; color: #fff; cursor: pointer; font-weight: 600; }
		button:hover { background: #1e3a5f; }
		.message { padding: 10px 12px; border-radius: 6px; margin-bottom: 12px; background: #fee2e2; color: #991b1b; }
		.success { background: #dcfce7; color: #166534; }
		.back { display: block; margin-top: 20px; text-align: center; color: #2563eb; text-decoration: none; }
		.muted { color: #64748b; text-align: center; margin: 20px 0 0; }
		.muted a { color: #2563eb; font-weight: 600; text-decoration: none; }
		.divider { display: flex; align-items: center; gap: 12px; color: #94a3b8; margin: 22px 0; font-size: 13px; }
		.divider::before, .divider::after { content: ''; height: 1px; background: #e2e8f0; flex: 1; }
		.password-wrap { position: relative; }
		.password-wrap input { padding-right: 44px; }
		.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: auto; margin: 0; padding: 4px; background: transparent; color: #64748b; }
	</style>
</head>
<body>
	<main class="auth-page">
		<section class="auth-card">
			<h1>Welcome back</h1>
			<p class="subtitle">Sign in to your DormSync account.</p>
			<?php if ($success): ?><div class="message success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
			<?php foreach ($errors as $error): ?>
				<?php foreach ((array) $error as $message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endforeach; ?>
			<?php endforeach; ?>
			<form method="post" action="/login.php">
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
		</section>
	</main>
	<script>
		function togglePassword(id, button) {
			const input = document.getElementById(id);
			input.type = input.type === 'password' ? 'text' : 'password';
			button.setAttribute('aria-label', input.type === 'password' ? 'Show password' : 'Hide password');
		}
	</script>
</body>
</html>
