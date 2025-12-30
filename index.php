<?php
// Simple error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load config, but handle errors gracefully
try {
    require_once 'config/config.php';
    require_once 'includes/settings_loader.php';
    $config_loaded = true;
} catch (Exception $e) {
    require_once 'config/site_defaults.php';
    $config_loaded = false;
    $error_message = $e->getMessage();
}

// Initialize website settings
$settings = initializePageSettings();

// Get prayer times from MyQuran API
require_once 'includes/prayer_myquran_api.php';

$today_prayer_api = getTodayPrayerSchedule();
$prayer_times_today = [];
$today_prayer_data = [
    'location' => getSiteSetting('location_name'),
    'formatted_date' => date('l, d F Y'),
    'times' => []
];

if ($today_prayer_api && $today_prayer_api['status']) {
    $jadwal = $today_prayer_api['data']['jadwal'];
    $prayer_times_today = [
        'fajr' => $jadwal['subuh'],
        'dhuhr' => $jadwal['dzuhur'],
        'asr' => $jadwal['ashar'],
        'maghrib' => $jadwal['maghrib'],
        'isha' => $jadwal['isya'],
        'dhuha' => $jadwal['dhuha']
    ];
    
    $today_prayer_data = [
        'location' => $today_prayer_api['data']['lokasi'] . ', ' . $today_prayer_api['data']['daerah'],
        'formatted_date' => $jadwal['tanggal'],
        'times' => $prayer_times_today,
        'api_success' => true
    ];
} else {
    // Fallback to static times if API fails
    $prayer_times_today = getFallbackPrayerTimes();
    
    $today_prayer_data = [
        'location' => getWebsiteSetting('location_name', 'Bekasi Utara'),
        'formatted_date' => date('l, d F Y'),
        'times' => $prayer_times_today,
        'api_success' => false
    ];
}

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
    <link rel="alternate icon" href="./assets/images/favicon.svg">
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
    

<?php include 'partials/header.php'; ?>

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
            <?php if (isset($today_prayer_data['api_success']) && !$today_prayer_data['api_success']): ?>
                <span class="text-yellow-600 text-sm ml-2">
                    <i class="fas fa-exclamation-triangle"></i> Menggunakan jadwal offline
                </span>
            <?php endif; ?>
        </p>
    </div>

    <!-- Next Prayer Countdown -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center mb-6">
        <h3 class="text-lg font-semibold text-green-800 mb-2">
            <i class="fas fa-clock mr-2"></i>Sholat Selanjutnya
        </h3>
        <p class="text-green-700" id="next-prayer-countdown">Memuat...</p>
        <p class="text-xs text-green-600 mt-1" id="current-time">Memuat waktu...</p>
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
                        <i class="fas fa-building text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Gedung Serba Guna</h3>
                    <p class="text-gray-600 text-sm">Penyewaan aula masjid untuk kebutuhaan kegiatan Akad Nikah, Wisuda, dan lain lain</p>
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

    

