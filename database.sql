-- ============================================================
-- Smart Hostel Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS SmartHostel;
USE SmartHostel;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','warden','admin') NOT NULL DEFAULT 'student',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    account_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- STUDENTS TABLE
-- ============================================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    room_id INT NULL,
    parent_contact VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- WARDENS TABLE
-- ============================================================
CREATE TABLE wardens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    assigned_block VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SINGLE HOSTEL PROFILE
-- ============================================================
CREATE TABLE hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    block VARCHAR(10) UNIQUE NOT NULL DEFAULT 'A',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO hostels (name, block, status) VALUES ('Smart Hostel', 'A', 'active');

-- ============================================================
-- WARDEN INVITATIONS
-- ============================================================
CREATE TABLE warden_invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invited_email VARCHAR(100) NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    used_by INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- ROOMS TABLE
-- ============================================================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    block VARCHAR(10) NOT NULL,
    floor INT NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    current_occupancy INT DEFAULT 0,
    status ENUM('available','occupied','maintenance','full') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- ROOM ALLOCATIONS TABLE
-- ============================================================
CREATE TABLE room_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- ============================================================
-- FEES TABLE
-- ============================================================
CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    due_date DATE NOT NULL,
    fee_month DATE NULL,
    status ENUM('paid','partial','unpaid','overdue') DEFAULT 'unpaid',
    payment_type ENUM('monthly','multiple_months','full_year') NOT NULL DEFAULT 'monthly',
    paid_at TIMESTAMP NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE fee_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    due_day TINYINT UNSIGNED NOT NULL,
    effective_from DATE NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_id INT NOT NULL,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cash',
    receipt_number VARCHAR(100) NULL,
    notes TEXT,
    recorded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- FEE CATEGORIES TABLE
-- ============================================================
CREATE TABLE fee_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL
);

-- ============================================================
-- COMPLAINTS TABLE
-- ============================================================
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending','in-progress','resolved') DEFAULT 'pending',
    priority ENUM('low','medium','high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by INT NULL,
    resolution_notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);

-- ============================================================
-- VISITORS TABLE
-- ============================================================
CREATE TABLE visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    visitor_name VARCHAR(100) NOT NULL,
    contact VARCHAR(20),
    purpose VARCHAR(255),
    check_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    check_out TIMESTAMP NULL,
    status ENUM('pending','approved','rejected','checked_in','checked_out') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- MEAL MANAGEMENT TABLES
-- ============================================================
CREATE TABLE meal_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meal_type ENUM('breakfast','lunch','dinner') NOT NULL,
    menu_date DATE NOT NULL,
    items TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY meal_menu_day (meal_type, menu_date),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE meal_attendance (
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
);

CREATE TABLE meal_feedback (
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
);

CREATE TABLE food_complaints (
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
);

-- ============================================================
-- LOGS TABLE
-- ============================================================
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Insert fee categories
INSERT INTO fee_categories (name, description, amount) VALUES
('Hostel Fee', 'Monthly hostel accommodation fee', 5000.00),
('Mess Fee', 'Monthly mess/food charges', 3000.00),
('Electricity', 'Monthly electricity charges', 500.00),
('Maintenance', 'Annual maintenance fee', 1000.00);

-- Remove all user records and dependent demo data
DELETE FROM notifications;
DELETE FROM food_complaints;
DELETE FROM meal_feedback;
DELETE FROM meal_attendance;
DELETE FROM meal_menus;
DELETE FROM visitors;
DELETE FROM complaints;
DELETE FROM fee_payments;
DELETE FROM fees;
DELETE FROM fee_structures;
DELETE FROM room_allocations;
DELETE FROM logs;
DELETE FROM wardens;
DELETE FROM students;
DELETE FROM users;