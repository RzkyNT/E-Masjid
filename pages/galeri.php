<?php
require_once '../config/config.php';
require_once '../includes/settings_loader.php';

// Initialize website settings
$settings = initializePageSettings();

// Set page variables
$page_title = 'Galeri Foto & Video';
$page_description = 'Galeri foto dan video kegiatan Masjid Jami Al-Muhajirin';
$breadcrumb = [
    ['title' => 'Galeri', 'url' => '']
];

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$valid_categories = ['all', 'kegiatan', 'fasilitas', 'kajian'];
if (!in_array($category_filter, $valid_categories)) {
    $category_filter = 'all';
}

// Pagination settings
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

try {
    // Build query based on category filter
    $where_clause = "WHERE status = 'active'";
    $params = [];
    
    if ($category_filter !== 'all') {
        $where_clause .= " AND category = :category";
        $params[':category'] = $category_filter;
    }
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM gallery " . $where_clause;
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Get gallery items
    $sql = "SELECT * FROM gallery " . $where_clause . " ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $gallery_items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $gallery_items = [];
    $total_pages = 0;
    error_log("Gallery query error: " . $e->getMessage());
}

include '../partials/header.php';
?>

<div class="bg-white">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <i class="fas fa-images mr-3"></i>Galeri Foto & Video
                </h1>
                <p class="text-xl text-green-100 max-w-3xl mx-auto">
                    Dokumentasi kegiatan dan fasilitas Masjid Jami Al-Muhajirin
                </p>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="bg-gray-50 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-wrap justify-center gap-2">
                <a href="?category=all<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                   class="<?php echo $category_filter === 'all' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'; ?> px-4 py-2 rounded-full border border-green-600 transition duration-200 text-sm font-medium">
                    <i class="fas fa-th mr-1"></i>Semua
                </a>
                <a href="?category=kegiatan<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                   class="<?php echo $category_filter === 'kegiatan' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'; ?> px-4 py-2 rounded-full border border-green-600 transition duration-200 text-sm font-medium">
                    <i class="fas fa-calendar-alt mr-1"></i>Kegiatan
                </a>
                <a href="?category=fasilitas<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                   class="<?php echo $category_filter === 'fasilitas' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'; ?> px-4 py-2 rounded-full border border-green-600 transition duration-200 text-sm font-medium">
                    <i class="fas fa-building mr-1"></i>Fasilitas
                </a>
                <a href="?category=kajian<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                   class="<?php echo $category_filter === 'kajian' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'; ?> px-4 py-2 rounded-full border border-green-600 transition duration-200 text-sm font-medium">
                    <i class="fas fa-book-open mr-1"></i>Kajian
                </a>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (empty($gallery_items)): ?>
            <div class="text-center py-16">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-images text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Galeri</h3>
                <p class="text-gray-600 mb-6">
                    <?php if ($category_filter !== 'all'): ?>
                        Belum ada foto atau video untuk kategori <?php echo ucfirst($category_filter); ?>.
                    <?php else: ?>
                        Galeri foto dan video akan segera ditambahkan.
                    <?php endif; ?>
                </p>
                <?php if ($category_filter !== 'all'): ?>
                    <a href="?category=all" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-th mr-2"></i>Lihat Semua Galeri
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Gallery Items Count -->
            <div class="mb-8">
                <p class="text-gray-600">
                    Menampilkan <?php echo count($gallery_items); ?> dari <?php echo $total_items; ?> item
                    <?php if ($category_filter !== 'all'): ?>
                        untuk kategori <strong><?php echo ucfirst($category_filter); ?></strong>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Gallery Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($gallery_items as $item): ?>
                    <div class="gallery-item group cursor-pointer" 
                         data-type="<?php echo htmlspecialchars($item['file_type']); ?>"
                         data-src="<?php echo htmlspecialchars($base_url . '/' . $item['file_path']); ?>"
                         data-title="<?php echo htmlspecialchars($item['title']); ?>"
                         data-description="<?php echo htmlspecialchars($item['description'] ?? ''); ?>">
                        
                        <div class="relative bg-gray-200 rounded-lg overflow-hidden aspect-square">
                            <?php if ($item['file_type'] === 'image'): ?>
                                <!-- Image Item -->
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3C/svg%3E"
                                     data-src="<?php echo htmlspecialchars($base_url . '/' . $item['file_path']); ?>"
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     class="lazy-image w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                
                                <!-- Image Overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition duration-300">
                                        <i class="fas fa-search-plus text-white text-2xl"></i>
                                    </div>
                                </div>
                                
                            <?php else: ?>
                                <!-- Video Item -->
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center relative">
                                    <!-- Video Thumbnail (if available) or placeholder -->
                                    <div class="text-white text-center">
                                        <i class="fas fa-play-circle text-6xl mb-2 opacity-80"></i>
                                        <p class="text-sm">Video</p>
                                    </div>
                                    
                                    <!-- Video Overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition duration-300 flex items-center justify-center">
                                        <div class="opacity-0 group-hover:opacity-100 transition duration-300">
                                            <i class="fas fa-play text-white text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Category Badge -->
                            <div class="absolute top-2 left-2">
                                <span class="bg-green-600 text-white text-xs px-2 py-1 rounded-full">
                                    <?php echo ucfirst($item['category']); ?>
                                </span>
                            </div>
                            
                            <!-- Type Badge -->
                            <div class="absolute top-2 right-2">
                                <span class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-<?php echo $item['file_type'] === 'image' ? 'image' : 'video'; ?>"></i>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Item Info -->
                        <div class="mt-3">
                            <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition duration-200 line-clamp-2">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <?php if (!empty($item['description'])): ?>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?category=<?php echo $category_filter; ?>&page=<?php echo $page - 1; ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md transition duration-200">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?category=<?php echo $category_filter; ?>&page=<?php echo $i; ?>" 
                               class="<?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 border border-gray-300 rounded-md transition duration-200">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?category=<?php echo $category_filter; ?>&page=<?php echo $page + 1; ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md transition duration-200">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full w-full">
        <!-- Close Button -->
        <button id="lightbox-close" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <!-- Navigation Buttons -->
        <button id="lightbox-prev" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
            <i class="fas fa-chevron-left text-xl"></i>
        </button>
        <button id="lightbox-next" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
            <i class="fas fa-chevron-right text-xl"></i>
        </button>
        
        <!-- Content Container -->
        <div id="lightbox-content" class="bg-white rounded-lg overflow-hidden max-h-full flex flex-col">
            <!-- Media Container -->
            <div id="lightbox-media" class="flex-1 flex items-center justify-center bg-black">
                <!-- Content will be inserted here -->
            </div>
            
            <!-- Info Panel -->
            <div id="lightbox-info" class="p-6 bg-white">
                <h3 id="lightbox-title" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                <p id="lightbox-description" class="text-gray-600"></p>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.lazy-image {
    transition: opacity 0.3s;
}

