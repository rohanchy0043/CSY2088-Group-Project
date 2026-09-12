<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class Fee {
    public static function generateCurrentMonth() {
        $month = date('Y-m-01');
        $today = date('Y-m-d');
        $stmt = db()->prepare("SELECT * FROM fee_structures WHERE status = 'active' AND effective_from <= ? ORDER BY effective_from DESC");
        $stmt->execute([$today]);
        $structures = $stmt->fetchAll();
        if (!$structures) return;
        $structure = $structures[0];
        $students = db()->query('SELECT id FROM students WHERE room_id IS NOT NULL')->fetchAll(PDO::FETCH_COLUMN);
        $day = min((int) $structure['due_day'], (int) date('t', strtotime($month)));
        $dueDate = date('Y-m-', strtotime($month)) . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
        $check = db()->prepare('SELECT id FROM fees WHERE student_id = ? AND fee_month = ? LIMIT 1');
        $insert = db()->prepare("INSERT INTO fees (student_id, amount, paid_amount, due_date, fee_month, status, payment_type, notes) VALUES (?, ?, 0, ?, ?, 'unpaid', 'monthly', ?)");
        foreach ($students as $studentId) {
            $check->execute([$studentId, $month]);
            if (!$check->fetchColumn()) $insert->execute([$studentId, $structure['monthly_amount'], $dueDate, $month, $structure['name']]);
        }
    }

    public static function generateForStudent($studentId) {
        $stmt = db()->prepare('SELECT room_id FROM students WHERE id = ?');
        $stmt->execute([(int) $studentId]);
        if (!$stmt->fetchColumn()) {
            return false;
        }
        self::generateCurrentMonth();
        return true;
    }

    public static function find($id) {
        $stmt = db()->prepare("SELECT f.*, s.user_id AS student_user_id, u.full_name, s.student_id AS student_code, u.email
                               FROM fees f 
                               JOIN students s ON f.student_id = s.id 
                               JOIN users u ON s.user_id = u.id 
                               WHERE f.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO fees (student_id, amount, paid_amount, due_date, fee_month, status, payment_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['student_id'],
            $data['amount'],
            $data['paid_amount'] ?? 0,
            $data['due_date'],
            $data['fee_month'] ?? null,
            $data['status'] ?? FEE_UNPAID,
            $data['payment_type'] ?? 'monthly',
            $data['notes'] ?? ''
        ]);
        return db()->lastInsertId();
    }

    public static function update($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;
        $stmt = db()->prepare("UPDATE fees SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function markPaid($id, $method = 'cash', $transactionId = null, $recordedBy = null) {
        db()->beginTransaction();
        try {
            $stmt = db()->prepare('SELECT * FROM fees WHERE id = ? FOR UPDATE');
            $stmt->execute([(int) $id]);
            $fee = $stmt->fetch();
            if (!$fee) {
                throw new InvalidArgumentException('Fee record not found.');
            }
            $balance = max(0, round((float) $fee['amount'] - (float) $fee['paid_amount'], 2));
            if ($balance > 0) {
                $stmt = db()->prepare('INSERT INTO fee_payments (fee_id, student_id, amount, payment_date, payment_method, receipt_number, notes, recorded_by) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)');
                $stmt->execute([$id, $fee['student_id'], $balance, $method, $transactionId, 'Full balance marked as paid', $recordedBy]);
            }
            $stmt = db()->prepare("UPDATE fees SET paid_amount = amount, status = 'paid', paid_at = NOW(), payment_method = ?, transaction_id = ? WHERE id = ?");
            $stmt->execute([$method, $transactionId, $id]);
            db()->commit();
            return true;
        } catch (Throwable $exception) {
            db()->rollBack();
            throw $exception;
        }
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM fees WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT f.*, u.full_name, s.student_id, u.email,
                                    COALESCE(f.paid_amount, 0) AS paid_amount,
                                    GREATEST(f.amount - COALESCE(f.paid_amount, 0), 0) AS remaining_amount
                             FROM fees f 
                             JOIN students s ON f.student_id = s.id 
                             JOIN users u ON s.user_id = u.id 
                             ORDER BY f.due_date DESC");
        return $stmt->fetchAll();
    }

    public static function forStudent($studentId) {
        self::generateCurrentMonth();
        $stmt = db()->prepare("SELECT f.*, COALESCE(f.paid_amount, 0) AS paid_amount, GREATEST(f.amount - COALESCE(f.paid_amount, 0), 0) AS remaining_amount FROM fees f WHERE student_id = ? ORDER BY COALESCE(f.fee_month, f.due_date) DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function recordPayment($feeId, $studentId, $amount, $date, $method, $receipt, $notes, $recordedBy) {
        db()->beginTransaction();
        try {
            $stmt = db()->prepare('SELECT * FROM fees WHERE id = ? AND student_id = ? FOR UPDATE');
            $stmt->execute([$feeId, $studentId]);
            $fee = $stmt->fetch();
            $amountCents = (int) round((float) $amount * 100);
            $feeAmountCents = $fee ? (int) round((float) $fee['amount'] * 100) : 0;
            $paidCents = $fee ? (int) round((float) $fee['paid_amount'] * 100) : 0;
            $balanceCents = max(0, $feeAmountCents - $paidCents);
            if (!$fee || $amountCents <= 0 || $amountCents > $balanceCents) {
                throw new InvalidArgumentException('Payment amount cannot exceed the remaining balance of NPR ' . number_format($balanceCents / 100, 2) . '.');
            }
            $paid = ($paidCents + $amountCents) / 100;
            $remaining = ($balanceCents - $amountCents) / 100;
            $isOverdue = !empty($fee['due_date']) && strtotime((string) $fee['due_date']) < strtotime(date('Y-m-d')) && $remaining > 0;
            $status = $remaining <= 0 ? 'paid' : ($isOverdue ? 'overdue' : ($paid > 0 ? 'partial' : 'unpaid'));
            $stmt = db()->prepare('INSERT INTO fee_payments (fee_id, student_id, amount, payment_date, payment_method, receipt_number, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            if (!$stmt->execute([(int) $feeId, (int) $studentId, $amount, $date, $method, $receipt ?: null, $notes ?: null, $recordedBy]) || $stmt->rowCount() !== 1) {
                throw new RuntimeException('The payment entry could not be created.');
            }
            $stmt = db()->prepare('SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE fee_id = ?');
            $stmt->execute([(int) $feeId]);
            $paidFromPayments = round((float) $stmt->fetchColumn(), 2);
            $paid = $paidFromPayments;
            $remaining = max(0, round((float) $fee['amount'] - $paid, 2));
            $status = $remaining <= 0 ? 'paid' : ($isOverdue ? 'overdue' : ($paid > 0 ? 'partial' : 'unpaid'));
            $stmt = db()->prepare('UPDATE fees SET paid_amount = ?, status = ?, paid_at = CASE WHEN ? = \'paid\' THEN NOW() ELSE paid_at END, payment_method = ?, transaction_id = ? WHERE id = ?');
            if (!$stmt->execute([$paid, $status, $status, $method, $receipt ?: null, $feeId]) || $stmt->rowCount() !== 1) {
                throw new RuntimeException('The fee balance could not be updated.');
            }
            if ($stmt->rowCount() === 0) {
                $check = db()->prepare('SELECT paid_amount, status FROM fees WHERE id = ?');
                $check->execute([(int) $feeId]);
                $current = $check->fetch();
                if (!$current || abs((float) $current['paid_amount'] - $paid) > 0.009 || $current['status'] !== $status) {
                    throw new RuntimeException('Fee balance update did not affect the selected record.');
                }
            }
            $stmt = db()->prepare('SELECT paid_amount, status FROM fees WHERE id = ?');
            $stmt->execute([(int) $feeId]);
            $updatedFee = $stmt->fetch();
            if (!$updatedFee || abs((float) $updatedFee['paid_amount'] - $paid) > 0.009 || $updatedFee['status'] !== $status) {
                throw new RuntimeException('Payment was not applied to the fee record.');
            }
            db()->commit();
            return ['paid' => $paid, 'remaining' => max(0, $remaining), 'status' => $status];
        } catch (Throwable $exception) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            throw $exception;
        }
    }

    public static function payments($feeId) {
        $stmt = db()->prepare('SELECT fp.*, u.full_name AS recorded_by_name FROM fee_payments fp LEFT JOIN users u ON u.id = fp.recorded_by WHERE fp.fee_id = ? ORDER BY fp.payment_date DESC, fp.id DESC');
        $stmt->execute([$feeId]);
        return $stmt->fetchAll();
    }

    public static function paymentsForStudent($studentId) {
        $stmt = db()->prepare('SELECT fp.*, f.fee_month, f.due_date, u.full_name AS recorded_by_name
                               FROM fee_payments fp
                               JOIN fees f ON f.id = fp.fee_id
                               LEFT JOIN users u ON u.id = fp.recorded_by
                               WHERE fp.student_id = ?
                               ORDER BY fp.payment_date DESC, fp.id DESC');
        $stmt->execute([(int) $studentId]);
        return $stmt->fetchAll();
    }

    public static function countUnpaid() {
        return db()->query("SELECT COUNT(*) FROM fees WHERE status IN ('unpaid', 'partial', 'overdue')")->fetchColumn();
    }

    public static function getTotalCollected() {
        $stmt = db()->query("SELECT SUM(COALESCE(paid_amount, 0)) FROM fees");
        return $stmt->fetchColumn() ?? 0;
    }

    public static function getTotalDue() {
        $stmt = db()->query("SELECT SUM(GREATEST(amount - COALESCE(paid_amount, 0), 0)) FROM fees");
        return $stmt->fetchColumn() ?? 0;
    }

    public static function getTotal() {
        $stmt = db()->query("SELECT SUM(amount) FROM fees");
        return $stmt->fetchColumn() ?? 0;
    }

    public static function getByStatus($status) {
        $stmt = db()->prepare("SELECT f.*, s.full_name, s.student_id FROM fees f JOIN students s ON f.student_id = s.id WHERE f.status = ? ORDER BY f.due_date DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function updateOverdueStatus() {
        $stmt = db()->prepare("UPDATE fees SET status = CASE WHEN amount <= paid_amount THEN 'paid' WHEN due_date < CURDATE() THEN 'overdue' WHEN paid_amount > 0 THEN 'partial' ELSE 'unpaid' END WHERE due_date IS NOT NULL");
        return $stmt->execute();
    }
}
?>