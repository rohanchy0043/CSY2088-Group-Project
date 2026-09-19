<?php
$labels = [
    'room' => 'My Room',
    'fees' => 'Fees',
    'complaints' => 'Complaints',
    'visitors' => 'Visitors',
    'notifications' => 'Notifications',
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
        body { background: #f7f9fc; color: #172033; font-family: Inter, Arial, sans-serif; margin: 0; }
        .topbar { align-items: center; background: #fff; border-bottom: 1px solid #e7ebf2; display: flex; justify-content: space-between; padding: 18px 6%; }
        .brand { color: #172b4d; font-size: 21px; font-weight: 700; }
        .topbar a { color: #2563eb; font-size: 14px; text-decoration: none; }
        main { margin: 0 auto; max-width: 1060px; padding: 42px 24px; }
        h1 { font-size: 28px; margin: 0 0 8px; }
        .complaints-title { text-align: center; }
        .intro { color: #718096; margin: 0 0 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 24px; }
        .complaints-panel { background: transparent; border: 0; padding: 0; }
        .complaint-layout { align-items: start; display: grid; gap: 22px; grid-template-columns: minmax(0, 1.15fr) minmax(340px, .85fr); }
        .complaint-history, .complaint-form { background: #fff; border: 1px solid #dbe3ee; border-radius: 10px; box-shadow: 0 14px 35px rgba(23, 43, 77, .07); }
        .complaint-history { padding: 24px; }
        .complaint-history h2 { border-bottom: 1px solid #dbe3ee; color: #172b4d; font-size: 18px; margin: 0 0 8px; padding-bottom: 16px; }
        .complaint-history { grid-column: 2; }
        .visitor-layout { align-items: start; display: grid; gap: 22px; grid-template-columns: minmax(0, 1.15fr) minmax(340px, .85fr); }
        .visitor-history, .visitor-form { background: #fff; border: 1px solid #dbe3ee; border-radius: 10px; box-shadow: 0 14px 35px rgba(23, 43, 77, .07); }
        .visitor-history { grid-column: 2; padding: 24px; }
        .visitor-history h2 { border-bottom: 1px solid #dbe3ee; color: #172b4d; font-size: 18px; margin: 0 0 8px; padding-bottom: 16px; }
        .visitor-form { border-top: 0; grid-column: 1; grid-row: 1; margin: 0; padding: 28px 30px 30px; }
        .visitor-form h2 { border-bottom: 1px solid #dbe3ee; color: #172b4d; font-size: 18px; letter-spacing: .05em; margin: -28px -30px 28px; padding: 21px 24px; text-align: center; text-transform: uppercase; }
        .row { border-bottom: 1px solid #e7ebf2; display: flex; justify-content: space-between; gap: 20px; padding: 14px 0; }
        .row:first-child { padding-top: 0; }
        .row:last-child { border-bottom: 0; padding-bottom: 0; }
        .notification { align-items: flex-start; display: flex; gap: 12px; justify-content: flex-start; }
        .notification-icon { background: #eef4ff; border-radius: 50%; color: #2563eb; flex: 0 0 32px; font-size: 16px; height: 32px; line-height: 32px; text-align: center; }
        .notification-content { flex: 1; }
        .notification-title { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .notification-message { color: #475569; font-size: 13px; line-height: 1.5; }
        .label { color: #718096; font-size: 13px; }
        .value { font-size: 14px; font-weight: 600; text-align: right; }
        .empty { color: #718096; font-size: 14px; margin: 0; }
        .message { border-radius: 6px; margin-bottom: 16px; padding: 10px 12px; background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        form { border-top: 1px solid #e7ebf2; margin-top: 24px; padding-top: 20px; }
        .complaint-form { border-top: 0; grid-column: 1; grid-row: 1; margin: 0; max-width: none; padding: 28px 30px 30px; }
        .complaint-form h2 { border-bottom: 1px solid #dbe3ee; color: #172b4d; font-size: 18px; letter-spacing: .05em; margin: -28px -30px 28px; padding: 21px 24px; text-align: center; text-transform: uppercase; }
        .complaint-form label { color: #334155; font-size: 14px; margin-top: 20px; }
        .complaint-form label:first-of-type { margin-top: 0; }
        .complaint-form input, .complaint-form select, .complaint-form textarea { background: #fbfcfe; border-color: #cbd5e1; border-radius: 5px; color: #172033; padding: 12px 13px; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
        .complaint-form input::placeholder, .complaint-form textarea::placeholder { color: #94a3b8; }
        .complaint-form input:focus, .complaint-form select:focus, .complaint-form textarea:focus { background: #fff; border-color: #172b4d; box-shadow: 0 0 0 3px rgba(23, 43, 77, .1); outline: 0; }
        .complaint-form textarea { min-height: 140px; line-height: 1.5; resize: vertical; }
        .complaint-form button { background: #172b4d; border-color: #172b4d; display: block; margin: 28px auto 0; max-width: 220px; padding: 12px 20px; transition: background .2s ease, transform .2s ease; }
        .complaint-form button:hover { background: #061426; border-color: #061426; transform: translateY(-1px); }
        .visitor-form button { background: #172b4d; border-color: #172b4d; margin-top: 28px; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 6px; }
        input, textarea, select, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 10px; width: 100%; }
        textarea { min-height: 100px; resize: vertical; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; font-weight: 600; margin-top: 16px; }
        @media (max-width: 800px) { .complaint-layout, .visitor-layout { grid-template-columns: 1fr; } .complaint-history, .complaint-form, .visitor-history, .visitor-form { grid-column: auto; grid-row: auto; } }
        @media (max-width: 560px) { .topbar { padding: 16px 20px; } main { padding: 30px 18px; } .row { align-items: flex-start; flex-direction: column; gap: 5px; } .value { text-align: left; } .complaint-history, .complaint-form, .visitor-history, .visitor-form { padding: 26px 20px 24px; } .complaint-form h2, .visitor-form h2 { margin: -26px -20px 24px; padding: 18px 16px; font-size: 16px; } .complaint-form button, .visitor-form button { max-width: none; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/student/dashboard">&larr; Back to dashboard</a></header>
<main>
    <h1 class="<?= in_array($section, ['complaints', 'visitors', 'profile'], true) ? 'complaints-title' : '' ?>"><?= htmlspecialchars($title) ?></h1>
    <?php $success = sessionSuccess(); $errorMessage = sessionError(); $errors = sessionErrors(); ?>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="message error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
    <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endforeach; endforeach; ?>
    <section class="panel<?= $section === 'complaints' ? ' complaints-panel' : '' ?>">
        <?php if ($section === 'room'): ?>
            <div class="row"><span class="label">Room</span><span class="value"><?= htmlspecialchars($student['room_number'] ?? 'Not assigned') ?></span></div>
            <div class="row"><span class="label">Block</span><span class="value"><?= htmlspecialchars($student['block'] ?? '-') ?></span></div>
            <div class="row"><span class="label">Status</span><span class="value"><?= $student && $student['room_number'] ? 'Occupied' : 'Not assigned' ?></span></div>
            <?php if ($student && $student['room_id']): ?><div class="row"><span class="label">Roommates</span><span class="value"><?php if ($items): ?><?php foreach ($items as $roommate): ?><?= htmlspecialchars($roommate['full_name']) ?> (<?= htmlspecialchars($roommate['student_id']) ?>)<br><?php endforeach; ?><?php else: ?>None<?php endif; ?></span></div><?php endif; ?>
            <?php if (!$student || !$student['room_id']): ?>
                <?php if ($items): ?><p class="message">Your room request is pending for room <?= htmlspecialchars($items[0]['room_number']) ?>.</p><?php elseif ($rooms): ?><form method="post" action="/student/room-request"><label for="room_id">Choose an available room</label><select id="room_id" name="room_id" required><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>">Room <?= htmlspecialchars($room['room_number']) ?> - Block <?= htmlspecialchars($room['block']) ?> (<?= (int) $room['capacity'] - (int) $room['current_occupancy'] ?> spaces left)</option><?php endforeach; ?></select><button type="submit">Request room</button></form><?php else: ?><p class="empty">No rooms are currently available.</p><?php endif; ?>
            <?php endif; ?>
        <?php elseif ($section === 'profile'): ?>
            <form method="post" action="/student/profile-update"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="<?= htmlspecialchars($student['full_name'] ?? currentUserFullName()) ?>" required><label for="phone">Phone</label><input id="phone" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>"><label for="parent_contact">Parent contact</label><input id="parent_contact" name="parent_contact" value="<?= htmlspecialchars($student['parent_contact'] ?? '') ?>"><label for="emergency_contact">Emergency contact</label><input id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($student['emergency_contact'] ?? '') ?>"><label for="address">Address</label><textarea id="address" name="address"><?= htmlspecialchars($student['address'] ?? '') ?></textarea><button type="submit">Save profile</button></form>
            <div class="row"><span class="label">Email</span><span class="value"><?= htmlspecialchars($student['email'] ?? currentUserEmail()) ?></span></div><div class="row"><span class="label">Student ID</span><span class="value"><?= htmlspecialchars($student['student_id'] ?? '-') ?></span></div>
        <?php elseif ($section === 'complaints'): ?>
            <div class="complaint-layout">
                <section class="complaint-history">
                    <h2>Complaint History</h2>
                    <?php if ($items): foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['subject']) ?><br><?= htmlspecialchars($item['description']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $item['status']))) ?><?php if (!empty($item['resolution_notes'])): ?><br><?= htmlspecialchars($item['resolution_notes']) ?><?php endif; ?></span></div><?php endforeach; else: ?><p class="empty">No complaints records are available yet.</p><?php endif; ?>
                </section>
                <form class="complaint-form" method="post" action="/student/complaint-store">
                    <h2>Submit Complaint</h2>
                    <label for="category">Complaint Category</label>
                    <select id="category" name="category" required>
                        <option value="" selected disabled>Select category</option>
                        <option value="room">Room</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="cleanliness">Cleanliness</option>
                        <option value="security">Security</option>
                    </select>
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" maxlength="200" placeholder="Enter complaint subject" required>
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your complaint..." required></textarea>
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="" selected disabled>Select Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <button type="submit">Submit Complaint</button>
                </form>
            </div>
        <?php elseif ($section === 'visitors'): ?>
            <div class="visitor-layout">
                <form class="visitor-form" method="post" action="/student/visitor-store">
                    <h2>Register Visitor</h2>
                    <label for="visitor_name">Visitor name</label><input id="visitor_name" name="visitor_name" maxlength="100" required>
                    <label for="contact">Contact</label><input id="contact" name="contact">
                    <label for="purpose">Purpose</label><input id="purpose" name="purpose" required>
                    <button type="submit">Register visitor</button>
                </form>
                <section class="visitor-history">
                    <h2>Visitor History</h2>
                    <?php if ($items): foreach ($items as $item): ?><div class="row"><span class="label"><?= htmlspecialchars($item['visitor_name']) ?><br><?= htmlspecialchars($item['purpose']) ?></span><span class="value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span></div><?php endforeach; else: ?><p class="empty">No visitors records are available yet.</p><?php endif; ?>
                </section>
            </div>
        <?php elseif ($section === 'notifications'): ?>
            <?php if ($items): foreach ($items as $item): ?>
                <div class="row notification"><span class="notification-icon">!</span><div class="notification-content"><div class="notification-title"><?= htmlspecialchars($item['title']) ?></div><div class="notification-message"><?= htmlspecialchars($item['message']) ?></div><?php if (!empty($item['created_at'])): ?><div class="label"><?= htmlspecialchars($item['created_at']) ?></div><?php endif; ?></div></div>
            <?php endforeach; else: ?><p class="empty">No notifications are available yet.</p><?php endif; ?>
        <?php elseif ($items): ?>
            <?php foreach ($items as $item): ?>
                <div class="row">
                    <?php if ($section === 'fees'): ?><span class="label"><?= htmlspecialchars(date('F Y', strtotime($item['fee_month'] ?: $item['due_date']))) ?><br>Due <?= htmlspecialchars(date('M j, Y', strtotime($item['due_date']))) ?></span><span class="value">Total NPR <?= number_format((float) $item['amount'], 2) ?><br>Paid NPR <?= number_format((float) ($item['paid_amount'] ?? 0), 2) ?><br>Remaining NPR <?= number_format((float) ($item['remaining_amount'] ?? ((float) $item['amount'] - (float) ($item['paid_amount'] ?? 0))), 2) ?><br><?= htmlspecialchars(strtoupper($item['status'])) ?><?php if ($item['status'] === 'paid'): ?> · <a href="/student/fee-receipt/<?= (int) $item['id'] ?>">Payment history</a><?php endif; ?></span>
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
