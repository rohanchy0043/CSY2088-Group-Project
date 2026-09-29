<?php
$fees = $fees ?? [];
$structures = $structures ?? [];
$success = sessionSuccess();
$errors = sessionErrors();
$structure = $structures[0] ?? null;
$totals = [
    'fee' => 0,
    'paid' => 0,
    'remaining' => 0,
];
foreach ($fees as $fee) {
    $totals['fee'] += (float) ($fee['amount'] ?? 0);
    $totals['paid'] += (float) ($fee['paid_amount'] ?? 0);
    $totals['remaining'] += max(0, (float) ($fee['amount'] ?? 0) - (float) ($fee['paid_amount'] ?? 0));
}
function formatMoney($value) {
    return 'NPR ' . number_format((float) $value, 2);
}
function feeStatusLabel($status) {
    return strtoupper(str_replace(['-', '_'], ' ', (string) $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees - DormSync</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f7f9fc; color: #172033; font-family: Arial, sans-serif; }
        .topbar { background: #fff; border-bottom: 1px solid #e7ebf2; padding: 18px 6%; }
        .topbar a { color: #2563eb; text-decoration: none; }
        main { max-width: 1200px; margin: 0 auto; padding: 38px 24px 52px; }
        h1 { margin: 0 0 8px; }
        .intro { color: #718096; margin: 0 0 24px; }
        .summary { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .card { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 18px; }
        .card small { display: block; color: #718096; margin-bottom: 8px; }
        .card strong { font-size: 22px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
        .message { padding: 11px 14px; margin-bottom: 16px; border-radius: 6px; background: #dcfce7; color: #166534; }
        .message.error { background: #fee2e2; color: #991b1b; }
        .fee-form { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .fee-form label { display: block; margin-bottom: 6px; color: #526078; font-size: 12px; }
        .fee-form input, .fee-form select { width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font: inherit; }
        .fee-form button { min-height: 40px; padding: 8px 14px; border: 0; border-radius: 5px; background: #1d4ed8; color: #fff; cursor: pointer; font: inherit; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 12px 10px; text-align: left; font-size: 13px; }
        th { color: #718096; text-transform: uppercase; font-size: 11px; }
        .empty { color: #718096; margin: 0; }
        .status { font-weight: 700; }
        .paid { color: #15803d; }
        .partial { color: #b45309; }
        .unpaid, .overdue { color: #b91c1c; }
        @media (max-width: 700px) { .summary { grid-template-columns: 1fr; } .fee-form { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/admin/dashboard">&larr; Back to dashboard</a></header>
<main>
    <h1>Fee Overview</h1>
    <p class="intro">System-wide payment summary for all students.</p>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php foreach ($errors as $group): foreach ((array) $group as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
    <div class="summary">
        <div class="card"><small>Total fee</small><strong><?= htmlspecialchars(formatMoney($totals['fee'])) ?></strong></div>
        <div class="card"><small>Collected</small><strong><?= htmlspecialchars(formatMoney($totals['paid'])) ?></strong></div>
        <div class="card"><small>Outstanding</small><strong><?= htmlspecialchars(formatMoney($totals['remaining'])) ?></strong></div>
    </div>
    <section class="panel" style="margin-bottom: 24px;">
        <h2><?= $structure ? 'Update fee structure' : 'Create fee structure' ?></h2>
        <form class="fee-form" method="post" action="<?= $structure ? '/admin/fee-structure-update/' . (int) $structure['id'] : '/admin/fee-structure-store' ?>">
            <div><label for="name">Fee name</label><input id="name" name="name" maxlength="100" value="<?= htmlspecialchars($structure['name'] ?? '') ?>" required></div>
            <div><label for="monthly_amount">Monthly amount (NPR)</label><input id="monthly_amount" name="monthly_amount" type="number" min="0.01" step="0.01" value="<?= htmlspecialchars((string) ($structure['monthly_amount'] ?? '')) ?>" required></div>
            <div><label for="due_day">Due day</label><input id="due_day" name="due_day" type="number" min="1" max="31" value="<?= htmlspecialchars((string) ($structure['due_day'] ?? '10')) ?>" required></div>
            <div><label for="status">Status</label><select id="status" name="status"><option value="active" <?= ($structure['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($structure['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
            <button type="submit"><?= $structure ? 'Save changes' : 'Create structure' ?></button>
        </form>
    </section>
    <section class="panel">
        <?php if ($fees): ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Total Fee</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Due date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $fee): ?>
                            <?php $remaining = max(0, (float) ($fee['amount'] ?? 0) - (float) ($fee['paid_amount'] ?? 0)); ?>
                            <tr>
                                <td><?= htmlspecialchars($fee['full_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($fee['student_id'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(formatMoney($fee['amount'] ?? 0)) ?></td>
                                <td><?= htmlspecialchars(formatMoney($fee['paid_amount'] ?? 0)) ?></td>
                                <td><?= htmlspecialchars(formatMoney($remaining)) ?></td>
                                <td><?= htmlspecialchars(!empty($fee['due_date']) ? date('M j, Y', strtotime($fee['due_date'])) : '-') ?></td>
                                <td class="status <?= strtolower((string) ($fee['status'] ?? 'unpaid')) ?>"><?= htmlspecialchars(feeStatusLabel($fee['status'] ?? 'unpaid')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty">No fee records are available yet.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
