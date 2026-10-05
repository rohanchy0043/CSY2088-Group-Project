<?php
require_once __DIR__ . '/../../config/database.php';

class WardenInvitation {
    private static function ensureAssignedBlockColumn() {
        $columns = db()->query('SHOW COLUMNS FROM warden_invitations')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('assigned_block', $columns, true)) {
            db()->exec("ALTER TABLE warden_invitations ADD assigned_block VARCHAR(10) NOT NULL DEFAULT '' AFTER invited_email");
        }
    }

    public static function findValid($code, $email) {
        self::ensureAssignedBlockColumn();
        $stmt = db()->prepare("SELECT * FROM warden_invitations WHERE invited_email = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email]);
        $invitation = $stmt->fetch();
        return $invitation && password_verify($code, $invitation['code_hash']) ? $invitation : null;
    }

    public static function markUsed($id, $userId) {
        $stmt = db()->prepare('UPDATE warden_invitations SET used_at = NOW(), used_by = ? WHERE id = ? AND used_at IS NULL');
        return $stmt->execute([$userId, $id]);
    }

    public static function create($email, $assignedBlock, $code, $expiresAt, $createdBy) {
        self::ensureAssignedBlockColumn();
        $stmt = db()->prepare('INSERT INTO warden_invitations (invited_email, assigned_block, code_hash, expires_at, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$email, trim($assignedBlock), password_hash($code, PASSWORD_DEFAULT), $expiresAt, $createdBy]);
        return $code;
    }

    public static function all() {
        return db()->query("SELECT wi.*, u.full_name AS created_by_name
                            FROM warden_invitations wi
                            LEFT JOIN users u ON wi.created_by = u.id
                            ORDER BY wi.created_at DESC")->fetchAll();
    }
}