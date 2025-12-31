<?php
// Add cache-busting headers for admin pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';
require_once '../../includes/modal_component.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$view = $_GET['view'] ?? 'calendar'; // Default to calendar view
$action = $_GET['action'] ?? 'list';
$schedule_id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        try {
            if ($action === 'add') {
                // Add new Friday schedule
                $stmt = $pdo->prepare("
                    INSERT INTO friday_schedules 
                    (friday_date, prayer_time, imam_name, khotib_name, khutbah_theme, khutbah_description, location, special_notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['friday_date'],
                    $_POST['prayer_time'],
                    $_POST['imam_name'],
                    $_POST['khotib_name'],
                    $_POST['khutbah_theme'],
                    $_POST['khutbah_description'],
                    $_POST['location'],
                    $_POST['special_notes'],
                    $current_user['id']
                ]);
                
                logActivity($current_user['id'], 'friday_schedule_created', 'Created Friday schedule', [
                    'friday_date' => $_POST['friday_date'],
                    'theme' => $_POST['khutbah_theme']
                ]);
                
                $success_message = 'Jadwal Jumat berhasil ditambahkan.';
                $action = 'list';
                
            } elseif ($action === 'edit' && $schedule_id) {
                // Update Friday schedule
                $stmt = $pdo->prepare("
                    UPDATE friday_schedules 
                    SET friday_date = ?, prayer_time = ?, imam_name = ?, khotib_name = ?, 
                        khutbah_theme = ?, khutbah_description = ?, location = ?, special_notes = ?, 
                        status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['friday_date'],
                    $_POST['prayer_time'],
                    $_POST['imam_name'],
                    $_POST['khotib_name'],
                    $_POST['khutbah_theme'],
                    $_POST['khutbah_description'],
                    $_POST['location'],
                    $_POST['special_notes'],
                    $_POST['status'],
                    $schedule_id
                ]);
                
                logActivity($current_user['id'], 'friday_schedule_updated', 'Updated Friday schedule', [
                    'schedule_id' => $schedule_id,
                    'friday_date' => $_POST['friday_date']
                ]);
                
                $success_message = 'Jadwal Jumat berhasil diperbarui.';
                $action = 'list';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error_message = 'Jadwal untuk tanggal tersebut sudah ada.';
            } else {
                $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $schedule_id && hasPermission($current_user['role'], 'masjid_content', 'delete')) {
    try {
        $stmt = $pdo->prepare("DELETE FROM friday_schedules WHERE id = ?");
        $stmt->execute([$schedule_id]);
        
        logActivity($current_user['id'], 'friday_schedule_deleted', 'Deleted Friday schedule', [
            'schedule_id' => $schedule_id
        ]);
        
        $success_message = 'Jadwal Jumat berhasil dihapus.';
        $action = 'list';
    } catch (PDOException $e) {
        $error_message = 'Gagal menghapus jadwal: ' . $e->getMessage();
    }
}

