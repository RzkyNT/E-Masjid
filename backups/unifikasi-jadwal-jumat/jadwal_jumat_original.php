<?php
// Add cache-busting headers for development
require_once '../includes/settings_loader.php';

require_once '../config/config.php';

$page_title = 'Jadwal Sholat Jumat';
$page_description = 'Jadwal sholat Jumat, imam, khotib, dan tema khutbah di Masjid Jami Al-Muhajirin';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Breadcrumb
$breadcrumb = [
    ['title' => 'Jadwal Sholat Jumat', 'url' => '']
];

// Get current and upcoming Friday schedules
try {
    // Get next 8 Friday schedules
    $stmt = $pdo->prepare("
        SELECT 
            id,
            friday_date,
            prayer_time,
            imam_name,
            khotib_name,
            khutbah_theme,
            khutbah_description,
            location,
            special_notes,
            status,
            CASE 
                WHEN friday_date = CURDATE() THEN 'today'
                WHEN friday_date > CURDATE() THEN 'upcoming'
                ELSE 'past'
            END as schedule_status
        FROM friday_schedules 
        WHERE friday_date >= CURDATE() 
        ORDER BY friday_date ASC 
        LIMIT 8
    ");
    $stmt->execute();
    $friday_schedules = $stmt->fetchAll();
    
    // Get today's schedule if it's Friday
    $today_schedule = null;
    if (date('N') == 5) { // Friday
        $stmt = $pdo->prepare("
            SELECT * FROM friday_schedules 
            WHERE friday_date = CURDATE()
        ");
        $stmt->execute();
        $today_schedule = $stmt->fetch();
    }
    
    // Get next Friday's schedule
    $next_friday = date('Y-m-d', strtotime('next friday'));
    $stmt = $pdo->prepare("
        SELECT * FROM friday_schedules 
        WHERE friday_date = ?
    ");
    $stmt->execute([$next_friday]);
    $next_friday_schedule = $stmt->fetch();
    
} catch (PDOException $e) {
    $friday_schedules = [];
    $today_schedule = null;
    $next_friday_schedule = null;
    error_log("Friday schedule error: " . $e->getMessage());
}

// Helper function to format Indonesian day names
function getIndonesianDayName($date) {
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin', 
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    return $days[date('l', strtotime($date))];
}

// Helper function to format Indonesian month names
function getIndonesianDate($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $day = date('j', strtotime($date));
    $month = $months[date('n', strtotime($date))];
    $year = date('Y', strtotime($date));
    
    return "$day $month $year";
}

include '../partials/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-calendar-alt text-4xl"></i>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Jadwal Sholat Jumat</h1>
            <p class="text-xl text-green-100 max-w-3xl mx-auto">
                Jadwal lengkap sholat Jumat dengan imam, khotib, dan tema khutbah di <?php echo htmlspecialchars($settings['site_name']); ?>
            </p>
        </div>
    </div>
</div>

<!-- Today's Schedule (if Friday) -->
<?php if ($today_schedule): ?>
<div class="bg-blue-50 border-l-4 border-blue-400 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-clock text-blue-400 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h2 class="text-2xl font-bold text-blue-900 mb-2">Sholat Jumat Hari Ini</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-blue-800">
                    <div>
                        <p class="font-semibold">Waktu Sholat</p>
                        <p class="text-lg"><?php echo date('H:i', strtotime($today_schedule['prayer_time'])); ?> WIB</p>
                    </div>
                    <div>
                        <p class="font-semibold">Imam</p>
                        <p><?php echo htmlspecialchars($today_schedule['imam_name']); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Khotib</p>
                        <p><?php echo htmlspecialchars($today_schedule['khotib_name']); ?></p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="font-semibold text-blue-900">Tema Khutbah:</p>
                    <p class="text-blue-800"><?php echo htmlspecialchars($today_schedule['khutbah_theme']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Next Friday Highlight -->
<?php if ($next_friday_schedule && !$today_schedule): ?>
<div class="bg-green-50 border-l-4 border-green-400 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar-check text-green-400 text-2xl"></i>
            </div>
            <div class="ml-4">
                <h2 class="text-2xl font-bold text-green-900 mb-2">Sholat Jumat Mendatang</h2>
                <p class="text-green-700 mb-3">
                    <?php echo getIndonesianDayName($next_friday_schedule['friday_date']); ?>, 
                    <?php echo getIndonesianDate($next_friday_schedule['friday_date']); ?>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-green-800">
                    <div>
                        <p class="font-semibold">Waktu Sholat</p>
                        <p class="text-lg"><?php echo date('H:i', strtotime($next_friday_schedule['prayer_time'])); ?> WIB</p>
                    </div>
                    <div>
                        <p class="font-semibold">Imam</p>
                        <p><?php echo htmlspecialchars($next_friday_schedule['imam_name']); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold">Khotib</p>
                        <p><?php echo htmlspecialchars($next_friday_schedule['khotib_name']); ?></p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="font-semibold text-green-900">Tema Khutbah:</p>
                    <p class="text-green-800"><?php echo htmlspecialchars($next_friday_schedule['khutbah_theme']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Schedule Cards -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Jadwal Sholat Jumat</h2>
        
        <?php if (!empty($friday_schedules)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($friday_schedules as $schedule): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200 <?php echo $schedule['schedule_status'] === 'today' ? 'ring-2 ring-blue-500' : ''; ?>">
                        <div class="p-6">
                            <!-- Date Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-green-100 rounded-full p-2 mr-3">
                                        <i class="fas fa-calendar text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?php echo getIndonesianDayName($schedule['friday_date']); ?>
                                        </h3>
                                        <p class="text-gray-600">
                                            <?php echo getIndonesianDate($schedule['friday_date']); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($schedule['schedule_status'] === 'today'): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        Hari Ini
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Prayer Time -->
                            <div class="mb-4">
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-clock mr-2 text-green-600"></i>
                                    <span class="font-medium">Waktu Sholat:</span>
                                    <span class="ml-2 text-lg font-semibold">
                                        <?php echo date('H:i', strtotime($schedule['prayer_time'])); ?> WIB
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Imam and Khotib -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Imam</p>
                                    <p class="text-gray-900 font-medium">
                                        <?php echo htmlspecialchars($schedule['imam_name']); ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Khotib</p>
                                    <p class="text-gray-900 font-medium">
                                        <?php echo htmlspecialchars($schedule['khotib_name']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Khutbah Theme -->
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500 mb-2">Tema Khutbah</p>
                                <h4 class="text-gray-900 font-semibold mb-2">
                                    <?php echo htmlspecialchars($schedule['khutbah_theme']); ?>
                                </h4>
                                <?php if (!empty($schedule['khutbah_description'])): ?>
                                    <p class="text-gray-600 text-sm">
                                        <?php echo htmlspecialchars($schedule['khutbah_description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Special Notes -->
                            <?php if (!empty($schedule['special_notes'])): ?>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <?php echo htmlspecialchars($schedule['special_notes']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Location -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($schedule['location']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Jadwal</h3>
                <p class="text-gray-600">Jadwal sholat Jumat akan segera diumumkan.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Information Section -->
    <div class="bg-gray-50 rounded-lg p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Informasi Sholat Jumat</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Waktu Pelaksanaan</h4>
                <p class="text-gray-600 text-sm">
                    Sholat Jumat dilaksanakan setiap hari Jumat pukul 12:00 WIB atau sesuai jadwal yang tertera
                </p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Jamaah</h4>
                <p class="text-gray-600 text-sm">
                    Terbuka untuk seluruh umat Muslim. Diharapkan hadir 15 menit sebelum waktu sholat
                </p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-microphone text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Khutbah</h4>
                <p class="text-gray-600 text-sm">
                    Khutbah disampaikan dalam bahasa Indonesia dengan tema yang bervariasi setiap minggunya
                </p>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900 mb-3">Lokasi</h4>
                <div class="flex items-center justify-center text-gray-600">
                    <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                    <span><?php echo htmlspecialchars($settings['site_name']); ?></span>
                </div>
                <?php 
                $contact_info = getContactInfo();
                if (!empty($contact_info['address'])): 
                ?>
                <p class="text-gray-600 text-sm mt-2">
                    <?php echo htmlspecialchars($contact_info['address']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Additional Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight today's schedule if it's Friday
    const today = new Date();
    if (today.getDay() === 5) { // Friday
        const todayCards = document.querySelectorAll('[data-date="' + today.toISOString().split('T')[0] + '"]');
        todayCards.forEach(card => {
            card.classList.add('ring-2', 'ring-blue-500');
        });
    }
    
    // Auto-refresh page every 30 minutes to keep schedule updated
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 1800000); // 30 minutes
});
</script>

<?php include '../partials/footer.php'; ?>