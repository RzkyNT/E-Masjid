<?php
/**
 * Content Seeding Script for Masjid Website
 * This script populates the database with sample content for testing and initial setup
 */

require_once 'config/config.php';

echo "<h2>Content Seeding for Masjid Website</h2>";

try {
    // Get admin user ID for content creation
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin_masjid' LIMIT 1");
    $stmt->execute();
    $admin_user = $stmt->fetch();
    $admin_id = $admin_user ? $admin_user['id'] : 1;
    
    echo "Using admin user ID: $admin_id<br><br>";
    
    // Seed Articles
    echo "<h3>Seeding Articles</h3>";
    $articles = [
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
        ],
        [
            'title' => 'Pembukaan Pendaftaran Bimbel Al-Muhajirin',
            'slug' => 'pendaftaran-bimbel-al-muhajirin',
            'content' => '<p>DKM Masjid Jami Al-Muhajirin dengan bangga mengumumkan pembukaan pendaftaran Bimbingan Belajar (Bimbel) Al-Muhajirin untuk tahun ajaran baru.</p><p><strong>Program yang tersedia:</strong></p><ul><li>Bimbel SD (Kelas 1-6)</li><li>Bimbel SMP (Kelas 7-9)</li><li>Bimbel SMA (Kelas 10-12)</li></ul><p><strong>Fasilitas:</strong></p><ul><li>Tenaga pengajar berpengalaman</li><li>Ruang belajar yang nyaman</li><li>Materi pembelajaran lengkap</li><li>Biaya terjangkau</li></ul><p><strong>Informasi Pendaftaran:</strong></p><ul><li>Periode: 1-30 Juni 2024</li><li>Tempat: Kantor DKM Masjid Al-Muhajirin</li><li>Kontak: 021-88888888</li></ul><p>Mari bergabung dan tingkatkan prestasi akademik putra-putri kita!</p>',
            'excerpt' => 'Pembukaan pendaftaran Bimbel Al-Muhajirin untuk SD, SMP, dan SMA.',
            'category' => 'pengumuman',
            'status' => 'published'
        ]
    ];
    
    $article_count = 0;
    foreach ($articles as $article) {
        try {
            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, excerpt, category, status, author_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE content = VALUES(content), excerpt = VALUES(excerpt)");
            $stmt->execute([
                $article['title'],
                $article['slug'],
                $article['content'],
                $article['excerpt'],
                $article['category'],
                $article['status'],
                $admin_id
            ]);
            $article_count++;
            echo "✓ Added article: {$article['title']}<br>";
        } catch (PDOException $e) {
            echo "⚠ Error adding article {$article['title']}: " . $e->getMessage() . "<br>";
        }
    }
    echo "Total articles added: $article_count<br><br>";
    
    // Seed Gallery
    echo "<h3>Seeding Gallery</h3>";
    $gallery_items = [
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
        ],
        [
            'title' => 'Perpustakaan Mini Masjid',
            'description' => 'Koleksi buku-buku Islam untuk jamaah',
            'file_path' => 'assets/uploads/gallery/perpustakaan.jpg',
            'file_type' => 'image',
            'category' => 'fasilitas',
            'sort_order' => 6
        ],
        [
            'title' => 'Kegiatan Buka Puasa Bersama',
            'description' => 'Suasana buka puasa bersama jamaah di bulan Ramadhan',
            'file_path' => 'assets/uploads/gallery/buka-puasa-bersama.jpg',
            'file_type' => 'image',
            'category' => 'kegiatan',
            'sort_order' => 7
        ],
        [
            'title' => 'Ruang Bimbel Al-Muhajirin',
            'description' => 'Fasilitas bimbingan belajar untuk anak-anak',
            'file_path' => 'assets/uploads/gallery/ruang-bimbel.jpg',
            'file_type' => 'image',
            'category' => 'fasilitas',
            'sort_order' => 8
        ]
    ];
    
    $gallery_count = 0;
    foreach ($gallery_items as $gallery) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, file_path, file_type, category, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE description = VALUES(description)");
            $stmt->execute([
                $gallery['title'],
                $gallery['description'],
                $gallery['file_path'],
                $gallery['file_type'],
                $gallery['category'],
                $gallery['sort_order']
            ]);
            $gallery_count++;
            echo "✓ Added gallery item: {$gallery['title']}<br>";
        } catch (PDOException $e) {
            echo "⚠ Error adding gallery item {$gallery['title']}: " . $e->getMessage() . "<br>";
        }
    }
    echo "Total gallery items added: $gallery_count<br><br>";
    
    // Seed Sample Contacts
    echo "<h3>Seeding Sample Contact Messages</h3>";
    $sample_contacts = [
        [
            'name' => 'Ahmad Wijaya',
            'email' => 'ahmad.wijaya@email.com',
            'subject' => 'Pertanyaan tentang Jadwal Kajian',
            'message' => 'Assalamu\'alaikum, saya ingin menanyakan apakah kajian Jumat malam masih rutin dilaksanakan? Terima kasih.',
            'status' => 'unread'
        ],
        [
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@email.com',
            'subject' => 'Informasi Donasi',
            'message' => 'Saya ingin menyalurkan donasi untuk kegiatan masjid. Bagaimana caranya? Mohon informasinya.',
            'status' => 'read'
        ],
        [
            'name' => 'Muhammad Rizki',
            'email' => 'rizki.muhammad@email.com',
            'subject' => 'Pendaftaran Bimbel',
            'message' => 'Apakah masih bisa mendaftar bimbel untuk anak SMP? Berapa biayanya?',
            'status' => 'replied'
        ]
    ];
    
    $contact_count = 0;
    foreach ($sample_contacts as $contact) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $contact['name'],
                $contact['email'],
                $contact['subject'],
                $contact['message'],
                $contact['status']
            ]);
            $contact_count++;
            echo "✓ Added contact message from: {$contact['name']}<br>";
        } catch (PDOException $e) {
            echo "⚠ Error adding contact message: " . $e->getMessage() . "<br>";
        }
    }
    echo "Total contact messages added: $contact_count<br><br>";
    
    // Seed Prayer Schedule for next 3 months
    echo "<h3>Seeding Prayer Schedule</h3>";
    $months_to_seed = 3;
    $prayer_count = 0;
    
    for ($month_offset = 0; $month_offset < $months_to_seed; $month_offset++) {
        $current_date = new DateTime();
        $current_date->modify("+$month_offset month");
        $current_date->modify('first day of this month');
        
        $year = $current_date->format('Y');
        $month = $current_date->format('m');
        $days_in_month = $current_date->format('t');
        
        echo "Adding prayer schedule for $year-$month...<br>";
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%s-%s-%02d', $year, $month, $day);
            
            // Calculate prayer times with gradual changes throughout the month
            $day_of_year = $current_date->format('z') + $day - 1;
            
            // Base times in minutes from midnight
            $fajr_base = 285; // 4:45
            $sunrise_base = 360; // 6:00
            $dhuhr_base = 735; // 12:15
            $asr_base = 930; // 15:30
            $maghrib_base = 1095; // 18:15
            $isha_base = 1170; // 19:30
            
            // Add seasonal variation (simplified)
            $seasonal_factor = sin(($day_of_year / 365) * 2 * M_PI);
            
            $fajr_minutes = $fajr_base + ($seasonal_factor * 30);
            $sunrise_minutes = $sunrise_base + ($seasonal_factor * 30);
            $dhuhr_minutes = $dhuhr_base; // Relatively stable
            $asr_minutes = $asr_base - ($seasonal_factor * 20);
            $maghrib_minutes = $maghrib_base + ($seasonal_factor * 40);
            $isha_minutes = $isha_base + ($seasonal_factor * 30);
            
            $prayer_times = [
                'fajr' => sprintf('%02d:%02d:00', floor($fajr_minutes / 60), abs($fajr_minutes % 60)),
                'sunrise' => sprintf('%02d:%02d:00', floor($sunrise_minutes / 60), abs($sunrise_minutes % 60)),
                'dhuhr' => sprintf('%02d:%02d:00', floor($dhuhr_minutes / 60), abs($dhuhr_minutes % 60)),
                'asr' => sprintf('%02d:%02d:00', floor($asr_minutes / 60), abs($asr_minutes % 60)),
                'maghrib' => sprintf('%02d:%02d:00', floor($maghrib_minutes / 60), abs($maghrib_minutes % 60)),
                'isha' => sprintf('%02d:%02d:00', floor($isha_minutes / 60), abs($isha_minutes % 60))
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
                $prayer_count++;
            } catch (PDOException $e) {
                echo "⚠ Error adding prayer schedule for $date: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "Total prayer schedule entries added: $prayer_count<br><br>";
    
    // Create placeholder images info
    echo "<h3>Creating Placeholder Image Information</h3>";
    $placeholder_info = [
        'assets/uploads/gallery/' => 'Gallery images directory created',
        'assets/uploads/gallery/thumbnails/' => 'Gallery thumbnails directory created',
        'assets/uploads/articles/' => 'Article images directory created',
        'assets/uploads/settings/' => 'Settings images directory created'
    ];
    
    foreach ($placeholder_info as $dir => $description) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "✓ $description<br>";
            } else {
                echo "⚠ Failed to create: $dir<br>";
            }
        } else {
            echo "✓ Directory exists: $dir<br>";
        }
    }
    
    // Create .gitkeep files for empty directories
    $gitkeep_dirs = [
        'assets/uploads/gallery/thumbnails/',
        'assets/uploads/articles/',
        'assets/uploads/settings/'
    ];
    
    foreach ($gitkeep_dirs as $dir) {
        $gitkeep_file = $dir . '.gitkeep';
        if (!file_exists($gitkeep_file)) {
            file_put_contents($gitkeep_file, '');
            echo "✓ Created .gitkeep in $dir<br>";
        }
    }
    
    echo "<br><h3>Content Seeding Summary</h3>";
    echo "<ul>";
    echo "<li>Articles: $article_count items</li>";
    echo "<li>Gallery: $gallery_count items</li>";
    echo "<li>Contact Messages: $contact_count items</li>";
    echo "<li>Prayer Schedule: $prayer_count entries</li>";
    echo "<li>Upload directories created and configured</li>";
    echo "</ul>";
    
    echo "<br><strong style='color: green;'>✓ Content seeding completed successfully!</strong><br>";
    echo "<p>The website now has sample content for testing and demonstration purposes.</p>";
    
    echo "<br><strong>Next Steps:</strong><br>";
    echo "<a href='index.php' style='margin-right: 10px;'>🏠 Visit Website</a>";
    echo "<a href='admin/masjid/dashboard.php' style='margin-right: 10px;'>📊 Admin Dashboard</a>";
    echo "<a href='pages/berita.php'>📰 View Articles</a>";
    
} catch (PDOException $e) {
    echo "<strong style='color: red;'>✗ Content seeding failed:</strong><br>";
    echo $e->getMessage();
}
?>