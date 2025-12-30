<?php
require_once '../config/config.php';
require_once '../includes/settings_loader.php';
require_once '../includes/prayer_myquran_api.php';

$page_title = 'Jadwal Sholat';
$page_description = 'Jadwal waktu sholat harian dan bulanan untuk wilayah Bekasi';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Get prayer settings
$prayer_settings = getPrayerSettings();

// Get current date
$current_date = date('Y-m-d');
$current_month = date('n');
$current_year = date('Y');

// Get today's prayer times from API
$today_prayer_api = getTodayPrayerSchedule();
$prayer_times_today = [];
$api_success = false;

if ($today_prayer_api && $today_prayer_api['status']) {
    $jadwal = $today_prayer_api['data']['jadwal'];
    $prayer_times_today = [
        'imsak' => $jadwal['imsak'],
        'fajr' => $jadwal['subuh'],
        'sunrise' => $jadwal['terbit'],
        'dhuha' => $jadwal['dhuha'],
        'dhuhr' => $jadwal['dzuhur'],
        'asr' => $jadwal['ashar'],
        'maghrib' => $jadwal['maghrib'],
        'isha' => $jadwal['isya']
    ];
    $api_success = true;
    $location_info = $today_prayer_api['data']['lokasi'] . ', ' . $today_prayer_api['data']['daerah'];
} else {
    // Fallback to static times if API fails
    $prayer_times_today = getFallbackPrayerTimes();
    $location_info = getSiteSetting('location_name');
}

// Get monthly prayer times from API
$monthly_prayer_api = getMonthlyPrayerSchedule($current_year, $current_month);
$monthly_prayer_times = [];

if ($monthly_prayer_api && $monthly_prayer_api['status']) {
    foreach ($monthly_prayer_api['data']['jadwal'] as $day_schedule) {
        $date = $day_schedule['date'];
        $monthly_prayer_times[$date] = [
            'imsak' => $day_schedule['imsak'],
            'fajr' => $day_schedule['subuh'],
            'sunrise' => $day_schedule['terbit'],
            'dhuha' => $day_schedule['dhuha'],
            'dhuhr' => $day_schedule['dzuhur'],
            'asr' => $day_schedule['ashar'],
            'maghrib' => $day_schedule['maghrib'],
            'isha' => $day_schedule['isya']
        ];
    }
} else {
    // Generate fallback monthly prayer times
    $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
        $monthly_prayer_times[$date] = getFallbackPrayerTimes();
    }
}

