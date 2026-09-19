<?php
require_once __DIR__ . '/../../config/database.php';

class Meal {
    public static function ensureTables() {
        db()->exec("CREATE TABLE IF NOT EXISTS meal_menus (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
            menu_date DATE NOT NULL,
            items TEXT NOT NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY meal_menu_day (meal_type, menu_date),
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )");
        db()->exec("CREATE TABLE IF NOT EXISTS meal_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
            status ENUM('present','absent') NOT NULL DEFAULT 'present',
            marked_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY student_meal_day (student_id, attendance_date, meal_type),
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
        )");
        db()->exec("CREATE TABLE IF NOT EXISTS meal_feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
            feedback_date DATE NOT NULL,
            rating TINYINT NOT NULL,
            taste_rating TINYINT NOT NULL DEFAULT 0,
            quality_rating TINYINT NOT NULL DEFAULT 0,
            quantity_rating TINYINT NOT NULL DEFAULT 0,
            hygiene_rating TINYINT NOT NULL DEFAULT 0,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )");
        db()->exec("ALTER TABLE meal_feedback ADD COLUMN IF NOT EXISTS taste_rating TINYINT NOT NULL DEFAULT 0");
        db()->exec("ALTER TABLE meal_feedback ADD COLUMN IF NOT EXISTS quality_rating TINYINT NOT NULL DEFAULT 0");
        db()->exec("ALTER TABLE meal_feedback ADD COLUMN IF NOT EXISTS quantity_rating TINYINT NOT NULL DEFAULT 0");
        db()->exec("ALTER TABLE meal_feedback ADD COLUMN IF NOT EXISTS hygiene_rating TINYINT NOT NULL DEFAULT 0");
        db()->exec("ALTER TABLE meal_feedback MODIFY taste_rating TINYINT NOT NULL DEFAULT 0, MODIFY quality_rating TINYINT NOT NULL DEFAULT 0, MODIFY quantity_rating TINYINT NOT NULL DEFAULT 0, MODIFY hygiene_rating TINYINT NOT NULL DEFAULT 0");
        db()->exec("CREATE TABLE IF NOT EXISTS food_complaints (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            meal_type ENUM('breakfast','lunch','dinner') NOT NULL DEFAULT 'breakfast',
            category VARCHAR(80) NOT NULL,
            complaint_date DATE NOT NULL,
            subject VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            photo_path VARCHAR(255) NULL,
            status ENUM('pending','in-progress','resolved') NOT NULL DEFAULT 'pending',
            resolution_notes TEXT,
            resolved_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
        )");
        db()->exec("ALTER TABLE food_complaints ADD COLUMN IF NOT EXISTS meal_type ENUM('breakfast','lunch','dinner') NOT NULL DEFAULT 'breakfast'");
        db()->exec("ALTER TABLE food_complaints ADD COLUMN IF NOT EXISTS complaint_date DATE NULL");
        db()->exec("ALTER TABLE food_complaints ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255) NULL");
    }

    public static function menus($from = null) {
        self::ensureTables();
        $from = $from ?: date('Y-m-d');
        $stmt = db()->prepare('SELECT m.*, u.full_name AS author FROM meal_menus m JOIN users u ON m.created_by = u.id WHERE m.menu_date >= ? ORDER BY m.menu_date, FIELD(m.meal_type, "breakfast", "lunch", "dinner")');
        $stmt->execute([$from]);
        return $stmt->fetchAll();
    }

    public static function menu($id) {
        self::ensureTables();
        $stmt = db()->prepare('SELECT * FROM meal_menus WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function saveMenu($data, $userId) {
        self::ensureTables();
        $stmt = db()->prepare('INSERT INTO meal_menus (meal_type, menu_date, items, created_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE items = VALUES(items), created_by = VALUES(created_by)');
        return $stmt->execute([$data['meal_type'], $data['menu_date'], $data['items'], $userId]);
    }

    public static function attendanceForStudent($studentId, $from = null) {
        self::ensureTables();
        $stmt = db()->prepare('SELECT * FROM meal_attendance WHERE student_id = ? AND attendance_date >= ? ORDER BY attendance_date DESC');
        $stmt->execute([$studentId, $from ?: date('Y-m-d', strtotime('-7 days'))]);
        return $stmt->fetchAll();
    }

    public static function attendance() {
        self::ensureTables();
        return db()->query('SELECT a.*, u.full_name, s.student_id FROM meal_attendance a JOIN students s ON a.student_id = s.id JOIN users u ON s.user_id = u.id ORDER BY a.attendance_date DESC, u.full_name')->fetchAll();
    }

    public static function markAttendance($studentId, $date, $mealType, $status, $markedBy) {
        self::ensureTables();
        $stmt = db()->prepare('INSERT INTO meal_attendance (student_id, attendance_date, meal_type, status, marked_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)');
        return $stmt->execute([$studentId, $date, $mealType, $status, $markedBy]);
    }

    public static function feedback($studentId = null) {
        self::ensureTables();
        $sql = 'SELECT f.*, u.full_name FROM meal_feedback f JOIN students s ON f.student_id = s.id JOIN users u ON s.user_id = u.id';
        $params = [];
        if ($studentId) { $sql .= ' WHERE f.student_id = ?'; $params[] = $studentId; }
        $sql .= ' ORDER BY f.created_at DESC';
        $stmt = db()->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function addFeedback($data, $studentId) {
        self::ensureTables();
        $stmt = db()->prepare('INSERT INTO meal_feedback (student_id, meal_type, feedback_date, rating, taste_rating, quality_rating, quantity_rating, hygiene_rating, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$studentId, $data['meal_type'], $data['feedback_date'], $data['rating'], $data['taste_rating'], $data['quality_rating'], $data['quantity_rating'], $data['hygiene_rating'], $data['comment'] ?? '']);
    }

    public static function complaints($studentId = null) {
        self::ensureTables();
        $sql = 'SELECT c.*, u.full_name FROM food_complaints c JOIN students s ON c.student_id = s.id JOIN users u ON s.user_id = u.id';
        $params = [];
        if ($studentId) { $sql .= ' WHERE c.student_id = ?'; $params[] = $studentId; }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = db()->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function addComplaint($data, $studentId) {
        self::ensureTables();
        $stmt = db()->prepare('INSERT INTO food_complaints (student_id, meal_type, category, complaint_date, subject, description, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$studentId, $data['meal_type'], $data['category'], $data['complaint_date'], $data['subject'], $data['description'], $data['photo_path'] ?? null]);
    }

    public static function updateComplaint($id, $status, $notes, $resolvedBy) {
        self::ensureTables();
        $stmt = db()->prepare('UPDATE food_complaints SET status = ?, resolution_notes = ?, resolved_by = ? WHERE id = ?');
        return $stmt->execute([$status, $notes, $status === 'resolved' ? $resolvedBy : null, $id]);
    }

}
