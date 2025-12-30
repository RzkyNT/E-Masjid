<?php
require_once '../config/config.php';
require_once '../includes/image_path_helper.php';

$base_url = '..';

// Get article slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: berita.php");
    exit();
}

try {
    // Get article details
    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name as author_name 
        FROM articles a 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.slug = ? AND a.status = 'published'
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header("HTTP/1.0 404 Not Found");
        include '../pages/404.php';
        exit();
    }
    
    // Get related articles (same category, excluding current article)
    $stmt = $pdo->prepare("
        SELECT id, title, slug, excerpt, featured_image, published_at, created_at 
        FROM articles 
        WHERE category = ? AND slug != ? AND status = 'published' 
        ORDER BY published_at DESC, created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$article['category'], $slug]);
    $related_articles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    include '../pages/500.php';
    exit();
}

// Set page meta data
$page_title = $article['title'];
$page_description = $article['excerpt'] ?: strip_tags(substr($article['content'], 0, 160));

// Breadcrumb
$breadcrumb = [
    ['title' => 'Berita & Kegiatan', 'url' => 'berita.php'],
    ['title' => $article['title'], 'url' => '']
];

include '../partials/header.php';
?>

<!-- Article Header -->
<section class="bg-white py-8 border-b">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Category Badge -->
        <div class="mb-4">
            <?php
            $category_colors = [
                'kajian' => 'bg-blue-100 text-blue-800',
                'pengumuman' => 'bg-yellow-100 text-yellow-800',
                'kegiatan' => 'bg-green-100 text-green-800'
            ];
            $color_class = $category_colors[$article['category']] ?? 'bg-gray-100 text-gray-800';
            ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $color_class; ?>">
                <i class="fas fa-tag mr-1"></i><?php echo ucfirst($article['category']); ?>
            </span>
        </div>
        
        <!-- Title -->
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight">
            <?php echo htmlspecialchars($article['title']); ?>
        </h1>
        
        <!-- Meta Information -->
        <div class="flex flex-wrap items-center text-sm text-gray-600 space-x-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-calendar mr-1"></i>
                <time datetime="<?php echo date('Y-m-d', strtotime($article['published_at'] ?: $article['created_at'])); ?>">
                    <?php echo date('d F Y', strtotime($article['published_at'] ?: $article['created_at'])); ?>
                </time>
            </div>
            
            <?php if ($article['author_name']): ?>
            <div class="flex items-center">
                <i class="fas fa-user mr-1"></i>
                <span><?php echo htmlspecialchars($article['author_name']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="flex items-center">
                <i class="fas fa-clock mr-1"></i>
                <span><?php echo ceil(str_word_count(strip_tags($article['content'])) / 200); ?> menit baca</span>
            </div>
        </div>
        
        <!-- Social Share Buttons -->
        <div class="flex items-center space-x-3 mb-6">
            <span class="text-sm text-gray-600">Bagikan:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
               target="_blank" 
               class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($article['title']); ?>" 
               target="_blank" 
               class="bg-blue-400 text-white px-3 py-2 rounded-lg hover:bg-blue-500 transition duration-200">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
               target="_blank" 
               class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fab fa-whatsapp"></i>
            </a>
            <button onclick="copyToClipboard()" 
                    class="bg-gray-600 text-white px-3 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                <i class="fas fa-link"></i>
            </button>
        </div>
    </div>
</section>

<!-- Article Content -->
<article class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            
            <!-- Featured Image -->
            <?php if ($article['featured_image']): ?>
            <div class="aspect-w-16 aspect-h-9">
                <img src="<?php echo htmlspecialchars(getImagePath($article['featured_image'], 'public')); ?>" 
                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                     class="w-full h-64 md:h-96 object-cover">
            </div>
            <?php endif; ?>
            
            <!-- Article Body -->
            <div class="p-8 md:p-12">
                <!-- Excerpt -->
                <?php if ($article['excerpt']): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-8">
                    <p class="text-lg text-green-800 font-medium leading-relaxed">
                        <?php echo htmlspecialchars($article['excerpt']); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="prose prose-lg max-w-none">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
                
                <!-- Tags (if any) -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Kategori:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $color_class; ?>">
                            <?php echo ucfirst($article['category']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Related Articles -->
<?php if (!empty($related_articles)): ?>
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Artikel Terkait</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($related_articles as $related): ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <!-- Featured Image -->
                <?php if ($related['featured_image']): ?>
                <div class="aspect-w-16 aspect-h-9">
                    <img src="<?php echo htmlspecialchars(getImagePath($related['featured_image'], 'public')); ?>" 
                         alt="<?php echo htmlspecialchars($related['title']); ?>"
                         class="w-full h-40 object-cover">
                </div>
                <?php else: ?>
                <div class="w-full h-40 bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center">
                    <i class="fas fa-newspaper text-white text-2xl"></i>
                </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="berita_detail.php?slug=<?php echo urlencode($related['slug']); ?>" 
                           class="hover:text-green-600 transition duration-200">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                        <?php 
                        $excerpt = $related['excerpt'] ?: strip_tags($related['title']);
                        echo htmlspecialchars(substr($excerpt, 0, 100) . (strlen($excerpt) > 100 ? '...' : ''));
                        ?>
                    </p>
                    
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('d M Y', strtotime($related['published_at'] ?: $related['created_at'])); ?>
                        </span>
                        <a href="berita_detail.php?slug=<?php echo urlencode($related['slug']); ?>" 
                           class="text-green-600 hover:text-green-700 font-medium">
                            Baca <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="berita.php" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-newspaper mr-2"></i>Lihat Semua Artikel
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Navigation -->
<section class="py-8 bg-gray-50 border-t">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <a href="berita.php" 
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Artikel
            </a>
            
            <div class="flex space-x-2">
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <button onclick="copyToClipboard()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-share mr-2"></i>Bagikan
                </button>
            </div>
        </div>
    </div>
</section>

<style>
.prose {
    color: #374151;
    line-height: 1.75;
}

.prose p {
    margin-bottom: 1.25em;
}

.prose h1, .prose h2, .prose h3, .prose h4 {
    color: #111827;
    font-weight: 600;
    margin-top: 2em;
    margin-bottom: 1em;
}

.prose h1 { font-size: 2.25em; }
.prose h2 { font-size: 1.875em; }
.prose h3 { font-size: 1.5em; }
.prose h4 { font-size: 1.25em; }

.prose ul, .prose ol {
    margin-bottom: 1.25em;
    padding-left: 1.625em;
}

.prose li {
    margin-bottom: 0.5em;
}

.prose blockquote {
    border-left: 4px solid #10b981;
    padding-left: 1em;
    margin: 1.5em 0;
    font-style: italic;
    background-color: #f0fdf4;
    padding: 1em;
    border-radius: 0.5em;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%;
}

.aspect-w-16 img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    padding: 16px;
    border-radius: 10px;
}

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
}
</style>

<script>
function copyToClipboard() {
    const url = window.location.href;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            showNotification('Link berhasil disalin!');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Link berhasil disalin!');
    }
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add reading progress indicator
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.createElement('div');
    progressBar.className = 'fixed top-0 left-0 h-1 bg-green-600 z-50 transition-all duration-150';
    progressBar.style.width = '0%';
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.body.offsetHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
});
</script>

<?php include '../partials/footer.php'; ?>