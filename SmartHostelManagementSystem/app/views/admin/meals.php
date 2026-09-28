<?php
$success = sessionSuccess();
$errors = sessionErrors();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Management - DormSync</title>
    <style>
        :root { --navy:#172b4d; --blue:#2563eb; --ink:#172033; --muted:#718096; --line:#e7ebf2; --surface:#fff; --bg:#f7f9fc; --green:#166534; --red:#991b1b; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--bg); color:var(--ink); font-family:Arial,sans-serif; }
        a { color:inherit; text-decoration:none; }
        .topbar { align-items:center; background:var(--surface); border-bottom:1px solid var(--line); display:flex; justify-content:space-between; padding:18px 6%; }
        .brand { color:var(--navy); font-size:20px; font-weight:700; }
        .back { color:var(--blue); font-size:13px; }
        main { margin:auto; max-width:1260px; padding:38px 28px 60px; }
        .heading { align-items:end; display:flex; justify-content:space-between; margin-bottom:26px; }
        h1 { font-size:30px; margin:0 0 7px; }
        h2 { font-size:17px; margin:0 0 18px; }
        .subtitle { color:var(--muted); margin:0; }
        .eyebrow { color:var(--blue); font-size:11px; font-weight:700; letter-spacing:1.4px; margin:0 0 8px; text-transform:uppercase; }
        .message { background:#dcfce7; border-radius:6px; color:var(--green); margin-bottom:14px; padding:11px 13px; }
        .error { background:#fee2e2; color:var(--red); }
        .layout { display:grid; gap:22px; grid-template-columns:310px 1fr; }
        .panel { background:var(--surface); border:1px solid var(--line); border-radius:8px; padding:22px; }
        .panel + .panel { margin-top:22px; }
        label { color:#475569; display:block; font-size:12px; font-weight:700; margin:14px 0 6px; }
        input,select,textarea,button { border:1px solid #cbd5e1; border-radius:6px; font:inherit; padding:11px 12px; width:100%; }
        textarea { min-height:108px; resize:vertical; }
        button { background:var(--blue); border-color:var(--blue); color:#fff; cursor:pointer; font-weight:700; margin-top:17px; }
        button:hover { background:#1d4ed8; }
        .menu-list { display:grid; gap:10px; }
        .menu-item { border:1px solid var(--line); border-left:4px solid var(--blue); border-radius:6px; padding:13px 14px; }
        .menu-meta { color:var(--blue); font-size:12px; font-weight:700; margin-bottom:5px; }
        .menu-text { color:#475569; font-size:13px; line-height:1.5; }
        .table-wrap { overflow-x:auto; }
        table { border-collapse:collapse; min-width:700px; width:100%; }
        th,td { border-bottom:1px solid var(--line); padding:12px 10px; text-align:left; vertical-align:top; font-size:13px; }
        th { color:var(--muted); font-size:10px; letter-spacing:.8px; text-transform:uppercase; }
        .status { color:var(--blue); font-weight:700; }
        .empty { color:var(--muted); font-size:13px; margin:0; }
        @media(max-width:850px) { .layout { grid-template-columns:1fr; } .heading { align-items:start; display:block; } }
    </style>
</head>
<body>
<header class="topbar"><a class="back" href="/admin/dashboard">&larr; Back to dashboard</a></header>
<main>
    <div class="heading"><div><p class="eyebrow">Cafeteria operations</p><h1>Meal management</h1><p class="subtitle">Manage menus and review hostel meal activity.</p></div></div>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php foreach ($errors as $group): foreach ((array) $group as $error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endforeach; endforeach; ?>
    <div class="layout">
        <aside>
            <section class="panel"><h2>Publish menu</h2><form method="post" action="/admin/meal-menu-store"><label for="meal_type">Meal</label><select id="meal_type" name="meal_type" required><option value="breakfast">Breakfast</option><option value="lunch">Lunch</option><option value="dinner">Dinner</option></select><label for="menu_date">Date</label><input id="menu_date" name="menu_date" type="date" value="<?= date('Y-m-d') ?>" required><label for="items">Menu items</label><textarea id="items" name="items" placeholder="Rice, dal, vegetables..." required></textarea><button type="submit">Save menu</button></form></section>
            <section class="panel"><h2>Quick reports</h2><p class="subtitle">Attendance reports are available from the dashboard.</p><p><a class="back" href="/admin/meal-attendance">View attendance report</a></p></section>
        </aside>
        <section>
            <section class="panel"><h2>Upcoming menus</h2><?php if ($menus): ?><div class="menu-list"><?php foreach ($menus as $menu): ?><article class="menu-item"><div class="menu-meta"><?= htmlspecialchars(ucfirst($menu['meal_type'])) ?> · <?= htmlspecialchars($menu['menu_date']) ?></div><div class="menu-text"><?= nl2br(htmlspecialchars($menu['items'])) ?></div></article><?php endforeach; ?></div><?php else: ?><p class="empty">No menus published yet.</p><?php endif; ?></section>
            <section class="panel"><h2>Attendance reports</h2><div class="table-wrap"><table><thead><tr><th>Student</th><th>Student ID</th><th>Date</th><th>Meal</th><th>Status</th></tr></thead><tbody><?php if ($attendance): foreach ($attendance as $item): ?><tr><td><?= htmlspecialchars($item['full_name']) ?></td><td><?= htmlspecialchars($item['student_id']) ?></td><td><?= htmlspecialchars($item['attendance_date']) ?></td><td><?= htmlspecialchars(ucfirst($item['meal_type'])) ?></td><td class="status"><?= htmlspecialchars(ucfirst($item['status'])) ?></td></tr><?php endforeach; else: ?><tr><td colspan="5" class="empty">No attendance records yet.</td></tr><?php endif; ?></tbody></table></div></section>
            <section class="panel"><h2>Meal feedback</h2><div class="table-wrap"><table><thead><tr><th>Student</th><th>Meal</th><th>Overall</th><th>Taste</th><th>Quality</th><th>Quantity</th><th>Hygiene</th><th>Comment</th></tr></thead><tbody><?php if ($feedback): foreach ($feedback as $item): ?><tr><td><?= htmlspecialchars($item['full_name']) ?></td><td><?= htmlspecialchars(ucfirst($item['meal_type'])) ?></td><td><?= (int) $item['rating'] ?>/5</td><td><?= (int) $item['taste_rating'] ?>/5</td><td><?= (int) $item['quality_rating'] ?>/5</td><td><?= (int) $item['quantity_rating'] ?>/5</td><td><?= (int) $item['hygiene_rating'] ?>/5</td><td><?= htmlspecialchars($item['comment'] ?? '-') ?></td></tr><?php endforeach; else: ?><tr><td colspan="8" class="empty">No feedback submitted yet.</td></tr><?php endif; ?></tbody></table></div></section>
            <section class="panel"><h2>Food complaints</h2><div class="table-wrap"><table><thead><tr><th>Student</th><th>Meal</th><th>Category</th><th>Date</th><th>Status</th><th>Description</th></tr></thead><tbody><?php if ($complaints): foreach ($complaints as $item): ?><tr><td><?= htmlspecialchars($item['full_name']) ?></td><td><?= htmlspecialchars(ucfirst($item['meal_type'])) ?></td><td><?= htmlspecialchars(ucfirst($item['category'])) ?></td><td><?= htmlspecialchars($item['complaint_date']) ?></td><td class="status"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $item['status']))) ?></td><td><?= htmlspecialchars($item['description']) ?></td></tr><?php endforeach; else: ?><tr><td colspan="6" class="empty">No food complaints submitted.</td></tr><?php endif; ?></tbody></table></div></section>
        </section>
    </div>
</main>
</body>
</html>
