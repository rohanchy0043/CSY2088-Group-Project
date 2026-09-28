<?php
$fees = $fees ?? [];
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
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 12px 10px; text-align: left; font-size: 13px; }
        th { color: #718096; text-transform: uppercase; font-size: 11px; }
        .empty { color: #718096; margin: 0; }
        .status { font-weight: 700; }
        .paid { color: #15803d; }
        .partial { color: #b45309; }
        .unpaid, .overdue { color: #b91c1c; }
        @media (max-width: 700px) { .summary { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="topbar"><a href="/admin/dashboard">&larr; Back to dashboard</a></header>
<main>
    <h1>Fee Overview</h1>
    <p class="intro">System-wide payment summary for all students.</p>
    <div class="summary">
        <div class="card"><small>Total fee</small><strong><?= htmlspecialchars(formatMoney($totals['fee'])) ?></strong></div>
        <div class="card"><small>Collected</small><strong><?= htmlspecialchars(formatMoney($totals['paid'])) ?></strong></div>
        <div class="card"><small>Outstanding</small><strong><?= htmlspecialchars(formatMoney($totals['remaining'])) ?></strong></div>
    </div>
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
