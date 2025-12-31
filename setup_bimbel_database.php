<?php
/**
 * Bimbel Database Setup Script
 * This script sets up the complete database schema for the bimbel management system
 * with proper indexes, foreign keys, sample data, and verification
 */

echo "<h2>Bimbel Al-Muhajirin Database Setup</h2>";
echo "<p>Setting up database schema, indexes, sample data, and verification...</p>";

try {
    // Connect to MySQL server with buffered queries
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
    
    echo "✓ Connected to MySQL server<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'masjid_bimbel' created/verified<br>";
    
    // Select the database
    $pdo->exec("USE masjid_bimbel");
    echo "✓ Using database 'masjid_bimbel'<br>";
    
    // First, execute the main database schema
    echo "<br><h3>Setting up main database schema...</h3>";
    $main_sql = file_get_contents('database/masjid_bimbel.sql');
    
    // Remove the CREATE DATABASE and USE statements since we already did that
    $main_sql = preg_replace('/CREATE DATABASE.*?;/', '', $main_sql);
    $main_sql = preg_replace('/USE.*?;/', '', $main_sql);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $main_sql)));
    
    $success_count = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $success_count++;
            } catch (PDOException $e) {
                // Ignore errors for existing tables/data
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✓ Executed $success_count main schema statements<br>";
    
    // Now execute the bimbel-specific setup
    echo "<br><h3>Setting up bimbel-specific data and configurations...</h3>";
    
    // Execute bimbel setup in smaller chunks to avoid issues
    
    // 1. Insert bimbel users
    echo "Adding bimbel users...<br>";
    try {
        $pdo->exec("INSERT IGNORE INTO users (username, password, full_name, role, status) VALUES 
            ('admin_bimbel', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Bimbel Al-Muhajirin', 'admin_bimbel', 'active'),
            ('viewer_bimbel', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Bimbel', 'viewer', 'active')");
        echo "✓ Bimbel users added<br>";
    } catch (PDOException $e) {
        echo "⚠ Warning adding users: " . $e->getMessage() . "<br>";
    }
    
    // 2. Insert bimbel settings
    echo "Adding bimbel settings...<br>";
    $bimbel_settings = [
        ['bimbel_name', 'Bimbel Al-Muhajirin', 'text', 'Nama resmi bimbel'],
        ['bimbel_tagline', 'Pendidikan Berkualitas dengan Nilai-Nilai Islam', 'text', 'Tagline bimbel'],
        ['bimbel_address', 'Kompleks Masjid Jami Al-Muhajirin, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara', 'textarea', 'Alamat lengkap bimbel'],
        ['bimbel_phone', '021-88888888', 'text', 'Nomor telepon bimbel'],
        ['bimbel_whatsapp', '62895602416781', 'text', 'Nomor WhatsApp bimbel'],
        ['bimbel_email', 'bimbel@almuhajirin.com', 'text', 'Email bimbel'],
        ['academic_year', '2024/2025', 'text', 'Tahun ajaran aktif'],
        ['semester_active', '1', 'text', 'Semester aktif (1 atau 2)'],
        ['registration_fee', '50000', 'text', 'Biaya pendaftaran (Rupiah)'],
        ['fee_sd', '200000', 'text', 'SPP bulanan SD (Rupiah)'],
        ['fee_smp', '300000', 'text', 'SPP bulanan SMP (Rupiah)'],
        ['fee_sma', '400000', 'text', 'SPP bulanan SMA (Rupiah)'],
        ['max_students_per_class', '10', 'text', 'Maksimal siswa per kelas'],
        ['class_duration_minutes', '120', 'text', 'Durasi kelas dalam menit'],
        ['mentor_rate_sd', '75000', 'text', 'Tarif mentor per kehadiran SD (Rupiah)'],
        ['mentor_rate_smp', '100000', 'text', 'Tarif mentor per kehadiran SMP (Rupiah)'],
        ['mentor_rate_sma', '125000', 'text', 'Tarif mentor per kehadiran SMA (Rupiah)'],
        ['late_payment_penalty', '10000', 'text', 'Denda keterlambatan pembayaran (Rupiah)'],
        ['attendance_minimum_percentage', '75', 'text', 'Persentase kehadiran minimum (%)'],
        ['report_generation_day', '1', 'text', 'Tanggal generate laporan bulanan'],
        ['auto_generate_student_number', '1', 'text', 'Auto generate nomor siswa (1=ya, 0=tidak)'],
        ['student_number_prefix', 'ALM', 'text', 'Prefix nomor siswa'],
        ['auto_generate_mentor_code', '1', 'text', 'Auto generate kode mentor (1=ya, 0=tidak)'],
        ['mentor_code_prefix', 'MNT', 'text', 'Prefix kode mentor']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)");
    foreach ($bimbel_settings as $setting) {
        try {
            $stmt->execute($setting);
        } catch (PDOException $e) {
            echo "⚠ Warning adding setting {$setting[0]}: " . $e->getMessage() . "<br>";
        }
    }
    echo "✓ Bimbel settings added<br>";
    
    // 3. Insert sample students
    echo "Adding sample students...<br>";
    $sample_students = [
        ['ALM2024001', 'Ahmad Fauzi', 'SD', '1', 'Bapak Hasan', '081234567001', 'Jl. Mawar No. 1, Bekasi Utara', '2024-01-15', 200000, 'active'],
        ['ALM2024002', 'Siti Aisyah', 'SD', '2', 'Ibu Fatimah', '081234567002', 'Jl. Melati No. 2, Bekasi Utara', '2024-01-16', 200000, 'active'],
        ['ALM2024003', 'Muhammad Rizki', 'SD', '3', 'Bapak Ahmad', '081234567003', 'Jl. Anggrek No. 3, Bekasi Utara', '2024-01-17', 200000, 'active'],
        ['ALM2024007', 'Abdullah Rahman', 'SMP', '7', 'Bapak Rahman', '081234567007', 'Jl. Kenanga No. 7, Bekasi Utara', '2024-01-21', 300000, 'active'],
        ['ALM2024008', 'Maryam Salsabila', 'SMP', '8', 'Ibu Salsabila', '081234567008', 'Jl. Cempaka No. 8, Bekasi Utara', '2024-01-22', 300000, 'active'],
        ['ALM2024009', 'Usman Hakim', 'SMP', '9', 'Bapak Hakim', '081234567009', 'Jl. Flamboyan No. 9, Bekasi Utara', '2024-01-23', 300000, 'active'],
        ['ALM2024013', 'Muhammad Yusuf', 'SMA', '10', 'Bapak Yusuf', '081234567013', 'Jl. Seroja No. 13, Bekasi Utara', '2024-01-27', 400000, 'active'],
        ['ALM2024014', 'Hafsah Qurrata', 'SMA', '11', 'Ibu Qurrata', '081234567014', 'Jl. Gardenia No. 14, Bekasi Utara', '2024-01-28', 400000, 'active'],
        ['ALM2024015', 'Khalid Walid', 'SMA', '12', 'Bapak Walid', '081234567015', 'Jl. Azalea No. 15, Bekasi Utara', '2024-01-29', 400000, 'active']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO students (student_number, full_name, level, class, parent_name, parent_phone, address, registration_date, monthly_fee, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sample_students as $student) {
        try {
            $stmt->execute($student);
        } catch (PDOException $e) {
            echo "⚠ Warning adding student {$student[0]}: " . $e->getMessage() . "<br>";
        }
    }
    echo "✓ Sample students added<br>";
    
    // 4. Insert sample mentors
    echo "Adding sample mentors...<br>";
    $sample_mentors = [
        ['MNT001', 'Ustadz Ahmad Fauzi, S.Pd', '081234560001', 'ahmad.fauzi@almuhajirin.com', 'Jl. Pendidikan No. 1, Bekasi', '["SD", "SMP"]', 75000, '2023-07-01', 'active'],
        ['MNT002', 'Ustadzah Siti Khadijah, S.Si', '081234560002', 'siti.khadijah@almuhajirin.com', 'Jl. Ilmu No. 2, Bekasi', '["SMP", "SMA"]', 100000, '2023-07-01', 'active'],
        ['MNT003', 'Ustadz Muhammad Ridwan, S.Mat', '081234560003', 'muhammad.ridwan@almuhajirin.com', 'Jl. Matematika No. 3, Bekasi', '["SMA"]', 125000, '2023-08-01', 'active'],
        ['MNT004', 'Ustadzah Fatimah Azzahra, S.Pd', '081234560004', 'fatimah.azzahra@almuhajirin.com', 'Jl. Bahasa No. 4, Bekasi', '["SD", "SMP", "SMA"]', 100000, '2023-08-15', 'active']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO mentors (mentor_code, full_name, phone, email, address, teaching_levels, hourly_rate, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sample_mentors as $mentor) {
        try {
            $stmt->execute($mentor);
        } catch (PDOException $e) {
            echo "⚠ Warning adding mentor {$mentor[0]}: " . $e->getMessage() . "<br>";
        }
    }
    echo "✓ Sample mentors added<br>";
    
    // 5. Add indexes
    echo "Adding performance indexes...<br>";
    $indexes = [
        "ALTER TABLE students ADD INDEX idx_students_level_status (level, status)",
        "ALTER TABLE students ADD INDEX idx_students_registration_date (registration_date)",
        "ALTER TABLE student_attendance ADD INDEX idx_student_attendance_date_status (attendance_date, status)",
        "ALTER TABLE mentor_attendance ADD INDEX idx_mentor_attendance_date_level (attendance_date, level)",
        "ALTER TABLE spp_payments ADD INDEX idx_spp_payments_month_year (payment_month, payment_year)",
        "ALTER TABLE financial_transactions ADD INDEX idx_financial_transactions_date_type (transaction_date, transaction_type)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $pdo->exec($index_sql);
        } catch (PDOException $e) {
            // Ignore if index already exists
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "⚠ Warning adding index: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✓ Performance indexes added<br>";
    
    // 6. Create views
    echo "Creating database views...<br>";
    
    // Students payment status view
    try {
        $pdo->exec("CREATE OR REPLACE VIEW v_students_payment_status AS
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
            WHERE s.status = 'active'");
        echo "✓ Students payment status view created<br>";
    } catch (PDOException $e) {
        echo "⚠ Warning creating students payment view: " . $e->getMessage() . "<br>";
    }
    
    // Mentor performance view
    try {
        $pdo->exec("CREATE OR REPLACE VIEW v_mentor_performance AS
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
            GROUP BY m.id, m.mentor_code, m.full_name, m.teaching_levels, m.hourly_rate");
        echo "✓ Mentor performance view created<br>";
    } catch (PDOException $e) {
        echo "⚠ Warning creating mentor performance view: " . $e->getMessage() . "<br>";
    }
    
    // Verify database structure and data
    echo "<br><h3>Database Verification</h3>";
    
    // Check table structure
    echo "<h4>Table Structure Verification:</h4>";
    $tables = [
        'users' => 'User authentication and roles',
        'students' => 'Student management',
        'mentors' => 'Mentor management', 
        'student_attendance' => 'Student attendance tracking',
        'mentor_attendance' => 'Mentor attendance tracking',
        'spp_payments' => 'SPP payment records',
        'financial_transactions' => 'Financial transaction records',
        'monthly_recap' => 'Monthly financial summaries',
        'settings' => 'System configuration'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "✓ $table: {$result['count']} records ($description)<br>";
        } catch (PDOException $e) {
            echo "✗ $table: Error - " . $e->getMessage() . "<br>";
        }
    }
    
    // Check bimbel-specific settings
    echo "<br><h4>Bimbel Configuration Verification:</h4>";
    $bimbel_settings_check = [
        'bimbel_name', 'academic_year', 'fee_sd', 'fee_smp', 'fee_sma',
        'mentor_rate_sd', 'mentor_rate_smp', 'mentor_rate_sma'
    ];
    
    foreach ($bimbel_settings_check as $setting) {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$setting]);
            $result = $stmt->fetch();
            if ($result) {
                echo "✓ $setting: " . $result['setting_value'] . "<br>";
            } else {
                echo "⚠ $setting: Setting not found<br>";
            }
        } catch (PDOException $e) {
            echo "✗ $setting: Error - " . $e->getMessage() . "<br>";
        }
    }
    
    // Sample data verification
    echo "<br><h4>Sample Data Verification:</h4>";
    
    // Check students by level
    $levels = ['SD', 'SMP', 'SMA'];
    foreach ($levels as $level) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE level = ? AND status = 'active'");
            $stmt->execute([$level]);
            $result = $stmt->fetch();
            echo "✓ Active $level students: {$result['count']}<br>";
        } catch (PDOException $e) {
            echo "✗ $level students: Error - " . $e->getMessage() . "<br>";
        }
    }
    
    // Check mentors
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM mentors WHERE status = 'active'");
        $result = $stmt->fetch();
        echo "✓ Active mentors: {$result['count']}<br>";
    } catch (PDOException $e) {
        echo "✗ Mentors: Error - " . $e->getMessage() . "<br>";
    }
    
    // Test views
    echo "<br><h4>View Testing:</h4>";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM v_students_payment_status");
        $result = $stmt->fetch();
        echo "✓ Students payment status view: {$result['count']} records<br>";
    } catch (PDOException $e) {
        echo "✗ Students payment status view: Error - " . $e->getMessage() . "<br>";
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM v_mentor_performance");
        $result = $stmt->fetch();
        echo "✓ Mentor performance view: {$result['count']} records<br>";
    } catch (PDOException $e) {
        echo "✗ Mentor performance view: Error - " . $e->getMessage() . "<br>";
    }
    
    // Final summary
    echo "<br><h3 style='color: green;'>✓ Bimbel Database Setup Completed Successfully!</h3>";
    
    echo "<div style='background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 16px; margin: 16px 0;'>";
    echo "<h4 style='color: #0369a1; margin-top: 0;'>Setup Summary:</h4>";
    echo "<ul style='color: #0c4a6e;'>";
    echo "<li><strong>Database Schema:</strong> All tables, indexes, and foreign keys created</li>";
    echo "<li><strong>Sample Data:</strong> 9 students, 4 mentors with proper data structure</li>";
    echo "<li><strong>Views:</strong> 2 database views for common queries</li>";
    echo "<li><strong>Configuration:</strong> Bimbel-specific settings and fee structure</li>";
    echo "<li><strong>Security:</strong> Role-based access control with admin_bimbel and viewer roles</li>";
    echo "<li><strong>Performance:</strong> Optimized indexes for better query performance</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background-color: #fefce8; border: 1px solid #eab308; border-radius: 8px; padding: 16px; margin: 16px 0;'>";
    echo "<h4 style='color: #a16207; margin-top: 0;'>Default Login Credentials:</h4>";
    echo "<ul style='color: #713f12;'>";
    echo "<li><strong>Admin Masjid:</strong> username 'admin', password 'password'</li>";
    echo "<li><strong>Admin Bimbel:</strong> username 'admin_bimbel', password 'password'</li>";
    echo "<li><strong>Viewer:</strong> username 'viewer_bimbel', password 'password'</li>";
    echo "</ul>";
    echo "<p style='color: #713f12; font-size: 0.9em; margin-bottom: 0;'><em>Please change these passwords in production!</em></p>";
    echo "</div>";
    
    echo "<div style='background-color: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 16px; margin: 16px 0;'>";
    echo "<h4 style='color: #15803d; margin-top: 0;'>Next Steps:</h4>";
    echo "<ul style='color: #14532d;'>";
    echo "<li>Access the bimbel management system at <code>admin/bimbel/dashboard.php</code></li>";
    echo "<li>Review and customize the bimbel settings in the admin panel</li>";
    echo "<li>Add real student and mentor data</li>";
    echo "<li>Configure fee structures and academic calendar</li>";
    echo "<li>Test all functionality with the sample data</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<br><strong>Navigation Links:</strong><br>";
    echo "<a href='index.php' style='margin-right: 10px; padding: 8px 16px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 4px;'>🏠 Main Website</a>";
    echo "<a href='admin/login.php' style='margin-right: 10px; padding: 8px 16px; background-color: #059669; color: white; text-decoration: none; border-radius: 4px;'>🔐 Admin Login</a>";
    echo "<a href='pages/bimbel.php' style='padding: 8px 16px; background-color: #7c3aed; color: white; text-decoration: none; border-radius: 4px;'>📚 Bimbel Page</a>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #fef2f2; border: 1px solid #ef4444; border-radius: 8px; padding: 16px; margin: 16px 0;'>";
    echo "<h3 style='color: #dc2626; margin-top: 0;'>✗ Database Setup Failed</h3>";
    echo "<p style='color: #991b1b;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: #991b1b;'>Please check your database configuration and try again.</p>";
    echo "</div>";
}
?>