-- Donation System Tables
-- For Masjid Al-Muhajirin Donation Management

USE masjid_bimbel;

-- Donation transactions table
CREATE TABLE donation_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_date DATE NOT NULL,
    category ENUM('operasional', 'pembangunan', 'pendidikan', 'sosial', 'ramadan', 'umum') NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    donor_name VARCHAR(100) NULL,
    donor_phone VARCHAR(20) NULL,
    payment_method ENUM('cash', 'transfer', 'digital', 'other') DEFAULT 'cash',
    reference_number VARCHAR(50) NULL,
    notes TEXT NULL,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verified_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_category (category),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Monthly donation summary table for faster reporting
CREATE TABLE donation_monthly_summary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    year INT NOT NULL,
    month INT NOT NULL,
    category ENUM('operasional', 'pembangunan', 'pendidikan', 'sosial', 'ramadan', 'umum') NOT NULL,
    total_income DECIMAL(15,2) DEFAULT 0,
    total_expense DECIMAL(15,2) DEFAULT 0,
    net_amount DECIMAL(15,2) DEFAULT 0,
    transaction_count INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_month_category (year, month, category),
    INDEX idx_year_month (year, month)
);

-- Insert sample donation data for current month
INSERT INTO donation_transactions (transaction_date, category, type, amount, description, donor_name, payment_method, status, verified_at) VALUES
-- Income transactions
(CURDATE(), 'operasional', 'income', 15500000, 'Donasi operasional masjid bulan ini', 'Jamaah Masjid', 'transfer', 'verified', NOW()),
(CURDATE(), 'pembangunan', 'income', 8200000, 'Donasi pembangunan dan renovasi', 'Jamaah Masjid', 'transfer', 'verified', NOW()),
(CURDATE(), 'pendidikan', 'income', 5800000, 'Donasi untuk program pendidikan', 'Jamaah Masjid', 'cash', 'verified', NOW()),
(CURDATE(), 'sosial', 'income', 4300000, 'Donasi kegiatan sosial', 'Jamaah Masjid', 'digital', 'verified', NOW()),
(CURDATE(), 'umum', 'income', 6700000, 'Infaq umum', 'Jamaah Masjid', 'transfer', 'verified', NOW()),

-- Expense transactions
(CURDATE(), 'operasional', 'expense', 3200000, 'Listrik & Air', NULL, 'transfer', 'verified', NOW()),
(CURDATE(), 'operasional', 'expense', 1800000, 'Kebersihan', NULL, 'cash', 'verified', NOW()),
(CURDATE(), 'pembangunan', 'expense', 12500000, 'Renovasi Atap', NULL, 'transfer', 'verified', NOW()),
(CURDATE(), 'pendidikan', 'expense', 4200000, 'Beasiswa Bimbel', NULL, 'transfer', 'verified', NOW()),
(CURDATE(), 'sosial', 'expense', 3500000, 'Santunan Yatim', NULL, 'cash', 'verified', NOW()),
(CURDATE(), 'operasional', 'expense', 2800000, 'Operasional Lain', NULL, 'transfer', 'verified', NOW());

-- Insert initial monthly summary for current month
INSERT INTO donation_monthly_summary (year, month, category, total_income, total_expense, net_amount, transaction_count)
SELECT 
    YEAR(CURDATE()) as year,
    MONTH(CURDATE()) as month,
    category,
    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as net_amount,
    COUNT(*) as transaction_count
FROM donation_transactions 
WHERE YEAR(transaction_date) = YEAR(CURDATE()) 
    AND MONTH(transaction_date) = MONTH(CURDATE())
    AND status = 'verified'
GROUP BY category;