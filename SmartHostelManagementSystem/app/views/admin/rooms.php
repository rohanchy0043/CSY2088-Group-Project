<?php $success = sessionSuccess(); $error = sessionError(); $errors = sessionErrors(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a, .action { color: #2563eb; text-decoration: none; }
        main { margin: auto; max-width: 1250px; padding: 36px 24px; }
        .grid { display: grid; gap: 22px; grid-template-columns: 320px 1fr; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 13px 0 6px; }
        input, select, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 10px; width: 100%; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 16px; }
        .message { background: #dcfce7; color: #166534; margin-bottom: 12px; padding: 10px; }
        .error { background: #fee2e2; color: #991b1b; }
        .field-error { color: #991b1b; font-size: 12px; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; min-width: 900px; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 10px; text-align: left; vertical-align: top; font-size: 12px; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        td input, td select { min-width: 85px; padding: 7px; }
        td button { margin: 0; padding: 7px 10px; width: auto; }
        .danger { background: #fff; border-color: #fecaca; color: #b91c1c; }
        .inline { display: inline; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/admin/dashboard">Back to dashboard</a></header>
<main>
    <h1>Room management</h1>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
    <div class="grid">
        <section class="panel"><h2>Add room</h2><form method="post" action="/admin/room-store"><label>Room number</label><input name="room_number" required><label>Block</label><input name="block" maxlength="10" required><label>Floor</label><input name="floor" type="number" min="1" required><label>Capacity</label><input name="capacity" type="number" min="1" required><button type="submit">Add room</button></form></section>
        <section class="panel"><h2>All rooms</h2><div class="table-wrap"><table><thead><tr><th>Room</th><th>Block</th><th>Floor</th><th>Capacity</th><th>Occupied</th><th>Status</th><th>Save</th><th>Delete</th></tr></thead><tbody>
        <?php if ($rooms): foreach ($rooms as $room): ?><tr><form method="post" action="/admin/room-update/<?= (int) $room['id'] ?>"><td><input name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>" required></td><td><input name="block" value="<?= htmlspecialchars($room['block']) ?>" maxlength="10" required></td><td><input name="floor" type="number" min="1" value="<?= (int) $room['floor'] ?>" required></td><td><input name="capacity" type="number" min="1" value="<?= (int) $room['capacity'] ?>" required></td><td><?= (int) $room['current_occupancy'] ?></td><td><select name="status"><option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Available</option><option value="occupied" <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option><option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option><option value="full" <?= $room['status'] === 'full' ? 'selected' : '' ?>>Full</option></select></td><td><button type="submit">Save</button></td></form><td><form class="inline" method="post" action="/admin/room-delete/<?= (int) $room['id'] ?>"><button class="danger" type="submit">Delete</button></form></td></tr><?php endforeach; else: ?><tr><td colspan="8">No rooms found.</td></tr><?php endif; ?>
        </tbody></table></div></section>
    </div>
</main>
</body>
</html>
