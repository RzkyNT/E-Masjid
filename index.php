<?php
require_once 'config/config.php';
// === Ambil Jadwal Sholat dari API ===
$jadwal_sholat = null;
$kota_sholat = 'Bekasi';

$api_url = "https://api.gimita.id/api/info/jadwalshalat?city=" . urlencode($kota_sholat);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $result = json_decode($response, true);
    if ($result && $result['success'] === true) {
        $jadwal_sholat = $result['data']['schedule'];
        $tanggal_sholat = $result['data']['date'];
        $kota_sholat = $result['data']['city'];
    }
}


// Get basic settings
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_description', 'masjid_address', 'contact_phone')");
    $stmt->execute();
    $settings_data = $stmt->fetchAll();
    
    $settings = [];
    foreach ($settings_data as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    $settings = [
        'site_name' => 'Masjid Jami Al-Muhajirin',
        'site_description' => 'Website resmi Masjid Jami Al-Muhajirin',
        'masjid_address' => 'Bekasi Utara, Kota Bekasi',
        'contact_phone' => '021-12345678'
    ];
}

// Get latest articles
try {
    $stmt = $pdo->prepare("SELECT title, slug, excerpt, created_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $latest_articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $latest_articles = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Jami Al-Muhajirin'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mosque text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Al-Muhajirin'); ?></h1>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="index.php" class="text-green-600 hover:text-green-700 px-3 py-2 rounded-md text-sm font-medium">Beranda</a>
                        <a href="pages/profil.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Profil</a>
                        <a href="pages/jadwal_sholat.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Jadwal Sholat</a>
                        <a href="pages/berita.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Berita</a>
                        <a href="pages/galeri.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Galeri</a>
                        <a href="pages/donasi.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Donasi</a>
                        <a href="pages/kontak.php" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">Kontak</a>
                        <a href="admin/login.php" class="bg-green-600 text-white hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i>Admin
                        </a>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 hover:text-green-600 focus:outline-none focus:text-green-600" id="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-50 rounded-lg mt-2">
                    <a href="index.php" class="text-green-600 block px-3 py-2 rounded-md text-base font-medium">Beranda</a>
                    <a href="pages/profil.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Profil</a>
                    <a href="pages/jadwal_sholat.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Jadwal Sholat</a>
                    <a href="pages/berita.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Berita</a>
                    <a href="pages/galeri.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Galeri</a>
                    <a href="pages/donasi.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Donasi</a>
                    <a href="pages/kontak.php" class="text-gray-700 hover:text-green-600 block px-3 py-2 rounded-md text-base font-medium">Kontak</a>
                    <a href="admin/login.php" class="bg-green-600 text-white block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i>Admin Login
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">
                    Selamat Datang di<br>
                    <?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Jami Al-Muhajirin'); ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    <?php echo htmlspecialchars($settings['site_description'] ?? 'Pusat ibadah dan dakwah umat Islam'); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="pages/jadwal_sholat.php" class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-clock mr-2"></i>Jadwal Sholat
                    </a>
                    <a href="pages/berita.php" class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-newspaper mr-2"></i>Berita Terbaru
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Prayer Time Today -->
<section class="py-14 bg-white">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="text-center mb-12">
        <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-mosque text-green-600 text-2xl"></i>
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-2">
            Jadwal Sholat Hari Ini
        </h2>
        <p class="text-gray-600 text-lg">
            <i class="fas fa-location-dot mr-1"></i>
            <?= htmlspecialchars($kota_sholat ?? ''); ?> —
            <i class="fas fa-calendar-day ml-2 mr-1"></i>
            <?= date('d F Y', strtotime($tanggal_sholat ?? date('Y-m-d'))); ?>
        </p>
    </div>

<?php if ($jadwal_sholat): ?>

    <!-- SHOLAT FARDHU -->
    <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">
        <i class="fas fa-clock text-green-600 mr-2"></i>
        Waktu Sholat Fardhu
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-5 mb-14">

        <?php
        $fardhu = [
            'Subuh'   => ['time' => $jadwal_sholat['subuh'],   'color' => 'from-blue-500 to-blue-600', 'icon' => 'fa-sun'],
            'Dzuhur' => ['time' => $jadwal_sholat['dzuhur'],  'color' => 'from-yellow-500 to-orange-500', 'icon' => 'fa-sun-bright'],
            'Ashar'  => ['time' => $jadwal_sholat['ashar'],   'color' => 'from-orange-500 to-red-500', 'icon' => 'fa-cloud-sun'],
            'Maghrib'=> ['time' => $jadwal_sholat['maghrib'], 'color' => 'from-red-500 to-pink-500', 'icon' => 'fa-sunset'],
            'Isya'   => ['time' => $jadwal_sholat['isya'],    'color' => 'from-purple-500 to-indigo-500', 'icon' => 'fa-moon'],
        ];
        ?>

        <?php foreach ($fardhu as $name => $data): ?>
        <div class="bg-gradient-to-br <?= $data['color']; ?> text-white p-6 rounded-xl text-center shadow-lg">
            <i class="fas <?= $data['icon']; ?> text-xl mb-2 opacity-90"></i>
            <h4 class="font-semibold text-lg mb-1"><?= $name; ?></h4>
            <p class="text-3xl font-bold tracking-wide"><?= $data['time']; ?></p>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- SHOLAT SUNNAH -->
    <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">
        <i class="fas fa-moon text-indigo-600 mr-2"></i>
        Waktu Sholat Sunnah
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <?php
        $sunnah = [
            ['Tahajud', "00:00 – {$jadwal_sholat['subuh']}", 'green', 'fa-bed'],
            ['Witir', "{$jadwal_sholat['isya']} – {$jadwal_sholat['subuh']}", 'blue', 'fa-moon'],
            ['Dhuha', "Mulai {$jadwal_sholat['dhuha']} hingga sebelum Dzuhur", 'yellow', 'fa-sun'],
            ['Rawatib Subuh', "Sebelum {$jadwal_sholat['subuh']}", 'purple', 'fa-person-praying'],
            ['Rawatib Dzuhur', "Sebelum & sesudah {$jadwal_sholat['dzuhur']}", 'orange', 'fa-person-praying'],
            ['Rawatib Maghrib', "Setelah {$jadwal_sholat['maghrib']}", 'red', 'fa-person-praying'],
            ['Rawatib Isya', "Setelah {$jadwal_sholat['isya']}", 'indigo', 'fa-person-praying'],
        ];
        ?>

        <?php foreach ($sunnah as [$name, $time, $color, $icon]): ?>
        <div class="bg-<?= $color; ?>-50 border-l-4 border-<?= $color; ?>-600 p-4 rounded-lg">
            <h4 class="font-semibold text-<?= $color; ?>-700 flex items-center gap-2">
                <i class="fas <?= $icon; ?>"></i> <?= $name; ?>
            </h4>
            <p class="text-gray-700 text-sm"><?= $time; ?></p>
        </div>
        <?php endforeach; ?>

    </div>

<?php else: ?>

    <p class="text-center text-red-500 font-semibold">
        <i class="fas fa-triangle-exclamation mr-2"></i>
        Jadwal sholat gagal dimuat. Silakan refresh halaman.
    </p>

<?php endif; ?>

</div>
</section>

    <!-- Latest News -->
    <?php if (!empty($latest_articles)): ?>
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Berita Terbaru</h2>
                <p class="text-gray-600">Informasi dan kegiatan terkini dari masjid</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($latest_articles as $article): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <a href="pages/berita_detail.php?slug=<?php echo urlencode($article['slug']); ?>" class="hover:text-green-600">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['title']), 0, 100) . '...'); ?>
                        </p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                            </span>
                            <a href="pages/berita_detail.php?slug=<?php echo urlencode($article['slug']); ?>" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                Baca selengkapnya <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="pages/berita.php" class="bg-green-600 text-white hover:bg-green-700 px-6 py-3 rounded-lg font-semibold transition duration-200">
                    Lihat Semua Berita
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Facilities -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Fasilitas Masjid</h2>
                <p class="text-gray-600">Berbagai fasilitas yang tersedia untuk jamaah</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-6">
                    <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-mosque text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Ruang Sholat</h3>
                    <p class="text-gray-600 text-sm">Ruang sholat yang luas dan nyaman untuk jamaah</p>
                </div>
                
                <div class="text-center p-6">
                    <div class="bg-blue-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Bimbel Al-Muhajirin</h3>
                    <p class="text-gray-600 text-sm">Bimbingan belajar untuk SD, SMP, dan SMA</p>
                </div>
                
                <div class="text-center p-6">
                    <div class="bg-purple-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-book-open text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Perpustakaan</h3>
                    <p class="text-gray-600 text-sm">Koleksi buku-buku islami dan pengetahuan umum</p>
                </div>
                
                <div class="text-center p-6">
                    <div class="bg-orange-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-car text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Parkir</h3>
                    <p class="text-gray-600 text-sm">Area parkir yang luas untuk kendaraan jamaah</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kontak Kami</h3>
                    <div class="space-y-2">
                        <p class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <?php echo htmlspecialchars($settings['masjid_address'] ?? 'Bekasi Utara, Kota Bekasi'); ?>
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <?php echo htmlspecialchars($settings['contact_phone'] ?? '021-12345678'); ?>
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            info@almuhajirin.com
                        </p>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Menu</h3>
                    <div class="space-y-2">
                        <a href="pages/profil.php" class="block hover:text-green-400 transition duration-200">Profil Masjid</a>
                        <a href="pages/jadwal_sholat.php" class="block hover:text-green-400 transition duration-200">Jadwal Sholat</a>
                        <a href="pages/berita.php" class="block hover:text-green-400 transition duration-200">Berita</a>
                        <a href="pages/galeri.php" class="block hover:text-green-400 transition duration-200">Galeri</a>
                        <a href="pages/donasi.php" class="block hover:text-green-400 transition duration-200">Donasi</a>
                        <a href="pages/kontak.php" class="block hover:text-green-400 transition duration-200">Kontak</a>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-green-400 transition duration-200">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-green-400 transition duration-200">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-green-400 transition duration-200">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                        <a href="#" class="text-white hover:text-green-400 transition duration-200">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Jami Al-Muhajirin'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Auto-update prayer times (placeholder - would integrate with real API)
        function updatePrayerTimes() {
            // This would fetch from prayer time API
            console.log('Prayer times updated');
        }

        // Update prayer times every hour
        setInterval(updatePrayerTimes, 3600000);
    </script>
</body>
</html>