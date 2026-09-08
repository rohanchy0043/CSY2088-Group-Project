<?php
require_once __DIR__ . '/../../config/database.php';

class Notification {
    public static function find($id) {
        $stmt = db()->prepare("SELECT * FROM notifications WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['message'],
            $data['type'] ?? 'info'
        ]);
        return db()->lastInsertId();
    }

    public static function markRead($id) {
        $stmt = db()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function markAllRead($userId) {
        $stmt = db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM notifications WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function forUser($userId) {
        $stmt = db()->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function unreadCount($userId) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public static function send($userId, $title, $message, $type = 'info') {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type
        ]);
    }
}
?>