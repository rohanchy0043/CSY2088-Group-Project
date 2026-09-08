<?php
$name = currentUserFullName() ?: 'Warden';
function wardenDashboardDate($date) {
	return $date ? date('M j', strtotime($date)) : '-';
}
function wardenDashboardStatus($status) {
	return ucwords(str_replace('-', ' ', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Warden Dashboard - DormSync</title>
	<style>
		:root { --navy: #172b4d; --blue: #2563eb; --ink: #172033; --muted: #718096; --line: #e7ebf2; --surface: #fff; --background: #f7f9fc; }
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--background); color: var(--ink); font-family: Arial, sans-serif; }
		a { color: inherit; text-decoration: none; }
		.app-shell { display: flex; min-height: 100vh; }
		.sidebar { width: 238px; flex: 0 0 238px; background: var(--navy); color: #d9e2f1; padding: 24px 16px; }
		.brand { color: #fff; font-size: 21px; font-weight: 700; padding: 0 14px 28px; }
		.brand sup { color: #9db8df; font-size: 10px; }
		.nav-label { color: #8298b7; font-size: 10px; font-weight: 700; letter-spacing: 1.4px; margin: 20px 14px 8px; text-transform: uppercase; }
		.nav { display: grid; gap: 5px; }
		.nav a { border-radius: 7px; display: flex; gap: 13px; padding: 11px 14px; font-size: 14px; }
		.nav a:hover, .nav a.active { background: #263e66; color: #fff; }
		.nav-icon { width: 18px; text-align: center; }
		.logout { color: #adc0db; }
		.main { flex: 1; min-width: 0; }
		.topbar { align-items: center; background: var(--surface); border-bottom: 1px solid var(--line); display: flex; justify-content: flex-end; min-height: 68px; padding: 0 34px; }
		.account { align-items: center; display: flex; gap: 10px; font-size: 14px; font-weight: 600; }
		.avatar { align-items: center; background: #dce9ff; border-radius: 50%; color: #2455a6; display: flex; font-size: 12px; height: 32px; justify-content: center; width: 32px; }
		.dashboard { margin: 0 auto; max-width: 1250px; padding: 38px 34px 54px; }
		.welcome h1 { font-size: 28px; margin: 0 0 8px; }
		.welcome p { color: var(--muted); margin: 0 0 30px; }
		.stats { display: grid; gap: 16px; grid-template-columns: repeat(4, 1fr); margin-bottom: 24px; }
		.stat, .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
		.stat { padding: 20px; }
		.stat-label { color: var(--muted); font-size: 13px; }
		.stat-value { color: var(--ink); display: block; font-size: 25px; font-weight: 700; margin: 10px 0 5px; }
		.stat-note { color: var(--muted); font-size: 12px; }
		.main-grid { display: grid; gap: 22px; grid-template-columns: 1fr 1fr; }
		.panel { padding: 22px; }
		.panel h2 { font-size: 16px; margin: 0 0 20px; }
		.occupancy-head { align-items: end; display: flex; justify-content: space-between; margin-bottom: 14px; }
		.occupancy-value { color: var(--blue); font-size: 27px; font-weight: 700; }
		.progress { background: #e9eef7; border-radius: 99px; height: 12px; overflow: hidden; }
		.progress span { background: var(--blue); border-radius: inherit; display: block; height: 100%; max-width: 100%; }
		.muted { color: var(--muted); font-size: 13px; }
		.complaints, .activities { display: grid; gap: 13px; }
		.complaint, .activity { align-items: center; border-bottom: 1px solid var(--line); display: flex; gap: 11px; padding-bottom: 12px; }
		.complaint:last-child, .activity:last-child { border-bottom: 0; padding-bottom: 0; }
		.dot { border-radius: 50%; flex: 0 0 9px; height: 9px; }
		.dot.high { background: #ef4444; } .dot.medium { background: #f59e0b; } .dot.low { background: #22c55e; }
		.complaint strong, .activity strong { display: block; font-size: 14px; margin-bottom: 3px; }
		.complaint small, .activity small { color: var(--muted); font-size: 12px; }
		.activity { display: block; }
		.activity strong { font-weight: 500; }
		.empty { color: var(--muted); font-size: 13px; margin: 0; }
		.activity-panel { margin-top: 22px; }
		@media (max-width: 900px) { .sidebar { flex-basis: 200px; width: 200px; } .stats { grid-template-columns: repeat(2, 1fr); } }
		@media (max-width: 680px) { .app-shell { display: block; } .sidebar { width: 100%; padding: 14px; } .brand { padding: 8px 10px 14px; } .nav { grid-template-columns: repeat(2, 1fr); } .nav-label { margin-top: 10px; } .topbar { padding: 0 18px; } .dashboard { padding: 28px 18px; } .main-grid { grid-template-columns: 1fr; } }
		@media (max-width: 420px) { .stats { grid-template-columns: 1fr; } }
	</style>
</head>
<body>
<div class="app-shell">
	<aside class="sidebar">
		<div class="brand">DormSync<sup>®</sup></div>
		<nav class="nav" aria-label="Warden navigation">
			<a class="active" href="/warden/dashboard"><span class="nav-icon">⌂</span>Dashboard</a>
			<div class="nav-label">Hostel</div>
			<a href="/warden/students"><span class="nav-icon">♙</span>Students</a>
			<a href="/warden/rooms"><span class="nav-icon">▣</span>Rooms</a>
			<a href="/warden/visitors"><span class="nav-icon">♧</span>Visitors</a>
			<a href="/warden/notifications"><span class="nav-icon">⚑</span>Notices</a>
			<div class="nav-label">Management</div>
			<a href="/warden/fees"><span class="nav-icon">₹</span>Fees</a>
			<a href="/warden/complaints"><span class="nav-icon">▤</span>Complaints</a>
			<div class="nav-label">Reports</div>
			<a href="/warden/reports"><span class="nav-icon">▥</span>Reports</a>
			<div class="nav-label">Account</div>
			<a href="/warden/profile"><span class="nav-icon">◎</span>Profile</a>
			<a href="/warden/settings"><span class="nav-icon">⚙</span>Settings</a>
			<a class="logout" href="/logout.php"><span class="nav-icon">↪</span>Logout</a>
		</nav>
	</aside>
	<div class="main">
		<header class="topbar"><div class="account"><span></span><span class="avatar"><?= htmlspecialchars(strtoupper(substr($name, 0, 1))) ?></span><span><?= htmlspecialchars($name) ?>⌄</span></div></header>
		<main class="dashboard">
			<section class="welcome"><h1>Good morning, <?= htmlspecialchars($name) ?></h1><p>Here's your hostel overview.</p></section>
			<section class="stats">
				<article class="stat"><span class="stat-label">Students</span><strong class="stat-value"><?= (int) $stats['total_students'] ?></strong><span class="stat-note">Registered students</span></article>
				<article class="stat"><span class="stat-label">Rooms</span><strong class="stat-value"><?= (int) $stats['total_rooms'] ?></strong><span class="stat-note">Hostel rooms</span></article>
				<article class="stat"><span class="stat-label">Occupied</span><strong class="stat-value"><?= (int) $stats['occupied_rooms'] ?></strong><span class="stat-note">Currently occupied</span></article>
				<article class="stat"><span class="stat-label">Vacant</span><strong class="stat-value"><?= (int) $stats['available_rooms'] ?></strong><span class="stat-note">Available rooms</span></article>
			</section>
			<section class="main-grid">
				<article class="panel"><h2>Room Occupancy</h2><div class="occupancy-head"><span class="muted">Current hostel occupancy</span><strong class="occupancy-value"><?= (int) $stats['occupancy_percent'] ?>%</strong></div><div class="progress" aria-label="Room occupancy <?= (int) $stats['occupancy_percent'] ?> percent"><span style="width: <?= min(100, (int) $stats['occupancy_percent']) ?>%"></span></div></article>
				<article class="panel"><h2>Pending Complaints</h2><div class="complaints"><?php if ($recentComplaints): ?><?php foreach ($recentComplaints as $complaint): ?><div class="complaint"><span class="dot <?= htmlspecialchars($complaint['priority']) ?>"></span><div><strong><?= htmlspecialchars($complaint['subject']) ?></strong><small><?= htmlspecialchars($complaint['full_name']) ?> · <?= htmlspecialchars(wardenDashboardStatus($complaint['status'])) ?></small></div></div><?php endforeach; ?><?php else: ?><p class="empty">No complaints found.</p><?php endif; ?></div></article>
			</section>
			<section class="panel activity-panel"><h2>Recent Activities</h2><div class="activities"><?php if ($recentActivities): ?><?php foreach ($recentActivities as $activity): ?><div class="activity"><strong><?= htmlspecialchars($activity['action']) ?><?= $activity['details'] ? ': ' . htmlspecialchars($activity['details']) : '' ?></strong><small><?= wardenDashboardDate($activity['created_at']) ?></small></div><?php endforeach; ?><?php else: ?><p class="empty">No recent activities recorded.</p><?php endif; ?></div></section>
		</main>
	</div>
</div>
</body>
</html>
