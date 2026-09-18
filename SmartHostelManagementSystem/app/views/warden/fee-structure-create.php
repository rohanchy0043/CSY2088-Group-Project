<?php
$errors = sessionErrors();
$editing = !empty($structure);
$structure = $structure ?? ['name' => 'Hostel + Food Fee', 'monthly_amount' => '10000', 'due_day' => '1', 'status' => 'active'];
$action = $editing ? '/warden/fee-structure-edit/' . (int) $structure['id'] : '/warden/fee-structure-store';
$buttonLabel = $editing ? 'Update' : 'Save Fee Structure';
$title = $editing ? 'Edit Fee Structure' : 'FEE STRUCTURE';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=htmlspecialchars($title)?> - DormSync</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif}.topbar{background:#fff;border-bottom:1px solid #e7ebf2;padding:18px 6%}.topbar a{color:#2563eb;text-decoration:none;font-weight:600}.main-wrap{max-width:620px;margin:36px auto;padding:0 20px}.panel{background:#fff;border:1px solid #e7ebf2;border-radius:10px;padding:32px 28px;box-shadow:0 1px 0 rgba(15,23,42,.02)}.panel h1{margin:0 0 26px;font-size:18px;text-transform:uppercase;letter-spacing:.12em;text-align:center;color:#111827}.field{margin-bottom:18px}.field label{display:block;font-size:13px;color:#475569;font-weight:600;margin-bottom:7px}.field input,.field select{width:100%;padding:12px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px;background:#fff;color:#111827}.field input:focus,.field select:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}.field small{display:block;margin-top:8px;color:#64748b;font-size:12px}.form-actions{display:flex;justify-content:flex-end;gap:12px;margin-top:26px}.btn{border-radius:8px;padding:12px 22px;font-size:14px;font-weight:600;cursor:pointer;border:1px solid #dfe6ee;text-decoration:none;display:inline-block;transition:.2s ease}.btn-primary{background:#2563eb;border-color:#2563eb;color:#fff}.btn-secondary{background:#f8fafc;color:#1f2937}.error{color:#991b1b;font-size:13px;margin:0 0 10px}.hidden{display:none}
    </style>
</head>
<body>
    <header class="topbar">
        <a href="/warden/fees">&larr; Back to fees</a>
    </header>
    <main class="main-wrap">
        <section class="panel">
            <h1><?=htmlspecialchars($title)?></h1>
            <?php foreach($errors as $group): foreach((array)$group as $message): ?>
                <p class="error"><?=htmlspecialchars($message)?></p>
            <?php endforeach; endforeach; ?>
            <form method="post" action="<?=htmlspecialchars($action)?>">
                <div class="field">
                    <label for="name">Fee Name</label>
                    <input id="name" name="name" value="<?=htmlspecialchars((string)($structure['name'] ?? 'Hostel + Food Fee'))?>" maxlength="100" required>
                </div>
                <div class="field">
                    <label for="monthly_amount">Monthly Amount</label>
                    <input id="monthly_amount" name="monthly_amount" type="number" min="1" step="0.01" value="<?=htmlspecialchars((string)$structure['monthly_amount'])?>" required>
                </div>
                <div class="field">
                    <label for="due_day">Due Day</label>
                    <input id="due_day" name="due_day" type="number" min="1" max="31" value="<?=htmlspecialchars((string)$structure['due_day'])?>" required>
                    <small>Every Month</small>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?=($structure['status'] ?? 'active') === 'active' ? 'selected' : ''?>>Active</option>
                        <option value="inactive" <?=($structure['status'] ?? 'active') === 'inactive' ? 'selected' : ''?>>Inactive</option>
                    </select>
                </div>
                <div class="form-actions">
                    <a class="btn btn-secondary" href="/warden/fees">Cancel</a>
                    <button class="btn btn-primary" type="submit"><?=htmlspecialchars($buttonLabel)?></button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