// Breadcrumb
$breadcrumb = [
    ['title' => 'Jadwal Sholat', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<!-- <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Jadwal Sholat</h1>
            <p class="text-xl opacity-90 mb-2"><?php echo htmlspecialchars($location_info); ?></p>
            <!-- <p class="text-lg opacity-75" id="current-date"><?php echo date('l, d F Y'); ?></p>
            <p class="text-sm opacity-75 mt-2" id="current-time"></p> -->
            <!-- <?php if (!$api_success): ?>
                <div class="mt-4 bg-yellow-500 bg-opacity-20 border border-yellow-300 rounded-lg p-3 inline-block">
                    <p class="text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Menggunakan jadwal offline - API tidak tersedia
                    </p>
                </div>
            <?php else: ?>
                <div class="mt-4 bg-green-500 bg-opacity-20 border border-green-300 rounded-lg p-3 inline-block">
                    <p class="text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        Data terbaru dari MyQuran API
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div> -->
<!-- </section> -->

<!-- Today's Prayer Times -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Jadwal Sholat Hari Ini</h2>
            <p class="text-xl opacity-90 mb-2"><?php echo htmlspecialchars($location_info); ?></p>
               <p class="text-lg opacity-75" id="current-date"><?php echo date('l, d F Y'); ?></p>
            <p class="text-sm opacity-75 mt-2" id="current-time"></p>
            <!-- <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p> -->
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <!-- Subuh -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-moon text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Subuh</h3>
                <p class="text-2xl font-bold" data-prayer="fajr"><?php echo $prayer_times_today['fajr']; ?></p>
                <p class="text-xs opacity-80 mt-1">Fajr</p>
            </div>
            
            <!-- Terbit -->
            <div class="bg-gradient-to-br from-yellow-400 to-orange-500 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-sun text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Terbit</h3>
                <p class="text-2xl font-bold" data-prayer="sunrise"><?php echo $prayer_times_today['sunrise']; ?></p>
                <p class="text-xs opacity-80 mt-1">Sunrise</p>
            </div>
            
            <!-- Dzuhur -->
            <div class="bg-gradient-to-br from-orange-500 to-red-500 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-sun text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Dzuhur</h3>
                <p class="text-2xl font-bold" data-prayer="dhuhr"><?php echo $prayer_times_today['dhuhr']; ?></p>
                <p class="text-xs opacity-80 mt-1">Dhuhr</p>
            </div>
            
            <!-- Ashar -->
            <div class="bg-gradient-to-br from-red-500 to-pink-500 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-cloud-sun text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Ashar</h3>
                <p class="text-2xl font-bold" data-prayer="asr"><?php echo $prayer_times_today['asr']; ?></p>
                <p class="text-xs opacity-80 mt-1">Asr</p>
            </div>
            
            <!-- Maghrib -->
            <div class="bg-gradient-to-br from-pink-500 to-purple-500 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-moon text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Maghrib</h3>
                <p class="text-2xl font-bold" data-prayer="maghrib"><?php echo $prayer_times_today['maghrib']; ?></p>
                <p class="text-xs opacity-80 mt-1">Maghrib</p>
            </div>
            
            <!-- Isya -->
            <div class="bg-gradient-to-br from-purple-600 to-indigo-600 text-white p-6 rounded-xl text-center shadow-lg">
                <div class="mb-2">
                    <i class="fas fa-star text-2xl opacity-80"></i>
                </div>
                <h3 class="font-semibold mb-1">Isya</h3>
                <p class="text-2xl font-bold" data-prayer="isha"><?php echo $prayer_times_today['isha']; ?></p>
                <p class="text-xs opacity-80 mt-1">Isha</p>
            </div>
        </div>
        
        <!-- Next Prayer Countdown -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <h3 class="text-lg font-semibold text-green-800 mb-2">Sholat Selanjutnya</h3>
            <div id="next-prayer-info" class="text-green-700">
                <p class="text-xl font-bold" id="next-prayer-name">Memuat...</p>
                <p class="text-sm" id="next-prayer-time">dalam <span id="countdown">memuat...</span></p>
            </div>
        </div>
    </div>
</section>

<!-- Monthly Prayer Schedule -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Jadwal Sholat Bulanan</h2>
            <div class="flex justify-center items-center space-x-4 mb-6">
                <button onclick="changeMonth(-1)" class="bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h3 class="text-xl font-semibold text-gray-800" id="month-year">
                    <?php echo date('F Y'); ?>
                </h3>
                <button onclick="changeMonth(1)" class="bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Imsak</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Subuh</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Terbit</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Dhuha</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Dzuhur</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ashar</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Maghrib</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Isya</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="prayer-schedule-body">
                        <?php foreach ($monthly_prayer_times as $date => $times): ?>
                        <?php 
                        $day_name = date('D', strtotime($date));
                        $day_num = date('j', strtotime($date));
                        $is_today = $date === $current_date;
                        $is_friday = $day_name === 'Fri';
                        ?>
                        <tr class="<?php echo $is_today ? 'bg-green-50 border-l-4 border-green-500' : ''; ?> <?php echo $is_friday && !$is_today ? 'bg-blue-50' : ''; ?>">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $day_name; ?>, <?php echo $day_num; ?>
                                        <?php if ($is_today): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Hari ini
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($is_friday && !$is_today): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Jumat
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['imsak']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['fajr']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['sunrise']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['dhuha']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['dhuhr']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['asr']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['maghrib']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-gray-900"><?php echo $times['isha']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Download Options -->
        <!-- <div class="text-center mt-8">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="downloadPDF()" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </button>
                <button onclick="downloadExcel()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-file-excel mr-2"></i>Download Excel
                </button>
                <button onclick="printSchedule()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-print mr-2"></i>Cetak Jadwal
                </button>
            </div>
        </div> -->
    </div>
</section>

<!-- Information Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Informasi Lokasi -->
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Informasi Lokasi</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-green-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Koordinat</h4>
                            <p class="text-gray-600"><?php echo getSiteSetting('coordinates_lat'); ?>° S, <?php echo getSiteSetting('coordinates_lng'); ?>° E</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-globe text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Zona Waktu</h4>
                            <p class="text-gray-600"><?php echo getSiteSetting('timezone'); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-compass text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Arah Kiblat</h4>
                            <p class="text-gray-600"><?php echo getSiteSetting('qibla_direction'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Catatan Penting -->
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Catatan Penting</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="space-y-3 text-sm text-yellow-800">
                        <p class="flex items-start">
                            <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                            Jadwal sholat dihitung berdasarkan koordinat Masjid Al-Muhajirin
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-clock mr-2 mt-0.5"></i>
                            Waktu iqamah biasanya 10-15 menit setelah adzan
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-calendar mr-2 mt-0.5"></i>
                            Jadwal dapat berubah 1-2 menit setiap harinya
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-mosque mr-2 mt-0.5"></i>
                            Sholat Jumat dimulai pukul <?php echo getSiteSetting('jumat_time'); ?>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-moon mr-2 mt-0.5"></i>
                            Imsak adalah waktu mulai puasa (10 menit sebelum Subuh)
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-sun mr-2 mt-0.5"></i>
                            Dhuha dimulai 15 menit setelah matahari terbit
                        </p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="font-medium text-gray-900 mb-3">Aplikasi Mobile</h4>
                    <p class="text-gray-600 text-sm mb-4">Download aplikasi untuk notifikasi waktu sholat:</p>
                    <div class="flex space-x-3">
                        <a href="#" class="bg-black text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800 transition duration-200">
                            <i class="fab fa-apple mr-2"></i>App Store
                        </a>
                        <a href="#" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition duration-200">
                            <i class="fab fa-google-play mr-2"></i>Play Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gray-50, .bg-white {
        background: white !important;
    }
    
    .shadow-md, .shadow-lg {
        box-shadow: none !important;
    }
    
    table {
        page-break-inside: avoid;
    }
}
</style>

<script>
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
    
    const dateString = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        timeZone: 'Asia/Jakarta'
    });
    
    document.getElementById('current-time').textContent = `Waktu sekarang: ${timeString} WIB`;
    document.getElementById('current-date').textContent = dateString;
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
    
    document.getElementById('next-prayer-name').textContent = nextPrayer.name;
    
    let countdownText = '';
    if (totalMinutes <= 0) {
        countdownText = 'Sekarang waktu sholat!';
    } else if (hours > 0) {
        countdownText = `${hours} jam ${minutes} menit`;
    } else {
        countdownText = `${minutes} menit`;
    }
    
    if (isNextDay) {
        countdownText += ' (besok)';
    }
    
    document.getElementById('countdown').textContent = countdownText;
    
    // Update prayer time indicator if very close (within 5 minutes)
    if (totalMinutes <= 5 && totalMinutes > 0) {
        document.getElementById('next-prayer-info').classList.add('animate-pulse');
        document.getElementById('next-prayer-info').classList.remove('text-green-700');
        document.getElementById('next-prayer-info').classList.add('text-red-700');
    } else {
        document.getElementById('next-prayer-info').classList.remove('animate-pulse');
        document.getElementById('next-prayer-info').classList.remove('text-red-700');
        document.getElementById('next-prayer-info').classList.add('text-green-700');
    }
}

