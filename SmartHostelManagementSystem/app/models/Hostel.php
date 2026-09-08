<?php
require_once __DIR__ . '/../../config/database.php';

class Hostel {
    public static function ensureTable() {
        db()->exec("CREATE TABLE IF NOT EXISTS hostels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            block VARCHAR(10) UNIQUE NOT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }

    public static function all() {
        self::ensureTable();
        return db()->query('SELECT h.*, (SELECT COUNT(*) FROM rooms r WHERE r.block = h.block) AS room_count FROM hostels h ORDER BY h.name')->fetchAll();
    }

    public static function find($id) {
        self::ensureTable();
        $stmt = db()->prepare('SELECT * FROM hostels WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        self::ensureTable();
        $stmt = db()->prepare('INSERT INTO hostels (name, block, status) VALUES (?, ?, ?)');
        $stmt->execute([$data['name'], $data['block'], $data['status'] ?? 'active']);
        return db()->lastInsertId();
    }

    public static function update($id, $data) {
        self::ensureTable();
        $stmt = db()->prepare('UPDATE hostels SET name = ?, block = ?, status = ? WHERE id = ?');
        return $stmt->execute([$data['name'], $data['block'], $data['status'], $id]);
    }
}
