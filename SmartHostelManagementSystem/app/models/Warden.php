<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/User.php';

class Warden {
    public static function find($id) {
        $stmt = db()->prepare("SELECT w.*, u.email, u.username, u.full_name, u.phone 
                               FROM wardens w 
                               JOIN users u ON w.user_id = u.id 
                               WHERE w.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByUserId($userId) {
        $stmt = db()->prepare("SELECT w.*, u.email, u.username, u.full_name, u.phone 
                               FROM wardens w 
                               JOIN users u ON w.user_id = u.id 
                               WHERE w.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO wardens (user_id, assigned_block) VALUES (?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['assigned_block'] ?? ''
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
        $stmt = db()->prepare("UPDATE wardens SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $warden = self::find($id);
        if ($warden) {
            User::delete($warden['user_id']);
        }
        return true;
    }

    public static function all() {
        $stmt = db()->query("SELECT w.*, u.email, u.username, u.full_name, u.phone 
                             FROM wardens w 
                             JOIN users u ON w.user_id = u.id 
                             ORDER BY w.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function count() {
        return db()->query("SELECT COUNT(*) FROM wardens")->fetchColumn();
    }
}
?>