// Highlight current prayer time using client time
function highlightCurrentPrayer() {
    const now = new Date();
    const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
    const currentHour = jakartaTime.getHours();
    const currentMinute = jakartaTime.getMinutes();
    const currentTimeInMinutes = currentHour * 60 + currentMinute;
    
    // Remove existing highlights
    document.querySelectorAll('[data-prayer]').forEach(el => {
        el.parentElement.parentElement.classList.remove('ring-4', 'ring-yellow-400', 'ring-opacity-75');
    });
    
    // Find current prayer period
    const prayers = [
        { key: 'fajr', time: prayerTimes.fajr },
        { key: 'dhuhr', time: prayerTimes.dhuhr },
        { key: 'asr', time: prayerTimes.asr },
        { key: 'maghrib', time: prayerTimes.maghrib },
        { key: 'isha', time: prayerTimes.isha }
    ];
    
    for (let i = 0; i < prayers.length; i++) {
        const [hours, minutes] = prayers[i].time.split(':').map(Number);
        const prayerTimeInMinutes = hours * 60 + minutes;
        
        const nextPrayerTimeInMinutes = i < prayers.length - 1 ? 
            (() => {
                const [h, m] = prayers[i + 1].time.split(':').map(Number);
                return h * 60 + m;
            })() : 24 * 60; // End of day
        
        if (currentTimeInMinutes >= prayerTimeInMinutes && currentTimeInMinutes < nextPrayerTimeInMinutes) {
            const element = document.querySelector(`[data-prayer="${prayers[i].key}"]`);
            if (element) {
                element.parentElement.parentElement.classList.add('ring-4', 'ring-yellow-400', 'ring-opacity-75');
            }
            break;
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
                    icon: '../assets/images/icon-192x192.png'
                });
            }
            break;
        }
    }
}

