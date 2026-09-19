<?php
$success = sessionSuccess();
$error = sessionError();
$errors = sessionErrors();
$selectedStudent = $selectedStudent ?? null;
$fees = $fees ?? [];
$paymentHistory = $paymentHistory ?? [];
$selectedFee = $fees[0] ?? null;
$paymentBalances = array_map(static function ($fee) {
    return [
        'id' => (int) $fee['id'],
        'amount' => (float) $fee['amount'],
        'paid' => (float) $fee['paid_amount'],
        'balance' => (float) $fee['remaining_amount'],
        'month' => date('Y-m', strtotime($fee['fee_month'] ?: $fee['due_date']))
    ];
}, $fees);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Record Payment - DormSync</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f1f5f9;color:#172033;font-family:Arial,sans-serif}
        .topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:18px 6%}
        .topbar a{color:#2563eb;text-decoration:none;font-weight:600}
        main{max-width:700px;margin:0 auto;padding:42px 20px}
        .heading{margin-bottom:24px;text-align:center}.heading h1{margin:0 0 8px;font-size:25px;letter-spacing:.08em;text-transform:uppercase;color:#0f172a}.heading p{margin:0;color:#64748b;font-size:14px}
        .panel{background:#fff;border:1px solid #dbe3ed;border-radius:14px;padding:32px;box-shadow:0 12px 30px rgba(15,23,42,.07);margin-bottom:22px}
        .message{padding:12px 14px;border-radius:8px;margin-bottom:16px;background:#dcfce7;color:#166534}
        .message.error{background:#fee2e2;color:#991b1b}.error-text{color:#991b1b;font-size:13px;margin:7px 0}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
        .form-section{grid-column:1/-1;border-bottom:1px solid #e8eef5;padding-bottom:24px;margin-bottom:2px}.form-section:last-child{border-bottom:0;padding-bottom:0}.section-title{display:flex;align-items:center;gap:10px;color:#1e293b;font-size:14px;font-weight:700;margin:0 0 16px}.section-number{display:grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#dbeafe;color:#1d4ed8;font-size:12px}
        .field{margin-bottom:4px}.field.full{grid-column:1/-1}.period-fields{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        label{display:block;color:#334155;font-size:13px;font-weight:700;margin-bottom:8px}
        input,select{width:100%;padding:13px 14px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#172033;font:inherit}
        input[readonly]{background:#f8fafc;color:#475569;cursor:default}
        input:focus,select:focus{outline:0;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
        .fee-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:28px 0 24px}
        .summary-card{background:linear-gradient(145deg,#f8fafc,#eef4ff);border:1px solid #dbe5f2;border-radius:12px;padding:18px}
        .summary-card span{display:block;color:#64748b;font-size:12px;font-weight:600}.summary-card strong{display:block;margin-top:7px;font-size:20px;color:#0f172a}
        .selected-student{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:13px 15px;color:#64748b;font-size:13px}.selected-student strong{display:block;color:#172033;font-size:15px;margin-top:4px}
        .fee-overview{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:22px 0 4px}.overview-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 15px}.overview-card span{display:block;color:#64748b;font-size:12px;margin-bottom:6px}.overview-card strong{font-size:18px;color:#172033}
        .calculation{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:15px 17px;margin-top:4px}.calculation-row{display:flex;justify-content:space-between;padding:7px 0;color:#475569}.calculation-row strong{color:#172033}.calculation-row.status{border-top:1px solid #e2e8f0;margin-top:5px;padding-top:12px}.calculation-row.status strong{color:#b45309}
        .actions{display:flex;justify-content:center;gap:12px;margin-top:30px}.actions:before,.actions:after{content:'';height:1px;background:#e2e8f0;flex:1;margin:auto 0}
        .button{border:0;border-radius:9px;padding:14px 36px;font-weight:700;font-size:14px;text-decoration:none;cursor:pointer}
        .button.cancel{background:#f1f5f9;color:#334155}.button.save{background:#2563eb;color:#fff;box-shadow:0 5px 12px rgba(37,99,235,.22)}.button.save:hover{background:#1d4ed8}
        .muted{color:#64748b;font-size:14px}.history{width:100%;border-collapse:collapse}.history th,.history td{padding:13px 10px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:13px}.history th{color:#64748b;font-size:11px;text-transform:uppercase}
        @media(max-width:650px){.grid,.fee-summary,.period-fields,.fee-overview{grid-template-columns:1fr}.field.full{grid-column:auto}.actions{flex-direction:column}.actions:before,.actions:after{display:none}.button{text-align:center;width:100%}}
    </style>
</head>
<body>
    <header class="topbar"><a href="/warden/fees">&larr; Back to fees</a></header>
    <main>
        <div class="heading"><h1>Record Payment</h1><p>Record a payment and automatically update the student's balance.</p></div>
        <section class="panel">
            <?php if($success): ?><div class="message"><?=htmlspecialchars($success)?></div><?php endif; ?>
            <?php if($error): ?><div class="message error"><?=htmlspecialchars($error)?></div><?php endif; ?>
            <?php foreach($errors as $group): foreach((array)$group as $message): ?><p class="error-text"><?=htmlspecialchars($message)?></p><?php endforeach; endforeach; ?>
            <form method="post" action="/warden/fee-payment-store">
                <div class="grid">
                    <div class="form-section">
                        <h2 class="section-title"><span class="section-number">1</span>Student and payment period</h2>
                    <div class="field full">
                        <?php if ($selectedStudent): ?>
                            <div class="selected-student"><span>Student</span><strong><?= htmlspecialchars($selectedStudent['full_name']) ?> (<?= htmlspecialchars($selectedStudent['student_id']) ?>)</strong></div>
                        <?php else: ?>
                            <label for="student_id">Student</label>
                            <select id="student_id" name="student_id" onchange="if(this.value) window.location.href='/warden/fee-payment/'+this.value" required>
                                <option value="">Select student</option>
                                <?php foreach($students as $student): ?>
                                    <option value="<?= (int)$student['id'] ?>"><?=htmlspecialchars($student['full_name'])?> (<?=htmlspecialchars($student['student_id'])?>)</option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <?php if($selectedStudent && $selectedFee): ?>
                    </div>
                    <div class="form-section">
                        <h2 class="section-title"><span class="section-number">2</span>Payment period</h2>
                        <div class="field full"><label for="payment_type">Payment Type</label><select id="payment_type" name="payment_type"><option value="monthly">Monthly</option><option value="multiple_months">Multiple Months</option><option value="full_year">Full Year</option></select><small class="muted">Choose one month, several months, or a full year.</small></div>
                        <div class="period-fields full">
                            <div class="field"><label for="payment_from">Payment From</label><input id="payment_from" name="payment_from" type="month" value="<?=htmlspecialchars(date('Y-m', strtotime($selectedFee['fee_month'] ?: $selectedFee['due_date'])))?>" required></div>
                            <div class="field"><label for="payment_to">Payment To</label><input id="payment_to" name="payment_to" type="month" value="<?=htmlspecialchars(date('Y-m', strtotime($selectedFee['fee_month'] ?: $selectedFee['due_date'])))?>" required></div>
                        </div>
                        <div class="fee-overview full">
                            <div class="overview-card"><span>Total Fee</span><strong>NPR <?=number_format((float)$selectedFee['amount'], 2)?></strong></div>
                            <div class="overview-card"><span>Previously Paid</span><strong>NPR <?=number_format((float)$selectedFee['paid_amount'], 2)?></strong></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="field full">
                            <label for="fee_id">Fee Month</label>
                            <select id="fee_id" name="fee_id" required>
                                <?php foreach ($fees as $fee): ?>
                                    <?php $feeMonth = date('M Y', strtotime($fee['fee_month'] ?: $fee['due_date'])); ?>
                                    <option value="<?= (int) $fee['id'] ?>" data-month="<?= htmlspecialchars(date('Y-m', strtotime($fee['fee_month'] ?: $fee['due_date']))) ?>" data-amount="<?= htmlspecialchars((float) $fee['amount']) ?>" data-paid="<?= htmlspecialchars((float) $fee['paid_amount']) ?>" data-balance="<?= htmlspecialchars((float) $fee['remaining_amount']) ?>" <?= (int) $fee['id'] === (int) $selectedFee['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($feeMonth) ?> · Balance NPR <?= number_format((float) $fee['remaining_amount'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="muted">Choose the exact unpaid fee record you want to update.</small>
                        </div>
                        <div class="field full"><label for="amount">Amount Paid Now</label><input id="amount" name="amount" type="number" min="0.01" max="<?= htmlspecialchars((float) $selectedFee['remaining_amount']) ?>" step="0.01" placeholder="Enter amount" required><small class="muted" id="amount-help">Maximum for this payment selection: NPR <?= number_format((float) $selectedFee['remaining_amount'], 2) ?></small></div>
                        <div class="field full"><label for="payment_method">Payment Method</label><select id="payment_method" name="payment_method" required><option value="cash">Cash</option><option value="bank transfer">Bank Transfer</option><option value="mobile wallet">Mobile Wallet</option><option value="cheque">Cheque</option></select></div>
                        <div class="field full"><label for="payment_date">Payment Date</label><input id="payment_date" name="payment_date" type="date" value="<?=htmlspecialchars(date('Y-m-d'))?>" max="<?=htmlspecialchars(date('Y-m-d'))?>" required></div>
                        <div class="calculation full">
                            <div class="calculation-row"><span>Total Paid</span><strong id="total-paid">NPR <?=number_format((float)$selectedFee['paid_amount'],2)?></strong></div>
                            <div class="calculation-row"><span>Remaining Amount</span><strong id="remaining">NPR <?=number_format((float)$selectedFee['remaining_amount'],2)?></strong></div>
                            <div class="calculation-row status"><span>Status</span><strong id="status"><?=((float)$selectedFee['paid_amount'] <= 0) ? 'UNPAID' : 'PARTIAL'?></strong></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h2 class="section-title"><span class="section-number">3</span>Confirm payment</h2>
                        <div class="actions full"><button class="button save" type="submit">Save Payment</button></div>
                    </div>
                    <?php else: ?>
                        <p class="muted full"><?= $selectedStudent ? 'This student has no outstanding fee available for payment.' : 'Select a student to record a payment.' ?></p>
                    <?php endif; ?>
                </div>
            </form>
        </section>
        <?php if($selectedStudent): ?>
            <section class="panel"><h2>Payment history</h2>
                <?php if($paymentHistory): ?>
                    <table class="history"><tr><th>Date</th><th>Amount</th><th>Method</th></tr>
                    <?php foreach($paymentHistory as $payment): ?>
                        <tr><td><?=htmlspecialchars($payment['payment_date'])?></td><td>NPR <?=number_format((float)$payment['amount'],2)?></td><td><?=htmlspecialchars(ucwords($payment['payment_method']))?></td></tr>
                    <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p class="muted">No payments recorded for this student yet.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    <?php if($selectedStudent && $selectedFee): ?>
    <script>
        const amountInput = document.getElementById('amount');
        const paymentType = document.getElementById('payment_type');
        const paymentFrom = document.getElementById('payment_from');
        const paymentTo = document.getElementById('payment_to');
        const feeSelect = document.getElementById('fee_id');
        const totalPaid = document.getElementById('total-paid');
        const remaining = document.getElementById('remaining');
        const status = document.getElementById('status');
        const paymentBalances = <?= json_encode($paymentBalances, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        let baseMonth = feeSelect.options[feeSelect.selectedIndex].dataset.month;
        const money = value => 'NPR ' + Number(value).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        function selectedPeriodBalance() {
            if (paymentType.value === 'monthly') {
                return paymentBalances.find(fee => fee.id === Number(feeSelect.value))?.balance || 0;
            }
            const from = paymentFrom.value;
            const to = paymentTo.value;
            return paymentBalances
                .filter(fee => fee.month >= from && fee.month <= to)
                .reduce((total, fee) => total + fee.balance, 0);
        }
        function updatePaymentSummary() {
            const balance = selectedPeriodBalance();
            const paid = paymentBalances
                .filter(fee => paymentType.value === 'monthly' ? fee.id === Number(feeSelect.value) : fee.month >= paymentFrom.value && fee.month <= paymentTo.value)
                .reduce((total, fee) => total + fee.paid, 0);
            const current = Math.max(0, Number(amountInput.value || 0));
            const newPaid = paid + Math.min(current, balance);
            const newBalance = Math.max(0, balance - current);
            totalPaid.textContent = money(newPaid);
            remaining.textContent = money(newBalance);
            status.textContent = newBalance === 0 ? 'PAID' : newPaid > 0 ? 'PARTIAL' : 'UNPAID';
            amountInput.max = balance.toFixed(2);
            document.getElementById('amount-help').textContent = 'Maximum for this payment selection: ' + money(balance);
        }
        function updatePaymentPeriod() {
            const isMonthly = paymentType.value === 'monthly';
            paymentFrom.readOnly = isMonthly;
            paymentTo.readOnly = isMonthly;
            if (isMonthly) {
                paymentFrom.value = paymentTo.value = baseMonth;
            } else if (paymentType.value === 'multiple_months') {
                paymentFrom.readOnly = false;
                paymentTo.readOnly = false;
                if (!paymentFrom.value) paymentFrom.value = baseMonth;
                if (!paymentTo.value || paymentTo.value < paymentFrom.value) paymentTo.value = paymentFrom.value;
            } else if (paymentType.value === 'full_year') {
                paymentFrom.readOnly = false;
                paymentTo.readOnly = true;
                const start = paymentFrom.value;
                if (start) {
                    const date = new Date(start + '-01T00:00:00');
                    date.setMonth(date.getMonth() + 11);
                    paymentTo.value = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
                }
            }
        }
        function updateSelectedFee() {
            const option = feeSelect.options[feeSelect.selectedIndex];
            baseMonth = option.dataset.month;
            paymentFrom.value = baseMonth;
            paymentTo.value = baseMonth;
            updatePaymentPeriod();
            updatePaymentSummary();
        }
        amountInput.addEventListener('input', updatePaymentSummary);
        feeSelect.addEventListener('change', updateSelectedFee);
        paymentType.addEventListener('change', () => { updatePaymentPeriod(); updatePaymentSummary(); });
        paymentFrom.addEventListener('change', () => {
            if (paymentType.value === 'full_year') {
                updatePaymentPeriod();
            } else if (paymentType.value === 'multiple_months' && paymentTo.value < paymentFrom.value) {
                paymentTo.value = paymentFrom.value;
            }
            updatePaymentSummary();
        });
        paymentTo.addEventListener('change', () => {
            if (paymentType.value === 'multiple_months' && paymentTo.value < paymentFrom.value) {
                paymentTo.value = paymentFrom.value;
            }
            updatePaymentSummary();
        });
        updatePaymentPeriod();
    </script>
    <?php endif; ?>
</body>
</html>
