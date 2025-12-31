-- Bimbel Al-Muhajirin Database Setup Script
-- This script sets up the complete database schema for the bimbel management system
-- with proper indexes, foreign keys, and initial data seeding

-- Use the existing database
USE masjid_bimbel;

-- ============================================================================
-- BIMBEL-SPECIFIC DATA SEEDING
-- ============================================================================

-- Insert bimbel-specific users (Admin Bimbel and additional roles)
INSERT IGNORE INTO users (username, password, full_name, role, status) VALUES 
('admin_bimbel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Bimbel Al-Muhajirin', 'admin_bimbel', 'active'),
('viewer_bimbel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Bimbel', 'viewer', 'active');

-- Insert bimbel-specific settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
-- Bimbel Information
('bimbel_name', 'Bimbel Al-Muhajirin', 'text', 'Nama resmi bimbel'),
('bimbel_tagline', 'Pendidikan Berkualitas dengan Nilai-Nilai Islam', 'text', 'Tagline bimbel'),
('bimbel_address', 'Kompleks Masjid Jami Al-Muhajirin, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara', 'textarea', 'Alamat lengkap bimbel'),
('bimbel_phone', '021-88888888', 'text', 'Nomor telepon bimbel'),
('bimbel_whatsapp', '62895602416781', 'text', 'Nomor WhatsApp bimbel'),
('bimbel_email', 'bimbel@almuhajirin.com', 'text', 'Email bimbel'),

-- Academic Settings
('academic_year', '2024/2025', 'text', 'Tahun ajaran aktif'),
('semester_active', '1', 'text', 'Semester aktif (1 atau 2)'),
('registration_fee', '50000', 'text', 'Biaya pendaftaran (Rupiah)'),

-- Fee Structure
('fee_sd', '200000', 'text', 'SPP bulanan SD (Rupiah)'),
('fee_smp', '300000', 'text', 'SPP bulanan SMP (Rupiah)'),
('fee_sma', '400000', 'text', 'SPP bulanan SMA (Rupiah)'),

-- Class Settings
('max_students_per_class', '10', 'text', 'Maksimal siswa per kelas'),
('class_duration_minutes', '120', 'text', 'Durasi kelas dalam menit'),

-- Mentor Settings
('mentor_rate_sd', '75000', 'text', 'Tarif mentor per kehadiran SD (Rupiah)'),
('mentor_rate_smp', '100000', 'text', 'Tarif mentor per kehadiran SMP (Rupiah)'),
('mentor_rate_sma', '125000', 'text', 'Tarif mentor per kehadiran SMA (Rupiah)'),

-- Operational Settings
('late_payment_penalty', '10000', 'text', 'Denda keterlambatan pembayaran (Rupiah)'),
('attendance_minimum_percentage', '75', 'text', 'Persentase kehadiran minimum (%)'),
('report_generation_day', '1', 'text', 'Tanggal generate laporan bulanan'),

-- System Settings
('auto_generate_student_number', '1', 'text', 'Auto generate nomor siswa (1=ya, 0=tidak)'),
('student_number_prefix', 'ALM', 'text', 'Prefix nomor siswa'),
('auto_generate_mentor_code', '1', 'text', 'Auto generate kode mentor (1=ya, 0=tidak)'),
('mentor_code_prefix', 'MNT', 'text', 'Prefix kode mentor')

ON DUPLICATE KEY UPDATE 
setting_value = VALUES(setting_value),
description = VALUES(description);

-- ============================================================================
-- SAMPLE DATA FOR TESTING AND DEMONSTRATION
-- ============================================================================