// Month navigation
let currentMonth = <?php echo $current_month; ?>;
let currentYear = <?php echo $current_year; ?>;

function changeMonth(direction) {
    currentMonth += direction;
    
    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    } else if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }
    
    // Update month display
    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    document.getElementById('month-year').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    
    // Fetch new prayer times for the selected month
    loadMonthlyPrayerTimes(currentYear, currentMonth);
}

// Load monthly prayer times via AJAX
async function loadMonthlyPrayerTimes(year, month) {
    try {
        const response = await fetch(`../api/prayer_times.php?action=monthly&year=${year}&month=${month}`);
        const data = await response.json();
        
        if (data.success && data.data && data.data.status) {
            updateMonthlyTable(data.data.data.jadwal);
        } else {
            console.error('Failed to load monthly prayer times');
        }
    } catch (error) {
        console.error('Error loading monthly prayer times:', error);
    }
}

// Update monthly table with new data
function updateMonthlyTable(jadwalData) {
    const tbody = document.getElementById('prayer-schedule-body');
    tbody.innerHTML = '';
    
    const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD format
    
    jadwalData.forEach(dayData => {
        const date = dayData.date;
        const dayName = new Date(date).toLocaleDateString('id-ID', { weekday: 'short' });
        const dayNum = new Date(date).getDate();
        const isToday = date === today;
        const isFriday = new Date(date).getDay() === 5;
        
        const row = document.createElement('tr');
        row.className = `${isToday ? 'bg-green-50 border-l-4 border-green-500' : ''} ${isFriday && !isToday ? 'bg-blue-50' : ''}`;
        
        row.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">
                        ${dayName}, ${dayNum}
                        ${isToday ? '<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Hari ini</span>' : ''}
                        ${isFriday && !isToday ? '<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Jumat</span>' : ''}
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.imsak}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.subuh}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.terbit}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.dhuha}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.dzuhur}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.ashar}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.maghrib}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-900">${dayData.isya}</td>
        `;
        
        tbody.appendChild(row);
    });
}

// Download functions
function downloadPDF() {
    alert('Fitur download PDF akan segera tersedia');
}

function downloadExcel() {
    alert('Fitur download Excel akan segera tersedia');
}

function printSchedule() {
    window.print();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    updateNextPrayer();
    highlightCurrentPrayer();
    
    // Update every 30 seconds for more accurate countdown
    setInterval(() => {
        updateCurrentTime();
        updateNextPrayer();
        highlightCurrentPrayer();
        checkPrayerTimeAlert();
    }, 30000);
    
    // Check prayer time alert every minute
    setInterval(checkPrayerTimeAlert, 60000);
    
    // Request notification permission on page load
    if ('Notification' in window && Notification.permission === 'default') {
        setTimeout(() => {
            if (confirm('Aktifkan notifikasi untuk pengingat waktu sholat?')) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Notification permission granted');
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                        successMsg.textContent = 'Notifikasi waktu sholat telah diaktifkan!';
                        document.body.appendChild(successMsg);
                        setTimeout(() => successMsg.remove(), 3000);
                    }
                });
            }
        }, 3000); // Wait 3 seconds before asking
    }
});
</script>

<?php include '../partials/footer.php'; ?>