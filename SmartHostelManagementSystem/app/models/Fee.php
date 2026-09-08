<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class Fee {
    public static function find($id) {
        $stmt = db()->prepare("SELECT f.*, s.full_name, s.student_id, u.email 
                               FROM fees f 
                               JOIN students s ON f.student_id = s.id 
                               JOIN users u ON s.user_id = u.id 
                               WHERE f.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO fees (student_id, amount, due_date, status, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['student_id'],
            $data['amount'],
            $data['due_date'],
            $data['status'] ?? FEE_UNPAID,
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

    public static function markPaid($id, $method = 'cash', $transactionId = null) {
        $stmt = db()->prepare("UPDATE fees SET status = 'paid', paid_at = NOW(), payment_method = ?, transaction_id = ? WHERE id = ?");
        return $stmt->execute([$method, $transactionId, $id]);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM fees WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT f.*, u.full_name, s.student_id, u.email 
                             FROM fees f 
                             JOIN students s ON f.student_id = s.id 
                             JOIN users u ON s.user_id = u.id 
                             ORDER BY f.due_date DESC");
        return $stmt->fetchAll();
    }

    public static function forStudent($studentId) {
        $stmt = db()->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY due_date DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function countUnpaid() {
        return db()->query("SELECT COUNT(*) FROM fees WHERE status IN ('unpaid', 'overdue')")->fetchColumn();
    }

    public static function getTotalCollected() {
        $stmt = db()->query("SELECT SUM(amount) FROM fees WHERE status = 'paid'");
        return $stmt->fetchColumn() ?? 0;
    }

    public static function getTotalDue() {
        $stmt = db()->query("SELECT SUM(amount) FROM fees WHERE status IN ('unpaid', 'overdue')");
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
        $stmt = db()->prepare("UPDATE fees SET status = 'overdue' WHERE status = 'unpaid' AND due_date < CURDATE()");
        return $stmt->execute();
    }
}
?>