// Get data for forms
if ($action === 'edit' && $schedule_id) {
    $stmt = $pdo->prepare("SELECT * FROM friday_schedules WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule_data = $stmt->fetch();
    
    if (!$schedule_data) {
        $error_message = 'Jadwal tidak ditemukan.';
        $action = 'list';
    }
}

// Get speakers for dropdown
$stmt = $pdo->prepare("SELECT name, role FROM friday_speakers WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$speakers = $stmt->fetchAll();

// Get themes for dropdown
$stmt = $pdo->prepare("SELECT theme_title FROM khutbah_themes WHERE is_active = 1 ORDER BY usage_count DESC, theme_title");
$stmt->execute();
$themes = $stmt->fetchAll();

// Get schedules for list view
if ($action === 'list') {
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT 
            id, friday_date, prayer_time, imam_name, khotib_name, 
            khutbah_theme, status, created_at,
            CASE 
                WHEN friday_date = CURDATE() THEN 'today'
                WHEN friday_date > CURDATE() THEN 'upcoming'
                ELSE 'past'
            END as schedule_status
        FROM friday_schedules 
        ORDER BY friday_date DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $schedules = $stmt->fetchAll();
    
    // Get total count for pagination
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM friday_schedules");
    $stmt->execute();
    $total_schedules = $stmt->fetchColumn();
    $total_pages = ceil($total_schedules / $limit);
}

$page_title = 'Kelola Jadwal Jumat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    
    <style>
        /* Custom FullCalendar styling */
        .fc {
            font-family: inherit;
        }
        
        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #1f2937;
        }
        
        .fc-button-primary {
            background-color: #059669 !important;
            border-color: #059669 !important;
        }
        
        .fc-button-primary:hover {
            background-color: #047857 !important;
            border-color: #047857 !important;
        }
        
        .fc-button-primary:disabled {
            background-color: #9ca3af !important;
            border-color: #9ca3af !important;
        }
        
        .fc-daygrid-day.fc-day-fri {
            background-color: #f0fdf4 !important;
            border: 2px solid #bbf7d0 !important;
            position: relative !important;
            transition: all 0.2s ease !important;
        }
        
        .fc-daygrid-day.fc-day-fri .fc-daygrid-day-number {
            color: #059669 !important;
            font-weight: 700 !important;
            background-color: #dcfce7 !important;
            border-radius: 50% !important;
            width: 28px !important;
            height: 28px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 3px auto !important;
            transition: all 0.2s ease !important;
            font-size: 14px !important;
        }
        
        /* Enhanced Friday with events styling for admin */
        .fc-daygrid-day.fc-day-fri.has-events {
            background-color: #ecfdf5 !important;
            border-color: #10b981 !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2) !important;
        }
        
        .fc-daygrid-day.fc-day-fri.has-events .fc-daygrid-day-number {
            background-color: #10b981 !important;
            color: white !important;
        }
        
        /* Admin Friday hover effects */
        .fc-daygrid-day.fc-day-fri:hover {
            background-color: #dcfce7 !important;
            border-color: #10b981 !important;
            cursor: pointer !important;
            transform: scale(1.02) !important;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3) !important;
        }
        
        .fc-daygrid-day.fc-day-fri:hover .fc-daygrid-day-number {
            background-color: #059669 !important;
            color: white !important;
            transform: scale(1.1) !important;
        }
        
        /* Admin Friday badges and indicators */
        .admin-friday-badge {
            position: absolute;
            top: 3px;
            left: 3px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 6px;
            z-index: 2;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .admin-add-indicator {
            position: absolute;
            bottom: 3px;
            right: 3px;
            background-color: #3b82f6;
            color: white;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 4px;
            z-index: 2;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .admin-event-indicator {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 12px;
            height: 12px;
            background-color: #10b981;
            border: 2px solid white;
            border-radius: 50%;
            z-index: 3;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .admin-event-count-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            background-color: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 10px;
            z-index: 4;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        /* Enhanced admin tooltip styling */
        .admin-friday-tooltip {
            position: absolute;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.95), rgba(0, 0, 0, 0.85));
            color: white;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
            max-width: 320px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.2s ease;
        }
        
        .admin-friday-tooltip.enhanced-admin-tooltip {
            line-height: 1.4;
        }
        
        .admin-friday-tooltip .admin-tooltip-header {
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: #10b981;
            font-size: 13px;
        }
        
        .admin-friday-tooltip .admin-tooltip-content {
            font-size: 11px;
        }
        
        .admin-friday-tooltip .admin-event-item {
            margin: 8px 0;
            padding-left: 10px;
        }
        
        .admin-friday-tooltip .admin-event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .admin-friday-tooltip .admin-event-time {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .admin-friday-tooltip .admin-event-status {
            font-size: 14px;
        }
        
        .admin-friday-tooltip .admin-event-details {
            font-size: 10px;
            opacity: 0.9;
            line-height: 1.4;
            margin: 4px 0;
        }
        
        .admin-friday-tooltip .admin-event-details div {
            margin: 2px 0;
        }
        
        .admin-friday-tooltip .admin-event-theme {
            font-size: 10px;
            font-style: italic;
            margin-top: 4px;
            opacity: 0.8;
        }
        
        .admin-friday-tooltip .admin-event-notes {
            font-size: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
            margin-top: 4px;
        }
        
        .admin-friday-tooltip .admin-no-events {
            text-align: center;
            opacity: 0.8;
            padding: 10px 0;
        }
        
        .admin-friday-tooltip .admin-add-prompt {
            font-size: 10px;
            color: #3b82f6;
            margin-top: 4px;
            font-weight: 600;
        }
        
        .admin-friday-tooltip .admin-past-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .admin-friday-tooltip .admin-tooltip-actions {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            font-size: 10px;
            color: #3b82f6;
            font-weight: 600;
        }
        
        .admin-friday-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -8px;
            border-width: 8px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.95) transparent transparent transparent;
        }
        
        .admin-friday-tooltip.admin-tooltip-bottom::after {
            top: -16px;
            border-color: transparent transparent rgba(0, 0, 0, 0.95) transparent;
        }
        
        /* Admin navigation animations */
        @keyframes adminPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(5, 150, 105, 0.8); }
        }
        
        .fc-toolbar {
            margin-bottom: 1.5rem !important;
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        
        .fc-button {
            transition: all 0.2s ease !important;
        }
        
        .fc-button:hover {
            transform: translateY(-1px) !important;
        }
        
        .fc-button:disabled {
            transform: none !important;
        }
        
        /* Admin calendar loading overlay */
        .admin-calendar-loading-overlay {
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
        
        /* Admin calendar stats */
        .admin-calendar-stats {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .stat-value {
            color: #059669;
            font-weight: 600;
            font-size: 16px;
        }
        
        .fc-event {
            background-color: #059669 !important;
            border-color: #047857 !important;
            cursor: pointer;
        }
        
        .fc-event:hover {
            background-color: #047857 !important;
        }
        
        .fc-daygrid-day:hover {
            background-color: #f9fafb;
            cursor: pointer;
        }
        
        .fc-daygrid-day.fc-day-fri:hover {
            background-color: #ecfdf5 !important;
        }
        
        /* View toggle styling */
        .view-toggle {
            display: inline-flex;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            padding: 0.25rem;
        }
        
        .view-toggle button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .view-toggle button.active {
            background-color: white;
            color: #059669;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .view-toggle button:not(.active) {
            color: #6b7280;
        }
        
        .view-toggle button:not(.active):hover {
            color: #374151;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-alt text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Kelola Jadwal Jumat</h1>
                        <p class="text-sm text-gray-500">Manajemen jadwal sholat Jumat</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../pages/jadwal_jumat.php" target="_blank" class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Publik
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                <?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?>
                            </div>
                            <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                
                <a href="berita.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-images mr-3"></i>Kelola Galeri
                </a>
                
                <a href="jadwal_jumat.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-calendar-alt mr-3"></i>Jadwal Jumat
                </a>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Kelola Donasi
                </a>
                
                <a href="konten.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-file-alt mr-3"></i>Kelola Konten
                </a>
                
                <a href="pengaturan.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-cog mr-3"></i>Pengaturan
                </a>
                
                <div class="border-t border-gray-700 mt-4 pt-4">
                    <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                        <i class="fas fa-arrow-left mr-3"></i>Kembali ke Dashboard
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- View Toggle -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Kelola Jadwal Jumat</h3>
                            
                            <div class="flex items-center space-x-4">
                                <!-- View Toggle -->
                                <div class="view-toggle">
                                    <button type="button" 
                                            onclick="switchView('calendar')" 
                                            class="<?php echo $view === 'calendar' ? 'active' : ''; ?>"
                                            id="calendar-view-btn">
                                        <i class="fas fa-calendar-alt mr-1"></i>Kalender
                                    </button>
                                    <button type="button" 
                                            onclick="switchView('list')" 
                                            class="<?php echo $view === 'list' ? 'active' : ''; ?>"
                                            id="list-view-btn">
                                        <i class="fas fa-list mr-1"></i>Daftar
                                    </button>
                                </div>
                                
                                <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                                    <button type="button" 
                                            onclick="openAddScheduleModal()" 
                                            class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Calendar View -->
                        <div id="calendar-view" class="<?php echo $view === 'calendar' ? '' : 'hidden'; ?>">
                            <div id="calendar" class="bg-white rounded-lg"></div>
                            <div class="mt-4 text-sm text-gray-600">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-green-600 rounded mr-2"></div>
                                        <span>Jadwal Terjadwal</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-600 rounded mr-2"></div>
                                        <span>Jadwal Selesai</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-red-600 rounded mr-2"></div>
                                        <span>Jadwal Dibatalkan</span>
                                    </div>
                                </div>
                                <p class="mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Klik pada tanggal Jumat untuk menambah jadwal baru. Klik pada jadwal yang ada untuk mengedit.
                                </p>
                            </div>
                        </div>
                        
                        <!-- List View -->
                        <div id="list-view" class="<?php echo $view === 'list' ? '' : 'hidden'; ?>">
                        <?php if (!empty($schedules)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imam</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khotib</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tema</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($schedules as $schedule): ?>
                                            <tr class="<?php echo $schedule['schedule_status'] === 'today' ? 'bg-blue-50' : ''; ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo date('d/m/Y', strtotime($schedule['friday_date'])); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo date('l', strtotime($schedule['friday_date'])); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('H:i', strtotime($schedule['prayer_time'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($schedule['imam_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($schedule['khotib_name']); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($schedule['khutbah_theme']); ?>">
                                                        <?php echo htmlspecialchars($schedule['khutbah_theme']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php
                                                    $status_classes = [
                                                        'scheduled' => 'bg-yellow-100 text-yellow-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'cancelled' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $status_labels = [
                                                        'scheduled' => 'Terjadwal',
                                                        'completed' => 'Selesai',
                                                        'cancelled' => 'Dibatalkan'
                                                    ];
                                                    ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_classes[$schedule['status']]; ?>">
                                                        <?php echo $status_labels[$schedule['status']]; ?>
                                                    </span>
                                                    <?php if ($schedule['schedule_status'] === 'today'): ?>
                                                        <div class="text-xs text-blue-600 font-medium mt-1">Hari Ini</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <?php if (hasPermission($current_user['role'], 'masjid_content', 'update')): ?>
                                                            <button type="button" 
                                                                    onclick="openEditScheduleModal(<?php echo htmlspecialchars(json_encode($schedule)); ?>)" 
                                                                    class="text-indigo-600 hover:text-indigo-900">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (hasPermission($current_user['role'], 'masjid_content', 'delete')): ?>
                                                            <button type="button" 
                                                                    onclick="confirmDeleteSchedule(<?php echo $schedule['id']; ?>)" 
                                                                    class="text-red-600 hover:text-red-900">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="mt-6 flex justify-center">
                                    <nav class="flex space-x-2">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="?view=list&page=<?php echo $i; ?>" 
                                               class="px-3 py-2 text-sm <?php echo $page == $i ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> border rounded-md">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Jadwal</h3>
                                <p class="text-gray-600 mb-4">Mulai dengan menambahkan jadwal sholat Jumat pertama.</p>
                                <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                                    <button type="button" 
                                            onclick="openAddScheduleModal()" 
                                            class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                            <?php echo $action === 'add' ? 'Tambah Jadwal Jumat' : 'Edit Jadwal Jumat'; ?>
                        </h3>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Friday Date -->
                                <div>
                                    <label for="friday_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Jumat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" 
                                           id="friday_date" 
                                           name="friday_date" 
                                           value="<?php echo $action === 'edit' ? $schedule_data['friday_date'] : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required>
                                </div>
                                
                                <!-- Prayer Time -->
                                <div>
                                    <label for="prayer_time" class="block text-sm font-medium text-gray-700 mb-2">
                                        Waktu Sholat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" 
                                           id="prayer_time" 
                                           name="prayer_time" 
                                           value="<?php echo $action === 'edit' ? $schedule_data['prayer_time'] : '12:00'; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Imam -->
                                <div>
                                    <label for="imam_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Imam <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="imam_name" 
                                           name="imam_name" 
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule_data['imam_name']) : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required
                                           list="imam_list">
                                    <datalist id="imam_list">
                                        <?php foreach ($speakers as $speaker): ?>
                                            <?php if ($speaker['role'] === 'imam' || $speaker['role'] === 'both'): ?>
                                                <option value="<?php echo htmlspecialchars($speaker['name']); ?>">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                
                                <!-- Khotib -->
                                <div>
                                    <label for="khotib_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Khotib <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="khotib_name" 
                                           name="khotib_name" 
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule_data['khotib_name']) : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required
                                           list="khotib_list">
                                    <datalist id="khotib_list">
                                        <?php foreach ($speakers as $speaker): ?>
                                            <?php if ($speaker['role'] === 'khotib' || $speaker['role'] === 'both'): ?>
                                                <option value="<?php echo htmlspecialchars($speaker['name']); ?>">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            
                            <!-- Khutbah Theme -->
                            <div>
                                <label for="khutbah_theme" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tema Khutbah <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="khutbah_theme" 
                                       name="khutbah_theme" 
                                       value="<?php echo $action === 'edit' ? htmlspecialchars($schedule_data['khutbah_theme']) : ''; ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required
                                       list="theme_list">
                                <datalist id="theme_list">
                                    <?php foreach ($themes as $theme): ?>
                                        <option value="<?php echo htmlspecialchars($theme['theme_title']); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            
                            <!-- Khutbah Description -->
                            <div>
                                <label for="khutbah_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Deskripsi Khutbah
                                </label>
                                <textarea id="khutbah_description" 
                                          name="khutbah_description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Deskripsi singkat tentang isi khutbah..."><?php echo $action === 'edit' ? htmlspecialchars($schedule_data['khutbah_description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Location -->
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lokasi
                                    </label>
                                    <input type="text" 
                                           id="location" 
                                           name="location" 
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule_data['location']) : 'Masjid Jami Al-Muhajirin'; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                                
                                <!-- Status (only for edit) -->
                                <?php if ($action === 'edit'): ?>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Status
                                    </label>
                                    <select id="status" 
                                            name="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        <option value="scheduled" <?php echo $schedule_data['status'] === 'scheduled' ? 'selected' : ''; ?>>Terjadwal</option>
                                        <option value="completed" <?php echo $schedule_data['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="cancelled" <?php echo $schedule_data['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Special Notes -->
                            <div>
                                <label for="special_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan Khusus
                                </label>
                                <textarea id="special_notes" 
                                          name="special_notes" 
                                          rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Catatan khusus untuk jamaah (opsional)..."><?php echo $action === 'edit' ? htmlspecialchars($schedule_data['special_notes']) : ''; ?></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <a href="?action=list" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400">
                                    Batal
                                </a>
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                    <i class="fas fa-save mr-1"></i>
                                    <?php echo $action === 'add' ? 'Tambah Jadwal' : 'Simpan Perubahan'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Friday Schedule Modals -->
    <?php
    // Render modals for CRUD operations
    renderFridayScheduleModal('addFridayScheduleModal', 'add', [], true);
    renderFridayScheduleModal('editFridayScheduleModal', 'edit', [], true);
    renderFridayScheduleModal('viewFridayScheduleModal', 'view', [], true);
    ?>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    
    <!-- Modal JS -->
    <script src="../../assets/js/modal.js"></script>
    <script src="../../assets/js/friday_schedule_modal.js"></script>

    <script>
        let calendar;
        let currentView = '<?php echo $view; ?>';
        
        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            
            // Auto-set Friday date for forms
            setupFridayDateValidation();
        });
        
        // Initialize FullCalendar
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                
                // Enhanced navigation configuration for admin
                navLinks: true,
                navLinkDayClick: function(date, jsEvent) {
                    handleAdminDayNavigation(date, jsEvent);
                },
                
                // Custom navigation buttons for admin
                customButtons: {
                    prevYear: {
                        text: '‹‹',
                        hint: 'Tahun Sebelumnya',
                        click: function() {
                            navigateAdminToYear(-1);
                        }
                    },
                    nextYear: {
                        text: '››',
                        hint: 'Tahun Berikutnya', 
                        click: function() {
                            navigateAdminToYear(1);
                        }
                    },
                    currentMonth: {
                        text: 'Bulan Ini',
                        hint: 'Kembali ke Bulan Ini',
                        click: function() {
                            navigateAdminToCurrentMonth();
                        }
                    },
                    quickAdd: {
                        text: '+ Jadwal',
                        hint: 'Tambah Jadwal Cepat',
                        click: function() {
                            openAddScheduleModal();
                        }
                    }
                },
                
                // Enhanced header with admin-specific buttons
                headerToolbar: {
                    left: 'prevYear,prev,currentMonth,next,nextYear',
                    center: 'title',
                    right: 'quickAdd today dayGridMonth,listMonth'
                },
                
                height: 'auto',
                locale: 'id',
                firstDay: 1, // Monday
                
                // Navigation state management for admin
                datesSet: function(dateInfo) {
                    handleAdminDateRangeChange(dateInfo);
                },
                
                // Event sources
                events: {
                    url: '../../api/friday_schedule_events.php',
                    method: 'GET',
                    failure: function(error) {
                        console.error('Calendar events loading failed:', error);
                        handleApiError(error, 'memuat jadwal');
                        
                        // Show retry option
                        showNotificationWithRetry(
                            'Gagal memuat jadwal. Periksa koneksi internet Anda.',
                            'error',
                            () => {
                                console.log('Retrying calendar events load...');
                                refreshCalendar();
                            }
                        );
                    },
                    success: function(events) {
                        console.log(`Loaded ${events.length} calendar events`);
                        
                        // Show success message only if there were previous errors
                        if (window.calendarLoadError) {
                            showNotification('Jadwal berhasil dimuat', 'success');
                            window.calendarLoadError = false;
                        }
                    }
                },
                
                // Event rendering
                eventDisplay: 'block',
                eventClassNames: function(arg) {
                    const status = arg.event.extendedProps.status;
                    switch(status) {
                        case 'completed':
                            return ['bg-blue-600', 'border-blue-700'];
                        case 'cancelled':
                            return ['bg-red-600', 'border-red-700'];
                        default:
                            return ['bg-green-600', 'border-green-700'];
                    }
                },
                
                // Click handlers
                dateClick: function(info) {
                    handleDateClick(info);
                },
                
                eventClick: function(info) {
                    handleEventClick(info);
                },
                
                // Day cell rendering with enhanced Friday highlighting
                dayCellDidMount: function(info) {
                    // Enhanced Friday highlighting with comprehensive visual indicators
                    if (info.date.getDay() === 5) {
                        info.el.classList.add('fc-day-fri');
                        
                        // Apply Friday-specific styling
                        applyAdminFridayHighlighting(info.el, info.date);
                        
                        // Add interactive hover effects with tooltips
                        setupAdminFridayInteractions(info.el, info.date);
                    }
                    
                    // Add visual indicators for dates with events (after events are loaded)
                    setTimeout(() => {
                        addAdminEventIndicators(info.el, info.date);
                    }, 100);
                },
                
                // Loading state
                loading: function(isLoading) {
                    if (isLoading) {
                        document.body.style.cursor = 'wait';
                        console.log('Calendar loading...');
                    } else {
                        document.body.style.cursor = 'default';
                        console.log('Calendar loading complete');
                    }
                },
                
                // Error handling for event rendering
                eventDidMount: function(info) {
                    // Add tooltip with event details
                    const event = info.event;
                    const tooltip = `
                        Imam: ${event.extendedProps.imam_name || 'TBD'}
                        Khotib: ${event.extendedProps.khotib_name || 'TBD'}
                        Waktu: ${event.extendedProps.prayer_time || '12:00'}
                        Status: ${event.extendedProps.status || 'scheduled'}
                    `;
                    
                    info.el.setAttribute('title', tooltip);
                },
                
                // Handle event updates
                eventChange: function(info) {
                    console.log('Event changed:', info.event.id);
                    
                    // This could be triggered by drag & drop in the future
                    // For now, we'll just log it
                },
                
                // Handle event removal
                eventRemove: function(info) {
                    console.log('Event removed:', info.event.id);
                }
            });
            
            // Add error handling for calendar rendering
            try {
                calendar.render();
                console.log('Calendar rendered successfully');
                
                // Initialize admin navigation features
                setupAdminKeyboardNavigation();
                restoreAdminNavigationState();
                
            } catch (error) {
                console.error('Calendar rendering failed:', error);
                handleApiError(error, 'menampilkan kalender');
                
                // Show fallback message
                const calendarEl = document.getElementById('calendar');
                if (calendarEl) {
                    calendarEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Kalender</h3>
                            <p class="text-gray-600 mb-4">Terjadi kesalahan saat memuat kalender.</p>
                            <button onclick="location.reload()" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                <i class="fas fa-refresh mr-1"></i>Muat Ulang Halaman
                            </button>
                        </div>
                    `;
                }
            }
        }
        
        // Handle date click (add new schedule)
        function handleDateClick(info) {
            const clickedDate = info.date;
            const dayOfWeek = clickedDate.getDay();
            
            // Only allow adding on Fridays
            if (dayOfWeek !== 5) {
                showNotification('Jadwal hanya dapat ditambahkan pada hari Jumat', 'warning');
                return;
            }
            
            // Check if date is in the past
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            clickedDate.setHours(0, 0, 0, 0);
            
            if (clickedDate < today) {
                showNotification('Tidak dapat menambah jadwal untuk tanggal yang sudah lewat', 'warning');
                return;
            }
            
            // Format date for input
            const formattedDate = clickedDate.toISOString().split('T')[0];
            
            // Open add modal with pre-filled date
            openAddScheduleModal(formattedDate);
        }
        
        // Handle event click (edit existing schedule)
        function handleEventClick(info) {
            info.jsEvent.preventDefault();
            
            const event = info.event;
            const eventData = {
                id: event.id,
                friday_date: event.startStr,
                prayer_time: event.extendedProps.prayer_time || '12:00',
                imam_name: event.extendedProps.imam_name || '',
                khotib_name: event.extendedProps.khotib_name || '',
                khutbah_theme: event.title || '',
                khutbah_description: event.extendedProps.khutbah_description || '',
                location: event.extendedProps.location || '',
                special_notes: event.extendedProps.special_notes || '',
                status: event.extendedProps.status || 'scheduled'
            };
            
            // Open edit modal
            openEditScheduleModal(eventData);
        }
        
        // Switch between calendar and list view
        function switchView(view) {
            currentView = view;
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.pushState({}, '', url);
            
            // Toggle view elements
            const calendarView = document.getElementById('calendar-view');
            const listView = document.getElementById('list-view');
            const calendarBtn = document.getElementById('calendar-view-btn');
            const listBtn = document.getElementById('list-view-btn');
            
            if (view === 'calendar') {
                calendarView.classList.remove('hidden');
                listView.classList.add('hidden');
                calendarBtn.classList.add('active');
                listBtn.classList.remove('active');
                
                // Refresh calendar if needed
                if (calendar) {
                    calendar.refetchEvents();
                }
            } else {
                calendarView.classList.add('hidden');
                listView.classList.remove('hidden');
                calendarBtn.classList.remove('active');
                listBtn.classList.add('active');
                
                // Load list view content
                loadListView();
            }
        }
        
        // Load list view content via AJAX
        function loadListView() {
            // For now, redirect to list view instead of AJAX
            const url = new URL(window.location);
            url.searchParams.set('view', 'list');
            window.location.href = url.toString();
        }
        
        // Refresh calendar events
        function refreshCalendar() {
            if (calendar) {
                console.log('Refreshing calendar events...');
                
                // Show loading indicator
                document.body.style.cursor = 'wait';
                
                // Refetch events from server
                calendar.refetchEvents();
                
                // Hide loading indicator after a short delay
                setTimeout(() => {
                    document.body.style.cursor = 'default';
                }, 500);
            }
        }
        
        // Handle API errors with user feedback
        function handleApiError(error, operation = 'operasi') {
            console.error(`API Error during ${operation}:`, error);
            
            let message = `Terjadi kesalahan saat ${operation}. `;
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                message += 'Periksa koneksi internet Anda.';
            } else if (error.status === 401) {
                message += 'Sesi Anda telah berakhir. Silakan login kembali.';
                // Redirect to login after delay
                setTimeout(() => {
                    window.location.href = '../login.php';
                }, 3000);
            } else if (error.status === 403) {
                message += 'Anda tidak memiliki izin untuk melakukan tindakan ini.';
            } else if (error.status >= 500) {
                message += 'Terjadi kesalahan server. Silakan coba lagi nanti.';
            } else {
                message += 'Silakan coba lagi.';
            }
            
            showNotification(message, 'error');
        }
        
        // Enhanced notification system with auto-retry option
        function showNotificationWithRetry(message, type = 'info', retryCallback = null) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full max-w-md`;
            
            // Set notification style based on type
            switch (type) {
                case 'success':
                    notification.classList.add('bg-green-500', 'text-white');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500', 'text-white');
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500', 'text-white');
                    break;
                default:
                    notification.classList.add('bg-blue-500', 'text-white');
                    break;
            }
            
            let notificationContent = `
                <div class="flex items-start">
                    <span class="flex-1">${message}</span>
                    <div class="ml-4 flex space-x-2">
            `;
            
            // Add retry button for error notifications
            if (type === 'error' && retryCallback) {
                notificationContent += `
                    <button onclick="(${retryCallback.toString()})()" class="text-white hover:text-gray-200 underline text-sm">
                        Coba Lagi
                    </button>
                `;
            }
            
            notificationContent += `
                        <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            notification.innerHTML = notificationContent;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after appropriate time based on type
            const autoRemoveTime = type === 'error' ? 10000 : 5000; // Errors stay longer
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, autoRemoveTime);
        }
        
        // Update the existing showNotification function to use the enhanced version
        function showNotification(message, type = 'info', retryCallback = null) {
            showNotificationWithRetry(message, type, retryCallback);
        }
        
        // Confirm delete schedule
        function confirmDeleteSchedule(scheduleId) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal Jumat ini?')) {
                // Redirect to delete action
                window.location.href = `?action=delete&id=${scheduleId}`;
            }
        }
        
        // Setup Friday date validation for forms
        function setupFridayDateValidation() {
            // This will be handled by the modal JavaScript
        }
        
        // Auto-set Friday date
        document.addEventListener('DOMContentLoaded', function() {
            const fridayDateInputs = document.querySelectorAll('input[name="friday_date"]');
            
            fridayDateInputs.forEach(input => {
                if (!input.value) {
                    // Get next Friday
                    const today = new Date();
                    const dayOfWeek = today.getDay();
                    const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
                    const nextFriday = new Date(today);
                    nextFriday.setDate(today.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
                    
                    input.value = nextFriday.toISOString().split('T')[0];
                }
                
                // Validate that selected date is a Friday
                input.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    if (selectedDate.getDay() !== 5) {
                        alert('Tanggal yang dipilih harus hari Jumat!');
                        this.focus();
                    }
                });
            });
        });
        
        // Enhanced Friday highlighting and visual indicator functions for admin
        function applyAdminFridayHighlighting(dayElement, date) {
            // Base Friday styling for admin interface
            dayElement.style.backgroundColor = '#f0fdf4';
            dayElement.style.border = '2px solid #bbf7d0';
            dayElement.style.position = 'relative';
            dayElement.style.transition = 'all 0.2s ease';
            
            // Enhanced day number styling for Fridays
            const dayNumber = dayElement.querySelector('.fc-daygrid-day-number');
            if (dayNumber) {
                dayNumber.style.color = '#059669';
                dayNumber.style.fontWeight = '700';
                dayNumber.style.backgroundColor = '#dcfce7';
                dayNumber.style.borderRadius = '50%';
                dayNumber.style.width = '28px';
                dayNumber.style.height = '28px';
                dayNumber.style.display = 'flex';
                dayNumber.style.alignItems = 'center';
                dayNumber.style.justifyContent = 'center';
                dayNumber.style.margin = '3px auto';
                dayNumber.style.transition = 'all 0.2s ease';
                dayNumber.style.fontSize = '14px';
            }
            
            // Add Friday indicator badge for admin
            const fridayBadge = document.createElement('div');
            fridayBadge.className = 'admin-friday-badge';
            fridayBadge.style.cssText = `
                position: absolute;
                top: 3px;
                left: 3px;
                background: linear-gradient(135deg, #059669, #047857);
                color: white;
                font-size: 9px;
                font-weight: 700;
                padding: 2px 5px;
                border-radius: 6px;
                z-index: 2;
                pointer-events: none;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            `;
            fridayBadge.textContent = 'JUMAT';
            dayElement.appendChild(fridayBadge);
            
            // Add click-to-add indicator for empty Fridays
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const cellDate = new Date(date);
            cellDate.setHours(0, 0, 0, 0);
            
            if (cellDate >= today) {
                const addIndicator = document.createElement('div');
                addIndicator.className = 'admin-add-indicator';
                addIndicator.style.cssText = `
                    position: absolute;
                    bottom: 3px;
                    right: 3px;
                    background-color: #3b82f6;
                    color: white;
                    font-size: 10px;
                    padding: 2px 4px;
                    border-radius: 4px;
                    z-index: 2;
                    pointer-events: none;
                    opacity: 0;
                    transition: opacity 0.2s ease;
                `;
                addIndicator.innerHTML = '<i class="fas fa-plus"></i>';
                dayElement.appendChild(addIndicator);
            }
        }
        
        function setupAdminFridayInteractions(dayElement, date) {
            // Enhanced hover effects for admin Fridays
            dayElement.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#dcfce7';
                this.style.borderColor = '#10b981';
                this.style.cursor = 'pointer';
                this.style.transform = 'scale(1.02)';
                this.style.boxShadow = '0 4px 8px rgba(16, 185, 129, 0.3)';
                
                const dayNumber = this.querySelector('.fc-daygrid-day-number');
                if (dayNumber) {
                    dayNumber.style.backgroundColor = '#059669';
                    dayNumber.style.color = 'white';
                    dayNumber.style.transform = 'scale(1.1)';
                }
                
                // Show add indicator for future Fridays
                const addIndicator = this.querySelector('.admin-add-indicator');
                if (addIndicator) {
                    addIndicator.style.opacity = '1';
                }
                
                // Show enhanced admin tooltip
                showAdminFridayTooltip(this, date);
            });
            
            dayElement.addEventListener('mouseleave', function() {
                // Check if this Friday has events for proper styling restoration
                const dateStr = date.toISOString().split('T')[0];
                const hasEvents = calendar && calendar.getEvents().some(event => 
                    event.startStr === dateStr
                );
                
                if (hasEvents) {
                    this.style.backgroundColor = '#ecfdf5';
                    this.style.borderColor = '#10b981';
                    this.style.boxShadow = '0 2px 4px rgba(16, 185, 129, 0.2)';
                } else {
                    this.style.backgroundColor = '#f0fdf4';
                    this.style.borderColor = '#bbf7d0';
                    this.style.boxShadow = 'none';
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
                
                // Hide add indicator
                const addIndicator = this.querySelector('.admin-add-indicator');
                if (addIndicator) {
                    addIndicator.style.opacity = '0';
                }
                
                // Hide tooltip
                hideAdminFridayTooltip();
            });
        }
        
        function addAdminEventIndicators(dayElement, date) {
            const dateStr = date.toISOString().split('T')[0];
            const dayEvents = calendar ? calendar.getEvents().filter(event => 
                event.startStr === dateStr
            ) : [];
            
            if (dayEvents.length > 0) {
                dayElement.classList.add('has-events');
                
                // Remove existing indicators to avoid duplicates
                const existingIndicators = dayElement.querySelectorAll('.admin-event-indicator');
                existingIndicators.forEach(indicator => indicator.remove());
                
                // Add enhanced event indicator for admin
                const indicator = document.createElement('div');
                indicator.className = 'admin-event-indicator';
                indicator.style.cssText = `
                    position: absolute;
                    top: 5px;
                    right: 5px;
                    width: 12px;
                    height: 12px;
                    background-color: #10b981;
                    border: 2px solid white;
                    border-radius: 50%;
                    z-index: 3;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                `;
                dayElement.appendChild(indicator);
                
                // Enhanced styling for Fridays with events in admin
                if (date.getDay() === 5) {
                    dayElement.style.backgroundColor = '#ecfdf5';
                    dayElement.style.borderColor = '#10b981';
                    dayElement.style.boxShadow = '0 2px 4px rgba(16, 185, 129, 0.2)';
                    
                    const dayNumber = dayElement.querySelector('.fc-daygrid-day-number');
                    if (dayNumber) {
                        dayNumber.style.backgroundColor = '#10b981';
                        dayNumber.style.color = 'white';
                    }
                    
                    // Add event count badge for multiple events
                    if (dayEvents.length > 1) {
                        const countBadge = document.createElement('div');
                        countBadge.className = 'admin-event-count-badge';
                        countBadge.style.cssText = `
                            position: absolute;
                            top: -3px;
                            right: -3px;
                            background-color: #ef4444;
                            color: white;
                            font-size: 10px;
                            font-weight: 700;
                            padding: 2px 5px;
                            border-radius: 10px;
                            z-index: 4;
                            min-width: 18px;
                            text-align: center;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
                        `;
                        countBadge.textContent = dayEvents.length;
                        indicator.appendChild(countBadge);
                    }
                    
                    // Add status indicators for different event statuses
                    const statuses = dayEvents.map(e => e.extendedProps.status);
                    if (statuses.includes('cancelled')) {
                        indicator.style.backgroundColor = '#ef4444';
                    } else if (statuses.includes('completed')) {
                        indicator.style.backgroundColor = '#6b7280';
                    }
                }
            }
        }
        
        // Enhanced admin Friday tooltip functions
        let currentAdminTooltip = null;
        
        function showAdminFridayTooltip(element, date) {
            // Remove existing tooltip
            hideAdminFridayTooltip();
            
            // Get events for this date
            const dateStr = date.toISOString().split('T')[0];
            const dayEvents = calendar ? calendar.getEvents().filter(event => 
                event.startStr === dateStr
            ) : [];
            
            // Create enhanced admin tooltip content
            let tooltipContent = `<div class="admin-tooltip-header"><strong>Jumat, ${formatAdminDate(date)}</strong></div>`;
            
            if (dayEvents.length > 0) {
                tooltipContent += `<div class="admin-tooltip-content">`;
                
                dayEvents.forEach((event, index) => {
                    const props = event.extendedProps;
                    const statusIcon = getAdminStatusIcon(props.status);
                    const statusColor = getAdminStatusColor(props.status);
                    
                    tooltipContent += `
                        <div class="admin-event-item" style="border-left: 3px solid ${statusColor}; padding-left: 10px; margin: 6px 0;">
                            <div class="admin-event-header">
                                <span class="admin-event-time">
                                    <i class="fas fa-clock"></i> ${props.prayer_time || '12:00'} WIB
                                </span>
                                <span class="admin-event-status">${statusIcon}</span>
                            </div>
                            <div class="admin-event-details">
                                <div><i class="fas fa-user"></i> <strong>Imam:</strong> ${props.imam_name || 'Belum ditentukan'}</div>
                                <div><i class="fas fa-microphone"></i> <strong>Khotib:</strong> ${props.khotib_name || 'Belum ditentukan'}</div>
                            </div>
                            ${props.khutbah_theme ? `<div class="admin-event-theme"><i class="fas fa-book"></i> <strong>Tema:</strong> ${props.khutbah_theme}</div>` : ''}
                            ${props.special_notes ? `<div class="admin-event-notes"><i class="fas fa-info-circle"></i> ${props.special_notes}</div>` : ''}
                        </div>
                    `;
                    
                    if (index < dayEvents.length - 1) {
                        tooltipContent += `<hr style="margin: 8px 0; border-color: rgba(255,255,255,0.2);">`;
                    }
                });
                
                tooltipContent += `</div>`;
                tooltipContent += `<div class="admin-tooltip-actions">Klik untuk mengedit jadwal</div>`;
            } else {
                // Check if this is a future Friday
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const cellDate = new Date(date);
                cellDate.setHours(0, 0, 0, 0);
                
                if (cellDate >= today) {
                    tooltipContent += `
                        <div class="admin-tooltip-content">
                            <div class="admin-no-events">
                                <i class="fas fa-calendar-plus"></i> Belum ada jadwal
                                <div class="admin-add-prompt">Klik untuk menambah jadwal baru</div>
                            </div>
                        </div>
                    `;
                } else {
                    tooltipContent += `
                        <div class="admin-tooltip-content">
                            <div class="admin-no-events past-date">
                                <i class="fas fa-calendar-times"></i> Tidak ada jadwal
                                <div class="admin-past-note">Tanggal sudah berlalu</div>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Create enhanced admin tooltip element
            const tooltip = document.createElement('div');
            tooltip.className = 'admin-friday-tooltip enhanced-admin-tooltip';
            tooltip.innerHTML = tooltipContent;
            
            // Position tooltip with smart positioning
            const rect = element.getBoundingClientRect();
            const tooltipWidth = 320;
            const tooltipHeight = 150; // Estimated
            
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
                tooltip.classList.add('admin-tooltip-bottom');
            }
            
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.transform = `translateX(${transformX}) translateY(${transformY})`;
            
            document.body.appendChild(tooltip);
            currentAdminTooltip = tooltip;
            
            // Add fade-in animation
            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform += ' scale(1)';
            }, 10);
        }
        
        function hideAdminFridayTooltip() {
            if (currentAdminTooltip) {
                currentAdminTooltip.style.opacity = '0';
                currentAdminTooltip.style.transform += ' scale(0.95)';
                setTimeout(() => {
                    if (currentAdminTooltip && currentAdminTooltip.parentNode) {
                        currentAdminTooltip.remove();
                    }
                    currentAdminTooltip = null;
                }, 200);
            }
        }
        
        function getAdminStatusIcon(status) {
            const icons = {
                'scheduled': '<i class="fas fa-clock" style="color: #10b981;" title="Terjadwal"></i>',
                'completed': '<i class="fas fa-check-circle" style="color: #6b7280;" title="Selesai"></i>',
                'cancelled': '<i class="fas fa-times-circle" style="color: #ef4444;" title="Dibatalkan"></i>'
            };
            return icons[status] || icons['scheduled'];
        }
        
        function getAdminStatusColor(status) {
            const colors = {
                'scheduled': '#10b981',
                'completed': '#6b7280',
                'cancelled': '#ef4444'
            };
            return colors[status] || colors['scheduled'];
        }
        
        function formatAdminDate(date) {
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            return date.toLocaleDateString('id-ID', options);
        }
        
        // Enhanced admin calendar navigation functions
        function handleAdminDayNavigation(date, jsEvent) {
            // Allow navigation to any day for admin, but provide context
            const dayOfWeek = date.getDay();
            
            if (dayOfWeek === 5) { // Friday
                // Navigate to Friday and show add/edit options
                calendar.gotoDate(date);
                
                setTimeout(() => {
                    const dateStr = date.toISOString().split('T')[0];
                    const dayEvents = calendar.getEvents().filter(event => 
                        event.startStr === dateStr
                    );
                    
                    if (dayEvents.length > 0) {
                        // Edit existing event
                        handleEventClick({ event: dayEvents[0], jsEvent });
                    } else {
                        // Add new event for this Friday
                        openAddScheduleModal(dateStr);
                    }
                }, 300);
            } else {
                // Non-Friday navigation
                jsEvent.preventDefault();
                showNotification('Jadwal Jumat hanya dapat ditambahkan pada hari Jumat', 'info');
                
                // Navigate to nearest Friday
                const nearestFriday = findNearestFriday(date);
                calendar.gotoDate(nearestFriday);
                
                setTimeout(() => {
                    highlightAdminElement(document.querySelector(`[data-date="${nearestFriday.toISOString().split('T')[0]}"]`));
                }, 300);
            }
        }
        
        function navigateAdminToYear(direction) {
            const currentDate = calendar.getDate();
            const newDate = new Date(currentDate);
            newDate.setFullYear(currentDate.getFullYear() + direction);
            
            // Validate year range for admin (more flexible than public)
            const currentYear = new Date().getFullYear();
            const targetYear = newDate.getFullYear();
            
            if (targetYear < currentYear - 5) {
                showNotification('Tidak dapat navigasi lebih dari 5 tahun ke belakang', 'warning');
                return;
            }
            
            if (targetYear > currentYear + 10) {
                showNotification('Tidak dapat navigasi lebih dari 10 tahun ke depan', 'warning');
                return;
            }
            
            // Perform navigation with loading state
            showAdminNavigationLoading(true);
            calendar.gotoDate(newDate);
            
            console.log(`Admin navigated to year: ${targetYear}`);
        }
        
        function navigateAdminToCurrentMonth() {
            const today = new Date();
            showAdminNavigationLoading(true);
            calendar.gotoDate(today);
            
            // Highlight today if it's visible
            setTimeout(() => {
                highlightAdminToday();
            }, 300);
            
            console.log('Admin navigated to current month');
        }
        
        function handleAdminDateRangeChange(dateInfo) {
            const { start, end, view } = dateInfo;
            
            // Store admin navigation state
            const adminNavigationState = {
                start: start.toISOString(),
                end: end.toISOString(),
                view: view.type,
                timestamp: Date.now(),
                userRole: 'admin'
            };
            
            // Save to localStorage
            localStorage.setItem('adminFridayCalendarNavigation', JSON.stringify(adminNavigationState));
            
            // Update admin navigation buttons
            updateAdminNavigationButtonsState(dateInfo);
            
            // Preload events and update statistics
            preloadAdminEventsForRange(start, end);
            updateAdminCalendarStats(dateInfo);
            
            console.log(`Admin calendar view changed: ${view.type}, Range: ${start.toDateString()} - ${end.toDateString()}`);
        }
        
        function showAdminNavigationLoading(show) {
            const calendarEl = document.getElementById('calendar');
            
            if (show) {
                calendarEl.style.opacity = '0.7';
                document.body.style.cursor = 'wait';
                
                // Show loading overlay
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'admin-calendar-loading-overlay';
                loadingOverlay.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-green-600"></i>';
                loadingOverlay.style.cssText = `
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
                `;
                calendarEl.style.position = 'relative';
                calendarEl.appendChild(loadingOverlay);
            } else {
                calendarEl.style.opacity = '1';
                document.body.style.cursor = 'default';
                
                // Remove loading overlay
                const loadingOverlay = calendarEl.querySelector('.admin-calendar-loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.remove();
                }
            }
        }
        
        function updateAdminNavigationButtonsState(dateInfo) {
            const { start } = dateInfo;
            const currentYear = new Date().getFullYear();
            const viewYear = start.getFullYear();
            
            // Update custom button states
            setTimeout(() => {
                const prevYearBtn = document.querySelector('.fc-prevYear-button');
                const nextYearBtn = document.querySelector('.fc-nextYear-button');
                const currentMonthBtn = document.querySelector('.fc-currentMonth-button');
                
                if (prevYearBtn) {
                    prevYearBtn.disabled = (viewYear <= currentYear - 5);
                    prevYearBtn.style.opacity = prevYearBtn.disabled ? '0.5' : '1';
                }
                
                if (nextYearBtn) {
                    nextYearBtn.disabled = (viewYear >= currentYear + 10);
                    nextYearBtn.style.opacity = nextYearBtn.disabled ? '0.5' : '1';
                }
                
                if (currentMonthBtn) {
                    const isCurrentMonth = (
                        start.getMonth() === new Date().getMonth() && 
                        start.getFullYear() === new Date().getFullYear()
                    );
                    currentMonthBtn.style.opacity = isCurrentMonth ? '0.5' : '1';
                }
            }, 100);
        }
        
        function preloadAdminEventsForRange(start, end) {
            const startStr = start.toISOString().split('T')[0];
            const endStr = end.toISOString().split('T')[0];
            
            console.log(`Admin preloading events for range: ${startStr} to ${endStr}`);
        }
        
        function updateAdminCalendarStats(dateInfo) {
            const { start, end } = dateInfo;
            const fridayCount = countFridaysInRange(start, end);
            
            // Update stats display if available
            const statsElement = document.querySelector('.admin-calendar-stats');
            if (statsElement) {
                statsElement.innerHTML = `
                    <div class="stat-item">
                        <span class="stat-label">Jumat dalam tampilan:</span>
                        <span class="stat-value">${fridayCount}</span>
                    </div>
                `;
            }
        }
        
        function countFridaysInRange(start, end) {
            let count = 0;
            const current = new Date(start);
            
            while (current <= end) {
                if (current.getDay() === 5) {
                    count++;
                }
                current.setDate(current.getDate() + 1);
            }
            
            return count;
        }
        
        function findNearestFriday(date) {
            const dayOfWeek = date.getDay();
            const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
            const nearestFriday = new Date(date);
            
            if (daysUntilFriday === 0) {
                nearestFriday.setDate(date.getDate() + 7);
            } else {
                nearestFriday.setDate(date.getDate() + daysUntilFriday);
            }
            
            return nearestFriday;
        }
        
        function highlightAdminElement(element) {
            if (element) {
                element.style.animation = 'adminPulse 1s ease-in-out 3';
                element.style.boxShadow = '0 0 15px rgba(5, 150, 105, 0.6)';
                
                setTimeout(() => {
                    element.style.animation = '';
                    element.style.boxShadow = '';
                }, 3000);
            }
        }
        
        function highlightAdminToday() {
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];
            const todayElement = document.querySelector(`[data-date="${todayStr}"]`);
            
            if (todayElement) {
                todayElement.style.boxShadow = '0 0 15px rgba(59, 130, 246, 0.6)';
                todayElement.style.transform = 'scale(1.03)';
                todayElement.style.zIndex = '10';
                
                setTimeout(() => {
                    todayElement.style.boxShadow = '';
                    todayElement.style.transform = '';
                    todayElement.style.zIndex = '';
                }, 3000);
            }
        }
        
        function setupAdminKeyboardNavigation() {
            document.addEventListener('keydown', function(event) {
                if (!calendar || document.activeElement.tagName === 'INPUT') return;
                
                const navigationKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'KeyN'];
                if (navigationKeys.includes(event.code)) {
                    event.preventDefault();
                }
                
                switch (event.code) {
                    case 'ArrowLeft':
                        if (event.ctrlKey) calendar.prev();
                        break;
                    case 'ArrowRight':
                        if (event.ctrlKey) calendar.next();
                        break;
                    case 'ArrowUp':
                        if (event.ctrlKey) navigateAdminToYear(-1);
                        break;
                    case 'ArrowDown':
                        if (event.ctrlKey) navigateAdminToYear(1);
                        break;
                    case 'Home':
                        navigateAdminToCurrentMonth();
                        break;
                    case 'End':
                        navigateToNextAdminFriday();
                        break;
                    case 'KeyN':
                        if (event.ctrlKey) openAddScheduleModal();
                        break;
                }
            });
        }
        
        function navigateToNextAdminFriday() {
            const today = new Date();
            const nextFriday = findNearestFriday(today);
            
            calendar.gotoDate(nextFriday);
            
            setTimeout(() => {
                const fridayStr = nextFriday.toISOString().split('T')[0];
                const fridayElement = document.querySelector(`[data-date="${fridayStr}"]`);
                if (fridayElement) {
                    fridayElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    highlightAdminElement(fridayElement);
                    
                    setTimeout(() => {
                        showNotification('Klik pada tanggal untuk menambah jadwal, atau tekan Ctrl+N', 'info');
                    }, 1000);
                }
            }, 300);
        }
        
        function restoreAdminNavigationState() {
            try {
                const savedState = localStorage.getItem('adminFridayCalendarNavigation');
                if (savedState) {
                    const state = JSON.parse(savedState);
                    
                    const stateAge = Date.now() - state.timestamp;
                    if (stateAge < 7 * 24 * 60 * 60 * 1000 && state.userRole === 'admin') {
                        const targetDate = new Date(state.start);
                        calendar.gotoDate(targetDate);
                        
                        if (state.view !== 'dayGridMonth') {
                            calendar.changeView(state.view);
                        }
                        
                        console.log('Restored admin navigation state:', state);
                    }
                }
            } catch (error) {
                console.warn('Failed to restore admin navigation state:', error);
            }
        }
    </script>
</body>
</html>