-- Insert sample students for each level
INSERT IGNORE INTO students (student_number, full_name, level, class, parent_name, parent_phone, address, registration_date, monthly_fee, status) VALUES
-- SD Students
('ALM2024001', 'Ahmad Fauzi', 'SD', '1', 'Bapak Hasan', '081234567001', 'Jl. Mawar No. 1, Bekasi Utara', '2024-01-15', 200000, 'active'),
('ALM2024002', 'Siti Aisyah', 'SD', '2', 'Ibu Fatimah', '081234567002', 'Jl. Melati No. 2, Bekasi Utara', '2024-01-16', 200000, 'active'),
('ALM2024003', 'Muhammad Rizki', 'SD', '3', 'Bapak Ahmad', '081234567003', 'Jl. Anggrek No. 3, Bekasi Utara', '2024-01-17', 200000, 'active'),
('ALM2024004', 'Fatimah Zahra', 'SD', '4', 'Ibu Khadijah', '081234567004', 'Jl. Dahlia No. 4, Bekasi Utara', '2024-01-18', 200000, 'active'),
('ALM2024005', 'Ali Akbar', 'SD', '5', 'Bapak Umar', '081234567005', 'Jl. Tulip No. 5, Bekasi Utara', '2024-01-19', 200000, 'active'),
('ALM2024006', 'Khadijah Nur', 'SD', '6', 'Ibu Aisyah', '081234567006', 'Jl. Sakura No. 6, Bekasi Utara', '2024-01-20', 200000, 'active'),

-- SMP Students
('ALM2024007', 'Abdullah Rahman', 'SMP', '7', 'Bapak Rahman', '081234567007', 'Jl. Kenanga No. 7, Bekasi Utara', '2024-01-21', 300000, 'active'),
('ALM2024008', 'Maryam Salsabila', 'SMP', '8', 'Ibu Salsabila', '081234567008', 'Jl. Cempaka No. 8, Bekasi Utara', '2024-01-22', 300000, 'active'),
('ALM2024009', 'Usman Hakim', 'SMP', '9', 'Bapak Hakim', '081234567009', 'Jl. Flamboyan No. 9, Bekasi Utara', '2024-01-23', 300000, 'active'),
('ALM2024010', 'Zainab Husna', 'SMP', '7', 'Ibu Husna', '081234567010', 'Jl. Bougenville No. 10, Bekasi Utara', '2024-01-24', 300000, 'active'),
('ALM2024011', 'Ibrahim Khalil', 'SMP', '8', 'Bapak Khalil', '081234567011', 'Jl. Kamboja No. 11, Bekasi Utara', '2024-01-25', 300000, 'active'),
('ALM2024012', 'Aminah Zahra', 'SMP', '9', 'Ibu Zahra', '081234567012', 'Jl. Teratai No. 12, Bekasi Utara', '2024-01-26', 300000, 'active'),

-- SMA Students
('ALM2024013', 'Muhammad Yusuf', 'SMA', '10', 'Bapak Yusuf', '081234567013', 'Jl. Seroja No. 13, Bekasi Utara', '2024-01-27', 400000, 'active'),
('ALM2024014', 'Hafsah Qurrata', 'SMA', '11', 'Ibu Qurrata', '081234567014', 'Jl. Gardenia No. 14, Bekasi Utara', '2024-01-28', 400000, 'active'),
('ALM2024015', 'Khalid Walid', 'SMA', '12', 'Bapak Walid', '081234567015', 'Jl. Azalea No. 15, Bekasi Utara', '2024-01-29', 400000, 'active'),
('ALM2024016', 'Ruqayyah Sakinah', 'SMA', '10', 'Ibu Sakinah', '081234567016', 'Jl. Peony No. 16, Bekasi Utara', '2024-01-30', 400000, 'active'),
('ALM2024017', 'Hamzah Malik', 'SMA', '11', 'Bapak Malik', '081234567017', 'Jl. Iris No. 17, Bekasi Utara', '2024-01-31', 400000, 'active'),
('ALM2024018', 'Ummu Salamah', 'SMA', '12', 'Ibu Salamah', '081234567018', 'Jl. Lavender No. 18, Bekasi Utara', '2024-02-01', 400000, 'active');

