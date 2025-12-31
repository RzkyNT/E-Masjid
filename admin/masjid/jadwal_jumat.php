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
        $error_message = 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage();
    }
}

// Get schedules for list view
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT fs.*, 
               CASE 
                   WHEN DATE(fs.friday_date) = CURDATE() THEN 'today'
                   WHEN DATE(fs.friday_date) < CURDATE() THEN 'past'
                   ELSE 'future'
               END as schedule_status
        FROM friday_schedules fs 
        ORDER BY fs.friday_date DESC
    ");
    $stmt->execute();
    $schedules = $stmt->fetchAll();
}

// Get single schedule for edit
if ($action === 'edit' && $schedule_id) {
    $stmt = $pdo->prepare("SELECT * FROM friday_schedules WHERE id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch();
    
    if (!$schedule) {
        $error_message = 'Jadwal tidak ditemukan.';
        $action = 'list';
    }
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                <?php echo htmlspecialchars($current_user['email']); ?>
                            </div>
                            <a href="../dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
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
                
                <a href="jadwal_jumat_simple.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-calendar-alt mr-3"></i>Jadwal Jumat
                </a>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Kelola Donasi
                </a>
                
                <a href="pengaturan.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-cog mr-3"></i>Pengaturan
                </a>
                
                <div class="border-t border-gray-700 mt-4 pt-4">
                    <a href="../dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                        <i class="fas fa-arrow-left mr-3"></i>Kembali ke Dashboard
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Messages akan ditampilkan dengan SweetAlert2 Toast -->
            <?php if ($success_message): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: '<?php echo addslashes($success_message); ?>',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: '<?php echo addslashes($error_message); ?>',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Schedule Management -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Jadwal Jumat</h3>
                                <p class="mt-1 text-sm text-gray-500">Kelola jadwal sholat Jumat dengan mudah</p>
                            </div>
                            
                            <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                                <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                    <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Schedule List -->
                        <?php if (!empty($schedules)): ?>
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <?php foreach ($schedules as $index => $schedule): ?>
                                    <?php
                                    $date = new DateTime($schedule['friday_date']);
                                    $isToday = $schedule['schedule_status'] === 'today';
                                    $statusClass = [
                                        'scheduled' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ][$schedule['status']];
                                    $statusLabel = [
                                        'scheduled' => 'Terjadwal',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ][$schedule['status']];
                                    ?>
                                    
                                    <div class="border-b border-gray-200 <?php echo $isToday ? 'bg-blue-50' : ''; ?> hover:bg-gray-50 transition duration-200">
                                        <div class="p-6">
                                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                                <!-- Date and Status -->
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-green-100 rounded-lg p-3 text-center min-w-[80px]">
                                                            <div class="text-2xl font-bold text-green-600"><?php echo $date->format('d'); ?></div>
                                                            <div class="text-xs text-green-600 uppercase"><?php echo $date->format('M'); ?></div>
                                                            <div class="text-xs text-gray-500"><?php echo $date->format('Y'); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex-grow">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h3 class="text-lg font-semibold text-gray-900">
                                                                <?php 
                                                                $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                                                echo $days[$date->format('w')]; 
                                                                ?>
                                                            </h3>
                                                            <?php if ($isToday): ?>
                                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">Hari Ini</span>
                                                            <?php endif; ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                                                <?php echo $statusLabel; ?>
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center text-gray-600 text-sm">
                                                            <i class="fas fa-clock mr-2 text-green-600"></i>
                                                            <span class="font-medium"><?php echo date('H:i', strtotime($schedule['prayer_time'])); ?> WIB</span>
                                                            <span class="mx-2">•</span>
                                                            <i class="fas fa-map-marker-alt mr-1 text-green-600"></i>
                                                            <span><?php echo htmlspecialchars($schedule['location']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Schedule Details -->
                                                <div class="flex-grow lg:max-w-2xl">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                                                        <div>
                                                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Imam</div>
                                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($schedule['imam_name']); ?></div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Khotib</div>
                                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($schedule['khotib_name']); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tema Khutbah</div>
                                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($schedule['khutbah_theme']); ?></div>
                                                        <?php if ($schedule['khutbah_description']): ?>
                                                            <div class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($schedule['khutbah_description']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <?php if ($schedule['special_notes']): ?>
                                                    <div class="mt-2">
                                                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-2">
                                                            <div class="flex">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-info-circle text-yellow-400 text-sm"></i>
                                                                </div>
                                                                <div class="ml-2">
                                                                    <div class="text-xs text-yellow-700"><?php echo htmlspecialchars($schedule['special_notes']); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Actions -->
                                                <div class="flex items-center space-x-2">
                                                        <a href="?action=edit&id=<?php echo $schedule['id']; ?>" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                                                            <i class="fas fa-edit mr-1"></i>Edit
                                                        </a>
                                                        <a href="#" 
                                                           onclick="confirmDelete(<?php echo $schedule['id']; ?>)"
                                                           class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700 transition duration-200">
                                                            <i class="fas fa-trash mr-1"></i>Hapus
                                                        </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Jadwal</h3>
                                <p class="text-gray-600 mb-4">Mulai dengan menambahkan jadwal sholat Jumat pertama.</p>
                                <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                                    <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i>Tambah Jadwal Pertama
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
                        <div class="mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                <?php echo $action === 'add' ? 'Tambah' : 'Edit'; ?> Jadwal Jumat
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                <?php echo $action === 'add' ? 'Tambahkan jadwal sholat Jumat baru' : 'Perbarui informasi jadwal sholat Jumat'; ?>
                            </p>
                        </div>

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
                                           value="<?php echo $action === 'edit' ? $schedule['friday_date'] : ''; ?>"
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
                                           value="<?php echo $action === 'edit' ? $schedule['prayer_time'] : '12:00'; ?>"
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
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule['imam_name']) : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required>
                                </div>
                                
                                <!-- Khotib -->
                                <div>
                                    <label for="khotib_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Khotib <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="khotib_name" 
                                           name="khotib_name" 
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule['khotib_name']) : ''; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                           required>
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
                                       value="<?php echo $action === 'edit' ? htmlspecialchars($schedule['khutbah_theme']) : ''; ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required>
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
                                          placeholder="Deskripsi singkat tentang isi khutbah..."><?php echo $action === 'edit' ? htmlspecialchars($schedule['khutbah_description']) : ''; ?></textarea>
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
                                           value="<?php echo $action === 'edit' ? htmlspecialchars($schedule['location']) : 'Masjid Jami Al-Muhajirin'; ?>"
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
                                        <option value="scheduled" <?php echo $schedule['status'] === 'scheduled' ? 'selected' : ''; ?>>Terjadwal</option>
                                        <option value="completed" <?php echo $schedule['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="cancelled" <?php echo $schedule['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
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
                                          placeholder="Catatan khusus untuk jamaah (opsional)..."><?php echo $action === 'edit' ? htmlspecialchars($schedule['special_notes']) : ''; ?></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <a href="?action=list" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                                    Batal
                                </a>
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                                    <i class="fas fa-save mr-1"></i><?php echo $action === 'add' ? 'Simpan' : 'Perbarui'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Validate Friday date input
        document.getElementById('friday_date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate.getDay() !== 5) {
                alert('Tanggal yang dipilih harus hari Jumat!');
                this.focus();
            }
        });
        
        // Auto-set next Friday for new schedules
        <?php if ($action === 'add'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const fridayDateInput = document.getElementById('friday_date');
            if (!fridayDateInput.value) {
                // Get next Friday
                const today = new Date();
                const dayOfWeek = today.getDay();
                const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
                const nextFriday = new Date(today);
                nextFriday.setDate(today.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
                
                fridayDateInput.value = nextFriday.toISOString().split('T')[0];
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>