.lazy-image[data-src] {
    opacity: 0;
}

.lazy-image.loaded {
    opacity: 1;
}

#lightbox img, #lightbox video {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
}
</style>

<!-- JavaScript for Lightbox and Lazy Loading -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lazy Loading Implementation
    const lazyImages = document.querySelectorAll('.lazy-image[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Lightbox Implementation
    const lightbox = document.getElementById('lightbox');
    const lightboxMedia = document.getElementById('lightbox-media');
    const lightboxTitle = document.getElementById('lightbox-title');
    const lightboxDescription = document.getElementById('lightbox-description');
    const lightboxClose = document.getElementById('lightbox-close');
    const lightboxPrev = document.getElementById('lightbox-prev');
    const lightboxNext = document.getElementById('lightbox-next');
    
    const galleryItems = document.querySelectorAll('.gallery-item');
    let currentIndex = 0;
    
    function openLightbox(index) {
        currentIndex = index;
        const item = galleryItems[index];
        const type = item.dataset.type;
        const src = item.dataset.src;
        const title = item.dataset.title;
        const description = item.dataset.description;
        
        // Clear previous content
        lightboxMedia.innerHTML = '';
        
        // Add media content
        if (type === 'image') {
            const img = document.createElement('img');
            img.src = src;
            img.alt = title;
            img.className = 'max-w-full max-h-full object-contain';
            lightboxMedia.appendChild(img);
        } else if (type === 'video') {
            // For video, we'll create a simple video player
            // In a real implementation, you might want to handle different video sources
            const video = document.createElement('video');
            video.src = src;
            video.controls = true;
            video.className = 'max-w-full max-h-full';
            lightboxMedia.appendChild(video);
        }
        
        // Set info
        lightboxTitle.textContent = title;
        lightboxDescription.textContent = description;
        
        // Show lightbox
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Update navigation buttons
        lightboxPrev.style.display = currentIndex > 0 ? 'flex' : 'none';
        lightboxNext.style.display = currentIndex < galleryItems.length - 1 ? 'flex' : 'none';
    }
    
    function closeLightbox() {
        lightbox.classList.add('hidden');
        document.body.style.overflow = '';
        lightboxMedia.innerHTML = '';
    }
    
    function showPrevious() {
        if (currentIndex > 0) {
            openLightbox(currentIndex - 1);
        }
    }
    
    function showNext() {
        if (currentIndex < galleryItems.length - 1) {
            openLightbox(currentIndex + 1);
        }
    }
    
    // Event listeners
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
    });
    
    lightboxClose.addEventListener('click', closeLightbox);
    lightboxPrev.addEventListener('click', showPrevious);
    lightboxNext.addEventListener('click', showNext);
    
    // Close on background click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('hidden')) {
            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    showPrevious();
                    break;
                case 'ArrowRight':
                    showNext();
                    break;
            }
        }
    });
});
</script>

<?php include '../partials/footer.php'; ?>