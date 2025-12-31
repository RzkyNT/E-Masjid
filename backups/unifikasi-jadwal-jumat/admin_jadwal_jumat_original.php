<?php
// Add cache-busting headers for admin pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
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
                <!-- List View -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Jadwal Jumat</h3>
                            <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                                <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                    <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                                </a>
                            <?php endif; ?>
                        </div>
                        
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
                                                            <a href="?action=edit&id=<?php echo $schedule['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (hasPermission($current_user['role'], 'masjid_content', 'delete')): ?>
                                                            <a href="?action=delete&id=<?php echo $schedule['id']; ?>" 
                                                               class="text-red-600 hover:text-red-900"
                                                               onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
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
                                            <a href="?page=<?php echo $i; ?>" 
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
                                    <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
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

    <script>
        // Auto-set Friday date
        document.addEventListener('DOMContentLoaded', function() {
            const fridayDateInput = document.getElementById('friday_date');
            
            if (fridayDateInput && !fridayDateInput.value) {
                // Get next Friday
                const today = new Date();
                const dayOfWeek = today.getDay();
                const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
                const nextFriday = new Date(today);
                nextFriday.setDate(today.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
                
                fridayDateInput.value = nextFriday.toISOString().split('T')[0];
            }
            
            // Validate that selected date is a Friday
            fridayDateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                if (selectedDate.getDay() !== 5) {
                    alert('Tanggal yang dipilih harus hari Jumat!');
                    this.focus();
                }
            });
        });
    </script>
</body>
</html>