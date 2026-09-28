<?php $success = sessionSuccess(); $error = sessionError(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostels - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a { color: #2563eb; text-decoration: none; }
        main { margin: auto; max-width: 900px; padding: 38px 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
        .message { background: #dcfce7; color: #166534; margin-bottom: 12px; padding: 10px; }
        .error { background: #fee2e2; color: #991b1b; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 13px 10px; text-align: left; font-size: 13px; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        .rooms-link { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
<header class="topbar"><a href="/admin/dashboard">Back to dashboard</a></header>
<main>
    <h1>Hostels</h1>
    <p>Overview of the hostel managed by this system.</p>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <section class="panel">
        <table><thead><tr><th>Name</th><th>Block</th><th>Status</th><th>Rooms</th></tr></thead><tbody>
        <?php foreach ($hostels as $hostel): ?><tr><td><?= htmlspecialchars($hostel['name']) ?></td><td><?= htmlspecialchars($hostel['block']) ?></td><td><?= htmlspecialchars(ucfirst($hostel['status'])) ?></td><td><a class="rooms-link" href="/admin/rooms"><?= (int) $hostel['room_count'] ?> rooms</a></td></tr><?php endforeach; ?>
        </tbody></table>
    </section>
</main>
</body>
</html>
