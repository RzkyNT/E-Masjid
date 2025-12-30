<?php
// Simple error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load config, but handle errors gracefully
try {
    require_once 'config/config.php';
    $config_loaded = true;
} catch (Exception $e) {
    $config_loaded = false;
    $error_message = $e->getMessage();
}

// Default settings if config fails
$settings = [
    'site_name' => 'Masjid Jami Al-Muhajirin',
    'site_description' => 'Website resmi Masjid Jami Al-Muhajirin',
    'masjid_address' => 'Bekasi Utara, Kota Bekasi',
    'contact_phone' => '021-12345678'
];

// Try to get settings from database if config loaded
if ($config_loaded) {
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_description', 'masjid_address', 'contact_phone')");
        $stmt->execute();
        $settings_data = $stmt->fetchAll();
        
        foreach ($settings_data as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
    } catch (PDOException $e) {
        // Use default settings if database query fails
    }
}

// Prayer times (static for now)
$prayer_times_today = [
    'fajr' => '04:30',
    'dhuhr' => '12:15',
    'asr' => '15:30',
    'maghrib' => '18:45',
    'isha' => '20:00',
    'dhuha' => '06:30'
];

$today_prayer_data = [
    'location' => 'Bekasi Utara, Jawa Barat',
    'formatted_date' => date('l, d F Y'),
    'times' => $prayer_times_today
];

