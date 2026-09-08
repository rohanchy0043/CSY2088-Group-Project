<?php
$name = currentUserFullName() ?: 'Student';
$roomLabel = $student['room_number'] ?? 'Not assigned';
$roomStatus = $student ? ($student['room_number'] ? 'Occupied' : 'Not assigned') : 'Profile unavailable';
$fee = $recentFees[0] ?? null;
function studentDashboardDate($date) {
	return $date ? date('M j', strtotime($date)) : '-';
}
function studentDashboardStatus($status) {
	return ucwords(str_replace('-', ' ', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student Dashboard - DormSync</title>
	<style>
		:root { --navy: #172b4d; --blue: #2563eb; --ink: #172033; --muted: #718096; --line: #e7ebf2; --surface: #fff; --background: #f7f9fc; }
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--background); color: var(--ink); font-family: Arial, sans-serif; }
		a { color: inherit; text-decoration: none; }
		.app-shell { display: flex; min-height: 100vh; }
		.sidebar { width: 238px; flex: 0 0 238px; background: var(--navy); color: #d9e2f1; padding: 24px 16px; }
		.brand { color: #fff; font-size: 21px; font-weight: 700; padding: 0 14px 28px; }
		.brand sup { color: #9db8df; font-size: 10px; }
		.nav { display: grid; gap: 6px; }
		.nav a { border-radius: 7px; display: flex; gap: 13px; padding: 12px 14px; font-size: 14px; }
		.nav a:hover, .nav a.active { background: #263e66; color: #fff; }
		.nav-icon { width: 18px; text-align: center; }
		.nav-divider { border-top: 1px solid #304565; margin: 20px 10px 14px; }
		.logout { color: #adc0db; }
		.main { flex: 1; min-width: 0; }
		.topbar { align-items: center; background: var(--surface); border-bottom: 1px solid var(--line); display: flex; justify-content: flex-end; min-height: 68px; padding: 0 34px; }
		.account { align-items: center; display: flex; gap: 10px; font-size: 14px; font-weight: 600; }
		.avatar { align-items: center; background: #dce9ff; border-radius: 50%; color: #2455a6; display: flex; font-size: 12px; height: 32px; justify-content: center; width: 32px; }
		.dashboard { margin: 0 auto; max-width: 1250px; padding: 38px 34px 54px; }
		.welcome h1 { font-size: 28px; margin: 0 0 8px; }
		.welcome p { color: var(--muted); margin: 0 0 30px; }
		.stats { display: grid; gap: 16px; grid-template-columns: repeat(4, 1fr); margin-bottom: 28px; }
		.stat, .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
		.stat { padding: 20px; }
		.stat-label { color: var(--muted); font-size: 13px; }
		.stat-value { color: var(--ink); display: block; font-size: 24px; font-weight: 700; margin: 10px 0 5px; }
		.stat-note { color: var(--muted); font-size: 12px; }
		.content-grid { display: grid; gap: 22px; grid-template-columns: 1.15fr .85fr; }
		.panel { padding: 22px; }
		.panel h2 { font-size: 16px; margin: 0 0 18px; }
		.detail-row { border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; padding: 11px 0; }
		.detail-row:last-child { border-bottom: 0; }
		.detail-row span:first-child { color: var(--muted); font-size: 13px; }
		.detail-row span:last-child { font-size: 14px; font-weight: 600; }
		.list { display: grid; gap: 12px; }
		.list-item { border-bottom: 1px solid var(--line); padding-bottom: 12px; }
		.list-item:last-child { border-bottom: 0; padding-bottom: 0; }
		.list-item strong { display: block; font-size: 14px; margin-bottom: 5px; }
		.list-item small, .empty { color: var(--muted); font-size: 13px; }
		.section-row { display: grid; gap: 22px; grid-template-columns: 1fr 1fr; margin-top: 22px; }
		.status { color: var(--blue); font-size: 12px; font-weight: 600; }
		@media (max-width: 900px) { .sidebar { width: 200px; flex-basis: 200px; } .stats { grid-template-columns: repeat(2, 1fr); } }
		@media (max-width: 680px) { .app-shell { display: block; } .sidebar { width: 100%; padding: 14px; } .brand { padding: 8px 10px 14px; } .nav { grid-template-columns: repeat(2, 1fr); } .nav-divider { display: none; } .topbar { padding: 0 18px; } .dashboard { padding: 28px 18px; } .content-grid, .section-row { grid-template-columns: 1fr; } }
		@media (max-width: 420px) { .stats { grid-template-columns: 1fr; } .nav { grid-template-columns: 1fr 1fr; } }
	</style>
</head>
<body>
<div class="app-shell">
	<aside class="sidebar">
		<div class="brand">DormSync<sup>®</sup></div>
		<nav class="nav" aria-label="Student navigation">
			<a class="active" href="/student/dashboard"><span class="nav-icon">⌂</span>Dashboard</a>
			<a href="/student/room"><span class="nav-icon">▣</span>My Room</a>
			<a href="/student/fees"><span class="nav-icon">₹</span>Fees</a>
			<a href="/student/complaints"><span class="nav-icon">▤</span>Complaints</a>
			<a href="/student/profile"><span class="nav-icon">◎</span>Profile</a>
			<a href="/student/visitors"><span class="nav-icon">♧</span>Visitors</a>
			<a href="/student/notifications"><span class="nav-icon">⚑</span>Notices</a>
			<div class="nav-divider"></div>
			<a href="/student/profile"><span class="nav-icon">⚙</span>Settings</a>
			<a class="logout" href="/logout.php"><span class="nav-icon">↪</span>Logout</a>
		</nav>
	</aside>
	<div class="main">
		<header class="topbar"><div class="account"><span></span><span class="avatar"><?= htmlspecialchars(strtoupper(substr($name, 0, 1))) ?></span><span><?= htmlspecialchars($name) ?>⌄</span></div></header>
		<main class="dashboard">
			<section class="welcome"><h1>Good morning, <?= htmlspecialchars($name) ?> </h1><p>Here's what's happening with your hostel.</p></section>
			<section class="stats">
				<article class="stat"><span class="stat-label">Room</span><strong class="stat-value"><?= htmlspecialchars($roomLabel) ?></strong><span class="stat-note"><?= htmlspecialchars($roomStatus) ?></span></article>
				<article class="stat"><span class="stat-label">Fees</span><strong class="stat-value">₹<?= number_format((float) $stats['total_fees_due'], 0) ?></strong><span class="stat-note">Pending</span></article>
				<article class="stat"><span class="stat-label">Complaints</span><strong class="stat-value"><?= (int) $stats['pending_complaints'] ?></strong><span class="stat-note">Pending</span></article>
				<article class="stat"><span class="stat-label">Visitors</span><strong class="stat-value"><?= (int) $stats['visitors_this_month'] ?></strong><span class="stat-note">This month</span></article>
			</section>
			<section class="content-grid">
				<article class="panel"><h2>Room Information</h2><div class="detail-row"><span>Room</span><span><?= htmlspecialchars($roomLabel) ?></span></div><div class="detail-row"><span>Block</span><span><?= htmlspecialchars($student['block'] ?? '-') ?></span></div><div class="detail-row"><span>Status</span><span><?= htmlspecialchars($roomStatus) ?></span></div></article>
				<article class="panel"><h2>Fee Status</h2><?php if ($fee): ?><div class="detail-row"><span><?= htmlspecialchars($fee['due_date'] ? date('F', strtotime($fee['due_date'])) : 'Fee') ?></span><span>₹<?= number_format((float) $fee['amount'], 0) ?></span></div><div class="detail-row"><span>Due</span><span><?= studentDashboardDate($fee['due_date']) ?></span></div><div class="detail-row"><span>Status</span><span class="status"><?= htmlspecialchars(studentDashboardStatus($fee['status'])) ?></span></div><?php else: ?><p class="empty">No fee records yet.</p><?php endif; ?></article>
			</section>
			<section class="section-row">
				<article class="panel"><h2>Recent Complaints</h2><div class="list"><?php if ($recentComplaints): ?><?php foreach ($recentComplaints as $complaint): ?><div class="list-item"><strong><?= htmlspecialchars($complaint['subject']) ?></strong><small><?= htmlspecialchars(studentDashboardStatus($complaint['status'])) ?> · <?= studentDashboardDate($complaint['created_at']) ?></small></div><?php endforeach; ?><?php else: ?><p class="empty">No complaints yet.</p><?php endif; ?></div></article>
				<article class="panel"><h2>Recent Notices</h2><div class="list"><?php if ($recentNotices): ?><?php foreach ($recentNotices as $notice): ?><div class="list-item"><strong><?= htmlspecialchars($notice['title']) ?></strong><small><?= htmlspecialchars($notice['message']) ?></small></div><?php endforeach; ?><?php else: ?><p class="empty">No new notices.</p><?php endif; ?></div></article>
			</section>
		</main>
	</div>
</div>
</body>
</html>
