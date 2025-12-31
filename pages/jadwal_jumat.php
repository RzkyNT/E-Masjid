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

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<!-- Custom responsive styles -->
<style>
    /* Mobile-first responsive design */
    @media (max-width: 640px) {
        .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
        
        .fc-button-group .fc-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .fc-daygrid-event {
            font-size: 0.75rem;
            padding: 1px 2px;
        }
        
        .fc-list-event-title {
            font-size: 0.875rem;
        }
        
        .fc-list-event-time {
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 480px) {
        .fc-header-toolbar h2 {
            font-size: 1.25rem;
        }
        
        .fc-button {
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
        }
        
        .fc-daygrid-day-number {
            font-size: 0.875rem;
        }
    }
    
    /* Loading animation */
    .loading-spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Smooth transitions */
    .view-transition {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
    
    .view-transition.hidden {
        opacity: 0;
        transform: translateY(10px);
    }
    
    /* Calendar loading overlay */
    .calendar-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    /* Enhanced Friday highlighting and visual indicators */
    .fc-daygrid-day.fc-day-fri {
        background-color: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        position: relative !important;
        transition: all 0.2s ease !important;
    }
    
    .fc-daygrid-day.fc-day-fri .fc-daygrid-day-number {
        color: #059669 !important;
        font-weight: 700 !important;
        background-color: #dcfce7 !important;
        border-radius: 50% !important;
        width: 24px !important;
        height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 2px auto !important;
        transition: all 0.2s ease !important;
    }
    
    /* Friday with events - enhanced highlighting */
    .fc-daygrid-day.fc-day-fri.has-events {
        background-color: #ecfdf5 !important;
        border: 2px solid #10b981 !important;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2) !important;
    }
    
    .fc-daygrid-day.fc-day-fri.has-events .fc-daygrid-day-number {
        background-color: #10b981 !important;
        color: white !important;
    }
    
    /* Visual indicators for dates with events */
    .fc-daygrid-day.has-events .event-indicator {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 10px;
        height: 10px;
        background-color: #10b981;
        border: 2px solid white;
        border-radius: 50%;
        z-index: 3;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    
    /* Hover effects for Fridays */
    .fc-daygrid-day.fc-day-fri:hover {
        background-color: #dcfce7 !important;
        cursor: pointer !important;
        transform: scale(1.02) !important;
    }
    
    .fc-daygrid-day.fc-day-fri:hover .fc-daygrid-day-number {
        background-color: #059669 !important;
        color: white !important;
        transform: scale(1.1) !important;
    }
    
    /* Enhanced tooltip styling */
    .friday-tooltip {
        position: absolute;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(0, 0, 0, 0.85));
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        max-width: 280px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        opacity: 0;
        transform: scale(0.95);
        transition: all 0.2s ease;
    }
    
    .friday-tooltip.enhanced-tooltip {
        line-height: 1.4;
    }
    
    .friday-tooltip .tooltip-header {
        font-weight: 600;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        color: #10b981;
    }
    
    .friday-tooltip .tooltip-content {
        font-size: 11px;
    }
    
    .friday-tooltip .event-item {
        margin: 6px 0;
        padding-left: 8px;
    }
    
    .friday-tooltip .event-time {
        font-weight: 600;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .friday-tooltip .event-details {
        font-size: 10px;
        opacity: 0.9;
        line-height: 1.3;
    }
    
    .friday-tooltip .event-theme {
        font-size: 10px;
        font-style: italic;
        margin-top: 2px;
        opacity: 0.8;
    }
    
    .friday-tooltip .event-notes {
        font-size: 10px;
        background: rgba(255, 255, 255, 0.1);
        padding: 3px 6px;
        border-radius: 4px;
        margin-top: 3px;
    }
    
    .friday-tooltip .no-events {
        text-align: center;
        opacity: 0.8;
        padding: 8px 0;
    }
    
    .friday-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -6px;
        border-width: 6px;
        border-style: solid;
        border-color: rgba(0, 0, 0, 0.95) transparent transparent transparent;
    }
    
    .friday-tooltip.tooltip-bottom::after {
        top: -12px;
        border-color: transparent transparent rgba(0, 0, 0, 0.95) transparent;
    }
    
    /* Friday badge styling */
    .friday-badge {
        position: absolute;
        top: 2px;
        left: 2px;
        background-color: #059669;
        color: white;
        font-size: 8px;
        font-weight: 600;
        padding: 1px 4px;
        border-radius: 4px;
        z-index: 2;
        pointer-events: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Event count badge */
    .event-count-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background-color: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 4px;
        border-radius: 8px;
        z-index: 4;
        min-width: 16px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    /* Responsive adjustments for mobile */
    @media (max-width: 640px) {
        .friday-tooltip {
            max-width: 250px;
            font-size: 11px;
            padding: 10px 12px;
        }
        
        .friday-tooltip .tooltip-content {
            font-size: 10px;
        }
        
        .friday-tooltip .event-details {
            font-size: 9px;
        }
        
        .fc-daygrid-day.fc-day-fri .fc-daygrid-day-number {
            width: 20px !important;
            height: 20px !important;
            font-size: 12px !important;
        }
        
        .friday-badge {
            font-size: 7px;
            padding: 1px 3px;
        }
        
        .event-indicator {
            width: 8px !important;
            height: 8px !important;
        }
    }
    
    /* Enhanced calendar navigation styles */
    .fc-toolbar {
        margin-bottom: 1rem !important;
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
    }
    
    .fc-toolbar-chunk {
        display: flex !important;
        align-items: center !important;
        gap: 0.25rem !important;
    }
    
    .fc-button {
        background: #059669 !important;
        border-color: #059669 !important;
        color: white !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }
    
    .fc-button:hover {
        background: #047857 !important;
        border-color: #047857 !important;
        transform: translateY(-1px) !important;
    }
    
    .fc-button:disabled {
        background: #9ca3af !important;
        border-color: #9ca3af !important;
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        transform: none !important;
    }
    
    .fc-button-active {
        background: #047857 !important;
        border-color: #047857 !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
        margin: 0 1rem !important;
    }
    
    /* Navigation animations */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes slideInFromRight {
        from { 
            opacity: 0; 
            transform: translateX(20px); 
        }
        to { 
            opacity: 1; 
            transform: translateX(0); 
        }
    }
    
    @keyframes slideInFromLeft {
        from { 
            opacity: 0; 
            transform: translateX(-20px); 
        }
        to { 
            opacity: 1; 
            transform: translateX(0); 
        }
    }
    
    .fc-daygrid-day {
        transition: all 0.2s ease !important;
    }
    
    .fc-daygrid-day:hover {
        transform: translateY(-1px) !important;
    }
    
    /* Navigation help button */
    .navigation-help-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        transition: all 0.2s ease;
        z-index: 10;
    }
    
    .navigation-help-btn:hover {
        background: #2563eb;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    /* Navigation help modal content */
    .navigation-help-content h4 {
        margin: 0 0 15px 0;
        color: #1f2937;
        font-size: 18px;
    }
    
    .help-shortcuts {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .shortcut-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #f9fafb;
        border-radius: 6px;
        border-left: 3px solid #059669;
    }
    
    .shortcut-item kbd {
        background: #374151;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        min-width: 20px;
        text-align: center;
    }
    
    .shortcut-item span {
        color: #4b5563;
        font-size: 14px;
    }
    
    .help-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 10px;
        margin: 0;
        color: #1e40af;
        font-size: 13px;
    }
    
    /* Loading states for navigation */
    .calendar-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(2px);
    }
    
    .loading-spinner {
        animation: spin 1s linear infinite;
        color: #059669;
        font-size: 24px;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Responsive navigation adjustments */
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column !important;
            align-items: center !important;
        }
        
        .fc-toolbar-chunk {
            margin: 0.25rem 0 !important;
        }
        
        .fc-toolbar-title {
            font-size: 1.25rem !important;
            margin: 0.5rem 0 !important;
        }
        
        .fc-button {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }
        
        .navigation-help-btn {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
        
        .shortcut-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
    }
</style>

<?php
// Include modal component for event details
require_once '../includes/modal_component.php';
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
    
    <!-- View Toggle -->
    <div class="mb-8 flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-1 inline-flex">
            <button id="cardViewBtn" 
                    class="px-4 sm:px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 bg-green-600 text-white shadow-sm">
                <i class="fas fa-th-large mr-1 sm:mr-2"></i><span class="hidden sm:inline">Tampilan </span>Card
            </button>
            <button id="calendarViewBtn" 
                    class="px-4 sm:px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-900">
                <i class="fas fa-calendar-alt mr-1 sm:mr-2"></i><span class="hidden sm:inline">Tampilan </span>Kalender
            </button>
        </div>
        
        <!-- Export Button -->
        <div>
            <a href="../api/friday_schedule_ical.php" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors duration-200" 
               title="Export ke Kalender">
                <i class="fas fa-download mr-1 sm:mr-2"></i><span class="hidden sm:inline">Export </span>iCal
            </a>
        </div>
    </div>
    
    <!-- Card View Container -->
    <div id="cardView" class="mb-12">
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
    
    <!-- Calendar View Container -->
    <div id="calendarView" class="hidden mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Kalender Sholat Jumat</h2>
        
        <!-- Calendar Controls -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div class="flex space-x-2">
                <button id="monthViewBtn" class="bg-green-600 text-white px-3 sm:px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                    <i class="fas fa-calendar mr-1"></i><span class="hidden sm:inline">Bulan</span>
                </button>
                <button id="listViewBtn" class="bg-gray-300 text-gray-700 px-3 sm:px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                    <i class="fas fa-list mr-1"></i><span class="hidden sm:inline">Daftar</span>
                </button>
            </div>
            
            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Terjadwal</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span>Hari Ini</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                    <span>Selesai</span>
                </div>
            </div>
        </div>
        
        <!-- Calendar Container -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <!-- Loading State -->
        <div id="calendarLoading" class="hidden text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <p class="mt-4 text-gray-600">Memuat kalender...</p>
        </div>
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

<?php
// Render event details modal
renderModal('eventDetailsModal', 'Detail Jadwal Jumat', '', '
    <button type="button" 
            onclick="closeModal(\'eventDetailsModal\')" 
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
        Tutup
    </button>
', ['size' => 'lg']);
?>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<!-- Modal JavaScript -->
<script src="../assets/js/modal.js"></script>

<!-- Additional Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const cardViewBtn = document.getElementById('cardViewBtn');
    const calendarViewBtn = document.getElementById('calendarViewBtn');
    const cardView = document.getElementById('cardView');
    const calendarView = document.getElementById('calendarView');
    const calendarLoading = document.getElementById('calendarLoading');
    
    let calendar = null;
    let currentView = 'card'; // Default view
    
    // Load saved view preference
    const savedView = localStorage.getItem('fridayScheduleView');
    if (savedView && savedView === 'calendar') {
        switchToCalendarView();
    }
    
    // Card view button click
    cardViewBtn.addEventListener('click', function() {
        if (currentView !== 'card') {
            switchToCardView();
        }
    });
    
    // Calendar view button click
    calendarViewBtn.addEventListener('click', function() {
        if (currentView !== 'calendar') {
            switchToCalendarView();
        }
    });
    
    function switchToCardView() {
        currentView = 'card';
        
        // Update button states
        cardViewBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        cardViewBtn.classList.add('bg-green-600', 'text-white', 'shadow-sm');
        
        calendarViewBtn.classList.remove('bg-green-600', 'text-white', 'shadow-sm');
        calendarViewBtn.classList.add('text-gray-600', 'hover:text-gray-900');
        
        // Smooth transition
        calendarView.classList.add('view-transition');
        setTimeout(() => {
            calendarView.classList.add('hidden');
            cardView.classList.remove('hidden');
            cardView.classList.add('view-transition');
            setTimeout(() => {
                cardView.classList.remove('view-transition');
                calendarView.classList.remove('view-transition');
            }, 50);
        }, 150);
        
        // Save preference
        localStorage.setItem('fridayScheduleView', 'card');
    }
    
    function switchToCalendarView() {
        currentView = 'calendar';
        
        // Update button states
        calendarViewBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        calendarViewBtn.classList.add('bg-green-600', 'text-white', 'shadow-sm');
        
        cardViewBtn.classList.remove('bg-green-600', 'text-white', 'shadow-sm');
        cardViewBtn.classList.add('text-gray-600', 'hover:text-gray-900');
        
        // Smooth transition
        cardView.classList.add('view-transition');
        setTimeout(() => {
            cardView.classList.add('hidden');
            calendarView.classList.remove('hidden');
            calendarView.classList.add('view-transition');
            setTimeout(() => {
                calendarView.classList.remove('view-transition');
                cardView.classList.remove('view-transition');
            }, 50);
        }, 150);
        
        // Initialize calendar if not already done
        if (!calendar) {
            initializeCalendar();
        }
        
        // Save preference
        localStorage.setItem('fridayScheduleView', 'calendar');
    }
    
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }
        
        // Show loading state
        calendarLoading.classList.remove('hidden');
        calendarEl.style.display = 'none';
        
        // Initialize FullCalendar
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            locale: 'id',
            firstDay: 1, // Monday
            height: 'auto',
            
            // Enhanced navigation configuration
            navLinks: true, // Enable date/week names to be clickable
            navLinkDayClick: function(date, jsEvent) {
                // Handle day click navigation
                handleDayNavigation(date, jsEvent);
            },
            
            // Custom navigation buttons
            customButtons: {
                prevYear: {
                    text: '‹‹',
                    hint: 'Tahun Sebelumnya',
                    click: function() {
                        navigateToYear(-1);
                    }
                },
                nextYear: {
                    text: '››',
                    hint: 'Tahun Berikutnya', 
                    click: function() {
                        navigateToYear(1);
                    }
                },
                currentMonth: {
                    text: 'Bulan Ini',
                    hint: 'Kembali ke Bulan Ini',
                    click: function() {
                        navigateToCurrentMonth();
                    }
                }
            },
            
            // Enhanced header with year navigation
            headerToolbar: {
                left: 'prevYear,prev,currentMonth,next,nextYear',
                center: 'title',
                right: 'today dayGridMonth,listMonth'
            },
            
            // Navigation state management
            datesSet: function(dateInfo) {
                handleDateRangeChange(dateInfo);
            },
            
            loading: function(isLoading) {
                handleCalendarLoading(isLoading);
            },
                } else {
                    calendarLoading.classList.add('hidden');
                }
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                // Show loading state
                calendarLoading.classList.remove('hidden');
                
                // Fetch events from API
                fetch('../api/friday_schedule_events.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Hide loading state
                        calendarLoading.classList.add('hidden');
                        calendarEl.style.display = 'block';
                        
                        if (data.success) {
                            successCallback(data.events);
                        } else {
                            throw new Error(data.message || 'Failed to fetch events');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching events:', error);
                        calendarLoading.classList.add('hidden');
                        calendarEl.style.display = 'block';
                        
                        // Show error message
                        showCalendarError('Gagal memuat data kalender. Silakan coba lagi.');
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            eventDidMount: function(info) {
                // Add tooltip
                info.el.setAttribute('title', info.event.title);
                
                // Add responsive classes
                if (window.innerWidth < 640) {
                    info.el.classList.add('text-xs');
                }
            },
            dayCellDidMount: function(info) {
                // Enhanced Friday highlighting with comprehensive visual indicators
                if (info.date.getDay() === 5) {
                    info.el.classList.add('fc-day-fri');
                    
                    // Apply Friday-specific styling
                    applyFridayHighlighting(info.el, info.date);
                    
                    // Add interactive hover effects with tooltips
                    setupFridayInteractions(info.el, info.date);
                }
                
                // Add visual indicators for dates with events (after events are loaded)
                setTimeout(() => {
                    addEventIndicators(info.el, info.date);
                }, 100);
            },
            eventContent: function(arg) {
                // Custom event rendering for mobile
                if (window.innerWidth < 640) {
                    return {
                        html: `<div class="text-xs truncate">${arg.event.title}</div>`
                    };
                }
                return { html: arg.event.title };
            }
        });
        
        calendar.render();
        
        // Initialize enhanced navigation features
        setupKeyboardNavigation();
        restoreNavigationState();
        
        // Add navigation help tooltip
        addNavigationHelp();
        
        // Calendar view toggle buttons
        const monthViewBtn = document.getElementById('monthViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        
        monthViewBtn.addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
            updateCalendarViewButtons('month');
        });
        
        listViewBtn.addEventListener('click', function() {
            calendar.changeView('listMonth');
            updateCalendarViewButtons('list');
        });
        
        // Handle window resize for responsive behavior
        window.addEventListener('resize', function() {
            if (calendar) {
                calendar.updateSize();
            }
        });
    }
    
    function showCalendarError(message) {
        const calendarContainer = document.querySelector('#calendarView .bg-white');
        if (calendarContainer) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4';
            errorDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            calendarContainer.insertBefore(errorDiv, calendarContainer.firstChild);
        }
    }
    
    function updateCalendarViewButtons(activeView) {
        const monthViewBtn = document.getElementById('monthViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        
        monthViewBtn.classList.remove('bg-green-600', 'text-white');
        monthViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        listViewBtn.classList.remove('bg-green-600', 'text-white');
        listViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        
        if (activeView === 'month') {
            monthViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
            monthViewBtn.classList.add('bg-green-600', 'text-white');
        } else {
            listViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
            listViewBtn.classList.add('bg-green-600', 'text-white');
        }
    }
    
    // Show event details in modal
    function showEventDetails(event) {
        const eventData = event.extendedProps;
        
        // Update modal title
        document.getElementById('eventDetailsModal-title').textContent = `Sholat Jumat - ${formatDate(event.start)}`;
        
        // Update modal content
        document.getElementById('eventDetailsModal-body').innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Waktu Sholat</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-clock mr-2 text-green-600"></i>
                            <span class="text-lg font-medium">${eventData.prayer_time} WIB</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(eventData.status)}">
                            ${getStatusLabel(eventData.status)}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Imam</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            <span>${eventData.imam_name}</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Khotib</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-microphone mr-2 text-purple-600"></i>
                            <span>${eventData.khotib_name}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Tema Khutbah</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-medium text-gray-900 mb-2">${eventData.khutbah_theme}</h5>
                        ${eventData.khutbah_description ? `<p class="text-gray-600 text-sm">${eventData.khutbah_description}</p>` : ''}
                    </div>
                </div>
                
                ${eventData.special_notes ? `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Catatan Khusus</h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">${eventData.special_notes}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Lokasi</h4>
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        <span>${eventData.location}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Show modal
        openModal('eventDetailsModal');
    }
    
    // Enhanced calendar navigation functions
    function handleDayNavigation(date, jsEvent) {
        // Prevent default navigation if not a Friday
        if (date.getDay() !== 5) {
            jsEvent.preventDefault();
            showNotification('Navigasi hari hanya tersedia untuk hari Jumat', 'info');
            return;
        }
        
        // Navigate to the specific Friday and highlight it
        calendar.gotoDate(date);
        
        // Show Friday details if available
        setTimeout(() => {
            const dateStr = date.toISOString().split('T')[0];
            const dayEvents = calendar.getEvents().filter(event => 
                event.startStr === dateStr
            );
            
            if (dayEvents.length > 0) {
                showEventDetails(dayEvents[0]);
            } else {
                showNotification(`Tidak ada jadwal untuk Jumat, ${formatDate(date)}`, 'info');
            }
        }, 300);
    }
    
    function navigateToYear(direction) {
        const currentDate = calendar.getDate();
        const newDate = new Date(currentDate);
        newDate.setFullYear(currentDate.getFullYear() + direction);
        
        // Validate year range (don't go too far back or forward)
        const currentYear = new Date().getFullYear();
        const targetYear = newDate.getFullYear();
        
        if (targetYear < currentYear - 2) {
            showNotification('Tidak dapat navigasi lebih dari 2 tahun ke belakang', 'warning');
            return;
        }
        
        if (targetYear > currentYear + 5) {
            showNotification('Tidak dapat navigasi lebih dari 5 tahun ke depan', 'warning');
            return;
        }
        
        // Perform navigation with loading state
        showNavigationLoading(true);
        calendar.gotoDate(newDate);
        
        // Log navigation for analytics
        console.log(`Navigated to year: ${targetYear}`);
    }
    
    function navigateToCurrentMonth() {
        const today = new Date();
        showNavigationLoading(true);
        calendar.gotoDate(today);
        
        // Highlight today if it's visible
        setTimeout(() => {
            highlightToday();
        }, 300);
        
        console.log('Navigated to current month');
    }
    
    function handleDateRangeChange(dateInfo) {
        // Update navigation state
        const { start, end, view } = dateInfo;
        
        // Store current view state
        const navigationState = {
            start: start.toISOString(),
            end: end.toISOString(),
            view: view.type,
            timestamp: Date.now()
        };
        
        // Save to localStorage for persistence
        localStorage.setItem('fridayCalendarNavigation', JSON.stringify(navigationState));
        
        // Update URL without reload (for bookmarking)
        updateNavigationURL(dateInfo);
        
        // Preload events for the new date range
        preloadEventsForRange(start, end);
        
        // Update navigation buttons state
        updateNavigationButtonsState(dateInfo);
        
        console.log(`Calendar view changed: ${view.type}, Range: ${start.toDateString()} - ${end.toDateString()}`);
    }
    
    function handleCalendarLoading(isLoading) {
        showNavigationLoading(isLoading);
        
        if (isLoading) {
            console.log('Calendar loading events...');
        } else {
            console.log('Calendar loading complete');
            
            // Re-apply Friday highlighting after events load
            setTimeout(() => {
                reapplyFridayHighlighting();
            }, 100);
        }
    }
    
    function showNavigationLoading(show) {
        if (show) {
            calendarLoading.classList.remove('hidden');
            calendarEl.style.opacity = '0.7';
            document.body.style.cursor = 'wait';
        } else {
            calendarLoading.classList.add('hidden');
            calendarEl.style.opacity = '1';
            document.body.style.cursor = 'default';
        }
    }
    
    function updateNavigationURL(dateInfo) {
        const { start, view } = dateInfo;
        const url = new URL(window.location);
        
        // Update URL parameters
        url.searchParams.set('month', start.getMonth() + 1);
        url.searchParams.set('year', start.getFullYear());
        url.searchParams.set('view', view.type);
        
        // Update URL without reload
        window.history.replaceState({}, '', url);
    }
    
    function preloadEventsForRange(start, end) {
        // This could be enhanced to preload events for adjacent months
        // For now, we'll just ensure current range is loaded
        
        const startStr = start.toISOString().split('T')[0];
        const endStr = end.toISOString().split('T')[0];
        
        console.log(`Preloading events for range: ${startStr} to ${endStr}`);
        
        // The calendar will automatically fetch events for the visible range
        // We could implement additional caching here if needed
    }
    
    function updateNavigationButtonsState(dateInfo) {
        const { start } = dateInfo;
        const currentYear = new Date().getFullYear();
        const viewYear = start.getFullYear();
        
        // Update custom button states based on current view
        const prevYearBtn = document.querySelector('.fc-prevYear-button');
        const nextYearBtn = document.querySelector('.fc-nextYear-button');
        const currentMonthBtn = document.querySelector('.fc-currentMonth-button');
        
        if (prevYearBtn) {
            prevYearBtn.disabled = (viewYear <= currentYear - 2);
            prevYearBtn.style.opacity = prevYearBtn.disabled ? '0.5' : '1';
        }
        
        if (nextYearBtn) {
            nextYearBtn.disabled = (viewYear >= currentYear + 5);
            nextYearBtn.style.opacity = nextYearBtn.disabled ? '0.5' : '1';
        }
        
        if (currentMonthBtn) {
            const isCurrentMonth = (
                start.getMonth() === new Date().getMonth() && 
                start.getFullYear() === new Date().getFullYear()
            );
            currentMonthBtn.style.opacity = isCurrentMonth ? '0.5' : '1';
        }
    }
    
    function reapplyFridayHighlighting() {
        // Re-apply Friday highlighting after navigation
        const dayElements = document.querySelectorAll('.fc-daygrid-day');
        
        dayElements.forEach(dayEl => {
            const dateAttr = dayEl.getAttribute('data-date');
            if (dateAttr) {
                const date = new Date(dateAttr + 'T00:00:00');
                if (date.getDay() === 5) { // Friday
                    // Re-apply Friday highlighting
                    applyFridayHighlighting(dayEl, date);
                    setupFridayInteractions(dayEl, date);
                    
                    // Re-apply event indicators
                    setTimeout(() => {
                        addEventIndicators(dayEl, date);
                    }, 50);
                }
            }
        });
    }
    
    function highlightToday() {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        const todayElement = document.querySelector(`[data-date="${todayStr}"]`);
        
        if (todayElement) {
            todayElement.style.boxShadow = '0 0 10px rgba(59, 130, 246, 0.5)';
            todayElement.style.transform = 'scale(1.02)';
            
            // Remove highlight after 2 seconds
            setTimeout(() => {
                todayElement.style.boxShadow = '';
                todayElement.style.transform = '';
            }, 2000);
        }
    }
    
    // Restore navigation state on page load
    function restoreNavigationState() {
        try {
            const savedState = localStorage.getItem('fridayCalendarNavigation');
            if (savedState) {
                const state = JSON.parse(savedState);
                
                // Check if state is recent (within 24 hours)
                const stateAge = Date.now() - state.timestamp;
                if (stateAge < 24 * 60 * 60 * 1000) {
                    const targetDate = new Date(state.start);
                    calendar.gotoDate(targetDate);
                    
                    if (state.view !== 'dayGridMonth') {
                        calendar.changeView(state.view);
                    }
                    
                    console.log('Restored navigation state:', state);
                }
            }
        } catch (error) {
            console.warn('Failed to restore navigation state:', error);
        }
    }
    
    // Keyboard navigation support
    function setupKeyboardNavigation() {
        document.addEventListener('keydown', function(event) {
            // Only handle keyboard navigation when calendar is visible
            if (currentView !== 'calendar' || !calendar) return;
            
            // Prevent default for navigation keys
            const navigationKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
            if (navigationKeys.includes(event.key)) {
                event.preventDefault();
            }
            
            switch (event.key) {
                case 'ArrowLeft':
                    if (event.ctrlKey) {
                        calendar.prev(); // Previous month
                    }
                    break;
                case 'ArrowRight':
                    if (event.ctrlKey) {
                        calendar.next(); // Next month
                    }
                    break;
                case 'ArrowUp':
                    if (event.ctrlKey) {
                        navigateToYear(-1); // Previous year
                    }
                    break;
                case 'ArrowDown':
                    if (event.ctrlKey) {
                        navigateToYear(1); // Next year
                    }
                    break;
                case 'Home':
                    navigateToCurrentMonth();
                    break;
                case 'End':
                    // Navigate to next Friday
                    navigateToNextFriday();
                    break;
            }
        });
    }
    
    function navigateToNextFriday() {
        const today = new Date();
        const dayOfWeek = today.getDay();
        const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
        const nextFriday = new Date(today);
        nextFriday.setDate(today.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
        
        calendar.gotoDate(nextFriday);
        
        // Highlight the Friday
        setTimeout(() => {
            const fridayStr = nextFriday.toISOString().split('T')[0];
            const fridayElement = document.querySelector(`[data-date="${fridayStr}"]`);
            if (fridayElement) {
                fridayElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                highlightElement(fridayElement);
            }
        }, 300);
    }
    
    function highlightElement(element) {
        element.style.animation = 'pulse 1s ease-in-out 3';
        setTimeout(() => {
            element.style.animation = '';
        }, 3000);
    }
    
    function addNavigationHelp() {
        // Add keyboard navigation help
        const helpButton = document.createElement('button');
        helpButton.innerHTML = '<i class="fas fa-keyboard"></i>';
        helpButton.className = 'navigation-help-btn';
        helpButton.title = 'Bantuan Navigasi Keyboard';
        helpButton.onclick = showNavigationHelp;
        
        const calendarContainer = document.querySelector('#calendarView .bg-white');
        if (calendarContainer) {
            calendarContainer.style.position = 'relative';
            calendarContainer.appendChild(helpButton);
        }
    }
    
    function showNavigationHelp() {
        const helpContent = `
            <div class="navigation-help-content">
                <h4>🎹 Navigasi Keyboard</h4>
                <div class="help-shortcuts">
                    <div class="shortcut-item">
                        <kbd>Ctrl</kbd> + <kbd>←</kbd> <span>Bulan Sebelumnya</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Ctrl</kbd> + <kbd>→</kbd> <span>Bulan Berikutnya</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Ctrl</kbd> + <kbd>↑</kbd> <span>Tahun Sebelumnya</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Ctrl</kbd> + <kbd>↓</kbd> <span>Tahun Berikutnya</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Home</kbd> <span>Kembali ke Bulan Ini</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>End</kbd> <span>Jumat Berikutnya</span>
                    </div>
                </div>
                <p class="help-note">💡 Klik pada tanggal Jumat untuk melihat detail jadwal</p>
            </div>
        `;
        
        // Update modal content
        document.getElementById('eventDetailsModal-title').textContent = 'Bantuan Navigasi Kalender';
        document.getElementById('eventDetailsModal-body').innerHTML = helpContent;
        
        // Show modal
        openModal('eventDetailsModal');
    }

    // Enhanced Friday highlighting and visual indicator functions
    function applyFridayHighlighting(dayElement, date) {
        // Base Friday styling
        dayElement.style.backgroundColor = '#f0fdf4';
        dayElement.style.border = '1px solid #bbf7d0';
        dayElement.style.position = 'relative';
        
        // Enhanced day number styling for Fridays
        const dayNumber = dayElement.querySelector('.fc-daygrid-day-number');
        if (dayNumber) {
            dayNumber.style.color = '#059669';
            dayNumber.style.fontWeight = '700';
            dayNumber.style.backgroundColor = '#dcfce7';
            dayNumber.style.borderRadius = '50%';
            dayNumber.style.width = '24px';
            dayNumber.style.height = '24px';
            dayNumber.style.display = 'flex';
            dayNumber.style.alignItems = 'center';
            dayNumber.style.justifyContent = 'center';
            dayNumber.style.margin = '2px auto';
            dayNumber.style.transition = 'all 0.2s ease';
        }
        
        // Add Friday indicator badge
        const fridayBadge = document.createElement('div');
        fridayBadge.className = 'friday-badge';
        fridayBadge.style.cssText = `
            position: absolute;
            top: 2px;
            left: 2px;
            background-color: #059669;
            color: white;
            font-size: 8px;
            font-weight: 600;
            padding: 1px 4px;
            border-radius: 4px;
            z-index: 2;
            pointer-events: none;
        `;
        fridayBadge.textContent = 'JUM';
        dayElement.appendChild(fridayBadge);
    }
    
    function setupFridayInteractions(dayElement, date) {
        // Enhanced hover effects for Fridays
        dayElement.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#dcfce7';
            this.style.cursor = 'pointer';
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'all 0.2s ease';
            
            const dayNumber = this.querySelector('.fc-daygrid-day-number');
            if (dayNumber) {
                dayNumber.style.backgroundColor = '#059669';
                dayNumber.style.color = 'white';
                dayNumber.style.transform = 'scale(1.1)';
            }
            
            // Show enhanced tooltip for Friday dates
            showFridayTooltip(this, date);
        });
        
        dayElement.addEventListener('mouseleave', function() {
            // Check if this Friday has events for proper styling restoration
            const dateStr = date.toISOString().split('T')[0];
            const hasEvents = calendar && calendar.getEvents().some(event => 
                event.startStr === dateStr
            );
            
            if (hasEvents) {
                this.style.backgroundColor = '#ecfdf5';
                this.style.border = '2px solid #10b981';
            } else {
                this.style.backgroundColor = '#f0fdf4';
                this.style.border = '1px solid #bbf7d0';
            }
            
            this.style.transform = 'scale(1)';
            
            const dayNumber = this.querySelector('.fc-daygrid-day-number');
            if (dayNumber) {
                if (hasEvents) {
                    dayNumber.style.backgroundColor = '#10b981';
                    dayNumber.style.color = 'white';
                } else {
                    dayNumber.style.backgroundColor = '#dcfce7';
                    dayNumber.style.color = '#059669';
                }
                dayNumber.style.transform = 'scale(1)';
            }
            
            // Hide tooltip
            hideFridayTooltip();
        });
    }
    
    function addEventIndicators(dayElement, date) {
        const dateStr = date.toISOString().split('T')[0];
        const dayEvents = calendar ? calendar.getEvents().filter(event => 
            event.startStr === dateStr
        ) : [];
        
        if (dayEvents.length > 0) {
            dayElement.classList.add('has-events');
            
            // Remove existing indicators to avoid duplicates
            const existingIndicators = dayElement.querySelectorAll('.event-indicator');
            existingIndicators.forEach(indicator => indicator.remove());
            
            // Add event indicator dot
            const indicator = document.createElement('div');
            indicator.className = 'event-indicator';
            indicator.style.cssText = `
                position: absolute;
                top: 4px;
                right: 4px;
                width: 10px;
                height: 10px;
                background-color: #10b981;
                border: 2px solid white;
                border-radius: 50%;
                z-index: 3;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            `;
            dayElement.appendChild(indicator);
            
            // Enhanced styling for Fridays with events
            if (date.getDay() === 5) {
                dayElement.style.backgroundColor = '#ecfdf5';
                dayElement.style.border = '2px solid #10b981';
                dayElement.style.boxShadow = '0 2px 4px rgba(16, 185, 129, 0.2)';
                
                const dayNumber = dayElement.querySelector('.fc-daygrid-day-number');
                if (dayNumber) {
                    dayNumber.style.backgroundColor = '#10b981';
                    dayNumber.style.color = 'white';
                }
                
                // Add event count badge for multiple events
                if (dayEvents.length > 1) {
                    const countBadge = document.createElement('div');
                    countBadge.className = 'event-count-badge';
                    countBadge.style.cssText = `
                        position: absolute;
                        top: -2px;
                        right: -2px;
                        background-color: #ef4444;
                        color: white;
                        font-size: 10px;
                        font-weight: 600;
                        padding: 2px 4px;
                        border-radius: 8px;
                        z-index: 4;
                        min-width: 16px;
                        text-align: center;
                    `;
                    countBadge.textContent = dayEvents.length;
                    indicator.appendChild(countBadge);
                }
            }
        }
    }
    
    // Enhanced Friday tooltip functions
    let currentTooltip = null;
    
    function showFridayTooltip(element, date) {
        // Remove existing tooltip
        hideFridayTooltip();
        
        // Get events for this date
        const dateStr = date.toISOString().split('T')[0];
        const dayEvents = calendar ? calendar.getEvents().filter(event => 
            event.startStr === dateStr
        ) : [];
        
        // Create enhanced tooltip content
        let tooltipContent = `<div class="tooltip-header"><strong>Jumat, ${formatDate(date)}</strong></div>`;
        
        if (dayEvents.length > 0) {
            tooltipContent += `<div class="tooltip-content">`;
            
            dayEvents.forEach((event, index) => {
                const props = event.extendedProps;
                const statusIcon = getStatusIcon(props.status);
                const statusColor = getStatusColor(props.status);
                
                tooltipContent += `
                    <div class="event-item" style="border-left: 3px solid ${statusColor}; padding-left: 8px; margin: 4px 0;">
                        <div class="event-time">
                            <i class="fas fa-clock"></i> ${props.prayer_time || '12:00'} WIB ${statusIcon}
                        </div>
                        <div class="event-details">
                            <i class="fas fa-user"></i> Imam: ${props.imam_name || 'TBD'}<br>
                            <i class="fas fa-microphone"></i> Khotib: ${props.khotib_name || 'TBD'}
                        </div>
                        ${props.khutbah_theme ? `<div class="event-theme"><i class="fas fa-book"></i> ${props.khutbah_theme}</div>` : ''}
                        ${props.special_notes ? `<div class="event-notes"><i class="fas fa-info-circle"></i> ${props.special_notes}</div>` : ''}
                    </div>
                `;
                
                if (index < dayEvents.length - 1) {
                    tooltipContent += `<hr style="margin: 8px 0; border-color: rgba(255,255,255,0.2);">`;
                }
            });
            
            tooltipContent += `</div>`;
        } else {
            tooltipContent += `
                <div class="tooltip-content">
                    <div class="no-events">
                        <i class="fas fa-calendar-plus"></i> Belum ada jadwal
                        <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">Klik untuk menambah jadwal</div>
                    </div>
                </div>
            `;
        }
        
        // Create enhanced tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'friday-tooltip enhanced-tooltip';
        tooltip.innerHTML = tooltipContent;
        
        // Position tooltip with smart positioning
        const rect = element.getBoundingClientRect();
        const tooltipWidth = 280;
        const tooltipHeight = 120; // Estimated
        
        let left = rect.left + rect.width / 2;
        let top = rect.top - 10;
        let transformX = '-50%';
        let transformY = '-100%';
        
        // Adjust horizontal position if tooltip goes off screen
        if (left - tooltipWidth / 2 < 10) {
            left = rect.left;
            transformX = '0%';
        } else if (left + tooltipWidth / 2 > window.innerWidth - 10) {
            left = rect.right;
            transformX = '-100%';
        }
        
        // Adjust vertical position if tooltip goes off screen
        if (top - tooltipHeight < 10) {
            top = rect.bottom + 10;
            transformY = '0%';
            
            // Update arrow direction for bottom positioning
            tooltip.classList.add('tooltip-bottom');
        }
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        tooltip.style.transform = `translateX(${transformX}) translateY(${transformY})`;
        
        document.body.appendChild(tooltip);
        currentTooltip = tooltip;
        
        // Add fade-in animation
        setTimeout(() => {
            tooltip.style.opacity = '1';
            tooltip.style.transform += ' scale(1)';
        }, 10);
    }
    
    function hideFridayTooltip() {
        if (currentTooltip) {
            currentTooltip.style.opacity = '0';
            currentTooltip.style.transform += ' scale(0.95)';
            setTimeout(() => {
                if (currentTooltip && currentTooltip.parentNode) {
                    currentTooltip.remove();
                }
                currentTooltip = null;
            }, 200);
        }
    }
    
    function getStatusIcon(status) {
        const icons = {
            'scheduled': '<i class="fas fa-clock" style="color: #10b981;"></i>',
            'completed': '<i class="fas fa-check-circle" style="color: #6b7280;"></i>',
            'cancelled': '<i class="fas fa-times-circle" style="color: #ef4444;"></i>'
        };
        return icons[status] || icons['scheduled'];
    }
    
    function getStatusColor(status) {
        const colors = {
            'scheduled': '#10b981',
            'completed': '#6b7280',
            'cancelled': '#ef4444'
        };
        return colors[status] || colors['scheduled'];
    }

    // Friday tooltip functions
    let currentTooltip = null;
    
    function showFridayTooltip(element, date) {
        // Remove existing tooltip
        hideFridayTooltip();
        
        // Get events for this date
        const dateStr = date.toISOString().split('T')[0];
        const dayEvents = calendar.getEvents().filter(event => 
            event.startStr === dateStr
        );
        
        // Create tooltip content
        let tooltipContent = `<strong>Jumat, ${formatDate(date)}</strong>`;
        
        if (dayEvents.length > 0) {
            const event = dayEvents[0];
            const props = event.extendedProps;
            tooltipContent += `<br>`;
            tooltipContent += `<i class="fas fa-clock"></i> ${props.prayer_time} WIB<br>`;
            tooltipContent += `<i class="fas fa-user"></i> Imam: ${props.imam_name}<br>`;
            tooltipContent += `<i class="fas fa-microphone"></i> Khotib: ${props.khotib_name}`;
            
            if (props.khutbah_theme) {
                tooltipContent += `<br><i class="fas fa-book"></i> ${props.khutbah_theme}`;
            }
        } else {
            tooltipContent += `<br><em>Belum ada jadwal</em>`;
        }
        
        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'friday-tooltip';
        tooltip.innerHTML = tooltipContent;
        
        // Position tooltip
        const rect = element.getBoundingClientRect();
        tooltip.style.left = (rect.left + rect.width / 2) + 'px';
        tooltip.style.top = (rect.top - 10) + 'px';
        tooltip.style.transform = 'translateX(-50%) translateY(-100%)';
        
        document.body.appendChild(tooltip);
        currentTooltip = tooltip;
        
        // Adjust position if tooltip goes off screen
        const tooltipRect = tooltip.getBoundingClientRect();
        if (tooltipRect.left < 10) {
            tooltip.style.left = '10px';
            tooltip.style.transform = 'translateY(-100%)';
        } else if (tooltipRect.right > window.innerWidth - 10) {
            tooltip.style.left = (window.innerWidth - 10) + 'px';
            tooltip.style.transform = 'translateX(-100%) translateY(-100%)';
        }
    }
    
    function hideFridayTooltip() {
        if (currentTooltip) {
            currentTooltip.remove();
            currentTooltip = null;
        }
    }
    
    // Helper functions
    function formatDate(date) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return date.toLocaleDateString('id-ID', options);
    }
    
    function getStatusClass(status) {
        const classes = {
            'scheduled': 'bg-green-100 text-green-800',
            'completed': 'bg-gray-100 text-gray-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }
    
    function getStatusLabel(status) {
        const labels = {
            'scheduled': 'Terjadwal',
            'completed': 'Selesai',
            'cancelled': 'Dibatalkan'
        };
        return labels[status] || 'Tidak Diketahui';
    }
    
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
            if (currentView === 'calendar' && calendar) {
                calendar.refetchEvents();
            } else {
                location.reload();
            }
        }
    }, 1800000); // 30 minutes
});
</script>

<?php include '../partials/footer.php'; ?>