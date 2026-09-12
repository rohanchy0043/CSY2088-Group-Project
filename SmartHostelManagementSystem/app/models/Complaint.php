<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class Complaint {
    public static function find($id) {
        $stmt = db()->prepare("SELECT c.*, s.id as student_record_id, student_user.full_name, s.student_id, resolver.username as resolved_by_name 
                       FROM complaints c 
                       JOIN students s ON c.student_id = s.id 
                       JOIN users student_user ON s.user_id = student_user.id
                       LEFT JOIN users resolver ON c.resolved_by = resolver.id 
                               WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO complaints (student_id, category, subject, description, priority) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['student_id'],
            $data['category'],
            $data['subject'],
            $data['description'],
            $data['priority'] ?? PRIORITY_MEDIUM
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
        $stmt = db()->prepare("UPDATE complaints SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function resolve($id, $resolvedBy, $notes = '') {
        $stmt = db()->prepare("UPDATE complaints SET status = 'resolved', resolved_at = NOW(), resolved_by = ?, resolution_notes = ? WHERE id = ?");
        return $stmt->execute([$resolvedBy, $notes, $id]);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM complaints WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT c.*, u.full_name, s.student_id 
                             FROM complaints c 
                             JOIN students s ON c.student_id = s.id 
                             JOIN users u ON s.user_id = u.id
                             ORDER BY c.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function forStudent($studentId) {
        $stmt = db()->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function countPending() {
        return db()->query("SELECT COUNT(*) FROM complaints WHERE status IN ('pending', 'in-progress')")->fetchColumn();
    }

    public static function getByStatus($status) {
        $stmt = db()->prepare("SELECT c.*, student_user.full_name FROM complaints c JOIN students s ON c.student_id = s.id JOIN users student_user ON s.user_id = student_user.id WHERE c.status = ? ORDER BY c.created_at DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function getByPriority($priority) {
        $stmt = db()->prepare("SELECT c.*, student_user.full_name FROM complaints c JOIN students s ON c.student_id = s.id JOIN users student_user ON s.user_id = student_user.id WHERE c.priority = ? AND c.status != 'resolved' ORDER BY c.created_at DESC");
        $stmt->execute([$priority]);
        return $stmt->fetchAll();
    }

    public static function getStatistics() {
        return [
            'total' => db()->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
            'pending' => db()->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn(),
            'in_progress' => db()->query("SELECT COUNT(*) FROM complaints WHERE status = 'in-progress'")->fetchColumn(),
            'resolved' => db()->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn()
        ];
    }
}
?>