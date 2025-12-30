<?php
require_once '../config/config.php';
require_once '../includes/settings_loader.php';
require_once '../includes/image_path_helper.php';

$page_title = 'Berita & Kegiatan';
$page_description = 'Informasi terbaru dan kegiatan dari Masjid Jami Al-Muhajirin';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Pagination settings
$items_per_page = 6;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query
$where_conditions = ["status = 'published'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($category) && in_array($category, ['kajian', 'pengumuman', 'kegiatan'])) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM articles WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_articles = $count_stmt->fetch()['total'];
    
    // Get articles
    $sql = "SELECT id, title, slug, excerpt, category, featured_image, author_id, published_at, created_at 
            FROM articles 
            WHERE $where_clause 
            ORDER BY published_at DESC, created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
    
    // Calculate pagination
    $total_pages = ceil($total_articles / $items_per_page);
    
} catch (PDOException $e) {
    $articles = [];
    $total_articles = 0;
    $total_pages = 0;
}

// Breadcrumb
$breadcrumb = [
    ['title' => 'Berita & Kegiatan', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-green-600 to-teal-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Berita & Kegiatan</h1>
            <p class="text-xl opacity-90 mb-6">Informasi terbaru dan kegiatan dari masjid</p>
            
            <!-- Search and Filter -->
            <div class="max-w-2xl mx-auto">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Cari berita atau kegiatan..." 
                               class="w-full px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                    </div>
                    <div>
                        <select name="category" class="px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                            <option value="">Semua Kategori</option>
                            <option value="kajian" <?php echo $category === 'kajian' ? 'selected' : ''; ?>>Kajian</option>
                            <option value="pengumuman" <?php echo $category === 'pengumuman' ? 'selected' : ''; ?>>Pengumuman</option>
                            <option value="kegiatan" <?php echo $category === 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-white text-green-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Articles Grid -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Results Info -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="text-gray-600">
                    Menampilkan <?php echo count($articles); ?> dari <?php echo $total_articles; ?> artikel
                    <?php if (!empty($search)): ?>
                        untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($category)): ?>
                        dalam kategori <strong><?php echo ucfirst($category); ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (!empty($search) || !empty($category)): ?>
            <div>
                <a href="berita.php" class="text-green-600 hover:text-green-700 text-sm">
                    <i class="fas fa-times mr-1"></i>Reset Filter
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($articles)): ?>
        <!-- Articles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <?php foreach ($articles as $article): ?>
            <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <!-- Featured Image -->
                <?php if ($article['featured_image']): ?>
                <div class="aspect-w-16 aspect-h-9">
                    <img src="<?php echo htmlspecialchars(getImagePath($article['featured_image'], 'public')); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>"
                         class="w-full h-48 object-cover">
                </div>
                <?php else: ?>
                <div class="w-full h-48 bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center">
                    <i class="fas fa-newspaper text-white text-4xl"></i>
                </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="p-6">
                    <!-- Category Badge -->
                    <div class="mb-3">
                        <?php
                        $category_colors = [
                            'kajian' => 'bg-blue-100 text-blue-800',
                            'pengumuman' => 'bg-yellow-100 text-yellow-800',
                            'kegiatan' => 'bg-green-100 text-green-800'
                        ];
                        $color_class = $category_colors[$article['category']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                            <?php echo ucfirst($article['category']); ?>
                        </span>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                        <a href="berita_detail.php?slug=<?php echo urlencode($article['slug']); ?>" 
                           class="hover:text-green-600 transition duration-200">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                    </h2>
                    
                    <!-- Excerpt -->
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                        <?php 
                        $excerpt = $article['excerpt'] ?: strip_tags($article['title']);
                        echo htmlspecialchars(substr($excerpt, 0, 150) . (strlen($excerpt) > 150 ? '...' : ''));
                        ?>
                    </p>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('d M Y', strtotime($article['published_at'] ?: $article['created_at'])); ?>
                        </div>
                        <a href="berita_detail.php?slug=<?php echo urlencode($article['slug']); ?>" 
                           class="text-green-600 hover:text-green-700 font-medium">
                            Baca selengkapnya <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center">
            <nav class="flex items-center space-x-2">
                <!-- Previous Page -->
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>" 
                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>" 
                       class="px-3 py-2 text-sm font-medium <?php echo $i === $current_page ? 'text-white bg-green-600 border-green-600' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'; ?> border rounded-md">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <!-- Next Page -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>" 
                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- No Articles Found -->
        <div class="text-center py-12">
            <div class="bg-white rounded-xl shadow-md p-8 max-w-md mx-auto">
                <i class="fas fa-newspaper text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada artikel ditemukan</h3>
                <p class="text-gray-600 mb-4">
                    <?php if (!empty($search) || !empty($category)): ?>
                        Coba ubah kata kunci pencarian atau filter kategori.
                    <?php else: ?>
                        Belum ada artikel yang dipublikasikan.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || !empty($category)): ?>
                <a href="berita.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-refresh mr-2"></i>Lihat Semua Artikel
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ingin Mendapat Update Terbaru?</h2>
        <p class="text-gray-600 mb-8">Ikuti media sosial kami untuk mendapat informasi kegiatan dan kajian terbaru</p>
        
        <div class="flex justify-center space-x-4">
            <a href="#" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fab fa-facebook-f mr-2"></i>Facebook
            </a>
            <a href="#" class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 transition duration-200">
                <i class="fab fa-instagram mr-2"></i>Instagram
            </a>
            <a href="#" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fab fa-whatsapp mr-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
}

.aspect-w-16 img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>

<?php include '../partials/footer.php'; ?>