-- Insert sample mentors
INSERT IGNORE INTO mentors (mentor_code, full_name, phone, email, address, teaching_levels, hourly_rate, join_date, status) VALUES
('MNT001', 'Ustadz Ahmad Fauzi, S.Pd', '081234560001', 'ahmad.fauzi@almuhajirin.com', 'Jl. Pendidikan No. 1, Bekasi', '["SD", "SMP"]', 75000, '2023-07-01', 'active'),
('MNT002', 'Ustadzah Siti Khadijah, S.Si', '081234560002', 'siti.khadijah@almuhajirin.com', 'Jl. Ilmu No. 2, Bekasi', '["SMP", "SMA"]', 100000, '2023-07-01', 'active'),
('MNT003', 'Ustadz Muhammad Ridwan, S.Mat', '081234560003', 'muhammad.ridwan@almuhajirin.com', 'Jl. Matematika No. 3, Bekasi', '["SMA"]', 125000, '2023-08-01', 'active'),
('MNT004', 'Ustadzah Fatimah Azzahra, S.Pd', '081234560004', 'fatimah.azzahra@almuhajirin.com', 'Jl. Bahasa No. 4, Bekasi', '["SD", "SMP", "SMA"]', 100000, '2023-08-15', 'active'),
('MNT005', 'Ustadz Ali Imran, S.Si', '081234560005', 'ali.imran@almuhajirin.com', 'Jl. Sains No. 5, Bekasi', '["SMP", "SMA"]', 110000, '2023-09-01', 'active'),
('MNT006', 'Ustadzah Maryam Qibtiyah, S.Pd', '081234560006', 'maryam.qibtiyah@almuhajirin.com', 'Jl. Sejarah No. 6, Bekasi', '["SD", "SMP"]', 85000, '2023-09-15', 'active'),
('MNT007', 'Ustadz Umar Faruq, S.Kom', '081234560007', 'umar.faruq@almuhajirin.com', 'Jl. Teknologi No. 7, Bekasi', '["SMA"]', 120000, '2023-10-01', 'active'),
('MNT008', 'Ustadzah Aisyah Radhiyallahu, S.Pd', '081234560008', 'aisyah.radhiyallahu@almuhajirin.com', 'Jl. Pendidikan No. 8, Bekasi', '["SD"]', 70000, '2023-10-15', 'active');

-- Insert sample attendance data for current month
-- Get current date components
SET @current_year = YEAR(CURDATE());
SET @current_month = MONTH(CURDATE());
SET @admin_bimbel_id = (SELECT id FROM users WHERE role = 'admin_bimbel' LIMIT 1);

-- Sample student attendance for the first 15 days of current month
INSERT IGNORE INTO student_attendance (student_id, attendance_date, status, recorded_by) 
SELECT 
    s.id,
    DATE(CONCAT(@current_year, '-', LPAD(@current_month, 2, '0'), '-', LPAD(day_num, 2, '0'))),
    CASE 
        WHEN RAND() < 0.85 THEN 'present'
        WHEN RAND() < 0.95 THEN 'absent'
        ELSE 'sick'
    END,
    @admin_bimbel_id
FROM students s
CROSS JOIN (
    SELECT 1 as day_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
) days
WHERE s.status = 'active'
AND DATE(CONCAT(@current_year, '-', LPAD(@current_month, 2, '0'), '-', LPAD(day_num, 2, '0'))) <= CURDATE();

-- Sample mentor attendance for the first 15 days of current month
INSERT IGNORE INTO mentor_attendance (mentor_id, attendance_date, level, status, hours_taught, recorded_by)
SELECT 
    m.id,
    DATE(CONCAT(@current_year, '-', LPAD(@current_month, 2, '0'), '-', LPAD(day_num, 2, '0'))),
    level_taught.level,
    CASE 
        WHEN RAND() < 0.90 THEN 'present'
        ELSE 'absent'
    END,
    CASE 
        WHEN RAND() < 0.90 THEN 2.0
        ELSE 0
    END,
    @admin_bimbel_id
FROM mentors m
CROSS JOIN (
    SELECT 1 as day_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
) days
CROSS JOIN (
    SELECT 'SD' as level UNION SELECT 'SMP' UNION SELECT 'SMA'
) level_taught
WHERE m.status = 'active'
AND JSON_CONTAINS(m.teaching_levels, JSON_QUOTE(level_taught.level))
AND DATE(CONCAT(@current_year, '-', LPAD(@current_month, 2, '0'), '-', LPAD(day_num, 2, '0'))) <= CURDATE()
AND DAYOFWEEK(DATE(CONCAT(@current_year, '-', LPAD(@current_month, 2, '0'), '-', LPAD(day_num, 2, '0')))) BETWEEN 2 AND 6; -- Monday to Friday

-- Insert sample SPP payments for previous month
SET @prev_month = IF(@current_month = 1, 12, @current_month - 1);
SET @prev_year = IF(@current_month = 1, @current_year - 1, @current_year);

