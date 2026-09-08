-- ============================================================
-- Smart Hostel Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS jobs;
USE jobs;

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
    due_date DATE NOT NULL,
    status ENUM('paid','unpaid','overdue') DEFAULT 'unpaid',
    paid_at TIMESTAMP NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
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

-- Insert users (password = 'password')
INSERT INTO users (username, email, password_hash, role, full_name, phone) VALUES
('admin', 'admin@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', '9800000000'),
('warden1', 'warden1@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warden', 'Hari Gurung', '9800000001'),
('student1', 'student1@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Ram Sharma', '9800000002'),
('student2', 'student2@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Sita Thapa', '9800000003');

-- Insert students
INSERT INTO students (user_id, student_id, room_id, parent_contact, address, emergency_contact) VALUES
(3, 'S001', NULL, '9800000010', 'Kathmandu, Nepal', '9800000011'),
(4, 'S002', NULL, '9800000012', 'Lalitpur, Nepal', '9800000013');

-- Insert wardens
INSERT INTO wardens (user_id, assigned_block) VALUES
(2, 'Block A');

-- Insert rooms
INSERT INTO rooms (room_number, block, floor, capacity, current_occupancy, status) VALUES
('101', 'A', 1, 2, 0, 'available'),
('102', 'A', 1, 2, 0, 'available'),
('103', 'A', 1, 2, 0, 'available'),
('201', 'A', 2, 3, 0, 'available'),
('202', 'A', 2, 3, 0, 'available'),
('301', 'B', 3, 2, 0, 'available'),
('302', 'B', 3, 2, 0, 'available');

-- Insert fees
INSERT INTO fees (student_id, amount, due_date, status) VALUES
(1, 5000.00, '2026-10-01', 'unpaid'),
(1, 3000.00, '2026-10-01', 'unpaid'),
(2, 5000.00, '2026-10-01', 'paid');

-- Insert complaints
INSERT INTO complaints (student_id, category, subject, description, status, priority) VALUES
(1, 'Plumbing', 'Water leakage', 'Pipe burst in the bathroom, water is flooding', 'pending', 'high'),
(2, 'Electrical', 'Light not working', 'Room light is flickering and sometimes goes off', 'in-progress', 'medium');

-- Insert notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(3, 'Welcome!', 'Welcome to DormSync hostel management system.', 'info'),
(4, 'Fee Reminder', 'Your hostel fee is due on 2026-10-01.', 'warning');