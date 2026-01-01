<?php
/**
 * Hadits Page - Direct Display
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays hadits collections directly with advanced search and filtering
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6
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
$collection = $_GET['collection'] ?? '';
$slug = $_GET['slug'] ?? '';
$nomor = isset($_GET['nomor']) ? (int)$_GET['nomor'] : null;
$page = (int)($_GET['page'] ?? 1);
$limit = 20;

// Initialize variables
$content = [];
$searchResults = [];
$haditsData = null;
$error_message = '';
$isSearchMode = !empty($search);
$isDetailMode = !empty($nomor) && !empty($collection);
$isCollectionView = !empty($collection) && empty($nomor);

// Define available collections
$available_collections = [
    'arbain' => [
        'name' => 'Hadits Arbain',
        'description' => '42 Hadits Pilihan',
        'total' => 42,
        'icon' => 'fas fa-star',
        'color' => 'green'
    ],
    'bulughul_maram' => [
        'name' => 'Bulughul Maram',
        'description' => 'Karya Ibnu Hajar Al-Asqalani',
        'total' => 1597,
        'icon' => 'fas fa-book',
        'color' => 'blue'
    ],
    'perawi' => [
        'name' => 'Hadits Perawi',
        'description' => 'Berbagai Perawi Terpercaya',
        'total' => 'Bervariasi',
        'icon' => 'fas fa-users',
        'color' => 'purple',
        'sub_collections' => [
            'bukhari' => 'Sahih Bukhari',
            'muslim' => 'Sahih Muslim',
            'ahmad' => 'Musnad Ahmad',
            'tirmidzi' => 'Sunan Tirmidzi',
            'abudaud' => 'Sunan Abu Daud',
            'nasai' => 'Sunan Nasai',
            'ibnumajah' => 'Sunan Ibnu Majah'
        ]
    ]
];

try {
    if ($isDetailMode) {
        // Detail mode - show specific hadits
        switch ($collection) {
            case 'arbain':
                if ($nomor < 1 || $nomor > 42) $nomor = 1;
                $haditsData = $api->getHaditsArbain($nomor);
                break;
            case 'bulughul_maram':
                if ($nomor < 1 || $nomor > 1597) $nomor = 1;
                $haditsData = $api->getHaditsBulughulMaram($nomor);
                break;
            case 'perawi':
                if (empty($slug)) $slug = 'bukhari';
                $haditsData = $api->getHaditsPerawi($slug, $nomor);
                break;
        }
        
        if (!isset($haditsData['data'])) {
            throw new Exception('Hadits tidak ditemukan');
        }
    } elseif ($isSearchMode) {
        // Search mode
        $filters = [];
        if (!empty($collection)) {
            $filters['collection'] = $collection;
            if ($collection === 'perawi' && !empty($slug)) {
                $filters['perawi'] = $slug;
            }
        }
        
        $searchResults = $searchEngine->search($search, 'hadits', $filters);
        $content = $searchResults['data'] ?? [];
    } else {
        // Direct display mode - show collections list
        $displayHandler = $displayEngine->getDisplayHandler('hadits');
        $allContent = $displayHandler->getDefaultContent();
        if ($allContent['success']) {
            $content = $allContent['data'];
        } else {
            throw new Exception($allContent['error'] ?? 'Gagal memuat data hadits');
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Hadits page error: " . $e->getMessage());
}

// Set page title and breadcrumb
$page_title = 'Hadits - Koleksi Hadits Terpercaya';
if ($isDetailMode) {
    $collectionName = $available_collections[$collection]['name'] ?? 'Hadits';
    $page_title = $collectionName . ' #' . $nomor . ' - Hadits';
} elseif ($isSearchMode) {
    $page_title = 'Pencarian Hadits: ' . htmlspecialchars($search);
} elseif ($isCollectionView) {
    $collectionName = $available_collections[$collection]['name'] ?? 'Koleksi';
    $page_title = $collectionName . ' - Hadits';
}

$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Hadits', 'url' => 'hadits.php']
];

if ($isDetailMode) {
    $collectionName = $available_collections[$collection]['name'] ?? 'Koleksi';
    $breadcrumb[] = ['title' => $collectionName, 'url' => '?collection=' . $collection];
    $breadcrumb[] = ['title' => 'Hadits #' . $nomor, 'url' => ''];
} elseif ($isSearchMode) {
    $breadcrumb[] = ['title' => 'Hasil Pencarian', 'url' => ''];
} elseif ($isCollectionView) {
    $collectionName = $available_collections[$collection]['name'] ?? 'Koleksi';
    $breadcrumb[] = ['title' => $collectionName, 'url' => ''];
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
                        <i class="fas fa-book-open text-green-600 mr-3"></i>
                        <?php 
                        if ($isDetailMode) {
                            $collectionName = $available_collections[$collection]['name'] ?? 'Hadits';
                            echo $collectionName . ' #' . $nomor;
                        } elseif ($isSearchMode) {
                            echo 'Hasil Pencarian Hadits';
                        } elseif ($isCollectionView) {
                            echo $available_collections[$collection]['name'] ?? 'Koleksi Hadits';
                        } else {
                            echo 'Koleksi Hadits Terpercaya';
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($isDetailMode) {
                            echo 'Detail hadits dengan teks Arab, terjemahan, dan informasi perawi';
                        } elseif ($isSearchMode) {
                            echo 'Hasil pencarian untuk "' . htmlspecialchars($search) . '"';
                        } elseif ($isCollectionView) {
                            $desc = $available_collections[$collection]['description'] ?? 'Koleksi hadits terpercaya';
                            echo $desc;
                        } else {
                            echo 'Jelajahi koleksi hadits dari berbagai sumber terpercaya untuk mempelajari sunnah Rasulullah SAW';
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
            <!-- Detail Mode - Show specific hadits -->
            <div class="mb-6">
                <a href="?collection=<?php echo $collection; ?><?php echo $collection === 'perawi' ? '&slug=' . $slug : ''; ?>" 
                   class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke <?php echo $available_collections[$collection]['name'] ?? 'Koleksi'; ?>
                </a>
            </div>
            
            <?php if (isset($haditsData)): ?>
                <div id="hadits-detail">
                    <?php echo $renderer->renderHadits($haditsData); ?>
                </div>
                
                <!-- Navigation to other hadits -->
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <?php if ($nomor > 1): ?>
                                <a href="?collection=<?php echo $collection; ?>&nomor=<?php echo $nomor - 1; ?><?php echo $collection === 'perawi' ? '&slug=' . $slug : ''; ?>" 
                                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                                    <i class="fas fa-chevron-left mr-2"></i>Hadits Sebelumnya
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            $maxNomor = $available_collections[$collection]['total'] ?? 1;
                            if (is_numeric($maxNomor) && $nomor < $maxNomor): 
                            ?>
                                <a href="?collection=<?php echo $collection; ?>&nomor=<?php echo $nomor + 1; ?><?php echo $collection === 'perawi' ? '&slug=' . $slug : ''; ?>" 
                                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                                    Hadits Selanjutnya<i class="fas fa-chevron-right ml-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            Hadits <?php echo $nomor; ?> dari <?php echo $maxNomor; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- List Mode - Show collections or search results -->
            
            <!-- Advanced Search Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-search text-green-600 mr-2"></i>
                        Pencarian Hadits
                    </h2>
                </div>
                
                <form method="GET" action="hadits.php" class="mb-4">
                    <input type="hidden" name="collection" value="<?php echo htmlspecialchars($collection); ?>">
                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
                    <div class="flex gap-2 mb-4">
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   id="hadits-search"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Cari berdasarkan teks hadits, perawi, atau tema..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <button type="submit" 
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <?php if ($isSearchMode): ?>
                            <a href="hadits.php" 
                               class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Hapus
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Collection and Perawi filters -->
                <div class="flex flex-wrap gap-2">
                    <select name="collection" id="collection-filter" onchange="applyCollectionFilter()" 
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">Semua Koleksi</option>
                        <option value="arbain" <?php echo $collection === 'arbain' ? 'selected' : ''; ?>>Hadits Arbain</option>
                        <option value="bulughul_maram" <?php echo $collection === 'bulughul_maram' ? 'selected' : ''; ?>>Bulughul Maram</option>
                        <option value="perawi" <?php echo $collection === 'perawi' ? 'selected' : ''; ?>>Hadits Perawi</option>
                    </select>
                    
                    <?php if ($collection === 'perawi'): ?>
                    <select name="slug" id="perawi-filter" onchange="applyPerawiFilter()" 
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">Semua Perawi</option>
                        <?php foreach ($available_collections['perawi']['sub_collections'] as $perawiSlug => $perawiName): ?>
                            <option value="<?php echo $perawiSlug; ?>" <?php echo $slug === $perawiSlug ? 'selected' : ''; ?>>
                                <?php echo $perawiName; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content Display -->
            <?php if (empty($error_message)): ?>
                <?php if ($isSearchMode && !empty($content)): ?>
                    <!-- Search Results -->
                    <div class="mb-4 text-sm text-gray-600">
                        Menampilkan <?php echo count($content); ?> hasil pencarian
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($content as $result): ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-200">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-2">
                                            <?php echo $result['collection']; ?> #<?php echo $result['number']; ?>
                                        </h3>
                                        <div class="text-sm text-gray-600">
                                            Relevansi: <?php echo round($result['score']); ?>%
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (isset($result['highlights'])): ?>
                                    <?php echo $renderer->renderHighlightedContent($result['highlights'], $result['data']); ?>
                                <?php else: ?>
                                    <?php echo $renderer->renderGenericContent($result['data']); ?>
                                <?php endif; ?>
                                
                                <div class="mt-4">
                                    <a href="?collection=<?php echo $result['collection']; ?>&nomor=<?php echo $result['number']; ?>" 
                                       class="text-green-600 hover:text-green-700 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i>Lihat Detail
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php elseif (!$isSearchMode && !empty($content)): ?>
                    <!-- Collections List -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-list text-green-600 mr-2"></i>
                                Daftar Koleksi Hadits
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Klik pada koleksi untuk membaca hadits dari sumber tersebut</p>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($content as $key => $collection_info): ?>
                                <?php if ($key === 'perawi'): ?>
                                    <!-- Perawi collections with sub-items -->
                                    <div class="p-4 hover:bg-green-50 transition duration-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <div class="bg-<?php echo $collection_info['color']; ?>-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3">
                                                    <i class="<?php echo $collection_info['icon']; ?>"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($collection_info['name']); ?></h3>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($collection_info['description']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Sub-collections -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 ml-13">
                                            <?php foreach ($collection_info['sub_collections'] as $slug => $name): ?>
                                                <a href="?collection=perawi&slug=<?php echo $slug; ?>" 
                                                   class="block p-3 bg-gray-50 hover:bg-purple-100 rounded-lg border border-gray-200 hover:border-purple-300 transition duration-200">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($name); ?></span>
                                                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Regular collections -->
                                    <a href="?collection=<?php echo $key; ?>" 
                                       class="block p-4 hover:bg-green-50 transition duration-200 group">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="bg-<?php echo $collection_info['color']; ?>-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-<?php echo $collection_info['color']; ?>-700 transition duration-200">
                                                    <i class="<?php echo $collection_info['icon']; ?>"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition duration-200">
                                                        <?php echo htmlspecialchars($collection_info['name']); ?>
                                                    </h3>
                                                    <p class="text-sm text-gray-600">
                                                        <?php echo htmlspecialchars($collection_info['description']); ?> • <?php echo $collection_info['total']; ?> hadits
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-gray-400 group-hover:text-green-600 transition duration-200">
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="mt-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-green-600">42</div>
                                <div class="text-sm text-gray-600">Hadits Arbain</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-blue-600">1,597</div>
                                <div class="text-sm text-gray-600">Bulughul Maram</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600">7</div>
                                <div class="text-sm text-gray-600">Perawi Utama</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-amber-600">∞</div>
                                <div class="text-sm text-gray-600">Hadits Tersedia</div>
                            </div>
                        </div>
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
                                echo 'Tidak ada hadits yang cocok dengan pencarian "' . htmlspecialchars($search) . '". Coba gunakan kata kunci yang berbeda.';
                            } else {
                                echo 'Data hadits tidak dapat dimuat. Silakan coba lagi nanti.';
                            }
                            ?>
                        </p>
                        <?php if ($isSearchMode): ?>
                            <a href="hadits.php" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-list mr-2"></i>
                                Lihat Semua Koleksi
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
        // Apply collection filter
        function applyCollectionFilter() {
            const collection = document.getElementById('collection-filter').value;
            const currentUrl = new URL(window.location);
            
            if (collection) {
                currentUrl.searchParams.set('collection', collection);
            } else {
                currentUrl.searchParams.delete('collection');
            }
            
            // Clear search and slug when changing collection
            currentUrl.searchParams.delete('search');
            currentUrl.searchParams.delete('slug');
            
            window.location.href = currentUrl.toString();
        }
        
        // Apply perawi filter
        function applyPerawiFilter() {
            const slug = document.getElementById('perawi-filter').value;
            const currentUrl = new URL(window.location);
            
            if (slug) {
                currentUrl.searchParams.set('slug', slug);
            } else {
                currentUrl.searchParams.delete('slug');
            }
            
            window.location.href = currentUrl.toString();
        }
        
        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('hadits-search');
            if (searchInput) {
                let searchTimeout;
                
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.value.length >= 3 || this.value.length === 0) {
                            performLiveSearch(this.value);
                        }
                    }, 500);
                });
            }
        });
        
        // Live search without page reload
        function performLiveSearch(query) {
            if (!query) {
                window.location.href = 'hadits.php';
                return;
            }
            
            const searchUrl = new URL(window.location);
            searchUrl.searchParams.set('search', query);
            
            setTimeout(() => {
                window.location.href = searchUrl.toString();
            }, 500);
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('hadits-search').focus();
                        break;
                }
            }
        });
        
        // Clear search
        function clearSearch() {
            window.location.href = 'hadits.php';
        }
        
        // Show search suggestions
        function showSearchSuggestions() {
            const suggestions = ['iman', 'islam', 'ihsan', 'sholat', 'puasa', 'zakat', 'haji', 'akhlak'];
            const searchInput = document.getElementById('hadits-search');
            const randomSuggestion = suggestions[Math.floor(Math.random() * suggestions.length)];
            searchInput.value = randomSuggestion;
            searchInput.form.submit();
        }
    </script>
</body>
</html>