INSERT IGNORE INTO spp_payments (student_id, payment_month, payment_year, amount, payment_date, payment_method, recorded_by)
SELECT 
    id,
    @prev_month,
    @prev_year,
    monthly_fee,
    DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY),
    CASE 
        WHEN RAND() < 0.7 THEN 'cash'
        WHEN RAND() < 0.9 THEN 'transfer'
        ELSE 'other'
    END,
    @admin_bimbel_id
FROM students 
WHERE status = 'active' 
AND RAND() < 0.85; -- 85% of students have paid

-- Insert sample financial transactions
INSERT IGNORE INTO financial_transactions (transaction_type, category, amount, description, transaction_date, reference_id, recorded_by) VALUES
-- Income transactions
('income', 'spp', 200000, 'Pembayaran SPP Ahmad Fauzi - Januari 2024', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 1, @admin_bimbel_id),
('income', 'spp', 300000, 'Pembayaran SPP Abdullah Rahman - Januari 2024', DATE_SUB(CURDATE(), INTERVAL 14 DAY), 7, @admin_bimbel_id),
('income', 'spp', 400000, 'Pembayaran SPP Muhammad Yusuf - Januari 2024', DATE_SUB(CURDATE(), INTERVAL 13 DAY), 13, @admin_bimbel_id),
('income', 'registration', 50000, 'Biaya pendaftaran siswa baru', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, @admin_bimbel_id),
('income', 'registration', 50000, 'Biaya pendaftaran siswa baru', DATE_SUB(CURDATE(), INTERVAL 8 DAY), NULL, @admin_bimbel_id),

-- Expense transactions
('expense', 'mentor_payment', 750000, 'Honor mentor Ustadz Ahmad Fauzi - Januari 2024', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1, @admin_bimbel_id),
('expense', 'mentor_payment', 1000000, 'Honor mentor Ustadzah Siti Khadijah - Januari 2024', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2, @admin_bimbel_id),
('expense', 'operational', 200000, 'Pembelian alat tulis dan kertas', DATE_SUB(CURDATE(), INTERVAL 7 DAY), NULL, @admin_bimbel_id),
('expense', 'utilities', 150000, 'Listrik dan air bulan Januari', DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, @admin_bimbel_id),
('expense', 'operational', 100000, 'Fotokopi materi pembelajaran', DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL, @admin_bimbel_id);

-- ============================================================================
-- CREATE ADDITIONAL INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional indexes for better query performance (MySQL compatible syntax)
ALTER TABLE students ADD INDEX idx_students_level_status (level, status);
ALTER TABLE students ADD INDEX idx_students_registration_date (registration_date);
ALTER TABLE mentors ADD INDEX idx_mentors_teaching_levels (teaching_levels(100));
ALTER TABLE student_attendance ADD INDEX idx_student_attendance_date_status (attendance_date, status);
ALTER TABLE mentor_attendance ADD INDEX idx_mentor_attendance_date_level (attendance_date, level);
ALTER TABLE spp_payments ADD INDEX idx_spp_payments_month_year (payment_month, payment_year);
ALTER TABLE financial_transactions ADD INDEX idx_financial_transactions_date_type (transaction_date, transaction_type);
ALTER TABLE monthly_recap ADD INDEX idx_monthly_recap_year_month (recap_year, recap_month);

-- ============================================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View for active students with payment status
CREATE OR REPLACE VIEW v_students_payment_status AS
SELECT 
    s.id,
    s.student_number,
    s.full_name,
    s.level,
    s.class,
    s.parent_name,
    s.parent_phone,
    s.monthly_fee,
    s.status,
    COALESCE(sp.payment_date, NULL) as last_payment_date,
    CASE 
        WHEN sp.payment_date IS NULL THEN 'Belum Bayar'
        WHEN sp.payment_month = MONTH(CURDATE()) AND sp.payment_year = YEAR(CURDATE()) THEN 'Lunas'
        ELSE 'Tunggakan'
    END as payment_status
FROM students s
LEFT JOIN spp_payments sp ON s.id = sp.student_id 
    AND sp.payment_month = MONTH(CURDATE()) 
    AND sp.payment_year = YEAR(CURDATE())
WHERE s.status = 'active';

-- View for mentor performance
CREATE OR REPLACE VIEW v_mentor_performance AS
SELECT 
    m.id,
    m.mentor_code,
    m.full_name,
    m.teaching_levels,
    m.hourly_rate,
    COUNT(ma.id) as total_attendance,
    SUM(ma.hours_taught) as total_hours,
    SUM(ma.hours_taught * m.hourly_rate) as total_earnings,
    ROUND(AVG(CASE WHEN ma.status = 'present' THEN 1 ELSE 0 END) * 100, 2) as attendance_rate
