-- Smart Rule-Based Hostel Allocation System
-- Database Schema Creation Script
-- MySQL 8+

CREATE DATABASE IF NOT EXISTS hostel_allocation;
USE hostel_allocation;

-- =============================================
-- Table: students
-- =============================================
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    level INT NOT NULL CHECK (level IN (100, 200, 300, 400)),
    medical_status BOOLEAN NOT NULL DEFAULT 0,
    allocation_status ENUM('not_requested', 'pending', 'allocated', 'waitlisted') NOT NULL DEFAULT 'not_requested',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: hostels
-- =============================================
CREATE TABLE IF NOT EXISTS hostels (
    hostel_id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_name VARCHAR(100) NOT NULL,
    gender_category ENUM('Male', 'Female') NOT NULL,
    total_rooms INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: rooms
-- =============================================
CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    occupied_slots INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(hostel_id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_in_hostel (hostel_id, room_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: allocations
-- =============================================
CREATE TABLE IF NOT EXISTS allocations (
    allocation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    date_allocated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_allocation (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: feedback
-- =============================================
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('bug', 'error', 'suggestion', 'other') NOT NULL DEFAULT 'other',
    status ENUM('pending', 'acknowledged', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
    admin_response TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Insert default admin account
-- Password: admin123 (hashed with password_hash)
-- =============================================
INSERT INTO students (fullname, email, password, gender, level, medical_status, allocation_status)
VALUES ('System Administrator', 'admin@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 400, 0, 'not_requested');

-- =============================================
-- Sample Hostels
-- =============================================
INSERT INTO hostels (hostel_name, gender_category, total_rooms) VALUES
('Independence Hall', 'Male', 0),
('Queen Elizabeth Hall', 'Female', 0),
('Alexander Brown Hall', 'Male', 0),
('Moremi Hall', 'Female', 0);

-- =============================================
-- Sample Rooms (will update total_rooms via trigger or manually)
-- =============================================
INSERT INTO rooms (hostel_id, room_number, capacity) VALUES
-- Independence Hall (hostel_id = 1)
(1, 'A101', 4),
(1, 'A102', 4),
(1, 'A103', 2),
(1, 'B101', 4),
(1, 'B102', 2),
-- Queen Elizabeth Hall (hostel_id = 2)
(2, 'A101', 4),
(2, 'A102', 4),
(2, 'A103', 2),
(2, 'B101', 4),
(2, 'B102', 2),
-- Alexander Brown Hall (hostel_id = 3)
(3, 'A101', 4),
(3, 'A102', 2),
(3, 'A103', 2),
-- Moremi Hall (hostel_id = 4)
(4, 'A101', 4),
(4, 'A102', 4),
(4, 'A103', 2);

-- Update total_rooms counts
UPDATE hostels SET total_rooms = (SELECT COUNT(*) FROM rooms WHERE rooms.hostel_id = hostels.hostel_id);

-- =============================================
-- Sample Students (password for all: password123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =============================================
INSERT INTO students (fullname, email, password, gender, level, medical_status, allocation_status) VALUES
('John Adebayo', 'john@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 400, 1, 'pending'),
('Mary Okonkwo', 'mary@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 300, 0, 'pending'),
('David Chukwu', 'david@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 200, 0, 'pending'),
('Grace Emeka', 'grace@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 400, 1, 'pending'),
('Samuel Ojo', 'samuel@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 100, 0, 'not_requested');
