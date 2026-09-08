<?php
$name = currentUserFullName() ?: 'Administrator';
function adminDashboardDate($date) {
	return $date ? date('M j', strtotime($date)) : '-';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Administrator Dashboard - DormSync</title>
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
		.stats { display: grid; gap: 16px; grid-template-columns: repeat(4, 1fr); margin-bottom: 26px; }
		.stat, .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; }
		.stat { padding: 20px; }
		.stat-label { color: var(--muted); font-size: 13px; }
		.stat-value { color: var(--ink); display: block; font-size: 24px; font-weight: 700; margin: 10px 0 5px; }
		.stat-note { color: var(--muted); font-size: 12px; }
		.overview-grid { display: grid; gap: 22px; grid-template-columns: 1.3fr .7fr; }
		.panel { padding: 22px; }
		.panel h2 { font-size: 16px; margin: 0 0 20px; }
		.chart { min-height: 190px; position: relative; }
		.chart-grid { border-bottom: 1px solid var(--line); border-top: 1px solid var(--line); bottom: 15px; left: 0; position: absolute; right: 0; top: 12px; }
		.chart-bars { align-items: end; bottom: 15px; display: flex; gap: 18px; height: 155px; left: 8%; position: absolute; right: 8%; }
		.bar { background: #9fc0ff; border-radius: 5px 5px 0 0; flex: 1; min-height: 15px; }
		.bar:nth-child(2), .bar:nth-child(4), .bar:nth-child(6) { background: var(--blue); }
		.chart-caption { color: var(--muted); font-size: 12px; margin: 0; position: absolute; text-align: center; top: 74px; width: 100%; }
		.actions { display: grid; gap: 14px; }
		.action { align-items: center; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; padding-bottom: 12px; }
		.action:last-child { border-bottom: 0; padding-bottom: 0; }
		.action-label { font-size: 14px; }
		.action-value { color: var(--blue); font-size: 18px; font-weight: 700; }
		.activity-panel { margin-top: 22px; }
		.activities { display: grid; gap: 12px; }
		.activity { border-bottom: 1px solid var(--line); padding-bottom: 12px; }
		.activity:last-child { border-bottom: 0; padding-bottom: 0; }
		.activity strong { display: block; font-size: 14px; font-weight: 500; margin-bottom: 4px; }
		.activity small, .empty { color: var(--muted); font-size: 12px; }
		.empty { margin: 0; }
		@media (max-width: 900px) { .sidebar { flex-basis: 200px; width: 200px; } .stats { grid-template-columns: repeat(2, 1fr); } .overview-grid { grid-template-columns: 1fr; } }
		@media (max-width: 680px) { .app-shell { display: block; } .sidebar { width: 100%; padding: 14px; } .brand { padding: 8px 10px 14px; } .nav { grid-template-columns: repeat(2, 1fr); } .nav-label { margin-top: 10px; } .topbar { padding: 0 18px; } .dashboard { padding: 28px 18px; } }
		@media (max-width: 420px) { .stats { grid-template-columns: 1fr; } }
	</style>
</head>
<body>
<div class="app-shell">
	<aside class="sidebar">
		<div class="brand">DormSync<sup>®</sup></div>
		<nav class="nav" aria-label="Administrator navigation">
			<a class="active" href="/admin/dashboard"><span class="nav-icon">⌂</span>Dashboard</a>
			<div class="nav-label">Users</div>
			<a href="/admin/students"><span class="nav-icon">♙</span>Students</a>
			<a href="/admin/wardens"><span class="nav-icon">♜</span>Wardens</a>
			<div class="nav-label">Hostels</div>
			<a href="/admin/hostels"><span class="nav-icon">▣</span>Hostels</a>
			<a href="/admin/rooms"><span class="nav-icon">▤</span>Rooms</a>
			<div class="nav-label">Finance</div>
			<a href="/admin/fees"><span class="nav-icon">₹</span>Payments</a>
			<div class="nav-label">Management</div>
			<a href="/admin/complaints"><span class="nav-icon">▤</span>Complaints</a>
			<a href="/admin/visitors"><span class="nav-icon">♧</span>Visitors</a>
			<a href="/admin/notifications"><span class="nav-icon">⚑</span>Notifications</a>
			<div class="nav-label">System</div>
			<a href="/admin/reports"><span class="nav-icon">▥</span>Reports</a>
			<a href="/admin/settings"><span class="nav-icon">⚙</span>Settings</a>
			<a href="/admin/profile"><span class="nav-icon">◎</span>Profile</a>
			<a href="/admin/users"><span class="nav-icon">⌁</span>Security</a>
			<a class="logout" href="/logout.php"><span class="nav-icon">↪</span>Logout</a>
		</nav>
	</aside>
	<div class="main">
		<header class="topbar"><div class="account"><span></span><span class="avatar"><?= htmlspecialchars(strtoupper(substr($name, 0, 1))) ?></span><span><?= htmlspecialchars($name) ?>⌄</span></div></header>
		<main class="dashboard">
			<section class="welcome"><h1>Good morning, <?= htmlspecialchars($name) ?> </h1><p>Here's your complete system overview.</p></section>
			<section class="stats">
				<article class="stat"><span class="stat-label">Students</span><strong class="stat-value"><?= (int) $stats['total_students'] ?></strong><span class="stat-note">Registered users</span></article>
				<article class="stat"><span class="stat-label">Wardens</span><strong class="stat-value"><?= (int) $stats['total_wardens'] ?></strong><span class="stat-note">Hostel staff</span></article>
				<article class="stat"><span class="stat-label">Hostels</span><strong class="stat-value"><?= (int) $stats['total_hostels'] ?></strong><span class="stat-note">Room blocks</span></article>
				<article class="stat"><span class="stat-label">Revenue</span><strong class="stat-value">NPR <?= number_format((float) $stats['total_collected'], 0) ?></strong><span class="stat-note">Collected payments</span></article>
			</section>
			<section class="overview-grid">
				<article class="panel"><h2>System Overview</h2><div class="chart"><div class="chart-grid"></div><?php if ($monthlyOverview): ?><div class="chart-bars"><?php $maxRegistrations = max(array_column($monthlyOverview, 'registrations')) ?: 1; ?><?php foreach ($monthlyOverview as $month): ?><span class="bar" title="<?= htmlspecialchars($month['month']) ?>: <?= (int) $month['registrations'] ?> registrations" style="height: <?= max(10, round(((int) $month['registrations'] / $maxRegistrations) * 100)) ?>%"></span><?php endforeach; ?></div><p class="chart-caption">Student registrations, last <?= count($monthlyOverview) ?> months</p><?php else: ?><p class="chart-caption">No registration data available yet.</p><?php endif; ?></div></article>
				<article class="panel"><h2>Pending Actions</h2><div class="actions"><div class="action"><span class="action-label">Warden registrations</span><strong class="action-value"><?= (int) $stats['pending_wardens'] ?></strong></div><div class="action"><span class="action-label">Pending complaints</span><strong class="action-value"><?= (int) $stats['pending_complaints'] ?></strong></div><div class="action"><span class="action-label">Pending fee payments</span><strong class="action-value"><?= (int) $stats['unpaid_fees'] ?></strong></div></div></article>
			</section>
			<section class="panel activity-panel"><h2>Recent Activities</h2><div class="activities"><?php if ($recentActivities): ?><?php foreach ($recentActivities as $activity): ?><div class="activity"><strong><?= htmlspecialchars($activity['action']) ?><?= !empty($activity['details']) ? ': ' . htmlspecialchars($activity['details']) : '' ?></strong><small><?= adminDashboardDate($activity['created_at']) ?></small></div><?php endforeach; ?><?php else: ?><p class="empty">No recent activities recorded.</p><?php endif; ?></div></section>
		</main>
	</div>
</div>
</body>
</html>
