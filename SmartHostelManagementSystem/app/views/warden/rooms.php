<?php $success = sessionSuccess(); $error = sessionError(); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rooms and Assignments - DormSync</title>
	<style>
		* { box-sizing: border-box; }
		body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
		.topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
		.topbar a, .action { color: #2563eb; text-decoration: none; }
		main { margin: auto; max-width: 1150px; padding: 36px 24px; }
		.panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
		.table-wrap { overflow-x: auto; }
		table { border-collapse: collapse; min-width: 900px; width: 100%; }
		th, td { border-bottom: 1px solid #e7ebf2; padding: 12px 10px; text-align: left; font-size: 13px; }
		th { color: #718096; font-size: 11px; text-transform: uppercase; }
		.button { background: #2563eb; border-radius: 6px; color: #fff; display: inline-block; margin: 18px 0; padding: 10px 14px; text-decoration: none; }
		.message { background: #dcfce7; color: #166534; margin-bottom: 12px; padding: 10px; }
		.error { background: #fee2e2; color: #991b1b; }
		.empty { color: #718096; }
	</style>
</head>
<body>
<header class="topbar"><a href="/warden/dashboard">&larr; Back to dashboard</a></header>
<main>
	<h1>Rooms and assignments</h1>
	<?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
	<?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
	<a class="button" href="/warden/allocations">Assign student to a room</a>
	<section class="panel">
		<?php if ($rooms): ?><div class="table-wrap"><table><thead><tr><th>Room</th><th>Floor</th><th>Capacity</th><th>Occupied</th><th>Assigned students</th><th>Status</th><th>Actions</th></tr></thead><tbody>
			<?php foreach ($rooms as $room): ?><tr><td><?= htmlspecialchars($room['room_number']) ?></td><td><?= (int) $room['floor'] ?></td><td><?= (int) $room['capacity'] ?></td><td><?= (int) $room['current_occupancy'] ?></td><td><?= htmlspecialchars($room['assigned_students'] ?? 'Unassigned') ?></td><td><?= htmlspecialchars(ucfirst($room['status'])) ?></td><td>View only</td></tr><?php endforeach; ?>
		</tbody></table></div><?php else: ?><p class="empty">No rooms found in your assigned block.</p><?php endif; ?>
	</section>
</main>
</body>
</html>
