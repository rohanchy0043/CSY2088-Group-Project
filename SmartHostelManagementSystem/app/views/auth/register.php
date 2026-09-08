<?php
$errors = sessionErrors();
$errorMessage = sessionError();
$type = $type ?? null;
function authErrors($errors, $errorMessage) {
	if ($errorMessage) {
		echo '<div class="message">' . htmlspecialchars($errorMessage) . '</div>';
	}
	foreach ($errors as $error) {
		foreach ((array) $error as $message) {
			echo '<div class="message">' . htmlspecialchars($message) . '</div>';
		}
	}
}
function fieldValue($key) {
	return htmlspecialchars(old($key));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $type ? 'Create ' . ucfirst($type) . ' Account' : 'Create Account' ?> - DormSync</title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; background: #f7f8f6; color: #1e293b; font-family: Arial, sans-serif; }
		.auth-page { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
		.auth-card { width: min(100%, 480px); background: #fff; padding: 36px; border-radius: 12px; box-shadow: 0 16px 45px rgba(10, 26, 43, .12); }
		h1 { margin: 0 0 8px; color: #0a1a2b; font-family: Georgia, serif; font-size: 34px; }
		.subtitle { margin: 0 0 28px; color: #64748b; }
		label { display: block; margin: 16px 0 7px; font-weight: 600; }
		input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; }
		input:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
		button, .account-choice { width: 100%; border: 0; border-radius: 6px; background: #0a1a2b; color: #fff; cursor: pointer; font-weight: 600; }
		button { margin-top: 24px; padding: 13px; }
		button:hover, .account-choice:hover { background: #1e3a5f; }
		.account-choice { display: block; text-align: left; text-decoration: none; padding: 18px; margin: 14px 0; }
		.account-choice strong { display: block; font-size: 16px; margin-bottom: 5px; }
		.account-choice span { color: #cbd5e1; font-size: 13px; font-weight: 400; }
		.message { padding: 10px 12px; border-radius: 6px; margin-bottom: 12px; background: #fee2e2; color: #991b1b; }
		.notice { padding: 12px; border-radius: 6px; background: #eef4ff; color: #1e40af; font-size: 14px; }
		.muted { color: #64748b; text-align: center; margin: 22px 0 0; }
		.muted a, .back { color: #2563eb; font-weight: 600; text-decoration: none; }
		.back { display: block; margin-top: 20px; text-align: center; }
		.checkbox { display: flex; align-items: center; gap: 8px; margin-top: 18px; font-size: 13px; color: #475569; }
		.checkbox input { width: auto; }
		.password-wrap { position: relative; }
		.password-wrap input { padding-right: 44px; }
		.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: auto; margin: 0; padding: 4px; background: transparent; color: #64748b; }
		@media (max-width: 520px) { .auth-card { padding: 26px 20px; } }
	</style>
</head>
<body>
<main class="auth-page">
	<section class="auth-card">
		<h1><?= $type ? 'Create ' . ucfirst($type) . ' Account' : 'Create your account' ?></h1>
		<p class="subtitle"><?= $type === 'student' ? 'Join your hostel community.' : ($type === 'warden' ? 'Warden accounts require an admin invitation.' : 'Choose the account type you need.') ?></p>
		<?php authErrors($errors, $errorMessage); ?>

		<?php if (!$type): ?>
			<p>What type of account do you need?</p>
			<a class="account-choice" href="/register.php?type=student"><strong>👨‍🎓 Student Account →</strong><span>For hostel students</span></a>
			<a class="account-choice" href="/register.php?type=warden"><strong>🛡 Warden Account →</strong><span>For hostel wardens</span></a>
		<?php else: ?>
			<?php if ($type === 'warden'): ?><div class="notice">⚿ Invitation Required<br>Warden accounts require an invitation from a DormSync admin. Enter the code provided by the administrator.</div><?php endif; ?>
			<form method="post" action="/register.php?type=<?= $type ?>">
				<input type="hidden" name="account_type" value="<?= $type ?>">
				<?php if ($type === 'warden'): ?>
					<label for="invitation_code">Invitation Code</label>
					<input id="invitation_code" name="invitation_code" placeholder="Enter invitation code" value="<?= fieldValue('invitation_code') ?>" required>
				<?php endif; ?>
				<label for="full_name">Full Name</label>
				<input id="full_name" name="full_name" placeholder="Enter your full name" value="<?= fieldValue('full_name') ?>" required>
				<?php if ($type === 'student'): ?>
					<label for="student_id">Student ID</label>
					<input id="student_id" name="student_id" placeholder="e.g. STU-2026-001" value="<?= fieldValue('student_id') ?>" required>
				<?php endif; ?>
				<label for="email">Email</label>
				<input id="email" name="email" type="email" placeholder="<?= $type === 'warden' ? 'Invited email' : 'student@example.com' ?>" value="<?= fieldValue('email') ?>" required>
				<label for="phone">Phone</label>
				<input id="phone" name="phone" placeholder="+977" value="<?= fieldValue('phone') ?>">
				<label for="password">Password</label>
				<div class="password-wrap"><input id="password" name="password" type="password" required><button class="password-toggle" type="button" onclick="togglePassword('password', this)">◉</button></div>
				<label for="password_confirmation">Confirm Password</label>
				<div class="password-wrap"><input id="password_confirmation" name="password_confirmation" type="password" required><button class="password-toggle" type="button" onclick="togglePassword('password_confirmation', this)">◉</button></div>
				<?php if ($type === 'student'): ?><label class="checkbox"><input type="checkbox" required> I agree to the Terms & Privacy</label><?php endif; ?>
				<button type="submit">Create <?= ucfirst($type) ?> Account →</button>
			</form>
		<?php endif; ?>
		<p class="muted">Already have an account? <a href="/login.php">Login</a></p>
		<?php if ($type): ?><a class="back" href="/register.php">← Choose another account type</a><?php endif; ?>
	</section>
</main>
<script>
function togglePassword(id, button) {
	const input = document.getElementById(id);
	input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
