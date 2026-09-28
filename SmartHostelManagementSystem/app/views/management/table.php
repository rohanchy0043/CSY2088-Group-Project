<?php
$title = $title ?? 'Management';
$backUrl = $backUrl ?? '/';
$columns = $columns ?? [];
$rows = $rows ?? [];
$resource = $resource ?? null;
$createUrl = $createUrl ?? null;
$success = sessionSuccess();
$error = sessionError();
$errors = sessionErrors();
function managementValue($value, $key) {
    if ($value === null || $value === '') return '-';
    if ($key === 'amount') return 'NPR ' . number_format((float) $value, 2);
    if (in_array($key, ['due_date', 'paid_at', 'created_at'], true)) {
        return date('M j, Y', strtotime((string) $value));
    }
    if ($key === 'status') return ucwords(str_replace(['-', '_'], ' ', (string) $value));
    return (string) $value;
}
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
        main { margin: 0 auto; max-width: 1240px; padding: 38px 24px 54px; }
        h1 { font-size: 28px; margin: 0 0 8px; }
        .intro { color: #718096; margin: 0 0 24px; }
        .panel { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; padding: 22px; }
        .message { background: #ecfdf5; border-radius: 6px; color: #166534; margin-bottom: 14px; padding: 11px 12px; }
        .error { background: #fef2f2; color: #991b1b; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; min-width: 680px; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 13px 10px; text-align: left; font-size: 13px; vertical-align: top; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        .empty { color: #718096; margin: 0; }
        .status { color: #2563eb; font-weight: 600; }
        .actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .actions form { display: inline; }
        .action { background: #eef4ff; border: 1px solid #bfdbfe; border-radius: 5px; color: #1d4ed8; cursor: pointer; font-size: 12px; padding: 6px 8px; }
        .action.danger { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        @media (max-width: 560px) { main { padding: 28px 18px; } }
    </style>
</head>
<body>
<header class="topbar"><a href="<?= htmlspecialchars($backUrl) ?>">&larr; Back to dashboard</a></header>
<main>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="intro">Live records from the hostel management system.</p>
    <?php if ($createUrl): ?><p><a class="action" href="<?= htmlspecialchars($createUrl) ?>">Add new</a></p><?php endif; ?>
    <?php if (isset($search)): ?><form method="get" style="display:flex;gap:8px;margin-bottom:16px"><input name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search records"><button class="action" type="submit">Search</button></form><?php endif; ?>
    <?php if ($success): ?><div class="message"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php foreach ($errors as $fieldErrors): foreach ((array) $fieldErrors as $message): ?><div class="message error"><?= htmlspecialchars($message) ?></div><?php endforeach; endforeach; ?>
    <section class="panel">
        <?php if ($rows): ?>
        <div class="table-wrap"><table><thead><tr><?php foreach ($columns as $label => $key): ?><th><?= htmlspecialchars($label) ?></th><?php endforeach; ?><?php if ($resource): ?><th>Actions</th><?php endif; ?></tr></thead><tbody>
        <?php foreach ($rows as $row): ?><tr><?php foreach ($columns as $key): ?><td class="<?= str_contains(strtolower((string) $key), 'status') ? 'status' : '' ?>"><?= htmlspecialchars(managementValue($row[$key] ?? null, $key)) ?></td><?php endforeach; ?><?php if ($resource): ?><td><div class="actions"><?php if ($resource === 'rooms'): ?><a class="action" href="/warden/room-edit/<?= (int) $row['id'] ?>">Edit</a><form method="post" action="/warden/room-delete/<?= (int) $row['id'] ?>"><button class="action danger" type="submit">Delete</button></form><?php elseif ($resource === 'fees' && ($row['status'] ?? '') !== 'paid'): ?><form method="post" action="/warden/fee-mark-paid/<?= (int) $row['id'] ?>"><button class="action" type="submit">Mark paid</button></form><?php elseif ($resource === 'complaints'): ?><a class="action" href="/warden/complaint-view/<?= (int) $row['id'] ?>">Manage</a><?php elseif ($resource === 'visitors'): ?><?php if (($row['status'] ?? '') === 'pending'): ?><form method="post" action="/warden/visitor-approve/<?= (int) $row['id'] ?>"><input type="hidden" name="action" value="approve"><button class="action" type="submit">Approve</button></form><form method="post" action="/warden/visitor-approve/<?= (int) $row['id'] ?>"><input type="hidden" name="action" value="reject"><button class="action danger" type="submit">Reject</button></form><?php elseif (($row['status'] ?? '') === 'approved'): ?><form method="post" action="/warden/visitor-check-in/<?= (int) $row['id'] ?>"><button class="action" type="submit">Check in</button></form><?php elseif (($row['status'] ?? '') === 'checked_in'): ?><form method="post" action="/warden/visitor-check-out/<?= (int) $row['id'] ?>"><button class="action" type="submit">Check out</button></form><?php endif; ?><?php endif; ?></div></td><?php endif; ?></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php else: ?><p class="empty">No records are available yet.</p><?php endif; ?>
    </section>
</main>
</body>
</html>
