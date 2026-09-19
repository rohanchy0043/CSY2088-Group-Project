<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

class User {
    public static function find($id) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByEmail($email) {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function findByUsername($username) {
        $stmt = db()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $stmt = db()->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone, account_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? ROLE_STUDENT,
            $data['full_name'],
            $data['phone'] ?? '',
            $data['account_status'] ?? 'approved'
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
        $stmt = db()->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete($id) {
        $stmt = db()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all() {
        $stmt = db()->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function count() {
        return db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public static function countByRole($role) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchColumn();
    }
}
?>