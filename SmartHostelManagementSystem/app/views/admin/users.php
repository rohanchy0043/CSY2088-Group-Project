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
$userStats = [
	'total' => count($users),
	'students' => count(array_filter($users, static function ($user) { return $user['role'] === 'student'; })),
	'wardens' => count(array_filter($users, static function ($user) { return $user['role'] === 'warden'; })),
	'admins' => count(array_filter($users, static function ($user) { return $user['role'] === 'admin'; }))
];
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
		.topbar a { color: #2563eb; font-size: 14px; text-decoration: none; }
		main { margin: 0 auto; max-width: 1240px; padding: 42px 30px 60px; }
		.page-heading { align-items: flex-start; display: flex; justify-content: space-between; gap: 20px; margin-bottom: 28px; }
		h1 { font-size: 30px; margin: 0 0 8px; }
		.intro { color: #718096; margin: 0; }
		.grid { display: grid; gap: 22px; grid-template-columns: minmax(280px, .75fr) minmax(0, 1.8fr); }
		.panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 10px; padding: 24px; }
		h2 { font-size: 17px; margin: 0 0 18px; }
		.stats { display: grid; gap: 14px; grid-template-columns: repeat(4, 1fr); margin-bottom: 24px; }
		.stat { background: #fff; border: 1px solid #e7ebf2; border-radius: 10px; padding: 18px 20px; }
		.stat-label { color: #718096; display: block; font-size: 12px; }
		.stat-value { display: block; font-size: 25px; margin-top: 8px; }
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
		.actions { display: flex; flex-wrap: wrap; gap: 6px; }
		.actions form { margin: 0; }
		.actions .action { margin: 0; width: auto; }
		.panel-header { align-items: center; display: flex; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
		.panel-header h2 { margin: 0; }
		.add-user { background: #2563eb; border: 1px solid #2563eb; border-radius: 6px; color: #fff; display: inline-block; font-size: 13px; font-weight: 600; padding: 10px 13px; text-decoration: none; }
		.add-user:hover { background: #1d4ed8; }
		.action { background: #eef4ff; border: 1px solid #bfdbfe; border-radius: 5px; color: #1d4ed8; padding: 6px 8px; text-decoration: none; font-size: 12px; }
		.action.danger { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
		.generated-code { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin-bottom: 18px; padding: 16px; }
		.generated-code strong { color: #1e40af; display: block; font-size: 13px; margin-bottom: 8px; }
		.generated-code code { background: #fff; border: 1px dashed #60a5fa; color: #172b4d; display: block; font-size: 24px; letter-spacing: 3px; padding: 12px; text-align: center; }
		.generated-code small { color: #475569; display: block; margin-top: 8px; }
		.muted { color: #718096; font-size: 13px; }
		@media (max-width: 900px) { .grid, .stats { grid-template-columns: 1fr 1fr; } .grid .panel:first-child { grid-column: 1 / -1; } }
		@media (max-width: 620px) { .grid, .stats { grid-template-columns: 1fr; } .grid .panel:first-child { grid-column: auto; } .page-heading { display: block; } .page-heading .add-user { margin-top: 16px; } main { padding: 28px 18px; } }
	</style>
</head>
<body>
<header class="topbar"><a href="/admin/dashboard">&larr; Back to dashboard</a></header>
<main>
	<header class="page-heading"><div><h1>User Management</h1><p class="intro">Manage accounts, roles, and access across your hostel.</p></div><a class="add-user" href="/admin/user-create">+ Add User</a></header>
	<?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
	<?php if ($errorMessage): ?><div class="message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
	<?php foreach ($errors as $error): foreach ((array) $error as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
	<?php if ($invitationCode): ?><section class="generated-code"><strong>New invitation code</strong><code><?= htmlspecialchars($invitationCode) ?></code><small>For <?= htmlspecialchars($invitationEmail) ?> · Expires <?= htmlspecialchars(adminUserDate($invitationExpiresAt)) ?>. Give this code to the warden during registration.</small></section><?php endif; ?>
	<section class="stats" aria-label="User summary">
		<article class="stat"><span class="stat-label">Total Users</span><strong class="stat-value"><?= $userStats['total'] ?></strong></article>
		<article class="stat"><span class="stat-label">Students</span><strong class="stat-value"><?= $userStats['students'] ?></strong></article>
		<article class="stat"><span class="stat-label">Wardens</span><strong class="stat-value"><?= $userStats['wardens'] ?></strong></article>
		<article class="stat"><span class="stat-label">Administrators</span><strong class="stat-value"><?= $userStats['admins'] ?></strong></article>
	</section>
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
			<div class="panel-header"><h2>All Users</h2><span class="muted"><?= count($users) ?> accounts</span></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>
			<?php foreach ($users as $user): ?><tr><td><?= htmlspecialchars($user['full_name']) ?></td><td><?= htmlspecialchars($user['email']) ?></td><td class="role"><?= htmlspecialchars(ucfirst($user['role'])) ?></td><td class="<?= ($user['account_status'] ?? 'approved') === 'pending' ? 'pending' : 'status' ?>"><?= htmlspecialchars(ucfirst($user['account_status'] ?? 'approved')) ?></td><td><?= adminUserDate($user['created_at']) ?></td><td><div class="actions"><a class="action" href="/admin/user-edit/<?= (int)$user['id'] ?>">Edit</a><?php if ((int)$user['id'] !== (int)currentUserId()): ?><form method="post" action="/admin/user-status/<?= (int)$user['id'] ?>"><input type="hidden" name="status" value="<?= ($user['account_status'] ?? 'approved') === 'approved' ? 'rejected' : 'approved' ?>"><button class="action" type="submit"><?= ($user['account_status'] ?? 'approved') === 'approved' ? 'Deactivate' : 'Activate' ?></button></form><form method="post" action="/admin/user-delete/<?= (int)$user['id'] ?>"><button class="action danger" type="submit">Delete</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
			</tbody></table></div>
		</article>
	</section>
	<section class="panel invitations"><h2>Invitation History</h2><div class="table-wrap"><table><thead><tr><th>Email</th><th>Code</th><th>Status</th><th>Expires</th></tr></thead><tbody><?php if ($invitations): ?><?php foreach ($invitations as $invitation): ?><tr><td><?= htmlspecialchars($invitation['invited_email']) ?></td><td class="code">Hidden after creation</td><td class="status"><?= htmlspecialchars(invitationStatus($invitation)) ?></td><td><?= adminUserDate($invitation['expires_at']) ?></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="4" class="muted">No invitation codes generated yet.</td></tr><?php endif; ?></tbody></table></div></section>
</main>
</body>
</html>
