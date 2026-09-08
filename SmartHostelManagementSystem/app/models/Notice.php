<?php
require_once __DIR__ . '/../../config/database.php';

class Notice {
    public static function ensureTable() {
        db()->exec("CREATE TABLE IF NOT EXISTS notices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            scope ENUM('system','hostel') NOT NULL DEFAULT 'hostel',
            block VARCHAR(10) NULL,
            created_by INT NOT NULL,
            published_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    public static function create($data) {
        self::ensureTable();
        $stmt = db()->prepare('INSERT INTO notices (title, message, scope, block, created_by, published_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['title'], $data['message'], $data['scope'], $data['block'] ?: null, $data['created_by'], $data['published_at'] ?? date('Y-m-d H:i:s')]);
        return db()->lastInsertId();
    }

    public static function all() {
        self::ensureTable();
        return db()->query('SELECT n.*, u.full_name AS author FROM notices n JOIN users u ON n.created_by = u.id ORDER BY n.created_at DESC')->fetchAll();
    }

    public static function forWarden($block) {
        self::ensureTable();
        $stmt = db()->prepare("SELECT n.*, u.full_name AS author FROM notices n JOIN users u ON n.created_by = u.id WHERE n.scope = 'hostel' AND (n.block IS NULL OR n.block = ?) ORDER BY n.created_at DESC");
        $stmt->execute([$block]);
        return $stmt->fetchAll();
    }

    public static function forStudent($block = null) {
        self::ensureTable();
        $stmt = db()->prepare("SELECT * FROM notices WHERE published_at IS NOT NULL AND published_at <= NOW() AND (scope = 'system' OR (scope = 'hostel' AND (block IS NULL OR block = ?))) ORDER BY published_at DESC");
        $stmt->execute([$block]);
        return $stmt->fetchAll();
    }

    public static function find($id) {
        self::ensureTable();
        $stmt = db()->prepare('SELECT * FROM notices WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function update($id, $data) {
        self::ensureTable();
        $stmt = db()->prepare('UPDATE notices SET title = ?, message = ?, scope = ?, block = ?, published_at = ? WHERE id = ?');
        return $stmt->execute([$data['title'], $data['message'], $data['scope'], $data['block'] ?: null, $data['published_at'] ?? null, $id]);
    }

    public static function delete($id) {
        self::ensureTable();
        $stmt = db()->prepare('DELETE FROM notices WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