<?php include 'partials/footer.php'; ?>

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
            
            // Initialize prayer time updates
            initializePrayerTimeUpdates();
        });

        // Prayer times data from server
        const prayerTimes = <?php echo json_encode($prayer_times_today); ?>;

        // Update current time using client time
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            });
            
            const currentTimeElement = document.getElementById('current-time');
            if (currentTimeElement) {
                currentTimeElement.textContent = `Waktu sekarang: ${timeString} WIB`;
            }
        }

        // Calculate next prayer time using client time
        function updateNextPrayer() {
            const now = new Date();
            
            // Convert to Jakarta timezone
            const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
            const currentHour = jakartaTime.getHours();
            const currentMinute = jakartaTime.getMinutes();
            const currentTimeInMinutes = currentHour * 60 + currentMinute;
            
            const prayers = [
                { name: 'Subuh', time: prayerTimes.fajr, key: 'fajr' },
                { name: 'Dzuhur', time: prayerTimes.dhuhr, key: 'dhuhr' },
                { name: 'Ashar', time: prayerTimes.asr, key: 'asr' },
                { name: 'Maghrib', time: prayerTimes.maghrib, key: 'maghrib' },
                { name: 'Isya', time: prayerTimes.isha, key: 'isha' }
            ];
            
            let nextPrayer = null;
            let isNextDay = false;
            
            // Find next prayer today
            for (let prayer of prayers) {
                const [hours, minutes] = prayer.time.split(':').map(Number);
                const prayerTimeInMinutes = hours * 60 + minutes;
                
                if (prayerTimeInMinutes > currentTimeInMinutes) {
                    nextPrayer = { 
                        ...prayer, 
                        timeInMinutes: prayerTimeInMinutes,
                        actualTime: new Date(jakartaTime.getFullYear(), jakartaTime.getMonth(), jakartaTime.getDate(), hours, minutes)
                    };
                    break;
                }
            }
            
            // If no prayer found today, next is Fajr tomorrow
            if (!nextPrayer) {
                const [hours, minutes] = prayerTimes.fajr.split(':').map(Number);
                const tomorrow = new Date(jakartaTime);
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(hours, minutes, 0, 0);
                
                nextPrayer = {
                    name: 'Subuh',
                    time: prayerTimes.fajr,
                    key: 'fajr',
                    actualTime: tomorrow,
                    isNextDay: true
                };
                isNextDay = true;
            }
            
            // Calculate countdown using actual Date objects for accuracy
            const timeDiff = nextPrayer.actualTime - jakartaTime;
            const totalMinutes = Math.floor(timeDiff / (1000 * 60));
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            
            const nextPrayerElement = document.getElementById('next-prayer-countdown');
            if (nextPrayerElement) {
                let countdownText = '';
                if (totalMinutes <= 0) {
                    countdownText = 'Sekarang waktu sholat!';
                } else if (hours > 0) {
                    countdownText = `${nextPrayer.name} dalam ${hours} jam ${minutes} menit`;
                } else {
                    countdownText = `${nextPrayer.name} dalam ${minutes} menit`;
                }
                
                if (isNextDay) {
                    countdownText += ' (besok)';
                }
                
                nextPrayerElement.textContent = countdownText;
                
                // Update styling if very close (within 5 minutes)
                if (totalMinutes <= 5 && totalMinutes > 0) {
                    nextPrayerElement.classList.add('text-red-600', 'font-bold');
                    nextPrayerElement.classList.remove('text-gray-600');
                } else {
                    nextPrayerElement.classList.remove('text-red-600', 'font-bold');
                    nextPrayerElement.classList.add('text-gray-600');
                }
            }
        }

        // Check if it's prayer time (within 5 minutes) using client time
        function checkPrayerTimeAlert() {
            const now = new Date();
            const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
            const currentHour = jakartaTime.getHours();
            const currentMinute = jakartaTime.getMinutes();
            const currentTimeInMinutes = currentHour * 60 + currentMinute;
            
            const prayers = [
                { name: 'Subuh', time: prayerTimes.fajr },
                { name: 'Dzuhur', time: prayerTimes.dhuhr },
                { name: 'Ashar', time: prayerTimes.asr },
                { name: 'Maghrib', time: prayerTimes.maghrib },
                { name: 'Isya', time: prayerTimes.isha }
            ];
            
            for (let prayer of prayers) {
                const [hours, minutes] = prayer.time.split(':').map(Number);
                const prayerTimeInMinutes = hours * 60 + minutes;
                const timeDiff = Math.abs(currentTimeInMinutes - prayerTimeInMinutes);
                
                // Alert if within 1 minute of prayer time
                if (timeDiff <= 1) {
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification(`Waktu ${prayer.name}`, {
                            body: `Sekarang waktu ${prayer.name} - ${prayer.time} WIB`,
                            icon: './assets/images/icon-192x192.png'
                        });
                    }
                    break;
                }
            }
        }

        // Initialize prayer time updates
        function initializePrayerTimeUpdates() {
            updateCurrentTime();
            updateNextPrayer();
            
            // Update every 30 seconds for more accurate countdown
            setInterval(() => {
                updateCurrentTime();
                updateNextPrayer();
                checkPrayerTimeAlert();
            }, 30000);
            
            // Check prayer time alert every minute
            setInterval(checkPrayerTimeAlert, 60000);
        }
        
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