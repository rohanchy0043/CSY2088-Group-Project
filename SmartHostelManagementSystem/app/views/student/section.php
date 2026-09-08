<?php
$labels = [
    'room' => 'My Room',
    'fees' => 'Fees',
    'complaints' => 'Complaints',
    'visitors' => 'Visitors',
    'notifications' => 'Notices',
    'profile' => 'Profile'
];
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
        .message { border-radius: 6px; margin-bottom: 16px; padding: 10px 12px; background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        form { border-top: 1px solid #e7ebf2; margin-top: 24px; padding-top: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 6px; }
        input, textarea, select, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 10px; width: 100%; }
        textarea { min-height: 100px; resize: vertical; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 16px; }
        @media (max-width: 560px) { .topbar { padding: 16px 20px; } main { padding: 30px 18px; } .row { align-items: flex-start; flex-direction: column; gap: 5px; } .value { text-align: left; } }
    </style>
</head>
<body>
<header class="topbar"><div class="brand">DormSync<sup>®</sup></div><a href="/student/dashboard">Back to dashboard</a></header>
<main>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="intro">Manage and review your hostel information.</p>
    <?php $success = sessionSuccess(); $errorMessage = sessionError(); $errors = sessionErrors(); ?>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
    <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endforeach; endforeach; ?>
    <section class="panel">
        <?php if ($section === 'room'): ?>
            <div class="row"><span class="label">Room</span><span class="value"><?= htmlspecialchars($student['room_number'] ?? 'Not assigned') ?></span></div>
            <div class="row"><span class="label">Block</span><span class="value"><?= htmlspecialchars($student['block'] ?? '-') ?></span></div>
            <div class="row"><span class="label">Status</span><span class="value"><?= $student && $student['room_number'] ? 'Occupied' : 'Not assigned' ?></span></div>
            <div class="row"><span class="label">Roommates</span><span class="value"><?php if ($items): ?><?php foreach ($items as $roommate): ?><?= htmlspecialchars($roommate['full_name']) ?> (<?= htmlspecialchars($roommate['student_id']) ?>)<br><?php endforeach; ?><?php else: ?>None<?php endif; ?></span></div>
        <?php elseif ($section === 'profile'): ?>
            <form method="post" action="/student/profile-update"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?= htmlspecialchars($student['full_name'] ?? currentUserFullName()) ?>" required><label for="phone">Phone</label><input id="phone" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>"><label for="parent_contact">Parent contact</label><input id="parent_contact" name="parent_contact" value="<?= htmlspecialchars($student['parent_contact'] ?? '') ?>"><label for="emergency_contact">Emergency contact</label><input id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($student['emergency_contact'] ?? '') ?>"><label for="address">Address</label><textarea id="address" name="address"><?= htmlspecialchars($student['address'] ?? '') ?></textarea><button type="submit">Save profile</button></form>
            <div class="row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($student['email'] ?? currentUserEmail()) ?></span></div><div class="row"><span class="label">Student ID</span><span class="value"><?= htmlspecialchars($student['student_id'] ?? '-') ?></span></div>
        <?php elseif ($section === 'complaints'): ?>
            <?php if ($items): foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['subject']) ?><br><?= htmlspecialchars($item['description']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $item['status']))) ?><?php if (!empty($item['resolution_notes'])): ?><br><?= htmlspecialchars($item['resolution_notes']) ?><?php endif; ?></span></div><?php endforeach; else: ?><p class="empty">No complaints records are available yet.</p><?php endif; ?>
            <form method="post" action="/student/complaint-store"><label for="category">Category</label><input id="category" name="category" required><label for="subject">Subject</label><input id="subject" name="subject" maxlength="200" required><label for="description">Description</label><textarea id="description" name="description" required></textarea><label for="priority">Priority</label><select id="priority" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select><button type="submit">Submit complaint</button></form>
        <?php elseif ($section === 'visitors'): ?>
            <?php if ($items): foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['visitor_name']) ?><br><?= htmlspecialchars($item['purpose']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span></div><?php endforeach; else: ?><p class="empty">No visitors records are available yet.</p><?php endif; ?>
            <form method="post" action="/student/visitor-store"><label for="visitor_name">Visitor name</label><input id="visitor_name" name="visitor_name" maxlength="100" required><label for="contact">Contact</label><input id="contact" name="contact"><label for="purpose">Purpose</label><input id="purpose" name="purpose" required><button type="submit">Register visitor</button></form>
        <?php elseif ($items): ?>
            <?php foreach ($items as $item): ?>
                <div class="row">
                    <?php if ($section === 'fees'): ?><span class="label"><?= htmlspecialchars($item['due_date']) ?></span><span class="value">₹<?= number_format((float) $item['amount'], 2) ?> · <?= htmlspecialchars(ucfirst($item['status'])) ?><?php if ($item['status'] === 'paid'): ?> · <a href="/student/fee-receipt/<?= (int) $item['id'] ?>">Download receipt</a><?php endif; ?></span>
                    <?php elseif ($section === 'complaints'): ?><span class="label"><?= htmlspecialchars($item['subject']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $item['status']))) ?></span>
                    <?php elseif ($section === 'visitors'): ?><span class="label"><?= htmlspecialchars($item['visitor_name']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span>
                    <?php else: ?><span class="label"><?= htmlspecialchars($item['title']) ?></span><span class="value"><?= htmlspecialchars($item['message']) ?></span><?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty">No <?= strtolower($title) ?> records are available yet.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
