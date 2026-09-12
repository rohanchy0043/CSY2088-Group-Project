<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class Visitor {
    public static function find($id) {
        $stmt = db()->prepare("SELECT v.*, s.id AS student_record_id, s.user_id, u.full_name, s.student_id, s.room_id, r.room_number 
                               FROM visitors v 
                               JOIN students s ON v.student_id = s.id 
                       JOIN users u ON s.user_id = u.id
                               LEFT JOIN rooms r ON s.room_id = r.id 
                               WHERE v.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO visitors (student_id, visitor_name, contact, purpose, status) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['student_id'],
            $data['visitor_name'],
            $data['contact'] ?? '',
            $data['purpose'] ?? '',
            $data['status'] ?? VISITOR_PENDING
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
        $stmt = db()->prepare("UPDATE visitors SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function approve($id, $approvedBy) {
        $stmt = db()->prepare("UPDATE visitors SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
        return $stmt->execute([$approvedBy, $id]);
    }

    public static function reject($id, $approvedBy) {
        $stmt = db()->prepare("UPDATE visitors SET status = 'rejected', approved_by = ?, approved_at = NOW() WHERE id = ?");
        return $stmt->execute([$approvedBy, $id]);
    }

    public static function checkIn($id) {
        $stmt = db()->prepare("UPDATE visitors SET status = 'checked_in', check_in = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function checkOut($id) {
        $stmt = db()->prepare("UPDATE visitors SET status = 'checked_out', check_out = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM visitors WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT v.*, u.full_name, s.student_id 
                             FROM visitors v 
                             JOIN students s ON v.student_id = s.id 
                             JOIN users u ON s.user_id = u.id
                             ORDER BY v.check_in DESC");
        return $stmt->fetchAll();
    }

    public static function forStudent($studentId) {
        $stmt = db()->prepare("SELECT * FROM visitors WHERE student_id = ? ORDER BY check_in DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function countPending() {
        return db()->query("SELECT COUNT(*) FROM visitors WHERE status = 'pending'")->fetchColumn();
    }

    public static function countApproved() {
        return db()->query("SELECT COUNT(*) FROM visitors WHERE status = 'approved'")->fetchColumn();
    }

    public static function countCheckedIn() {
        return db()->query("SELECT COUNT(*) FROM visitors WHERE status = 'checked_in'")->fetchColumn();
    }

    public static function getByStatus($status) {
        $stmt = db()->prepare("SELECT v.*, s.full_name, s.student_id FROM visitors v JOIN students s ON v.student_id = s.id WHERE v.status = ? ORDER BY v.check_in DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
}
?>