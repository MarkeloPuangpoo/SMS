-- ตั้งค่าฐานข้อมูล
SET NAMES utf8mb4;
SET character_set_client = utf8mb4;
SET character_set_connection = utf8mb4;
SET character_set_results = utf8mb4;
SET character_set_database = utf8mb4;
SET GLOBAL character_set_server = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;
SET GLOBAL collation_server = utf8mb4_unicode_ci;

-- ตาราง users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE auth_system;

-- Set proper character encoding
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection = utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- Create tables
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(5) NOT NULL UNIQUE,
    title_prefix VARCHAR(20) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('ชาย', 'หญิง') NOT NULL,
    birth_date DATE,
    citizen_id VARCHAR(13) NOT NULL UNIQUE,
    address TEXT NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    class_level VARCHAR(10),
    class_room VARCHAR(5),
    status ENUM('กำลังศึกษา', 'จบการศึกษา', 'ลาออก', 'พักการเรียน') DEFAULT 'กำลังศึกษา'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO students (student_id, title_prefix, first_name, last_name, gender, birth_date, citizen_id, address, phone, email, class_level, class_room, status) VALUES 
('16530', 'เด็กชาย', 'สมชาย', 'ใจดี', 'ชาย', '2010-01-01', '1234567890001', '123 ถ.สุขุมวิท', '0811111111', 'somchai@example.com', 'ม.1', '1', 'กำลังศึกษา'),
('16531', 'เด็กหญิง', 'สมหญิง', 'รักดี', 'หญิง', '2010-02-02', '1234567890002', '124 ถ.สุขุมวิท', '0822222222', 'somying@example.com', 'ม.1', '1', 'กำลังศึกษา'),
('16532', 'เด็กชาย', 'วิชัย', 'สมบูรณ์', 'ชาย', '2010-03-03', '1234567890003', '125 ถ.สุขุมวิท', '0833333333', 'wichai@example.com', 'ม.1', '2', 'กำลังศึกษา');