// Try to get latest articles if config loaded
$latest_articles = [];
if ($config_loaded) {
    try {
        $stmt = $pdo->prepare("SELECT title, slug, excerpt, created_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $latest_articles = $stmt->fetchAll();
    } catch (PDOException $e) {
        // No articles available
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Jami Al-Muhajirin'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? ''); ?>">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Al-Muhajirin">
    <meta name="msapplication-TileColor" content="#059669">
    <meta name="msapplication-config" content="./browserconfig.xml">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="./manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="152x152" href="./assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/images/icon-180x180.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="./assets/images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/images/favicon-16x16.png">
    <link rel="shortcut icon" href="./assets/images/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $config_loaded ? getFileVersion('assets/css/style.css') : time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Service Worker Registration with error handling -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('sw.js?v=<?php echo $config_loaded ? getFileVersion('sw.js') : time(); ?>')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                        
                        // Request notification permission
                        if ('Notification' in window && 'PushManager' in window) {
                            if (Notification.permission === 'default') {
                                // Show a subtle prompt for notifications
                                setTimeout(() => {
                                    if (confirm('Aktifkan notifikasi untuk pengingat waktu sholat?')) {
                                        Notification.requestPermission().then(permission => {
                                            if (permission === 'granted') {
                                                console.log('Notification permission granted');
                                                // Subscribe to push notifications
                                                subscribeToPush(registration);
                                            }
                                        });
                                    }
                                }, 3000); // Wait 3 seconds before asking
                            } else if (Notification.permission === 'granted') {
                                subscribeToPush(registration);
                            }
                        }
                        
                        // Force update if new version available
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available, refresh page
                                    window.location.reload();
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
        
        // Subscribe to push notifications
        function subscribeToPush(registration) {
            const applicationServerKey = urlBase64ToUint8Array('YOUR_VAPID_PUBLIC_KEY_HERE'); // Replace with your VAPID key
            
            registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            }).then(function(subscription) {
                console.log('Push subscription successful:', subscription);
                // Send subscription to server
                sendSubscriptionToServer(subscription);
            }).catch(function(error) {
                console.log('Push subscription failed:', error);
            });
        }
        
        // Convert VAPID key
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
        
        // Send subscription to server
        function sendSubscriptionToServer(subscription) {
            fetch('./api/save_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(subscription)
            }).then(response => response.json())
              .then(data => console.log('Subscription saved:', data))
              .catch(error => console.error('Error saving subscription:', error));
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php if (!$config_loaded): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
        <p class="font-bold">Configuration Warning</p>
        <p>Database connection failed. Using default settings. Error: <?php echo htmlspecialchars($error_message); ?></p>
        <p><a href="setup_database.php" class="underline">Click here to setup database</a></p>
    </div>
    <?php endif; ?>
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
                    <button id="installButton" class="hidden bg-yellow-500 text-white hover:bg-yellow-600 px-8 py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-download mr-2"></i>Install App
                    </button>
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
            <?php echo htmlspecialchars($today_prayer_data['location']); ?> —
            <i class="fas fa-calendar-day ml-2 mr-1"></i>
            <?php echo $today_prayer_data['formatted_date']; ?>
        </p>
    </div>

    <!-- SHOLAT FARDHU -->
    <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">
        <i class="fas fa-clock text-green-600 mr-2"></i>
        Waktu Sholat Fardhu
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-5 mb-14">
        <?php
        $fardhu = [
            'Subuh'   => ['time' => $prayer_times_today['fajr'],   'color' => 'from-blue-500 to-blue-600', 'icon' => 'fa-moon'],
            'Dzuhur'  => ['time' => $prayer_times_today['dhuhr'],  'color' => 'from-yellow-500 to-orange-500', 'icon' => 'fa-sun'],
            'Ashar'   => ['time' => $prayer_times_today['asr'],    'color' => 'from-orange-500 to-red-500', 'icon' => 'fa-cloud-sun'],
            'Maghrib' => ['time' => $prayer_times_today['maghrib'], 'color' => 'from-red-500 to-pink-500', 'icon' => 'fa-sunset'],
            'Isya'    => ['time' => $prayer_times_today['isha'],   'color' => 'from-purple-500 to-indigo-500', 'icon' => 'fa-star'],
        ];
        ?>

        <?php foreach ($fardhu as $name => $data): ?>
        <div class="bg-gradient-to-br <?php echo $data['color']; ?> text-white p-6 rounded-xl text-center shadow-lg">
            <i class="fas <?php echo $data['icon']; ?> text-xl mb-2 opacity-90"></i>
            <h4 class="font-semibold text-lg mb-1"><?php echo $name; ?></h4>
            <p class="text-3xl font-bold tracking-wide"><?php echo $data['time']; ?></p>
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
            ['Tahajud', "00:00 – {$prayer_times_today['fajr']}", 'green', 'fa-bed'],
            ['Witir', "{$prayer_times_today['isha']} – {$prayer_times_today['fajr']}", 'blue', 'fa-moon'],
            ['Dhuha', "Mulai {$prayer_times_today['dhuha']} hingga sebelum Dzuhur", 'yellow', 'fa-sun'],
            ['Rawatib Subuh', "Sebelum {$prayer_times_today['fajr']}", 'purple', 'fa-person-praying'],
            ['Rawatib Dzuhur', "Sebelum & sesudah {$prayer_times_today['dhuhr']}", 'orange', 'fa-person-praying'],
            ['Rawatib Maghrib', "Setelah {$prayer_times_today['maghrib']}", 'red', 'fa-person-praying'],
            ['Rawatib Isya', "Setelah {$prayer_times_today['isha']}", 'indigo', 'fa-person-praying'],
        ];
        ?>

        <?php foreach ($sunnah as [$name, $time, $color, $icon]): ?>
        <div class="bg-<?php echo $color; ?>-50 border-l-4 border-<?php echo $color; ?>-600 p-4 rounded-lg">
            <h4 class="font-semibold text-<?php echo $color; ?>-700 flex items-center gap-2">
                <i class="fas <?php echo $icon; ?>"></i> <?php echo $name; ?>
            </h4>
            <p class="text-gray-700 text-sm"><?php echo $time; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

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
        // Global error handler
        window.addEventListener('error', function(event) {
            console.error('JavaScript error:', event.error);
        });
        
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
        });

        // PWA Install functionality
        let deferredPrompt;
        const installButton = document.getElementById('installButton');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show the install button
            installButton.classList.remove('hidden');
        });

        installButton.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                // Clear the deferredPrompt variable
                deferredPrompt = null;
                // Hide the install button
                installButton.classList.add('hidden');
            }
        });

        // Hide install button if app is already installed
        window.addEventListener('appinstalled', () => {
            installButton.classList.add('hidden');
            console.log('PWA was installed');
        });

        // Mobile menu toggle with error handling
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });

        // Auto-update prayer times (placeholder - would integrate with real API)
        function updatePrayerTimes() {
            // This would fetch from prayer time API
            console.log('Prayer times updated');
        }

        // Update prayer times every hour
        setInterval(updatePrayerTimes, 3600000);
        
        // Force reload if page is served from cache and might be stale
        if (performance.navigation.type === 2) {
            // Page was accessed by going back/forward
            const lastModified = document.lastModified;
            const cacheTime = localStorage.getItem('pageLoadTime');
            const currentTime = new Date().getTime();
            
            if (!cacheTime || (currentTime - parseInt(cacheTime)) > 300000) { // 5 minutes
                localStorage.setItem('pageLoadTime', currentTime.toString());
                window.location.reload(true);
            }
        } else {
            localStorage.setItem('pageLoadTime', new Date().getTime().toString());
        }
    </script>
</body>
</html>