<?php
$success = sessionSuccess();
$error = sessionError();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a, .action { color: #2563eb; text-decoration: none; }
        main { margin: auto; max-width: 1150px; padding: 36px 24px; }
        .toolbar { display: flex; gap: 8px; margin: 18px 0; }
        input, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 10px; }
        input { flex: 1; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; min-width: 760px; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 12px 10px; text-align: left; font-size: 13px; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        .message { background: #dcfce7; color: #166534; margin-bottom: 12px; padding: 10px; }
        .error { background: #fee2e2; color: #991b1b; }
        .empty { color: #718096; }
    </style>
</head>
<body>
<header class="topbar"><a href="/warden/dashboard">Back to dashboard</a></header>
<main>
    <h1>Student details</h1>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form class="toolbar" method="get">
        <input name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, ID, or email">
        <button type="submit">Search</button>
    </form>
    <section class="panel">
        <?php if ($rows): ?><div class="table-wrap"><table><thead><tr><th>Name</th><th>Student ID</th><th>Email</th><th>Room</th><th>Block</th><th>Phone</th><th>Details</th></tr></thead><tbody>
            <?php foreach ($rows as $row): ?><tr><td><?= htmlspecialchars($row['full_name']) ?></td><td><?= htmlspecialchars($row['student_id']) ?></td><td><?= htmlspecialchars($row['email']) ?></td><td><?= htmlspecialchars($row['room_number'] ?? '-') ?></td><td><?= htmlspecialchars($row['block'] ?? '-') ?></td><td><?= htmlspecialchars($row['phone'] ?? '-') ?></td><td><a class="action" href="/warden/student-view/<?= (int) $row['id'] ?>">View details</a></td></tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><p class="empty">No students found.</p><?php endif; ?>
    </section>
</main>
</body>
</html>
