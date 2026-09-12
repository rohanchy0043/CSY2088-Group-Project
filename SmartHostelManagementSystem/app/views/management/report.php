<?php
$title = $title ?? 'Reports';
$backUrl = $backUrl ?? '/';
$sections = $sections ?? [];
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
        h1 { font-size: 28px; margin: 0 0 24px; }
        .section { background: #fff; border: 1px solid #e7ebf2; border-radius: 8px; margin-bottom: 20px; padding: 22px; }
        h2 { font-size: 16px; margin: 0 0 16px; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; min-width: 600px; width: 100%; }
        th, td { border-bottom: 1px solid #e7ebf2; padding: 11px 9px; text-align: left; font-size: 13px; }
        th { color: #718096; font-size: 11px; text-transform: uppercase; }
        .empty { color: #718096; font-size: 13px; }
        @media (max-width: 560px) { main { padding: 28px 18px; } }
    </style>
</head>
<body>
<header class="topbar"><a href="<?= htmlspecialchars($backUrl) ?>">&larr; Back to dashboard</a></header>
<main><h1><?= htmlspecialchars($title) ?></h1>
<?php foreach ($sections as $section): ?><section class="section"><h2><?= htmlspecialchars($section['title']) ?></h2><?php if ($section['rows']): ?><div class="table-wrap"><table><thead><tr><?php foreach ($section['columns'] as $label => $key): ?><th><?= htmlspecialchars($label) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($section['rows'] as $row): ?><tr><?php foreach ($section['columns'] as $key): ?><td><?= htmlspecialchars((string) ($row[$key] ?? '-')) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div><?php else: ?><p class="empty">No report data available.</p><?php endif; ?></section><?php endforeach; ?>
</main>
</body>
</html>
