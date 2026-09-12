<?php
// Database configuration
$host = 'mysql';
$dbname = 'SmartHostel';
$user = 'student';
$pass = 'student';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("CREATE TABLE IF NOT EXISTS fee_structures (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, monthly_amount DECIMAL(10,2) NOT NULL, due_day TINYINT UNSIGNED NOT NULL, effective_from DATE NOT NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL)");
    $feeColumns = $pdo->query("SHOW COLUMNS FROM fees")->fetchAll(PDO::FETCH_COLUMN);
    $pdo->exec("ALTER TABLE fees MODIFY status ENUM('paid','partial','unpaid','overdue') NOT NULL DEFAULT 'unpaid'");
    if (!in_array('fee_month', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD fee_month DATE NULL AFTER due_date");
    if (!in_array('paid_amount', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER amount");
    if (!in_array('payment_type', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD payment_type ENUM('monthly','multiple_months','full_year') NOT NULL DEFAULT 'monthly' AFTER status");
    if (!in_array('paid_at', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD paid_at DATETIME NULL");
    if (!in_array('payment_method', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD payment_method VARCHAR(50) NULL");
    if (!in_array('transaction_id', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD transaction_id VARCHAR(100) NULL");
    if (!in_array('notes', $feeColumns, true)) $pdo->exec("ALTER TABLE fees ADD notes TEXT NULL");
    $pdo->exec("CREATE TABLE IF NOT EXISTS fee_payments (id INT AUTO_INCREMENT PRIMARY KEY, fee_id INT NOT NULL, student_id INT NOT NULL, amount DECIMAL(10,2) NOT NULL, payment_date DATE NOT NULL, payment_method VARCHAR(50) NOT NULL DEFAULT 'cash', receipt_number VARCHAR(100) NULL, notes TEXT, recorded_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE, FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE, FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL)");
    $paymentColumns = $pdo->query("SHOW COLUMNS FROM fee_payments")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('receipt_number', $paymentColumns, true)) $pdo->exec("ALTER TABLE fee_payments ADD receipt_number VARCHAR(100) NULL");
    if (!in_array('notes', $paymentColumns, true)) $pdo->exec("ALTER TABLE fee_payments ADD notes TEXT NULL");
    if (!in_array('recorded_by', $paymentColumns, true)) $pdo->exec("ALTER TABLE fee_payments ADD recorded_by INT NULL");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to get database connection
function db() {
    global $pdo;
    return $pdo;
}
?>