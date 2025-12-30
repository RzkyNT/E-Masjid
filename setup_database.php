<?php
// Setup database for Masjid Al-Muhajirin with sample content
echo "<h2>Database Setup & Content Initialization</h2>";

try {
    // Connect to MySQL without selecting database
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'masjid_bimbel' created/verified<br>";
    
    // Select the database
    $pdo->exec("USE masjid_bimbel");
    echo "✓ Using database 'masjid_bimbel'<br>";
    
    // Read and execute SQL file
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
                    echo "⚠ Warning: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✓ Executed $success_count SQL statements<br>";
    
    // Initialize sample content for website
    echo "<br><h3>Initializing Sample Content</h3>";
    
    // Get admin user ID for content creation
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin_masjid' LIMIT 1");
    $stmt->execute();
    $admin_user = $stmt->fetch();
    $admin_id = $admin_user ? $admin_user['id'] : 1;
    
    // Insert sample articles
    echo "Adding sample articles...<br>";
    $sample_articles = [
        [
            'title' => 'Selamat Datang di Website Masjid Jami Al-Muhajirin',
            'slug' => 'selamat-datang-website-masjid',
            'content' => '<p>Assalamu\'alaikum warahmatullahi wabarakatuh,</p><p>Alhamdulillahirabbil\'alamiin, kami dengan bangga mempersembahkan website resmi Masjid Jami Al-Muhajirin. Website ini hadir sebagai sarana informasi dan komunikasi antara masjid dengan jamaah serta masyarakat umum.</p><p>Melalui website ini, jamaah dapat mengakses berbagai informasi penting seperti jadwal sholat, kegiatan masjid, pengumuman, galeri dokumentasi, dan informasi donasi. Kami berharap website ini dapat memudahkan jamaah dalam mengikuti perkembangan kegiatan masjid.</p><p>Barakallahu fiikum.</p>',
            'excerpt' => 'Website resmi Masjid Jami Al-Muhajirin hadir sebagai sarana informasi dan komunikasi dengan jamaah.',
            'category' => 'pengumuman',
            'status' => 'published'
        ],
        [
            'title' => 'Kajian Rutin Setiap Jumat Malam',
            'slug' => 'kajian-rutin-jumat-malam',
            'content' => '<p>Masjid Jami Al-Muhajirin mengadakan kajian rutin setiap Jumat malam setelah sholat Maghrib. Kajian ini terbuka untuk umum dan membahas berbagai tema keislaman yang relevan dengan kehidupan sehari-hari.</p><p><strong>Jadwal Kajian:</strong></p><ul><li>Hari: Setiap Jumat</li><li>Waktu: Setelah Maghrib (19:30 - 21:00)</li><li>Tempat: Ruang utama masjid</li><li>Pembicara: Ustadz Ahmad Fauzi, Lc.</li></ul><p>Kajian ini membahas tafsir Al-Quran, hadits, fiqh, dan akhlak. Mari bergabung untuk menambah ilmu agama kita bersama-sama.</p>',
            'excerpt' => 'Kajian rutin setiap Jumat malam setelah Maghrib membahas berbagai tema keislaman.',
            'category' => 'kajian',
            'status' => 'published'
        ],
        [
            'title' => 'Kegiatan Santunan Anak Yatim Bulan Ramadhan',
            'slug' => 'santunan-anak-yatim-ramadhan',
            'content' => '<p>Alhamdulillah, DKM Masjid Jami Al-Muhajirin telah melaksanakan kegiatan santunan anak yatim dalam rangka menyambut bulan suci Ramadhan. Kegiatan ini diikuti oleh 50 anak yatim dari sekitar wilayah masjid.</p><p><strong>Detail Kegiatan:</strong></p><ul><li>Tanggal: 15 Ramadhan 1445 H</li><li>Peserta: 50 anak yatim</li><li>Bantuan: Uang tunai, paket sembako, dan perlengkapan sekolah</li><li>Total dana: Rp 25.000.000</li></ul><p>Kegiatan ini dapat terlaksana berkat dukungan dan donasi dari jamaah masjid. Semoga kegiatan ini dapat memberikan manfaat dan keberkahan bagi anak-anak yatim.</p><p>Jazakumullahu khairan kepada semua pihak yang telah berpartisipasi.</p>',
            'excerpt' => 'Kegiatan santunan untuk 50 anak yatim dalam rangka menyambut bulan Ramadhan.',
            'category' => 'kegiatan',
            'status' => 'published'
        ],
        [
            'title' => 'Pengumuman Jadwal Sholat Tarawih',
            'slug' => 'jadwal-sholat-tarawih',
            'content' => '<p>Bismillahirrahmanirrahim,</p><p>Dalam rangka menyambut bulan suci Ramadhan, DKM Masjid Jami Al-Muhajirin mengumumkan jadwal sholat Tarawih yang akan dilaksanakan setiap malam selama bulan Ramadhan.</p><p><strong>Jadwal Sholat Tarawih:</strong></p><ul><li>Waktu: Setelah sholat Isya (sekitar 20:00)</li><li>Jumlah rakaat: 20 rakaat + 3 rakaat witir</li><li>Imam: Ustadz Ahmad Fauzi, Lc. dan Ustadz Muhammad Ridwan</li><li>Durasi: Sekitar 1 jam</li></ul><p>Seluruh jamaah diundang untuk mengikuti sholat Tarawih berjamaah. Mari kita manfaatkan bulan Ramadhan ini untuk meningkatkan ibadah dan taqwa kepada Allah SWT.</p>',
            'excerpt' => 'Jadwal sholat Tarawih selama bulan Ramadhan setelah sholat Isya.',
            'category' => 'pengumuman',
            'status' => 'published'
        ]
    ];
    
    foreach ($sample_articles as $article) {
        try {
            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, excerpt, category, status, author_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $article['title'],
                $article['slug'],
                $article['content'],
                $article['excerpt'],
                $article['category'],
                $article['status'],
                $admin_id
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "⚠ Warning adding article: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✓ Sample articles added<br>";
    
    // Insert sample gallery items
    echo "Adding sample gallery items...<br>";
    $sample_gallery = [
        [
            'title' => 'Suasana Sholat Jumat',
            'description' => 'Dokumentasi sholat Jumat yang dihadiri jamaah dengan khusyuk',
            'file_path' => 'assets/uploads/gallery/sholat-jumat.jpg',
            'file_type' => 'image',
            'category' => 'kegiatan',
            'sort_order' => 1
        ],
        [
            'title' => 'Kajian Rutin Jumat Malam',
            'description' => 'Suasana kajian rutin yang disampaikan oleh Ustadz Ahmad Fauzi',
            'file_path' => 'assets/uploads/gallery/kajian-jumat.jpg',
            'file_type' => 'image',
            'category' => 'kajian',
            'sort_order' => 2
        ],
        [
            'title' => 'Fasilitas Tempat Wudhu',
            'description' => 'Tempat wudhu yang bersih dan nyaman untuk jamaah',
            'file_path' => 'assets/uploads/gallery/tempat-wudhu.jpg',
            'file_type' => 'image',
            'category' => 'fasilitas',
            'sort_order' => 3
        ],
        [
            'title' => 'Ruang Utama Masjid',
            'description' => 'Ruang utama masjid yang luas dan nyaman untuk beribadah',
            'file_path' => 'assets/uploads/gallery/ruang-utama.jpg',
            'file_type' => 'image',
            'category' => 'fasilitas',
            'sort_order' => 4
        ],
        [
            'title' => 'Kegiatan Santunan Anak Yatim',
            'description' => 'Dokumentasi kegiatan santunan anak yatim bulan Ramadhan',
            'file_path' => 'assets/uploads/gallery/santunan-yatim.jpg',
            'file_type' => 'image',
            'category' => 'kegiatan',
            'sort_order' => 5
        ]
    ];
    
    foreach ($sample_gallery as $gallery) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, file_path, file_type, category, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([
                $gallery['title'],
                $gallery['description'],
                $gallery['file_path'],
                $gallery['file_type'],
                $gallery['category'],
                $gallery['sort_order']
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "⚠ Warning adding gallery: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✓ Sample gallery items added<br>";
    
    // Insert comprehensive website settings
    echo "Adding website settings...<br>";
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
        ['donation_qris', 'assets/images/qris-donation.png', 'image', 'QR Code untuk donasi digital'],
        
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
    
    foreach ($website_settings as $setting) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)");
            $stmt->execute($setting);
        } catch (PDOException $e) {
            echo "⚠ Warning adding setting: " . $e->getMessage() . "<br>";
        }
    }
    echo "✓ Website settings added<br>";
    
    // Insert sample prayer schedule for current month
    echo "Adding sample prayer schedule...<br>";
    $current_date = new DateTime();
    $days_in_month = $current_date->format('t');
    $year = $current_date->format('Y');
    $month = $current_date->format('m');
    
    // Sample prayer times (will be adjusted slightly for each day)
    $base_times = [
        'fajr' => '04:45:00',
        'sunrise' => '06:00:00',
        'dhuhr' => '12:15:00',
        'asr' => '15:30:00',
        'maghrib' => '18:15:00',
        'isha' => '19:30:00'
    ];
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%s-%s-%02d', $year, $month, $day);
        
        // Add slight variation to prayer times (realistic changes)
        $fajr_minutes = 285 + ($day * 0.5); // 4:45 + gradual change
        $sunrise_minutes = 360 + ($day * 0.5); // 6:00 + gradual change
        $dhuhr_minutes = 735; // 12:15 (relatively stable)
        $asr_minutes = 930 - ($day * 0.3); // 15:30 - gradual change
        $maghrib_minutes = 1095 + ($day * 0.8); // 18:15 + gradual change
        $isha_minutes = 1170 + ($day * 0.8); // 19:30 + gradual change
        
        $prayer_times = [
            'fajr' => sprintf('%02d:%02d:00', floor($fajr_minutes / 60), $fajr_minutes % 60),
            'sunrise' => sprintf('%02d:%02d:00', floor($sunrise_minutes / 60), $sunrise_minutes % 60),
            'dhuhr' => sprintf('%02d:%02d:00', floor($dhuhr_minutes / 60), $dhuhr_minutes % 60),
            'asr' => sprintf('%02d:%02d:00', floor($asr_minutes / 60), $asr_minutes % 60),
            'maghrib' => sprintf('%02d:%02d:00', floor($maghrib_minutes / 60), $maghrib_minutes % 60),
            'isha' => sprintf('%02d:%02d:00', floor($isha_minutes / 60), $isha_minutes % 60)
        ];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO prayer_schedule (date, fajr, sunrise, dhuhr, asr, maghrib, isha) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE fajr = VALUES(fajr), sunrise = VALUES(sunrise), dhuhr = VALUES(dhuhr), asr = VALUES(asr), maghrib = VALUES(maghrib), isha = VALUES(isha)");
            $stmt->execute([
                $date,
                $prayer_times['fajr'],
                $prayer_times['sunrise'],
                $prayer_times['dhuhr'],
                $prayer_times['asr'],
                $prayer_times['maghrib'],
                $prayer_times['isha']
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "⚠ Warning adding prayer schedule: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✓ Sample prayer schedule for current month added<br>";
    
    // Create upload directories if they don't exist
    echo "Creating upload directories...<br>";
    $upload_dirs = [
        'assets/uploads/gallery',
        'assets/uploads/gallery/thumbnails',
        'assets/uploads/articles',
        'assets/uploads/settings'
    ];
    
    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "✓ Created directory: $dir<br>";
            } else {
                echo "⚠ Failed to create directory: $dir<br>";
            }
        }
    }
    
    // Test the connection with our config
    require_once 'config/config.php';
    echo "✓ Config file loaded successfully<br>";
    
    // Test queries and show statistics
    echo "<br><h3>Database Statistics</h3>";
    
    $tables = ['users', 'articles', 'gallery', 'settings', 'prayer_schedule'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "✓ $table: {$result['count']} records<br>";
    }
    
    echo "<br><strong style='color: green;'>✓ Database setup and content initialization completed successfully!</strong><br>";
    echo "<p>Sample content has been added including:</p>";
    echo "<ul>";
    echo "<li>4 sample articles (welcome, kajian, kegiatan, pengumuman)</li>";
    echo "<li>5 sample gallery items with different categories</li>";
    echo "<li>Comprehensive website settings and configuration</li>";
    echo "<li>Prayer schedule for the current month</li>";
    echo "<li>Upload directories for media files</li>";
    echo "</ul>";
    
    echo "<br><strong>Next Steps:</strong><br>";
    echo "<a href='index.php' style='margin-right: 10px;'>🏠 Visit Main Website</a>";
    echo "<a href='admin/login.php' style='margin-right: 10px;'>🔐 Admin Login</a>";
    echo "<a href='admin/masjid/dashboard.php'>📊 Admin Dashboard</a>";
    
    echo "<br><br><em>Default admin login: username 'admin', password 'password'</em>";
    
} catch (PDOException $e) {
    echo "<strong style='color: red;'>✗ Database setup failed:</strong><br>";
    echo $e->getMessage();
}
?>