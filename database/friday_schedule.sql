-- Friday Prayer Schedule Database Tables
-- Tabel untuk mengelola jadwal sholat Jumat, imam, khotib, dan tema khutbah

-- Tabel untuk jadwal sholat Jumat
CREATE TABLE IF NOT EXISTS friday_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    friday_date DATE NOT NULL,
    prayer_time TIME NOT NULL DEFAULT '12:00:00',
    imam_name VARCHAR(255) NOT NULL,
    khotib_name VARCHAR(255) NOT NULL,
    khutbah_theme VARCHAR(500) NOT NULL,
    khutbah_description TEXT,
    location VARCHAR(255) DEFAULT 'Masjid Jami Al-Muhajirin',
    special_notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    UNIQUE KEY unique_friday_date (friday_date),
    INDEX idx_friday_date (friday_date),
    INDEX idx_status (status)
);

-- Tabel untuk daftar imam dan khotib
CREATE TABLE IF NOT EXISTS friday_speakers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role ENUM('imam', 'khotib', 'both') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    bio TEXT,
    photo VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Tabel untuk tema khutbah yang sering digunakan
CREATE TABLE IF NOT EXISTS khutbah_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_title VARCHAR(500) NOT NULL,
    theme_category VARCHAR(100),
    description TEXT,
    keywords VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (theme_category),
    INDEX idx_active (is_active)
);

-- Insert sample data untuk imam dan khotib
INSERT INTO friday_speakers (name, role, phone, email, bio) VALUES
('Ustadz Ahmad Fauzi', 'both', '081234567890', 'ahmad.fauzi@email.com', 'Imam dan khotib tetap masjid dengan pengalaman 15 tahun'),
('Ustadz Muhammad Ridwan', 'khotib', '081234567891', 'ridwan@email.com', 'Khotib tamu dengan spesialisasi kajian Al-Quran'),
('Ustadz Abdullah Salim', 'imam', '081234567892', 'abdullah@email.com', 'Imam muda dengan hafalan 30 juz'),
('Ustadz Yusuf Mansur', 'both', '081234567893', 'yusuf@email.com', 'Da\'i dan motivator Islam'),
('Ustadz Hasan Basri', 'khotib', '081234567894', 'hasan@email.com', 'Pakar hadits dan fiqih');

-- Insert sample themes
INSERT INTO khutbah_themes (theme_title, theme_category, description, keywords) VALUES
('Pentingnya Sholat Berjamaah', 'Ibadah', 'Menjelaskan keutamaan dan hikmah sholat berjamaah dalam Islam', 'sholat, jamaah, ibadah, keutamaan'),
('Akhlak Mulia dalam Kehidupan Sehari-hari', 'Akhlak', 'Pembahasan tentang penerapan akhlak mulia dalam interaksi sosial', 'akhlak, mulia, sosial, kehidupan'),
('Mengelola Harta dengan Bijak Menurut Islam', 'Ekonomi Islam', 'Panduan Islam dalam mengelola rezeki dan kekayaan', 'harta, ekonomi, rezeki, bijak'),
('Pentingnya Menuntut Ilmu', 'Pendidikan', 'Motivasi untuk terus belajar dan menuntut ilmu sepanjang hayat', 'ilmu, pendidikan, belajar, motivasi'),
('Menjaga Persatuan Umat', 'Sosial', 'Pentingnya menjaga persatuan dan kesatuan dalam masyarakat', 'persatuan, umat, sosial, masyarakat'),
('Syukur dan Sabar dalam Menghadapi Cobaan', 'Spiritual', 'Cara bersyukur dan bersabar dalam menghadapi ujian hidup', 'syukur, sabar, cobaan, ujian'),
('Keutamaan Bulan Ramadhan', 'Ramadhan', 'Persiapan dan keutamaan bulan suci Ramadhan', 'ramadhan, puasa, keutamaan, persiapan'),
('Haji dan Umrah: Persiapan Spiritual', 'Haji', 'Persiapan mental dan spiritual untuk ibadah haji dan umrah', 'haji, umrah, spiritual, persiapan');

-- Insert sample Friday schedules untuk 8 minggu ke depan
INSERT INTO friday_schedules (friday_date, prayer_time, imam_name, khotib_name, khutbah_theme, khutbah_description, special_notes) VALUES
(DATE_ADD(CURDATE(), INTERVAL (6 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Ahmad Fauzi', 'Ustadz Ahmad Fauzi', 'Pentingnya Sholat Berjamaah', 'Khutbah akan membahas keutamaan sholat berjamaah dan dampaknya terhadap kehidupan spiritual umat Islam', 'Khutbah dalam bahasa Indonesia dan Arab'),
(DATE_ADD(CURDATE(), INTERVAL (13 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Abdullah Salim', 'Ustadz Muhammad Ridwan', 'Akhlak Mulia dalam Kehidupan Sehari-hari', 'Pembahasan praktis tentang penerapan akhlak mulia dalam berinteraksi dengan sesama', NULL),
(DATE_ADD(CURDATE(), INTERVAL (20 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Ahmad Fauzi', 'Ustadz Yusuf Mansur', 'Mengelola Harta dengan Bijak Menurut Islam', 'Panduan praktis mengelola rezeki dan kekayaan sesuai ajaran Islam', 'Akan ada sesi tanya jawab setelah sholat'),
(DATE_ADD(CURDATE(), INTERVAL (27 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Abdullah Salim', 'Ustadz Hasan Basri', 'Pentingnya Menuntut Ilmu', 'Motivasi untuk terus belajar dan menuntut ilmu dalam segala aspek kehidupan', NULL),
(DATE_ADD(CURDATE(), INTERVAL (34 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Ahmad Fauzi', 'Ustadz Ahmad Fauzi', 'Menjaga Persatuan Umat', 'Pentingnya menjaga persatuan dan kesatuan dalam masyarakat yang beragam', NULL),
(DATE_ADD(CURDATE(), INTERVAL (41 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Abdullah Salim', 'Ustadz Muhammad Ridwan', 'Syukur dan Sabar dalam Menghadapi Cobaan', 'Cara praktis bersyukur dan bersabar dalam menghadapi berbagai ujian hidup', NULL),
(DATE_ADD(CURDATE(), INTERVAL (48 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Ahmad Fauzi', 'Ustadz Yusuf Mansur', 'Keutamaan Bulan Ramadhan', 'Persiapan mental dan spiritual menyambut bulan suci Ramadhan', 'Khutbah khusus persiapan Ramadhan'),
(DATE_ADD(CURDATE(), INTERVAL (55 - WEEKDAY(CURDATE())) DAY), '12:00:00', 'Ustadz Abdullah Salim', 'Ustadz Hasan Basri', 'Haji dan Umrah: Persiapan Spiritual', 'Panduan persiapan mental dan spiritual untuk menunaikan ibadah haji dan umrah', 'Untuk jamaah yang akan berangkat haji');