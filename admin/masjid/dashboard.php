<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();

// Get statistics
try {
    // Articles statistics
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_articles,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_articles,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_articles
        FROM articles");
    $stmt->execute();
    $article_stats = $stmt->fetch();
    
    // Gallery statistics
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_media,
        SUM(CASE WHEN file_type = 'image' THEN 1 ELSE 0 END) as total_images,
        SUM(CASE WHEN file_type = 'video' THEN 1 ELSE 0 END) as total_videos
        FROM gallery WHERE status = 'active'");
    $stmt->execute();
    $gallery_stats = $stmt->fetch();
    
    // Contact messages statistics
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_messages,
        SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread_messages
        FROM contacts");
    $stmt->execute();
    $contact_stats = $stmt->fetch();
    
    // Recent articles
    $stmt = $pdo->prepare("SELECT id, title, status, created_at FROM articles ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_articles = $stmt->fetchAll();
    
    // Recent contacts
    $stmt = $pdo->prepare("SELECT name, subject, status, created_at FROM contacts ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_contacts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $article_stats = ['total_articles' => 0, 'published_articles' => 0, 'draft_articles' => 0];
    $gallery_stats = ['total_media' => 0, 'total_images' => 0, 'total_videos' => 0];
    $contact_stats = ['total_messages' => 0, 'unread_messages' => 0];
    $recent_articles = [];
    $recent_contacts = [];
}

$page_title = 'Dashboard Masjid';
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
                        <i class="fas fa-mosque text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">CMS Masjid</h1>
                        <p class="text-sm text-gray-500">Content Management System</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../index.php"  class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Website
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
                            <a href="../dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard Utama
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
                <a href="dashboard.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                
                <a href="berita.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-images mr-3"></i>Kelola Galeri
                </a>
                
                <a href="jadwal_jumat.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-calendar-alt mr-3"></i>Jadwal Jumat
                </a>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Kelola Donasi
                </a>
                
                <!-- <a href="konten.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-file-alt mr-3"></i>Kelola Konten
                </a> -->
                
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
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-green-100 rounded-full p-3">
                                <i class="fas fa-globe text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-lg font-medium text-gray-900">
                                Selamat datang di CMS Masjid, <?php echo htmlspecialchars($current_user['full_name']); ?>!
                            </h2>
                            <p class="text-sm text-gray-500">
                                Kelola konten website masjid dengan mudah dan efisien
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Articles Stats -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-newspaper text-blue-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Artikel</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $article_stats['total_articles']; ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex text-sm text-gray-500">
                                <span class="text-green-600"><?php echo $article_stats['published_articles']; ?> Published</span>
                                <span class="mx-2">•</span>
                                <span class="text-yellow-600"><?php echo $article_stats['draft_articles']; ?> Draft</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallery Stats -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-images text-purple-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Media Galeri</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $gallery_stats['total_media']; ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex text-sm text-gray-500">
                                <span class="text-blue-600"><?php echo $gallery_stats['total_images']; ?> Foto</span>
                                <span class="mx-2">•</span>
                                <span class="text-red-600"><?php echo $gallery_stats['total_videos']; ?> Video</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Messages -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-green-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pesan Kontak</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $contact_stats['total_messages']; ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex text-sm">
                                <?php if ($contact_stats['unread_messages'] > 0): ?>
                                    <span class="text-red-600 font-medium"><?php echo $contact_stats['unread_messages']; ?> Belum dibaca</span>
                                <?php else: ?>
                                    <span class="text-green-600">Semua sudah dibaca</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="text-center">
                            <i class="fas fa-plus-circle text-orange-600 text-3xl mb-2"></i>
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Aksi Cepat</h3>
                            <div class="space-y-2">
                                <a href="berita.php?action=add" class="block bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">
                                    + Artikel Baru
                                </a>
                                <a href="galeri.php?action=add" class="block bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                                    + Upload Media
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="grid grid-cols-1 gap-6">
                <!-- Recent Articles -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Artikel Terbaru</h3>
                        <?php if (!empty($recent_articles)): ?>
                            <div class="space-y-3">
                                <?php foreach ($recent_articles as $article): ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo date('d M Y H:i', strtotime($article['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $article['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $article['status'] === 'published' ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4">
                                <a href="berita.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                    Lihat semua artikel <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-newspaper text-gray-300 text-3xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Belum ada artikel</p>
                                <a href="berita.php?action=add" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                    Buat artikel pertama
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>