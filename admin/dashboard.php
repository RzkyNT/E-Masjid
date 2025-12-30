<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../includes/user_functions.php';

// Require login
requireLogin();

// Get current user
$current_user = getCurrentUser();

// Get user statistics (only for admin_masjid)
$user_stats = null;
if ($current_user['role'] === 'admin_masjid') {
    $user_stats = getUserStats();
}

// Get recent activities (placeholder for now)
$recent_activities = [
    ['action' => 'Login berhasil', 'user' => $current_user['full_name'], 'time' => date('Y-m-d H:i:s')],
    ['action' => 'Dashboard diakses', 'user' => $current_user['full_name'], 'time' => date('Y-m-d H:i:s')]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mosque text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                        <p class="text-sm text-gray-500">Masjid Jami Al-Muhajirin</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                Role: <?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?>
                            </div>
                            <?php if (hasPermission('user_management')): ?>
                                <a href="users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-users mr-2"></i>Kelola Pengguna
                                </a>
                            <?php endif; ?>
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-home text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-medium text-gray-900">
                            Selamat datang, <?php echo htmlspecialchars($current_user['full_name']); ?>!
                        </h2>
                        <p class="text-sm text-gray-500">
                            Anda login sebagai <?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?> 
                            pada <?php echo date('d F Y, H:i'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards (for admin_masjid) -->
        <?php if ($current_user['role'] === 'admin_masjid' && $user_stats): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Pengguna Aktif</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $user_stats['total_active']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-shield text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Admin Masjid</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $user_stats['by_role']['admin_masjid'] ?? 0; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chalkboard-teacher text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Admin Bimbel</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $user_stats['by_role']['admin_bimbel'] ?? 0; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-eye text-orange-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Viewer</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $user_stats['by_role']['viewer'] ?? 0; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Navigation Menu -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Menu Utama</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <?php if (hasPermission('masjid_content')): ?>
                            <a href="masjid/dashboard.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                                <i class="fas fa-globe text-blue-600 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">Website Masjid</p>
                                    <p class="text-xs text-blue-700">Lihat website publik</p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (hasPermission('bimbel_management')): ?>
                            <a href="bimbel/dashboard.php" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                                <i class="fas fa-graduation-cap text-green-600 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-900">Sistem Bimbel</p>
                                    <p class="text-xs text-green-700">Kelola bimbingan belajar</p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (hasPermission('user_management')): ?>
                            <a href="users.php" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                                <i class="fas fa-users text-purple-600 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-purple-900">Kelola Pengguna</p>
                                    <p class="text-xs text-purple-700">Manajemen user sistem</p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (hasPermission('reports')): ?>
                            <a href="reports.php" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition duration-200">
                                <i class="fas fa-chart-bar text-orange-600 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-orange-900">Laporan</p>
                                    <p class="text-xs text-orange-700">Lihat laporan sistem</p>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aktivitas Terbaru</h3>
                    <div class="space-y-3">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-green-100 rounded-full p-2">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['action']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($activity['user']); ?> - 
                                        <?php echo date('d/m/Y H:i', strtotime($activity['time'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Informasi Sistem</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo date('d'); ?></div>
                        <div class="text-sm text-gray-500"><?php echo date('F Y'); ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo date('H:i'); ?></div>
                        <div class="text-sm text-gray-500">WIB</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">v1.0</div>
                        <div class="text-sm text-gray-500">Versi Sistem</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh time every minute
        setInterval(function() {
            location.reload();
        }, 60000);

        // Session timeout warning
        let sessionTimeout = <?php echo SESSION_TIMEOUT; ?> * 1000; // Convert to milliseconds
        let warningTime = sessionTimeout - (5 * 60 * 1000); // 5 minutes before timeout

        setTimeout(function() {
            if (confirm('Sesi Anda akan berakhir dalam 5 menit. Klik OK untuk memperpanjang sesi.')) {
                location.reload();
            }
        }, warningTime);

        // Auto logout on session timeout
        setTimeout(function() {
            alert('Sesi Anda telah berakhir. Anda akan diarahkan ke halaman login.');
            window.location.href = 'logout.php';
        }, sessionTimeout);
    </script>
</body>
</html>