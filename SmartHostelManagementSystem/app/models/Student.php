<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/User.php';

class Student {
    public static function find($id) {
        $stmt = db()->prepare("SELECT s.*, u.email, u.username, u.full_name, u.phone, r.room_number, r.block 
                               FROM students s 
                               JOIN users u ON s.user_id = u.id 
                               LEFT JOIN rooms r ON s.room_id = r.id 
                               WHERE s.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByUserId($userId) {
        $stmt = db()->prepare("SELECT s.*, u.email, u.username, u.full_name, u.phone, r.room_number, r.block 
                               FROM students s 
                               JOIN users u ON s.user_id = u.id 
                               LEFT JOIN rooms r ON s.room_id = r.id 
                               WHERE s.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function findByStudentId($studentId) {
        $stmt = db()->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO students (user_id, student_id, room_id, parent_contact, address, emergency_contact) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['student_id'],
            $data['room_id'] ?? null,
            $data['parent_contact'] ?? '',
            $data['address'] ?? '',
            $data['emergency_contact'] ?? ''
        ]);
        return db()->lastInsertId();
    }

    public static function update($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'user_id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;
        $stmt = db()->prepare("UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $student = self::find($id);
        if ($student) {
            User::delete($student['user_id']);
        }
        return true;
    }

    public static function all() {
        $stmt = db()->query("SELECT s.*, u.email, u.username, u.full_name, u.phone, r.room_number, r.block 
                             FROM students s 
                             JOIN users u ON s.user_id = u.id 
                             LEFT JOIN rooms r ON s.room_id = r.id 
                             ORDER BY s.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function count() {
        return db()->query("SELECT COUNT(*) FROM students")->fetchColumn();
    }

    public static function getUnpaidFees($studentId) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM fees WHERE student_id = ? AND status IN ('unpaid', 'partial', 'overdue')");
        $stmt->execute([$studentId]);
        return $stmt->fetchColumn();
    }

    public static function getPendingComplaints($studentId) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status != 'resolved'");
        $stmt->execute([$studentId]);
        return $stmt->fetchColumn();
    }

    public static function getTotalFeesDue($studentId) {
        $stmt = db()->prepare("SELECT SUM(GREATEST(amount - COALESCE(paid_amount, 0), 0)) FROM fees WHERE student_id = ? AND status IN ('unpaid', 'partial', 'overdue')");
        $stmt->execute([$studentId]);
        return $stmt->fetchColumn() ?? 0;
    }
}
?>