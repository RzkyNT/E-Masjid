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
$page_description = 'Content Management System';

// Include admin header with sidebar
include '../../partials/admin_header.php';
?>

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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
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

                <!-- Contact Messages
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
                </div> -->

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

    <script>
        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>

<?php include '../../partials/admin_footer.php'; ?>