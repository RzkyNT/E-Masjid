<?php
/**
 * Website Initialization Script
 * Complete setup for Masjid Jami Al-Muhajirin website
 * This script combines database setup and content seeding
 */

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Website Initialization - Masjid Jami Al-Muhajirin</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }";
echo "h1, h2, h3 { color: #2c5530; }";
echo ".success { color: #28a745; }";
echo ".warning { color: #ffc107; }";
echo ".error { color: #dc3545; }";
echo ".step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #2c5530; }";
echo ".navigation { background: #e9ecef; padding: 15px; margin: 20px 0; text-align: center; }";
echo ".navigation a { margin: 0 10px; padding: 8px 16px; background: #2c5530; color: white; text-decoration: none; border-radius: 4px; }";
echo ".navigation a:hover { background: #1e3a23; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🕌 Website Initialization</h1>";
echo "<h2>Masjid Jami Al-Muhajirin</h2>";

$start_time = microtime(true);

try {
    // Step 1: Database Setup
    echo "<div class='step'>";
    echo "<h3>Step 1: Database Setup</h3>";
    
    // Connect to MySQL without selecting database
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<span class='success'>✓ Connected to MySQL server</span><br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<span class='success'>✓ Database 'masjid_bimbel' created/verified</span><br>";
    
    // Select the database
    $pdo->exec("USE masjid_bimbel");
    echo "<span class='success'>✓ Using database 'masjid_bimbel'</span><br>";
    
    // Read and execute SQL file
    if (file_exists('database/masjid_bimbel.sql')) {
        $sql = file_get_contents('database/masjid_bimbel.sql');
        
        // Remove the CREATE DATABASE and USE statements since we already did that
        $sql = preg_replace('/CREATE DATABASE.*?;/', '', $sql);
        $sql = preg_replace('/USE.*?;/', '', $sql);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
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
                        echo "<span class='warning'>⚠ Warning: " . $e->getMessage() . "</span><br>";
                    }
                }
            }
        }
        
        echo "<span class='success'>✓ Executed $success_count SQL statements</span><br>";
    } else {
        echo "<span class='error'>✗ SQL file not found: database/masjid_bimbel.sql</span><br>";
    }
    echo "</div>";
    
    // Step 2: Configuration Check
    echo "<div class='step'>";
    echo "<h3>Step 2: Configuration Check</h3>";
    
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "<span class='success'>✓ Config file loaded successfully</span><br>";
    } else {
        echo "<span class='error'>✗ Config file not found</span><br>";
    }
    
    if (file_exists('config/site_defaults.php')) {
        require_once 'config/site_defaults.php';
        echo "<span class='success'>✓ Site defaults loaded successfully</span><br>";
    } else {
        echo "<span class='error'>✗ Site defaults file not found</span><br>";
    }
    echo "</div>";
    
    // Step 3: Content Initialization
    echo "<div class='step'>";
    echo "<h3>Step 3: Content Initialization</h3>";
    
    // Get admin user ID for content creation
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin_masjid' LIMIT 1");
    $stmt->execute();
    $admin_user = $stmt->fetch();
    $admin_id = $admin_user ? $admin_user['id'] : 1;
    echo "<span class='success'>✓ Using admin user ID: $admin_id</span><br>";
    
    // Initialize comprehensive website settings
    $website_settings = [
        // Basic site information
        ['site_name', 'Masjid Jami Al-Muhajirin', 'text', 'Nama website masjid'],
        ['site_tagline', 'Masjid yang Memakmurkan Umat', 'text', 'Tagline website'],
        ['site_description', 'Website resmi Masjid Jami Al-Muhajirin - Pusat ibadah dan pendidikan Islam di Bekasi Utara', 'textarea', 'Deskripsi website untuk SEO'],
        
        // Masjid information
        ['masjid_name', 'Masjid Jami Al-Muhajirin', 'text', 'Nama lengkap masjid'],
        ['masjid_address', 'Q2X5+P3M, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara, Kota Bekasi, Jawa Barat 17124', 'textarea', 'Alamat lengkap masjid'],
        ['masjid_coordinates', '-6.2008,107.0082', 'text', 'Koordinat GPS masjid (lat,lng)'],
        ['masjid_established', '2010', 'text', 'Tahun berdiri masjid'],
        
        // Contact information
        ['contact_phone', '021-88888888', 'text', 'Nomor telepon masjid'],
        ['contact_whatsapp', '6281234567890', 'text', 'Nomor WhatsApp (dengan kode negara)'],
        ['contact_email', 'info@almuhajirin.com', 'text', 'Email resmi masjid'],
        
        // Social media
        ['social_facebook', 'https://facebook.com/almuhajirinbekasi', 'text', 'URL Facebook'],
        ['social_instagram', 'https://instagram.com/almuhajirinbekasi', 'text', 'URL Instagram'],
        ['social_youtube', 'https://youtube.com/@almuhajirinbekasi', 'text', 'URL YouTube'],
        
        // Donation information
        ['donation_bank_mandiri', '1234567890', 'text', 'Rekening Bank Mandiri'],
        ['donation_bank_bca', '0987654321', 'text', 'Rekening Bank BCA'],
        ['donation_bank_bni', '1122334455', 'text', 'Rekening Bank BNI'],
        ['donation_account_name', 'DKM Masjid Jami Al-Muhajirin', 'text', 'Nama pemilik rekening'],
        
        // Masjid profile content
        ['masjid_history', 'Masjid Jami Al-Muhajirin didirikan pada tahun 2010 atas prakarsa warga sekitar yang ingin memiliki tempat ibadah yang representatif. Pembangunan masjid ini dilakukan secara gotong royong dengan dukungan penuh dari masyarakat setempat. Nama "Al-Muhajirin" dipilih sebagai simbol semangat hijrah menuju kebaikan dan ketaqwaan kepada Allah SWT.', 'textarea', 'Sejarah singkat masjid'],
        ['masjid_vision', 'Menjadi masjid yang memakmurkan umat melalui kegiatan ibadah, pendidikan, dan sosial yang berkelanjutan.', 'textarea', 'Visi masjid'],
        ['masjid_mission', 'Menyelenggarakan kegiatan ibadah yang khusyuk dan berjamaah|Memberikan pendidikan Islam yang berkualitas|Mengembangkan kegiatan sosial untuk kesejahteraan umat|Membangun ukhuwah islamiyah yang kuat', 'textarea', 'Misi masjid (pisahkan dengan |)'],
        
        // DKM Structure
        ['dkm_ketua', 'H. Ahmad Suryadi', 'text', 'Nama Ketua DKM'],
        ['dkm_wakil_ketua', 'H. Muhammad Ridwan', 'text', 'Nama Wakil Ketua DKM'],
        ['dkm_sekretaris', 'Ustadz Ahmad Fauzi, Lc.', 'text', 'Nama Sekretaris DKM'],
        ['dkm_bendahara', 'Hj. Siti Aminah', 'text', 'Nama Bendahara DKM'],
        ['dkm_sie_ibadah', 'Ustadz Muhammad Ali', 'text', 'Koordinator Seksi Ibadah'],
        ['dkm_sie_pendidikan', 'Ustadz Ahmad Fauzi, Lc.', 'text', 'Koordinator Seksi Pendidikan'],
        ['dkm_sie_sosial', 'H. Bambang Sutrisno', 'text', 'Koordinator Seksi Sosial'],
        
        // Facilities
        ['facilities_list', 'Ruang sholat utama (kapasitas 500 jamaah)|Tempat wudhu pria dan wanita|Ruang kajian dan aula serbaguna|Perpustakaan mini|Tempat parkir yang luas|Kantor DKM|Ruang bimbel Al-Muhajirin', 'textarea', 'Daftar fasilitas (pisahkan dengan |)'],
        
        // Prayer time settings
        ['prayer_api_enabled', '1', 'text', 'Aktifkan API jadwal sholat (1=ya, 0=tidak)'],
        ['prayer_api_city', 'bekasi', 'text', 'Nama kota untuk API jadwal sholat'],
        ['prayer_manual_adjustment', '0', 'text', 'Penyesuaian manual jadwal (menit)'],
        
        // Website settings
        ['site_maintenance', '0', 'text', 'Mode maintenance (1=aktif, 0=tidak)'],
        ['site_analytics', '', 'text', 'Google Analytics ID'],
        ['site_logo', 'assets/images/logo-masjid.png', 'image', 'Logo masjid'],
        ['site_favicon', 'assets/images/favicon.png', 'image', 'Favicon website']
    ];
    
    $settings_count = 0;
    foreach ($website_settings as $setting) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)");
            $stmt->execute($setting);
            $settings_count++;
        } catch (PDOException $e) {
            echo "<span class='warning'>⚠ Warning adding setting: " . $e->getMessage() . "</span><br>";
        }
    }
    echo "<span class='success'>✓ Website settings initialized: $settings_count items</span><br>";
    echo "</div>";
    
    // Step 4: Directory Structure
    echo "<div class='step'>";
    echo "<h3>Step 4: Directory Structure</h3>";
    
    $upload_dirs = [
        'assets/uploads/gallery',
        'assets/uploads/gallery/thumbnails',
        'assets/uploads/articles',
        'assets/uploads/settings'
    ];
    
    $dir_count = 0;
    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<span class='success'>✓ Created directory: $dir</span><br>";
                $dir_count++;
            } else {
                echo "<span class='warning'>⚠ Failed to create directory: $dir</span><br>";
            }
        } else {
            echo "<span class='success'>✓ Directory exists: $dir</span><br>";
            $dir_count++;
        }
        
        // Create .gitkeep file
        $gitkeep_file = $dir . '/.gitkeep';
        if (!file_exists($gitkeep_file)) {
            file_put_contents($gitkeep_file, '');
        }
    }
    echo "<span class='success'>✓ Upload directories configured: $dir_count directories</span><br>";
    echo "</div>";
    
    // Step 5: Database Statistics
    echo "<div class='step'>";
    echo "<h3>Step 5: Database Statistics</h3>";
    
    $tables = ['users', 'articles', 'gallery', 'contacts', 'settings', 'prayer_schedule'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "<span class='success'>✓ $table: {$result['count']} records</span><br>";
        } catch (PDOException $e) {
            echo "<span class='warning'>⚠ Table $table: " . $e->getMessage() . "</span><br>";
        }
    }
    echo "</div>";
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    echo "<div class='step'>";
    echo "<h3>🎉 Initialization Complete!</h3>";
    echo "<p><strong style='color: green;'>Website initialization completed successfully in {$execution_time} seconds!</strong></p>";
    echo "<p>The website is now ready with:</p>";
    echo "<ul>";
    echo "<li>Database schema and tables</li>";
    echo "<li>Comprehensive website settings</li>";
    echo "<li>Upload directory structure</li>";
    echo "<li>Default configuration files</li>";
    echo "</ul>";
    
    echo "<p><strong>To add sample content (articles, gallery, etc.), run:</strong></p>";
    echo "<p><code><a href='seed_content.php' style='background: #f8f9fa; padding: 4px 8px; border: 1px solid #ddd; text-decoration: none;'>seed_content.php</a></code></p>";
    echo "</div>";
    
    echo "<div class='navigation'>";
    echo "<h3>Quick Navigation</h3>";
    echo "<a href='index.php'>🏠 Main Website</a>";
    echo "<a href='admin/login.php'>🔐 Admin Login</a>";
    echo "<a href='admin/masjid/dashboard.php'>📊 Admin Dashboard</a>";
    echo "<a href='seed_content.php'>🌱 Seed Sample Content</a>";
    echo "</div>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0066cc;'>";
    echo "<h4>📋 Default Login Information</h4>";
    echo "<p><strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> password</p>";
    echo "<p><em>Please change the default password after first login for security.</em></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='step'>";
    echo "<h3 class='error'>✗ Initialization Failed</h3>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body>";
echo "</html>";
?>