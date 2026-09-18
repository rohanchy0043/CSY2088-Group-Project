<?php
$success = sessionSuccess();
$error = sessionError();
$errors = sessionErrors();
$students = $students ?? [];
$structures = $structures ?? [];
$money = static fn($value) => 'NPR ' . number_format((float) $value, 2);
$statusLabel = static fn($value) => strtoupper(str_replace(['-', '_'], ' ', (string) $value));
$total = 0;
$paid = 0;
foreach ($students as $student) {
    $total += (float) ($student['total_fee'] ?? 0);
    $paid += (float) ($student['paid_amount'] ?? 0);
}
$remaining = max(0, $total - $paid);
$statusCounts = ['paid' => 0, 'partial' => 0, 'unpaid' => 0, 'overdue' => 0];
foreach ($students as $student) {
    $status = strtolower((string) ($student['status'] ?? 'unpaid'));
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee Management - DormSync</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif}
        .topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:18px 6%}.topbar a{color:#2563eb;text-decoration:none;font-weight:600}
        main{max-width:1200px;margin:auto;padding:38px 24px 56px}.heading{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:24px}.heading h1{margin:0 0 7px;font-size:28px}.heading p{margin:0;color:#64748b}
        .panel{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;box-shadow:0 4px 14px rgba(15,23,42,.04);margin-bottom:22px}.panel h2{font-size:18px;margin:0 0 18px}
        .message{padding:12px 14px;border-radius:8px;margin-bottom:16px;background:#dcfce7;color:#166534}.message.error{background:#fee2e2;color:#991b1b}
        .summary{display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:22px}.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px}.card small{display:block;color:#64748b;margin-bottom:8px}.card strong{font-size:22px}.card.paid-card{border-top:4px solid #16a34a}.card.due-card{border-top:4px solid #f59e0b}
        .status-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:0 0 22px}.status-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px}.status-card span{display:block;color:#64748b;font-size:12px;margin-bottom:5px}.status-card strong{font-size:20px}.status-card.paid strong{color:#15803d}.status-card.partial strong{color:#b45309}.status-card.unpaid strong,.status-card.overdue strong{color:#b91c1c}
        .structure{display:grid;grid-template-columns:2fr 1fr 1fr auto;align-items:center;gap:18px}.structure small{display:block;color:#64748b;font-size:12px;margin-bottom:5px}.structure strong{font-size:15px}.update{color:#2563eb;font-weight:700;text-decoration:none;white-space:nowrap}
        .toolbar{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:18px}.toolbar h2{margin:0}.search,.filter{padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font:inherit}.search{min-width:240px}.filter{min-width:140px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:780px}th,td{padding:14px 12px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:13px}th{color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.04em}tbody tr:hover{background:#f8fafc}.student{font-weight:700}.student small{display:block;color:#64748b;font-weight:400;margin-top:4px}.status{font-weight:700}.paid{color:#15803d}.partial{color:#b45309}.unpaid,.overdue{color:#b91c1c}.badge{display:inline-block;padding:5px 9px;border-radius:999px;font-size:11px;font-weight:700;background:#f1f5f9}.badge.paid{background:#dcfce7}.badge.partial{background:#fef3c7}.badge.unpaid,.badge.overdue{background:#fee2e2}.action{display:inline-block;color:#fff;background:#2563eb;padding:9px 12px;border-radius:7px;text-decoration:none;font-weight:700;font-size:12px;white-space:nowrap}.action:hover{background:#1d4ed8}.empty{color:#64748b}
        @media(max-width:700px){.heading{display:block}.summary,.status-summary{grid-template-columns:1fr}.structure{grid-template-columns:1fr 1fr}.structure .wide{grid-column:1/-1}.toolbar{display:block}.search,.filter{width:100%;margin-top:10px}}
    </style>
</head>
<body>
    <header class="topbar"><a href="/warden/dashboard">&larr; Back to dashboard</a></header>
    <main>
        <div class="heading"><div><h1>Fee Management</h1><p>Manage the monthly fee rule and student payment balances.</p></div><?php if(!$structures): ?><a class="update" href="/warden/fee-structure-create">Create Fee Structure</a><?php endif; ?></div>
        <?php if($success): ?><div class="message"><?=htmlspecialchars($success)?></div><?php endif; ?>
        <?php if($error): ?><div class="message error"><?=htmlspecialchars($error)?></div><?php endif; ?>
        <?php foreach($errors as $group): foreach((array)$group as $message): ?><div class="message error"><?=htmlspecialchars($message)?></div><?php endforeach; endforeach; ?>
        <div class="summary">
            <div class="card"><small>Total billed</small><strong><?=htmlspecialchars($money($total))?></strong></div>
            <div class="card paid-card"><small>Total collected</small><strong><?=htmlspecialchars($money($paid))?></strong></div>
            <div class="card due-card"><small>Total outstanding</small><strong><?=htmlspecialchars($money($remaining))?></strong></div>
        </div>
        <div class="status-summary">
            <div class="status-card paid"><span>Paid students</span><strong><?= (int) $statusCounts['paid'] ?></strong></div>
            <div class="status-card partial"><span>Partial payments</span><strong><?= (int) $statusCounts['partial'] ?></strong></div>
            <div class="status-card unpaid"><span>Unpaid students</span><strong><?= (int) $statusCounts['unpaid'] ?></strong></div>
            <div class="status-card overdue"><span>Overdue students</span><strong><?= (int) $statusCounts['overdue'] ?></strong></div>
        </div>
        <section class="panel">
            <h2>Fee Structure</h2>
            <?php if($structures): $structure = $structures[0]; ?>
                <div class="structure">
                    <div class="wide"><small>Fee Name</small><strong><?=htmlspecialchars($structure['name'])?></strong></div>
                    <div><small>Monthly Amount</small><strong><?=htmlspecialchars($money($structure['monthly_amount']))?></strong></div>
                    <div><small>Due Day</small><strong><?= (int)$structure['due_day'] ?> every month</strong></div>
                    <a class="update" href="/warden/fee-structure-edit/<?= (int)$structure['id'] ?>">Update</a>
                </div>
            <?php else: ?><p class="empty">No fee structure configured.</p><?php endif; ?>
        </section>
        <section class="panel">
            <div class="toolbar"><h2>Student Fee Management</h2><div><input class="search" id="fee-search" type="search" placeholder="Search student..."><select class="filter" id="fee-filter"><option value="all">All statuses</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="unpaid">Unpaid</option><option value="overdue">Overdue</option></select></div></div>
            <?php if($students): ?>
                <div class="table-wrap"><table id="fee-table"><thead><tr><th>Student</th><th>Total fee</th><th>Paid</th><th>Remaining</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php foreach($students as $student): ?>
                    <?php $studentStatus = strtolower((string) ($student['status'] ?? 'unpaid')); ?>
                    <tr data-status="<?=htmlspecialchars($studentStatus)?>"><td class="student"><?=htmlspecialchars($student['full_name'])?><small><?=htmlspecialchars($student['student_id'])?></small></td><td><?=htmlspecialchars($money($student['total_fee']))?></td><td><?=htmlspecialchars($money($student['paid_amount']))?></td><td><?=htmlspecialchars($money($student['remaining_amount']))?></td><td class="status"><span class="badge <?=htmlspecialchars($studentStatus)?>"><?=htmlspecialchars($statusLabel($studentStatus))?></span></td><td><a class="action" href="/warden/fee-payment/<?= (int)$student['student_record_id'] ?>"><?= $studentStatus === 'paid' ? 'View history' : 'Record payment' ?></a></td></tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?><p class="empty">No student fee records are available.</p><?php endif; ?>
        </section>
    </main>
    <script>
        const feeSearch = document.getElementById('fee-search');
        const feeFilter = document.getElementById('fee-filter');
        function filterFees() {
            const query = (feeSearch.value || '').toLowerCase().trim();
            const status = feeFilter.value;
            document.querySelectorAll('#fee-table tbody tr').forEach(row => {
                const matchesText = row.textContent.toLowerCase().includes(query);
                const matchesStatus = status === 'all' || row.dataset.status === status;
                row.style.display = matchesText && matchesStatus ? '' : 'none';
            });
        }
        if (feeSearch) feeSearch.addEventListener('input', filterFees);
        if (feeFilter) feeFilter.addEventListener('change', filterFees);
    </script>
</body>
</html>
