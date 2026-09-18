<?php
$errors = sessionErrors();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Complaint - DormSync</title>
	<style>
		* { box-sizing: border-box; }
		body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
		.topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
		.topbar a { color: #2563eb; text-decoration: none; }
		main { margin: auto; max-width: 700px; padding: 38px 24px; }
		.panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 24px; }
		h1 { margin: 0 0 8px; }
		.meta { color: #64748b; font-size: 14px; margin-bottom: 22px; }
		.description { background: #f8fafc; border-left: 3px solid #2563eb; line-height: 1.6; margin: 18px 0; padding: 14px; }
		label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
		select, textarea, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 11px; width: 100%; }
		textarea { min-height: 120px; resize: vertical; }
		button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 18px; }
		.error { color: #991b1b; font-size: 13px; }
	</style>
</head>
<body>
<header class="topbar"><a href="/warden/complaints">Back to complaints</a></header>
<main>
	<section class="panel">
		<h1><?= htmlspecialchars($complaint['subject']) ?></h1>
		<div class="meta">Student: <?= htmlspecialchars($complaint['full_name']) ?> (<?= htmlspecialchars($complaint['student_id']) ?>) | Priority: <?= htmlspecialchars(ucfirst($complaint['priority'])) ?></div>
		<div class="description"><?= nl2br(htmlspecialchars($complaint['description'])) ?></div>
		<?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endforeach; endforeach; ?>
		<form method="post" action="/warden/complaint-view/<?= (int) $complaint['id'] ?>">
			<label for="status">Complaint status</label>
			<select id="status" name="status" required>
				<option value="pending" <?= $complaint['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
				<option value="in-progress" <?= $complaint['status'] === 'in-progress' ? 'selected' : '' ?>>In progress</option>
				<option value="resolved" <?= $complaint['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
			</select>
			<label for="resolution_notes">Resolution notes</label>
			<textarea id="resolution_notes" name="resolution_notes" maxlength="500"><?= htmlspecialchars($complaint['resolution_notes'] ?? '') ?></textarea>
			<button type="submit">Update complaint</button>
		</form>
	</section>
</main>
</body>
</html>
