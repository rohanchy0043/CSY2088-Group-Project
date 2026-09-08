<?php
$success = sessionSuccess();
$errors = sessionErrors();
$errorMessage = sessionError();
$invitationCode = sessionFlash('invitation_code');
$invitationEmail = sessionFlash('invitation_email');
$invitationExpiresAt = sessionFlash('invitation_expires_at');
function adminUserDate($date) {
	return $date ? date('M j, Y', strtotime($date)) : '-';
}
function invitationStatus($invitation) {
	if ($invitation['used_at']) return 'Used';
	if (strtotime($invitation['expires_at']) <= time()) return 'Expired';
	return 'Active';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Management - DormSync</title>
	<style>
		* { box-sizing: border-box; }
		body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
		.topbar { align-items: center; background: #fff; border-bottom: 1px solid #e7ebf2; display: flex; justify-content: space-between; padding: 18px 6%; }
		.brand { color: #172b4d; font-size: 21px; font-weight: 700; }
		.topbar a { color: #2563eb; font-size: 14px; text-decoration: none; }
		main { margin: 0 auto; max-width: 1180px; padding: 38px 24px 54px; }
		h1 { font-size: 28px; margin: 0 0 8px; }
		.intro { color: #718096; margin: 0 0 26px; }
		.grid { display: grid; gap: 22px; grid-template-columns: 1fr 1.4fr; }
		.panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
		h2 { font-size: 16px; margin: 0 0 18px; }
		label { color: #475569; display: block; font-size: 13px; font-weight: 600; margin: 13px 0 6px; }
		input, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 11px 12px; width: 100%; }
		button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 16px; }
		button:hover { background: #1d4ed8; }
		.message { background: #ecfdf5; border-radius: 6px; color: #166534; margin-bottom: 14px; padding: 11px 12px; }
		.error { background: #fef2f2; color: #991b1b; }
		.table-wrap { overflow-x: auto; }
		table { border-collapse: collapse; min-width: 620px; width: 100%; }
		th, td { border-bottom: 1px solid #e7ebf2; padding: 12px 10px; text-align: left; font-size: 13px; }
		th { color: #718096; font-size: 11px; text-transform: uppercase; }
		.role, .status { color: #2563eb; font-weight: 600; }
		.pending { color: #b45309; }
		.invitations { margin-top: 22px; }
		.code { font-family: monospace; font-weight: 700; letter-spacing: 1px; }
		.actions { display: flex; gap: 6px; }
		.action { background: #eef4ff; border: 1px solid #bfdbfe; border-radius: 5px; color: #1d4ed8; padding: 6px 8px; text-decoration: none; font-size: 12px; }
		.action.danger { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
		.generated-code { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin-bottom: 18px; padding: 16px; }
		.generated-code strong { color: #1e40af; display: block; font-size: 13px; margin-bottom: 8px; }
		.generated-code code { background: #fff; border: 1px dashed #60a5fa; color: #172b4d; display: block; font-size: 24px; letter-spacing: 3px; padding: 12px; text-align: center; }
		.generated-code small { color: #475569; display: block; margin-top: 8px; }
		.muted { color: #718096; font-size: 13px; }
		@media (max-width: 820px) { .grid { grid-template-columns: 1fr; } main { padding: 28px 18px; } }
	</style>
</head>
<body>
<header class="topbar"><div class="brand">DormSync<sup>®</sup></div><a href="/admin/dashboard">Back to dashboard</a></header>
<main>
	<h1>User Management</h1>
	<p class="intro">Review real user accounts and generate invitation codes for warden registration.</p>
	<?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
	<?php if ($errorMessage): ?><div class="message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
	<?php foreach ($errors as $error): foreach ((array) $error as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
	<?php if ($invitationCode): ?><section class="generated-code"><strong>New invitation code</strong><code><?= htmlspecialchars($invitationCode) ?></code><small>For <?= htmlspecialchars($invitationEmail) ?> · Expires <?= htmlspecialchars(adminUserDate($invitationExpiresAt)) ?>. Give this code to the warden during registration.</small></section><?php endif; ?>
	<section class="grid">
		<article class="panel">
			<h2>Generate Warden Invitation</h2>
			<p class="muted">The code is shown once after creation. Send it to the invited email address.</p>
			<form method="post" action="/admin/invite-warden">
				<label for="email">Warden email</label>
				<input id="email" name="email" type="email" placeholder="warden@example.com" required>
				<label for="expires_at">Expires</label>
				<input id="expires_at" name="expires_at" type="datetime-local" required>
				<button type="submit">Generate Invitation Code</button>
			</form>
		</article>
		<article class="panel">
			<h2>Users</h2>
			<p><a class="action" href="/admin/user-create">Add user</a></p><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>
			<?php foreach ($users as $user): ?><tr><td><?= htmlspecialchars($user['full_name']) ?></td><td><?= htmlspecialchars($user['email']) ?></td><td class="role"><?= htmlspecialchars(ucfirst($user['role'])) ?></td><td class="<?= ($user['account_status'] ?? 'approved') === 'pending' ? 'pending' : 'status' ?>"><?= htmlspecialchars(ucfirst($user['account_status'] ?? 'approved')) ?></td><td><?= adminUserDate($user['created_at']) ?></td><td><div class="actions"><a class="action" href="/admin/user-edit/<?= (int)$user['id'] ?>">Edit</a><?php if ((int)$user['id'] !== (int)currentUserId()): ?><form method="post" action="/admin/user-status/<?= (int)$user['id'] ?>"><input type="hidden" name="status" value="<?= ($user['account_status'] ?? 'approved') === 'approved' ? 'rejected' : 'approved' ?>"><button class="action" type="submit"><?= ($user['account_status'] ?? 'approved') === 'approved' ? 'Deactivate' : 'Activate' ?></button></form><form method="post" action="/admin/user-delete/<?= (int)$user['id'] ?>"><button class="action danger" type="submit">Delete</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
			</tbody></table></div>
		</article>
	</section>
	<section class="panel invitations"><h2>Invitation History</h2><div class="table-wrap"><table><thead><tr><th>Email</th><th>Code</th><th>Status</th><th>Expires</th></tr></thead><tbody><?php if ($invitations): ?><?php foreach ($invitations as $invitation): ?><tr><td><?= htmlspecialchars($invitation['invited_email']) ?></td><td class="code">Hidden after creation</td><td class="status"><?= htmlspecialchars(invitationStatus($invitation)) ?></td><td><?= adminUserDate($invitation['expires_at']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="muted">No invitation codes generated yet.</td></tr><?php endif; ?></tbody></table></div></section>
</main>
</body>
</html>
