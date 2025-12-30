<?php
require_once '../config/config.php';

$page_title = 'Jadwal Sholat';
$page_description = 'Jadwal waktu sholat harian dan bulanan untuk wilayah Bekasi';
$base_url = '..';

// Get current date
$current_date = date('Y-m-d');
$current_month = date('n');
$current_year = date('Y');

// Prayer times (static for now - would integrate with API in production)
$prayer_times_today = [
    'imsak' => '04:20',
    'fajr' => '04:30',
    'sunrise' => '05:45',
    'dhuha' => '06:30',
    'dhuhr' => '12:15',
    'asr' => '15:30',
    'maghrib' => '18:45',
    'isha' => '20:00'
];

// Generate monthly prayer times (simplified - would use actual API)
$monthly_prayer_times = [];
$days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));

for ($day = 1; $day <= $days_in_month; $day++) {
    $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
    $monthly_prayer_times[$date] = [
        'imsak' => '04:20',
        'fajr' => '04:30',
        'sunrise' => '05:45',
        'dhuha' => '06:30',
        'dhuhr' => '12:15',
        'asr' => '15:30',
        'maghrib' => '18:45',
        'isha' => '20:00'
    ];
}

// Breadcrumb
$breadcrumb = [
    ['title' => 'Jadwal Sholat', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Jadwal Sholat</h1>
            <p class="text-xl opacity-90 mb-2">Wilayah Bekasi Utara, Jawa Barat</p>
            <p class="text-lg opacity-75" id="current-date"><?php echo date('l, d F Y'); ?></p>
            <p class="text-sm opacity-75 mt-2" id="current-time"></p>
        </div>
    </div>
</section>

<!-- Today's Prayer Times -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Jadwal Sholat Hari Ini</h2>
            <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
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
                <p class="text-xl font-bold" id="next-prayer-name">Dzuhur</p>
                <p class="text-sm" id="next-prayer-time">dalam <span id="countdown">2 jam 30 menit</span></p>
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
        <div class="text-center mt-8">
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
        </div>
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
                            <p class="text-gray-600">-6.2088° S, 107.0139° E</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-globe text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Zona Waktu</h4>
                            <p class="text-gray-600">WIB (UTC+7)</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-compass text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Arah Kiblat</h4>
                            <p class="text-gray-600">295° dari Utara</p>
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
                            Sholat Jumat dimulai pukul 12:00 WIB
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
// Prayer times data
const prayerTimes = <?php echo json_encode($prayer_times_today); ?>;

// Update current time
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('current-time').textContent = `Waktu sekarang: ${timeString} WIB`;
}

// Calculate next prayer time
function updateNextPrayer() {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    
    const prayers = [
        { name: 'Subuh', time: prayerTimes.fajr, key: 'fajr' },
        { name: 'Dzuhur', time: prayerTimes.dhuhr, key: 'dhuhr' },
        { name: 'Ashar', time: prayerTimes.asr, key: 'asr' },
        { name: 'Maghrib', time: prayerTimes.maghrib, key: 'maghrib' },
        { name: 'Isya', time: prayerTimes.isha, key: 'isha' }
    ];
    
    let nextPrayer = null;
    
    for (let prayer of prayers) {
        const [hours, minutes] = prayer.time.split(':').map(Number);
        const prayerTime = hours * 60 + minutes;
        
        if (prayerTime > currentTime) {
            nextPrayer = { ...prayer, timeInMinutes: prayerTime };
            break;
        }
    }
    
    // If no prayer found today, next is Fajr tomorrow
    if (!nextPrayer) {
        const [hours, minutes] = prayerTimes.fajr.split(':').map(Number);
        nextPrayer = {
            name: 'Subuh',
            time: prayerTimes.fajr,
            key: 'fajr',
            timeInMinutes: (hours * 60 + minutes) + (24 * 60) // Add 24 hours
        };
    }
    
    // Calculate countdown
    const timeDiff = nextPrayer.timeInMinutes - currentTime;
    const hours = Math.floor(timeDiff / 60);
    const minutes = timeDiff % 60;
    
    document.getElementById('next-prayer-name').textContent = nextPrayer.name;
    
    let countdownText = '';
    if (hours > 0) {
        countdownText = `${hours} jam ${minutes} menit`;
    } else {
        countdownText = `${minutes} menit`;
    }
    
    document.getElementById('countdown').textContent = countdownText;
}

// Highlight current prayer time
function highlightCurrentPrayer() {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    
    // Remove existing highlights
    document.querySelectorAll('[data-prayer]').forEach(el => {
        el.parentElement.parentElement.classList.remove('ring-2', 'ring-yellow-400');
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
        const prayerTime = hours * 60 + minutes;
        
        const nextPrayerTime = i < prayers.length - 1 ? 
            (() => {
                const [h, m] = prayers[i + 1].time.split(':').map(Number);
                return h * 60 + m;
            })() : 24 * 60; // End of day
        
        if (currentTime >= prayerTime && currentTime < nextPrayerTime) {
            const element = document.querySelector(`[data-prayer="${prayers[i].key}"]`);
            if (element) {
                element.parentElement.parentElement.classList.add();
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
    
    // Here you would typically fetch new prayer times for the selected month
    // For now, we'll just show a loading message
    console.log(`Loading prayer times for ${monthNames[currentMonth - 1]} ${currentYear}`);
}

// Download functions
function downloadPDF() {
    // This would generate and download a PDF
    alert('Fitur download PDF akan segera tersedia');
}

function downloadExcel() {
    // This would generate and download an Excel file
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
    
    // Update every minute
    setInterval(() => {
        updateCurrentTime();
        updateNextPrayer();
        highlightCurrentPrayer();
    }, 60000);
});

// Notification permission (for future prayer time notifications)
if ('Notification' in window && 'serviceWorker' in navigator) {
    // Request notification permission
    Notification.requestPermission();
}
</script>

<?php include '../partials/footer.php'; ?>