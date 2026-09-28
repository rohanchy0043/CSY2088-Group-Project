<?php
$success = sessionSuccess();
$error = sessionError();
$errors = sessionErrors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors - DormSync</title>
    <style>
        :root { --ink: #172033; --muted: #718096; --line: #e7ebf2; --blue: #2563eb; --surface: #fff; --background: #f7f9fc; }
        * { box-sizing: border-box; }
        body { background: var(--background); color: var(--ink); font-family: Arial, sans-serif; margin: 0; }
        a { color: inherit; text-decoration: none; }
        main { margin: 0 auto; max-width: 1180px; padding: 42px 28px 60px; }
        .heading { align-items: flex-start; display: flex; justify-content: space-between; gap: 20px; }
        h1 { font-size: 28px; margin: 0 0 8px; }
        .intro { color: var(--muted); margin: 0 0 30px; }
        .button { background: var(--blue); border: 1px solid var(--blue); border-radius: 6px; color: #fff; cursor: pointer; font: inherit; font-size: 14px; font-weight: 600; padding: 11px 16px; }
        .button:hover { background: #1d4ed8; }
        .stats { display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 30px; }
        .stat, .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 9px; }
        .stat { padding: 20px 22px; }
        .stat-label { color: var(--muted); display: block; font-size: 13px; }
        .stat-value { display: block; font-size: 27px; margin-top: 10px; }
        .panel { padding: 24px; }
        .panel-heading { align-items: center; display: flex; justify-content: space-between; margin-bottom: 20px; }
        h2 { font-size: 17px; margin: 0; }
        .filters { display: flex; gap: 12px; margin-bottom: 18px; }
        .search { flex: 1; position: relative; }
        .search span { color: var(--muted); left: 12px; position: absolute; top: 11px; }
        input, select { border: 1px solid #cbd5e1; border-radius: 6px; color: var(--ink); font: inherit; padding: 10px 12px; width: 100%; }
        .search input { padding-left: 34px; }
        select { max-width: 150px; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; min-width: 680px; width: 100%; }
        th, td { border-top: 1px solid var(--line); font-size: 13px; padding: 14px 10px; text-align: left; }
        th { color: var(--muted); font-size: 11px; text-transform: uppercase; }
        .status { align-items: center; display: inline-flex; gap: 7px; font-weight: 600; }
        .status::before { background: #94a3b8; border-radius: 50%; content: ""; height: 8px; width: 8px; }
        .status-pending::before { background: #eab308; }
        .status-approved::before { background: #22c55e; }
        .status-rejected::before, .status-checked_out::before { background: #ef4444; }
        .status-checked_in::before { background: #3b82f6; }
        .message { background: #ecfdf5; border-radius: 6px; color: #166534; margin-bottom: 16px; padding: 11px 12px; }
        .message.error { background: #fef2f2; color: #991b1b; }
        .empty { color: var(--muted); margin: 18px 0 0; }
        dialog { border: 0; border-radius: 10px; box-shadow: 0 20px 60px rgba(15, 23, 42, .25); max-width: 440px; padding: 0; width: calc(100% - 32px); }
        dialog::backdrop { background: rgba(15, 23, 42, .45); }
        .modal { padding: 26px; }
        .modal-heading { align-items: flex-start; display: flex; justify-content: space-between; }
        .modal-heading h2 { font-size: 20px; }
        .modal-heading p { color: var(--muted); font-size: 13px; margin: 7px 0 22px; }
        .close { background: none; border: 0; color: var(--muted); cursor: pointer; font-size: 22px; padding: 0; width: auto; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 15px 0 6px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }
        .secondary { background: #fff; border-color: #cbd5e1; color: #334155; }
        @media (max-width: 620px) { main { padding: 28px 18px; } .heading { display: block; } .heading .button { margin-top: 16px; } .stats { grid-template-columns: 1fr; } .filters { display: block; } select { margin-top: 10px; max-width: none; } }
    </style>
</head>
<body>
<main>
    <header class="heading">
        <div><h1>Visitors</h1><p class="intro">Manage and track your hostel visitors.</p></div>
        <button class="button" type="button" id="openVisitorModal">+ Register Visitor</button>
    </header>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
    <section class="stats" aria-label="Visitor summary">
        <article class="stat"><span class="stat-label">Total</span><strong class="stat-value"><?= (int) $stats['total'] ?></strong></article>
        <article class="stat"><span class="stat-label">Pending</span><strong class="stat-value"><?= (int) $stats['pending'] ?></strong></article>
        <article class="stat"><span class="stat-label">Approved</span><strong class="stat-value"><?= (int) $stats['approved'] ?></strong></article>
    </section>
    <section class="panel">
        <div class="panel-heading"><h2>Visitor Records</h2></div>
        <div class="filters">
            <div class="search"><span aria-hidden="true">🔍</span><input id="visitorSearch" type="search" placeholder="Search visitors..." aria-label="Search visitors"></div>
            <select id="statusFilter" aria-label="Filter by status"><option value="">All Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="checked_in">Checked In</option><option value="checked_out">Checked Out</option></select>
        </div>
        <?php if ($visitors): ?>
        <div class="table-wrap"><table><thead><tr><th>Visitor</th><th>Contact</th><th>Purpose</th><th>Student</th><th>Status</th></tr></thead><tbody id="visitorRows">
        <?php foreach ($visitors as $visitor): ?><tr data-status="<?= htmlspecialchars($visitor['status']) ?>" data-search="<?= htmlspecialchars(strtolower($visitor['visitor_name'] . ' ' . $visitor['contact'] . ' ' . $visitor['purpose'] . ' ' . $visitor['full_name'])) ?>"><td><?= htmlspecialchars($visitor['visitor_name']) ?></td><td><?= htmlspecialchars($visitor['contact']) ?></td><td><?= htmlspecialchars($visitor['purpose']) ?></td><td><?= htmlspecialchars($visitor['full_name']) ?></td><td><span class="status status-<?= htmlspecialchars($visitor['status']) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $visitor['status']))) ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div><p class="empty" id="noMatches" hidden>No visitors match your filters.</p>
        <?php else: ?><p class="empty">No visitor records are available yet.</p><?php endif; ?>
    </section>
</main>
<dialog id="visitorModal">
    <form class="modal" method="post" action="/admin/visitor-store">
        <div class="modal-heading"><div><h2>Register Visitor</h2><p>Add details about your visitor.</p></div><button class="close" type="button" id="closeVisitorModal" aria-label="Close">&times;</button></div>
        <label for="student_id">Resident student</label><select id="student_id" name="student_id" required><option value="">Select student</option><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>"><?= htmlspecialchars($student['full_name'] . ' (' . $student['student_id'] . ')') ?></option><?php endforeach; ?></select>
        <label for="visitor_name">Visitor Name</label><input id="visitor_name" name="visitor_name" placeholder="Enter visitor name" maxlength="100" required>
        <label for="contact">Contact Number</label><input id="contact" name="contact" type="tel" placeholder="Enter contact number" required>
        <label for="purpose">Purpose of Visit</label><input id="purpose" name="purpose" placeholder="Why are they visiting?" maxlength="255" required>
        <div class="modal-actions"><button class="button secondary" type="button" id="cancelVisitorModal">Cancel</button><button class="button" type="submit">Register</button></div>
    </form>
</dialog>
<script>
const modal = document.getElementById('visitorModal');
document.getElementById('openVisitorModal').addEventListener('click', () => modal.showModal());
document.getElementById('closeVisitorModal').addEventListener('click', () => modal.close());
document.getElementById('cancelVisitorModal').addEventListener('click', () => modal.close());
const filterVisitors = () => {
    const query = document.getElementById('visitorSearch').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value;
    let visible = 0;
    document.querySelectorAll('#visitorRows tr').forEach(row => {
        const matches = (!status || row.dataset.status === status) && (!query || row.dataset.search.includes(query));
        row.hidden = !matches;
        if (matches) visible++;
    });
    const noMatches = document.getElementById('noMatches');
    if (noMatches) noMatches.hidden = visible !== 0;
};
document.getElementById('visitorSearch').addEventListener('input', filterVisitors);
document.getElementById('statusFilter').addEventListener('change', filterVisitors);
</script>
</body>
</html>
