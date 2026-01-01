<?php
/**
 * Doa Page - Direct Display
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays all 108 doa directly with advanced search and category filtering
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';
require_once __DIR__ . '/../includes/advanced_search_engine.php';
require_once __DIR__ . '/../includes/direct_display_engine.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();
$searchEngine = new AdvancedSearchEngine($api);
$displayEngine = new DirectDisplayEngine($api, $renderer);

// Handle parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20; // Show 20 doa per page
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Initialize variables
$content = [];
$searchResults = [];
$error_message = '';
$totalItems = 108;
$isSearchMode = !empty($search);
$isDetailMode = !empty($selectedId);

try {
    // Get display handler
    $displayHandler = $displayEngine->getDisplayHandler('doa');
    
    if ($isDetailMode) {
        // Detail mode - show specific doa
        $doaData = $api->getDoa($selectedId);
        if (!isset($doaData['data'])) {
            throw new Exception('Doa tidak ditemukan');
        }
    } elseif ($isSearchMode) {
        // Search mode
        $filters = [];
        if (!empty($category)) {
            $filters['category'] = $category;
        }
        
        try {
            $searchResults = $searchEngine->search($search, 'doa', $filters);
            $content = $searchResults['data'] ?? [];
            $totalItems = $searchResults['total'] ?? 0;
            
            // Log search for debugging
            error_log("Doa search: query='$search', results=" . count($content));
        } catch (Exception $searchError) {
            error_log("Doa search failed: " . $searchError->getMessage());
            $content = [];
            $totalItems = 0;
        }
    } else {
        // Direct display mode - show all content
        $allContent = $displayHandler->getDefaultContent();
        if ($allContent['success']) {
            $content = $allContent['data'];
            $totalItems = count($content);
            
            // Apply category filter if specified
            if (!empty($category)) {
                $content = array_filter($content, function($doa) use ($category) {
                    return isset($doa['category']) && $doa['category'] === $category;
                });
                $totalItems = count($content);
            }
        } else {
            throw new Exception($allContent['error'] ?? 'Gagal memuat data doa');
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Doa page error: " . $e->getMessage());
}

// Set page title and breadcrumb
$page_title = 'Doa-Doa - 108 Doa Pilihan';
if ($isDetailMode) {
    $page_title = 'Doa #' . $selectedId . ' - Doa-Doa';
} elseif ($isSearchMode) {
    $page_title = 'Pencarian Doa: ' . htmlspecialchars($search);
} elseif (!empty($category)) {
    $categoryNames = [
        'harian' => 'Doa Harian',
        'ibadah' => 'Doa Ibadah', 
        'perlindungan' => 'Doa Perlindungan',
        'khusus' => 'Doa Khusus'
    ];
    $page_title = ($categoryNames[$category] ?? 'Kategori') . ' - Doa-Doa';
}

$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Doa-Doa', 'url' => 'doa.php']
];

if ($isDetailMode) {
    $breadcrumb[] = ['title' => 'Doa #' . $selectedId, 'url' => ''];
} elseif ($isSearchMode) {
    $breadcrumb[] = ['title' => 'Hasil Pencarian', 'url' => ''];
} elseif (!empty($category)) {
    $breadcrumb[] = ['title' => $categoryNames[$category] ?? 'Kategori', 'url' => ''];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/islamic-content.css">
</head>
<body class="bg-gray-50">
    <?php include '../partials/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">

        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-hands text-purple-600 mr-3"></i>
                        <?php 
                        if ($isDetailMode) {
                            echo 'Doa #' . $selectedId;
                        } elseif ($isSearchMode) {
                            echo 'Hasil Pencarian Doa';
                        } elseif (!empty($category)) {
                            echo $categoryNames[$category] ?? 'Kategori Doa';
                        } else {
                            echo '108 Doa Pilihan';
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($isDetailMode) {
                            echo 'Detail doa dengan teks Arab, transliterasi, dan terjemahan';
                        } elseif ($isSearchMode) {
                            echo 'Hasil pencarian untuk "' . htmlspecialchars($search) . '" - ' . $totalItems . ' doa ditemukan';
                        } elseif (!empty($category)) {
                            echo 'Doa-doa dalam kategori ' . strtolower($categoryNames[$category] ?? $category);
                        } else {
                            echo 'Kumpulan doa-doa pilihan dari Al-Quran, Hadits, dan sumber terpercaya';
                        }
                        ?>
                    </p>
                </div>
                
                <?php if (!$isDetailMode): ?>
                <!-- Font Size Controls -->
                <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                    <span class="text-sm text-gray-700 font-medium">Ukuran Font:</span>
                    <button onclick="changeIslamicFontSize('decrease')" 
                            class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-700 rounded text-sm transition duration-200 font-medium border"
                            title="Perkecil font"
                            aria-label="Perkecil ukuran font">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span id="islamic-font-size-indicator" class="text-sm text-gray-700 min-w-[3rem] text-center">100%</span>
                    <button onclick="changeIslamicFontSize('increase')" 
                            class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-700 rounded text-sm transition duration-200 font-medium border"
                            title="Perbesar font"
                            aria-label="Perbesar ukuran font">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button onclick="resetIslamicFontSize()" 
                            class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-600 rounded text-sm transition duration-200 ml-2 border"
                            title="Reset ukuran font"
                            aria-label="Reset ukuran font ke default">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isDetailMode): ?>
            <!-- Detail Mode - Show specific doa -->
            <div class="mb-6">
                <a href="doa.php" 
                   class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Doa
                </a>
            </div>
            
            <?php if (isset($doaData)): ?>
                <div id="doa-detail">
                    <?php echo $renderer->renderDoa($doaData); ?>
                </div>
                
                <!-- Navigation to other doa -->
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <?php if ($selectedId > 1): ?>
                                <a href="?id=<?php echo $selectedId - 1; ?>" 
                                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                                    <i class="fas fa-chevron-left mr-2"></i>Doa Sebelumnya
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($selectedId < 108): ?>
                                <a href="?id=<?php echo $selectedId + 1; ?>" 
                                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                                    Doa Selanjutnya<i class="fas fa-chevron-right ml-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            Doa <?php echo $selectedId; ?> dari 108
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- List Mode - Show all doa with search and filters -->
            
            <!-- Advanced Search Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-search text-purple-600 mr-2"></i>
                        Pencarian Doa
                    </h2>
                </div>
                
                <form method="GET" action="doa.php" class="mb-4">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <div class="flex gap-2 mb-4">
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   id="doa-search"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Cari berdasarkan judul, isi doa, atau situasi..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <button type="submit" 
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <?php if ($isSearchMode || !empty($category)): ?>
                            <a href="doa.php" 
                               class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-times mr-2"></i>Hapus
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Category filters -->
                <div class="flex flex-wrap gap-2">
                    <a href="doa.php" 
                       class="category-filter-btn <?php echo empty($category) ? 'active bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?> px-3 py-2 rounded-full text-sm transition duration-200">
                        Semua (108)
                    </a>
                    <a href="?category=harian" 
                       class="category-filter-btn <?php echo $category === 'harian' ? 'active bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?> px-3 py-2 rounded-full text-sm transition duration-200">
                        Harian (1-30)
                    </a>
                    <a href="?category=ibadah" 
                       class="category-filter-btn <?php echo $category === 'ibadah' ? 'active bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?> px-3 py-2 rounded-full text-sm transition duration-200">
                        Ibadah (31-60)
                    </a>
                    <a href="?category=perlindungan" 
                       class="category-filter-btn <?php echo $category === 'perlindungan' ? 'active bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?> px-3 py-2 rounded-full text-sm transition duration-200">
                        Perlindungan (61-90)
                    </a>
                    <a href="?category=khusus" 
                       class="category-filter-btn <?php echo $category === 'khusus' ? 'active bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?> px-3 py-2 rounded-full text-sm transition duration-200">
                        Khusus (91-108)
                    </a>
                </div>
            </div>

            <!-- Content Display -->
            <?php if (empty($error_message)): ?>
                <?php if (!empty($content)): ?>
                    <!-- Results count -->
                    <div class="mb-4 text-sm text-gray-600">
                        <?php if ($isSearchMode): ?>
                            Menampilkan <?php echo count($content); ?> dari <?php echo $totalItems; ?> hasil
                            <?php if (isset($searchResults['search_time'])): ?>
                                (<?php echo number_format(($searchResults['search_time'] - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2); ?>ms)
                            <?php endif; ?>
                        <?php elseif (!empty($category)): ?>
                            Menampilkan <?php echo count($content); ?> doa dalam kategori <?php echo $categoryNames[$category] ?? $category; ?>
                        <?php else: ?>
                            Menampilkan <?php echo count($content); ?> doa pilihan
                        <?php endif; ?>
                    </div>
                    
                    <!-- Category Statistics -->
                    <?php if (!$isSearchMode): ?>
                        <?php
                        $categoryStats = ['harian' => 0, 'ibadah' => 0, 'perlindungan' => 0, 'khusus' => 0];
                        foreach ($content as $doa) {
                            $doaData = $isSearchMode ? $doa['data'] : $doa;
                            if (isset($doaData['category']) && isset($categoryStats[$doaData['category']])) {
                                $categoryStats[$doaData['category']]++;
                            }
                        }
                        ?>
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mb-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-yellow-600"><?php echo $categoryStats['harian']; ?></div>
                                    <div class="text-sm text-gray-600">Doa Harian</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-green-600"><?php echo $categoryStats['ibadah']; ?></div>
                                    <div class="text-sm text-gray-600">Doa Ibadah</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-blue-600"><?php echo $categoryStats['perlindungan']; ?></div>
                                    <div class="text-sm text-gray-600">Doa Perlindungan</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-purple-600"><?php echo $categoryStats['khusus']; ?></div>
                                    <div class="text-sm text-gray-600">Doa Khusus</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Content Grid by Category -->
                    <div id="doa-content">
                        <?php
                        // Group content by category
                        $categorizedContent = [
                            'harian' => [],
                            'ibadah' => [],
                            'perlindungan' => [],
                            'khusus' => []
                        ];
                        
                        foreach ($content as $doa) {
                            $doaData = $isSearchMode ? $doa['data'] : $doa;
                            $doaCategory = $doaData['category'] ?? 'lainnya';
                            if (isset($categorizedContent[$doaCategory])) {
                                $categorizedContent[$doaCategory][] = $doa;
                            }
                        }
                        
                        $categoryNames = [
                            'harian' => 'Doa Harian',
                            'ibadah' => 'Doa Ibadah',
                            'perlindungan' => 'Doa Perlindungan',
                            'khusus' => 'Doa Khusus'
                        ];
                        
                        foreach ($categorizedContent as $cat => $doas):
                            if (empty($doas)) continue;
                            
                            // Skip category grouping if filtering by specific category or searching
                            if (!empty($category) || $isSearchMode) {
                                // Show all items without category headers
                                ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($doas as $doa): ?>
                                        <?php 
                                        $doaData = $isSearchMode ? $doa['data'] : $doa;
                                        $doaId = $doaData['id'] ?? '';
                                        ?>
                                        <div class="doa-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition duration-200" data-id="<?php echo $doaId; ?>">
                                            <div class="flex items-start justify-between mb-2">
                                                <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($doaData['judul'] ?? 'Doa ' . $doaId); ?></h4>
                                                <span class="text-sm text-purple-600 font-medium">#<?php echo $doaId; ?></span>
                                            </div>
                                            
                                            <?php if (isset($doaData['arab'])): ?>
                                                <div class="text-right mb-2 text-lg font-arabic text-gray-800"><?php echo htmlspecialchars(substr($doaData['arab'], 0, 100)); ?>...</div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($doaData['arti'])): ?>
                                                <div class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($doaData['arti'], 0, 100)); ?>...</div>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center justify-between mt-3">
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo ucfirst($cat); ?></span>
                                                <a href="?id=<?php echo $doaId; ?>" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                                    <i class="fas fa-eye mr-1"></i>Lihat Detail
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php
                                break; // Only show one category when filtering
                            } else {
                                // Show with category headers
                                ?>
                                <div class="category-section mb-8" data-category="<?php echo $cat; ?>">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-folder text-purple-600 mr-2"></i>
                                        <?php echo $categoryNames[$cat]; ?> (<?php echo count($doas); ?>)
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php foreach ($doas as $doa): ?>
                                            <?php 
                                            $doaData = $doa;
                                            $doaId = $doaData['id'] ?? '';
                                            ?>
                                            <div class="doa-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition duration-200" data-id="<?php echo $doaId; ?>">
                                                <div class="flex items-start justify-between mb-2">
                                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($doaData['judul'] ?? 'Doa ' . $doaId); ?></h4>
                                                    <span class="text-sm text-purple-600 font-medium">#<?php echo $doaId; ?></span>
                                                </div>
                                                
                                                <?php if (isset($doaData['arab'])): ?>
                                                    <div class="text-right mb-2 text-lg font-arabic text-gray-800"><?php echo htmlspecialchars(substr($doaData['arab'], 0, 100)); ?>...</div>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($doaData['arti'])): ?>
                                                    <div class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars(substr($doaData['arti'], 0, 100)); ?>...</div>
                                                <?php endif; ?>
                                                
                                                <div class="flex items-center justify-between mt-3">
                                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo ucfirst($cat); ?></span>
                                                    <a href="?id=<?php echo $doaId; ?>" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                                        <i class="fas fa-eye mr-1"></i>Lihat Detail
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        endforeach;
                        ?>
                    </div>
                    
                <?php else: ?>
                    <!-- No content message -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <i class="fas fa-search text-gray-400 text-6xl mb-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            <?php echo $isSearchMode ? 'Tidak ada hasil ditemukan' : 'Tidak ada data'; ?>
                        </h2>
                        <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                            <?php 
                            if ($isSearchMode) {
                                echo 'Tidak ada doa yang cocok dengan pencarian "' . htmlspecialchars($search) . '". Coba gunakan kata kunci yang berbeda.';
                            } else {
                                echo 'Data doa tidak dapat dimuat. Silakan coba lagi nanti.';
                            }
                            ?>
                        </p>
                        <?php if ($isSearchMode || !empty($category)): ?>
                            <a href="doa.php" 
                               class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-list mr-2"></i>
                                Lihat Semua Doa
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Error Message -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-red-800 font-medium text-lg mb-2">Terjadi Kesalahan</h3>
                            <p class="text-red-700 mb-4"><?php echo htmlspecialchars($error_message); ?></p>
                            <button onclick="window.location.reload()" 
                                    class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-800 rounded-md text-sm font-medium transition duration-200">
                                <i class="fas fa-refresh mr-2"></i>Muat Ulang
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/islamic-content.js"></script>
    <script>
        // Background loader for missing doa
        let missingDoaLoader = {
            isLoading: false,
            loadedCount: 0,
            totalMissing: 22,
            
            async loadMissingDoa() {
                if (this.isLoading) return;
                
                this.isLoading = true;
                this.showLoadingIndicator();
                
                try {
                    let startId = 87;
                    let hasMore = true;
                    
                    while (hasMore && startId <= 108) {
                        const response = await fetch(`../api/load_missing_doa.php?start=${startId}&batch=3`);
                        const data = await response.json();
                        
                        if (data.success && data.data.length > 0) {
                            this.loadedCount += data.data.length;
                            this.updateLoadingProgress();
                            
                            // Add loaded doa to the page
                            this.addDoaToPage(data.data);
                        }
                        
                        hasMore = data.has_more;
                        startId = data.next_start;
                        
                        // Wait between batches to avoid rate limiting
                        if (hasMore) {
                            await new Promise(resolve => setTimeout(resolve, 2000));
                        }
                    }
                    
                    this.hideLoadingIndicator();
                    this.showSuccessMessage();
                    
                } catch (error) {
                    console.error('Error loading missing doa:', error);
                    this.showErrorMessage();
                } finally {
                    this.isLoading = false;
                }
            },
            
            showLoadingIndicator() {
                const indicator = document.createElement('div');
                indicator.id = 'doa-loading-indicator';
                indicator.className = 'fixed bottom-4 right-4 bg-purple-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                indicator.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="fas fa-spinner fa-spin"></i>
                        <div>
                            <div class="font-medium">Memuat Doa Khusus...</div>
                            <div class="text-sm opacity-90">0 dari ${this.totalMissing} doa dimuat</div>
                        </div>
                    </div>
                `;
                document.body.appendChild(indicator);
            },
            
            updateLoadingProgress() {
                const indicator = document.getElementById('doa-loading-indicator');
                if (indicator) {
                    const progressText = indicator.querySelector('.text-sm');
                    if (progressText) {
                        progressText.textContent = `${this.loadedCount} dari ${this.totalMissing} doa dimuat`;
                    }
                }
            },
            
            hideLoadingIndicator() {
                const indicator = document.getElementById('doa-loading-indicator');
                if (indicator) {
                    indicator.remove();
                }
            },
            
            showSuccessMessage() {
                const message = document.createElement('div');
                message.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                message.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <div class="font-medium">Berhasil!</div>
                            <div class="text-sm opacity-90">${this.loadedCount} Doa Khusus berhasil dimuat</div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                document.body.appendChild(message);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (message.parentElement) {
                        message.remove();
                    }
                }, 5000);
            },
            
            showErrorMessage() {
                const message = document.createElement('div');
                message.className = 'fixed bottom-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                message.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <div class="font-medium">Gagal memuat doa</div>
                            <div class="text-sm opacity-90">Silakan coba lagi nanti</div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                document.body.appendChild(message);
            },
            
            addDoaToPage(doaList) {
                const khususSection = document.querySelector('[data-category="khusus"]');
                if (!khususSection) {
                    // Create khusus section if it doesn't exist
                    this.createKhususSection();
                }
                
                const khususGrid = document.querySelector('[data-category="khusus"] .grid');
                if (khususGrid) {
                    doaList.forEach(doa => {
                        const doaElement = this.createDoaElement(doa);
                        khususGrid.appendChild(doaElement);
                    });
                    
                    // Update section title with count
                    const sectionTitle = document.querySelector('[data-category="khusus"] h3');
                    if (sectionTitle) {
                        const currentCount = khususGrid.children.length;
                        sectionTitle.innerHTML = `
                            <i class="fas fa-folder text-purple-600 mr-2"></i>
                            Doa Khusus (${currentCount})
                        `;
                    }
                }
            },
            
            createKhususSection() {
                const doaContent = document.getElementById('doa-content');
                if (doaContent) {
                    const section = document.createElement('div');
                    section.className = 'category-section mb-8';
                    section.setAttribute('data-category', 'khusus');
                    section.innerHTML = `
                        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-folder text-purple-600 mr-2"></i>
                            Doa Khusus (0)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                    `;
                    doaContent.appendChild(section);
                }
            },
            
            createDoaElement(doa) {
                const element = document.createElement('div');
                element.className = 'doa-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition duration-200';
                element.setAttribute('data-id', doa.id);
                
                element.innerHTML = `
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-gray-900">${doa.judul || 'Doa #' + doa.id}</h4>
                        <span class="text-sm text-purple-600 font-medium">#${doa.id}</span>
                    </div>
                    ${doa.arab ? `<div class="text-right mb-2 text-lg font-arabic text-gray-800">${doa.arab.substring(0, 100)}...</div>` : ''}
                    ${doa.arti ? `<div class="text-sm text-gray-600 mb-2">${doa.arti.substring(0, 100)}...</div>` : ''}
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Khusus</span>
                        <a href="?id=${doa.id}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>Lihat Detail
                        </a>
                    </div>
                `;
                
                return element;
            }
        };
        
        // Auto-load missing doa when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we're on the main doa page (not detail or search)
            const urlParams = new URLSearchParams(window.location.search);
            const isMainPage = !urlParams.has('id') && !urlParams.has('search');
            
            if (isMainPage) {
                // Check if khusus category is empty
                const khususSection = document.querySelector('[data-category="khusus"]');
                const khususGrid = khususSection ? khususSection.querySelector('.grid') : null;
                const khususCount = khususGrid ? khususGrid.children.length : 0;
                
                if (khususCount === 0) {
                    // Show notification about missing doa
                    setTimeout(() => {
                        const notification = document.createElement('div');
                        notification.className = 'fixed bottom-4 left-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        notification.innerHTML = `
                            <div class="flex items-center gap-3">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <div class="font-medium">Doa Khusus belum dimuat</div>
                                    <div class="text-sm opacity-90">Klik untuk memuat 22 doa khusus</div>
                                </div>
                                <button onclick="missingDoaLoader.loadMissingDoa(); this.parentElement.parentElement.remove();" 
                                        class="ml-2 bg-blue-500 hover:bg-blue-400 px-3 py-1 rounded text-sm">
                                    Muat Sekarang
                                </button>
                                <button onclick="this.parentElement.parentElement.remove()" class="ml-1 text-white hover:text-gray-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        document.body.appendChild(notification);
                    }, 2000);
                }
            }
        });
        
        // View doa detail
        function viewDoaDetail(id) {
            window.location.href = `?id=${id}`;
        }
        
        // Filter by category
        function filterByCategory(category) {
            const currentUrl = new URL(window.location);
            if (category === 'all') {
                currentUrl.searchParams.delete('category');
            } else {
                currentUrl.searchParams.set('category', category);
            }
            currentUrl.searchParams.delete('search'); // Clear search when filtering
            window.location.href = currentUrl.toString();
        }
        
        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('doa-search');
            const searchForm = searchInput ? searchInput.closest('form') : null;
            
            if (searchInput && searchForm) {
                let searchTimeout;
                
                // Handle form submission
                searchForm.addEventListener('submit', function(e) {
                    const query = searchInput.value.trim();
                    if (query.length === 0) {
                        e.preventDefault();
                        window.location.href = 'doa.php';
                        return;
                    }
                });
                
                // Real-time search with debouncing
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    searchTimeout = setTimeout(() => {
                        if (query.length >= 2) {
                            // Show loading indicator
                            showSearchLoading();
                            
                            // Perform search
                            const searchUrl = new URL(window.location.origin + window.location.pathname);
                            searchUrl.searchParams.set('search', query);
                            
                            // Keep current category if any
                            const currentCategory = new URLSearchParams(window.location.search).get('category');
                            if (currentCategory) {
                                searchUrl.searchParams.set('category', currentCategory);
                            }
                            
                            window.location.href = searchUrl.toString();
                        } else if (query.length === 0) {
                            // Clear search
                            window.location.href = 'doa.php';
                        }
                    }, 800); // Wait 800ms after user stops typing
                });
                
                // Handle Enter key
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimeout);
                        searchForm.dispatchEvent(new Event('submit'));
                    }
                });
            }
        });
        
        // Show search loading indicator
        function showSearchLoading() {
            const content = document.getElementById('doa-content');
            if (content) {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-4"></i>
                        <p class="text-lg text-gray-600">Mencari doa...</p>
                        <p class="text-sm text-gray-500 mt-2">Mohon tunggu sebentar</p>
                    </div>
                `;
            }
        }
        
        // Live search without page reload
        function performLiveSearch(query) {
            if (!query || query.trim() === '') {
                // If empty, reload to show all content
                window.location.href = 'doa.php';
                return;
            }
            
            // Show loading indicator
            const content = document.getElementById('doa-content');
            if (content) {
                content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i><p class="mt-2 text-gray-600">Mencari...</p></div>';
            }
            
            // Perform search via URL redirect
            const searchUrl = new URL(window.location);
            searchUrl.searchParams.set('search', query.trim());
            searchUrl.searchParams.delete('category'); // Clear category filter when searching
            
            setTimeout(() => {
                window.location.href = searchUrl.toString();
            }, 300);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('doa-search').focus();
                        break;
                }
            }
        });
        
        // Clear search
        function clearSearch() {
            window.location.href = 'doa.php';
        }
        
        // Show search suggestions
        function showSearchSuggestions() {
            const suggestions = ['makan', 'tidur', 'perjalanan', 'perlindungan', 'rezeki', 'kesehatan', 'belajar'];
            const searchInput = document.getElementById('doa-search');
            const randomSuggestion = suggestions[Math.floor(Math.random() * suggestions.length)];
            searchInput.value = randomSuggestion;
            searchInput.form.submit();
        }
    </script>
</body>
</html>