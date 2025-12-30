-- Database: masjid_bimbel
-- Created for Sistem Informasi Masjid Jami Al-Muhajirin

CREATE DATABASE IF NOT EXISTS masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE masjid_bimbel;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin_masjid', 'admin_bimbel', 'viewer') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Articles table for website content
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    category ENUM('kajian', 'pengumuman', 'kegiatan') NOT NULL,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    author_id INT,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id),
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at)
);

-- Gallery table for photos and videos
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') NOT NULL,
    category ENUM('kegiatan', 'fasilitas', 'kajian') NOT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_sort_order (sort_order)
);

-- Contacts table for contact form submissions
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Settings table for website configuration
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Prayer schedule table
CREATE TABLE prayer_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    fajr TIME NOT NULL,
    sunrise TIME NOT NULL,
    dhuhr TIME NOT NULL,
    asr TIME NOT NULL,
    maghrib TIME NOT NULL,
    isha TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date),
    INDEX idx_date (date)
);

-- Students table for bimbel management
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    level ENUM('SD', 'SMP', 'SMA') NOT NULL,
    class VARCHAR(10) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    registration_date DATE NOT NULL,
    monthly_fee DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_number (student_number),
    INDEX idx_level (level),
    INDEX idx_status (status)
);

-- Mentors table for bimbel management
CREATE TABLE mentors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT NOT NULL,
    teaching_levels JSON NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    join_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mentor_code (mentor_code),
    INDEX idx_status (status)
);

-- Student attendance table
CREATE TABLE student_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'sick', 'permission') NOT NULL,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_student_date (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_status (status)
);

-- Mentor attendance table
CREATE TABLE mentor_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    level ENUM('SD', 'SMP', 'SMA') NOT NULL,
    status ENUM('present', 'absent', 'sick', 'permission') NOT NULL,
    hours_taught DECIMAL(4,2) DEFAULT 0,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES mentors(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_mentor_date_level (mentor_id, attendance_date, level),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_level (level)
);

-- SPP payments table
CREATE TABLE spp_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    payment_month INT NOT NULL,
    payment_year INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'transfer', 'other') DEFAULT 'cash',
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_student_month_year (student_id, payment_month, payment_year),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_month_year (payment_month, payment_year)
);

-- Financial transactions table
CREATE TABLE financial_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_type ENUM('income', 'expense') NOT NULL,
    category ENUM('spp', 'registration', 'operational', 'mentor_payment', 'utilities', 'other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    transaction_date DATE NOT NULL,
    reference_id INT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_category (category),
    INDEX idx_transaction_date (transaction_date)
);

-- Monthly recap table
CREATE TABLE monthly_recap (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recap_month INT NOT NULL,
    recap_year INT NOT NULL,
    opening_balance DECIMAL(12,2) NOT NULL,
    total_income DECIMAL(12,2) NOT NULL,
    total_expense DECIMAL(12,2) NOT NULL,
    closing_balance DECIMAL(12,2) NOT NULL,
    spp_income DECIMAL(12,2) NOT NULL,
    registration_income DECIMAL(12,2) NOT NULL,
    mentor_payment_expense DECIMAL(12,2) NOT NULL,
    operational_expense DECIMAL(12,2) NOT NULL,
    total_students INT NOT NULL,
    total_mentors INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by INT NOT NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    UNIQUE KEY unique_month_year (recap_month, recap_year),
    INDEX idx_recap_month_year (recap_month, recap_year)
);

-- Insert default admin user
INSERT INTO users (username, password, full_name, role, status) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin_masjid', 'active');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Masjid Jami Al-Muhajirin', 'text', 'Nama website'),
('site_description', 'Website resmi Masjid Jami Al-Muhajirin dan Bimbel Al-Muhajirin', 'textarea', 'Deskripsi website'),
('masjid_address', 'Q2X5+P3M, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara, Kota Bekasi', 'textarea', 'Alamat masjid'),
('contact_phone', '021-12345678', 'text', 'Nomor telepon'),
('contact_email', 'info@almuhajirin.com', 'text', 'Email kontak'),
('donation_account', 'Bank Mandiri: 1234567890 a.n. DKM Al-Muhajirin', 'textarea', 'Rekening donasi');