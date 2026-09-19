<?php
$success = sessionSuccess();
$error = sessionError();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Assignments - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; margin: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a { color: #2563eb; text-decoration: none; }
        main { margin: auto; max-width: 1100px; padding: 36px 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; margin-bottom: 20px; padding: 22px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 12px; text-align: left; font-size: 13px; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        select, button { border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; padding: 9px; }
        select { width: 100%; }
        button { background: #2563eb; border-color: #2563eb; color: #fff; cursor: pointer; }
        .message { background: #dcfce7; border-radius: 6px; color: #166534; margin-bottom: 14px; padding: 10px; }
        .error { background: #fee2e2; color: #991b1b; }
        .empty { color: #718096; }
        .form-grid { display: grid; gap: 10px; grid-template-columns: 1fr 1fr auto; }
        @media (max-width: 650px) { .form-grid { grid-template-columns: 1fr; } .table-wrap { overflow-x: auto; } table { min-width: 700px; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/warden/dashboard">Back to dashboard</a></header>
<main>
    <h1>Room assignments</h1>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <section class="panel">
        <h2>Assign room directly</h2>
        <?php if ($unassignedStudents && $rooms): ?>
            <form method="post" action="/warden/assign-room" class="form-grid">
                <select name="student_id" required>
                    <option value="">Select unassigned student</option>
                    <?php foreach ($unassignedStudents as $student): ?><option value="<?= (int) $student['id'] ?>"><?= htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ')') ?></option><?php endforeach; ?>
                </select>
                <select name="room_id" required>
                    <option value="">Select available room</option>
                    <?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>">Room <?= htmlspecialchars($room['room_number']) ?> (<?= (int) $room['capacity'] - (int) $room['current_occupancy'] ?> spaces)</option><?php endforeach; ?>
                </select>
                <button type="submit">Assign room</button>
            </form>
        <?php else: ?><p class="empty">No unassigned students or available rooms are currently available.</p><?php endif; ?>
    </section>

    <section class="panel">
        <h2>Pending requests</h2>
        <?php if ($allocations): ?><div class="table-wrap"><table><thead><tr><th>Student</th><th>Student ID</th><th>Requested room</th><th>Date</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($allocations as $allocation): ?><tr><td><?= htmlspecialchars($allocation['full_name']) ?></td><td><?= htmlspecialchars($allocation['student_id']) ?></td><td><?= htmlspecialchars($allocation['room_number']) ?></td><td><?= htmlspecialchars($allocation['request_date']) ?></td><td><form method="post" action="/warden/allocation-action"><input type="hidden" name="allocation_id" value="<?= (int) $allocation['id'] ?>"><button name="action" value="approve" type="submit">Approve</button> <button name="action" value="reject" type="submit">Reject</button></form></td></tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><p class="empty">No pending room requests.</p><?php endif; ?>
    </section>
</main>
</body>
</html>
