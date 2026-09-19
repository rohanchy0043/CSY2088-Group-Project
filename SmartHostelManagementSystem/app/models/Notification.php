<?php
require_once __DIR__ . '/../../config/database.php';

class Notification {
    private static function ensureTable() {
        db()->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info',
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    public static function find($id) {
        self::ensureTable();
        $stmt = db()->prepare("SELECT * FROM notifications WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        self::ensureTable();
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
        self::ensureTable();
        $stmt = db()->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function unreadCount($userId) {
        self::ensureTable();
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

    public static function sendToUsers(array $userIds, $title, $message, $type = 'info') {
        self::ensureTable();
        $notificationIds = [];
        foreach (array_unique(array_map('intval', $userIds)) as $userId) {
            if ($userId > 0) {
                $notificationIds[] = self::send($userId, $title, $message, $type);
            }
        }
        return $notificationIds;
    }
}
?>