FROM mentors m
LEFT JOIN mentor_attendance ma ON m.id = ma.mentor_id 
    AND ma.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
WHERE m.status = 'active'
GROUP BY m.id, m.mentor_code, m.full_name, m.teaching_levels, m.hourly_rate;

-- View for monthly financial summary
CREATE OR REPLACE VIEW v_monthly_financial_summary AS
SELECT 
    YEAR(transaction_date) as year,
    MONTH(transaction_date) as month,
    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense,
    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as net_balance,
    SUM(CASE WHEN transaction_type = 'income' AND category = 'spp' THEN amount ELSE 0 END) as spp_income,
    SUM(CASE WHEN transaction_type = 'income' AND category = 'registration' THEN amount ELSE 0 END) as registration_income,
    SUM(CASE WHEN transaction_type = 'expense' AND category = 'mentor_payment' THEN amount ELSE 0 END) as mentor_expense,
    SUM(CASE WHEN transaction_type = 'expense' AND category = 'operational' THEN amount ELSE 0 END) as operational_expense
FROM financial_transactions
GROUP BY YEAR(transaction_date), MONTH(transaction_date)
ORDER BY year DESC, month DESC;

-- ============================================================================
-- CREATE STORED PROCEDURES FOR COMMON OPERATIONS
-- ============================================================================

-- Procedure to generate student number
DROP PROCEDURE IF EXISTS GenerateStudentNumber;
CREATE PROCEDURE GenerateStudentNumber(OUT new_number VARCHAR(20))
BEGIN
    DECLARE next_id INT;
    DECLARE prefix VARCHAR(10);
    
    SELECT setting_value INTO prefix FROM settings WHERE setting_key = 'student_number_prefix';
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(student_number, LENGTH(prefix) + 1) AS UNSIGNED)), 0) + 1 
    INTO next_id 
    FROM students 
    WHERE student_number LIKE CONCAT(prefix, '%');
    
    SET new_number = CONCAT(prefix, YEAR(CURDATE()), LPAD(next_id, 3, '0'));
END;

-- Procedure to generate mentor code
DROP PROCEDURE IF EXISTS GenerateMentorCode;
CREATE PROCEDURE GenerateMentorCode(OUT new_code VARCHAR(20))
BEGIN
    DECLARE next_id INT;
    DECLARE prefix VARCHAR(10);
    
    SELECT setting_value INTO prefix FROM settings WHERE setting_key = 'mentor_code_prefix';
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(mentor_code, LENGTH(prefix) + 1) AS UNSIGNED)), 0) + 1 
    INTO next_id 
    FROM mentors 
    WHERE mentor_code LIKE CONCAT(prefix, '%');
    
    SET new_code = CONCAT(prefix, LPAD(next_id, 3, '0'));
END;

-- Procedure to calculate monthly mentor payment
DROP PROCEDURE IF EXISTS CalculateMentorPayment;
CREATE PROCEDURE CalculateMentorPayment(
    IN mentor_id INT, 
    IN payment_month INT, 
    IN payment_year INT,
    OUT total_payment DECIMAL(10,2)
)
BEGIN
    SELECT SUM(ma.hours_taught * m.hourly_rate)
    INTO total_payment
    FROM mentor_attendance ma
    JOIN mentors m ON ma.mentor_id = m.id
    WHERE ma.mentor_id = mentor_id
    AND MONTH(ma.attendance_date) = payment_month
    AND YEAR(ma.attendance_date) = payment_year
    AND ma.status = 'present';
    
    SET total_payment = COALESCE(total_payment, 0);
END;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Show table counts
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'mentors', COUNT(*) FROM mentors
UNION ALL
SELECT 'student_attendance', COUNT(*) FROM student_attendance
UNION ALL
SELECT 'mentor_attendance', COUNT(*) FROM mentor_attendance
UNION ALL
SELECT 'spp_payments', COUNT(*) FROM spp_payments
UNION ALL
SELECT 'financial_transactions', COUNT(*) FROM financial_transactions
UNION ALL
SELECT 'settings', COUNT(*) FROM settings;