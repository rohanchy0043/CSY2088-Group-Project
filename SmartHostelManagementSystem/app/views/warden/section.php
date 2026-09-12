<?php
$labels = ['notifications' => 'Notices', 'profile' => 'Profile'];
$title = $labels[$section];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { align-items: center; background: #fff; border-bottom: 1px solid #e7ebf2; display: flex; justify-content: space-between; padding: 18px 6%; }
        .brand { color: #172b4d; font-size: 21px; font-weight: 700; }
        .topbar a { color: #2563eb; font-size: 14px; text-decoration: none; }
        main { margin: 0 auto; max-width: 1060px; padding: 42px 24px; }
        h1 { font-size: 28px; margin: 0 0 8px; }
        .intro { color: #718096; margin: 0 0 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 24px; }
        .row { border-bottom: 1px solid #e7ebf2; display: flex; justify-content: space-between; gap: 20px; padding: 14px 0; }
        .row:first-child { padding-top: 0; }
        .row:last-child { border-bottom: 0; padding-bottom: 0; }
        .label { color: #718096; font-size: 13px; }
        .value { font-size: 14px; font-weight: 600; text-align: right; }
        .empty { color: #718096; font-size: 14px; margin: 0; }
        @media (max-width: 560px) { .topbar { padding: 16px 20px; } main { padding: 30px 18px; } .row { align-items: flex-start; flex-direction: column; gap: 5px; } .value { text-align: left; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/warden/dashboard">&larr; Back to dashboard</a></header>
<main>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="intro">Manage your warden account and hostel updates.</p>
    <section class="panel">
        <?php if ($section === 'notifications'): ?>
            <p><a href="/warden/notice-create">Create hostel notice</a></p>
            <?php foreach ($notices as $notice): ?><div class="row"><span class="label"><?= htmlspecialchars($notice['title']) ?><br><?= htmlspecialchars($notice['message']) ?></span><span class="value"><?= htmlspecialchars($notice['scope']) ?><form method="post" action="/warden/notice-delete/<?= (int) $notice['id'] ?>"><button type="submit">Delete</button></form></span></div><?php endforeach; ?>
            <?php foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['title']) ?></span><span class="value"><?= htmlspecialchars($item['message']) ?></span></div><?php endforeach; ?>
        <?php elseif ($section === 'profile'): ?>
            <?php $success = sessionSuccess(); $errors = sessionErrors(); if ($success): ?><p><?= htmlspecialchars($success) ?></p><?php endif; foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; endforeach; ?><form method="post" action="/warden/profile-update"><label>Full name</label><input name="full_name" value="<?= htmlspecialchars($warden['full_name'] ?? currentUserFullName()) ?>" required><label>Phone</label><input name="phone" value="<?= htmlspecialchars($warden['phone'] ?? '') ?>"><button type="submit">Save profile</button></form><div class="row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($warden['email'] ?? currentUserEmail()) ?></span></div><div class="row"><span class="label">Assigned block</span><span class="value"><?= htmlspecialchars($warden['assigned_block'] ?? '-') ?></span></div>
        <?php elseif ($items): ?>
            <?php foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['title']) ?></span><span class="value"><?= htmlspecialchars($item['message']) ?></span></div><?php endforeach; ?>
        <?php else: ?>
            <p class="empty">No notices are available yet.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
