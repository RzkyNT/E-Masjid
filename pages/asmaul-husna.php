<?php
/**
 * Asmaul Husna Page - Direct Display
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays all 99 beautiful names of Allah directly with advanced search
 * Requirements: 1.1, 1.2, 1.4, 1.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6
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
$view = $_GET['view'] ?? 'grid'; // grid or list
$search = $_GET['search'] ?? '';
$numberRange = $_GET['number_range'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 33; // Show 33 items per page (3 rows of 11 in grid)

// Initialize variables
$content = [];
$searchResults = [];
$error_message = '';
$totalItems = 99;
$isSearchMode = !empty($search);

try {
    // Get display handler
    $displayHandler = $displayEngine->getDisplayHandler('asmaul_husna');
    
    if ($isSearchMode) {
        // Search mode
        $filters = [];
        if (!empty($numberRange)) {
            $filters['number_range'] = $numberRange;
        }
        
        $searchResults = $searchEngine->search($search, 'asmaul_husna', $filters);
        $content = $searchResults['data'] ?? [];
        $totalItems = $searchResults['total'] ?? 0;
    } else {
        // Direct display mode - show all content
        $allContent = $displayHandler->getDefaultContent();
        if ($allContent['success']) {
            $content = $allContent['data'];
            $totalItems = count($content);
        } else {
            throw new Exception($allContent['error'] ?? 'Gagal memuat data Asmaul Husna');
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Asmaul Husna page error: " . $e->getMessage());
}

// Set page title and breadcrumb
$page_title = 'Asmaul Husna - 99 Nama Indah Allah SWT';
if ($isSearchMode) {
    $page_title = 'Pencarian Asmaul Husna: ' . htmlspecialchars($search);
}

$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Asmaul Husna', 'url' => 'asmaul-husna.php']
];

if ($isSearchMode) {
    $breadcrumb[] = ['title' => 'Hasil Pencarian', 'url' => ''];
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
                        <i class="fas fa-star-and-crescent text-amber-600 mr-3"></i>
                        <?php echo $isSearchMode ? 'Hasil Pencarian Asmaul Husna' : '99 Nama Indah Allah SWT'; ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($isSearchMode) {
                            echo 'Hasil pencarian untuk "' . htmlspecialchars($search) . '" - ' . $totalItems . ' nama ditemukan';
                        } else {
                            echo 'Jelajahi 99 nama indah Allah SWT dengan makna dan hikmah yang mendalam';
                        }
                        ?>
                    </p>
                </div>
                
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
            </div>
        </div>

        <!-- Advanced Search Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-search text-amber-600 mr-2"></i>
                    Pencarian Asmaul Husna
                </h2>
                <div class="flex items-center gap-2">
                    <button onclick="toggleView('grid')" id="grid-view-btn" 
                            class="px-3 py-2 <?php echo $view === 'grid' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'; ?> rounded-lg text-sm transition duration-200">
                        <i class="fas fa-th-large mr-1"></i>Grid
                    </button>
                    <button onclick="toggleView('list')" id="list-view-btn"
                            class="px-3 py-2 <?php echo $view === 'list' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'; ?> rounded-lg text-sm transition duration-200">
                        <i class="fas fa-list mr-1"></i>List
                    </button>
                </div>
            </div>
            
            <form method="GET" action="asmaul-husna.php" class="mb-4">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                <div class="flex gap-2 mb-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               id="asmaul-husna-search"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Cari berdasarkan nama Arab, transliterasi, atau arti..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <button type="submit" 
                            class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <?php if ($isSearchMode): ?>
                        <a href="asmaul-husna.php" 
                           class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                            <i class="fas fa-times mr-2"></i>Hapus
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Advanced filters -->
                <div class="flex flex-wrap gap-2">
                    <select name="number_range" onchange="this.form.submit()" 
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">Semua Nomor</option>
                        <option value="1-25" <?php echo $numberRange === '1-25' ? 'selected' : ''; ?>>1-25</option>
                        <option value="26-50" <?php echo $numberRange === '26-50' ? 'selected' : ''; ?>>26-50</option>
                        <option value="51-75" <?php echo $numberRange === '51-75' ? 'selected' : ''; ?>>51-75</option>
                        <option value="76-99" <?php echo $numberRange === '76-99' ? 'selected' : ''; ?>>76-99</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Quick Navigation (only show when not searching) -->
        <?php if (!$isSearchMode): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Navigasi Cepat</h3>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-2">
                <?php for ($i = 1; $i <= 99; $i += 10): ?>
                    <?php $end = min($i + 9, 99); ?>
                    <button onclick="scrollToNumber(<?php echo $i; ?>)" 
                            class="p-2 text-center bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 hover:border-amber-300 transition duration-200 text-sm">
                        <?php echo $i; ?>-<?php echo $end; ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Content Display -->
        <?php if (empty($error_message)): ?>
            <?php if (!empty($content)): ?>
                <!-- Results count -->
                <div class="mb-4 text-sm text-gray-600">
                    <?php if ($isSearchMode): ?>
                        Menampilkan <?php echo count($content); ?> dari <?php echo $totalItems; ?> hasil
                    <?php else: ?>
                        Menampilkan <?php echo count($content); ?> nama indah Allah SWT
                    <?php endif; ?>
                </div>
                
                <!-- Content Grid/List -->
                <div id="asmaul-husna-content">
                    <?php if ($view === 'list'): ?>
                        <div class="space-y-3">
                            <?php foreach ($content as $asma): ?>
                                <?php 
                                $asmaData = $isSearchMode ? $asma['data'] : $asma;
                                echo $renderer->renderAsmaulHusna(['data' => $asmaData], ['layout' => 'list', 'show_copy' => false]); 
                                ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($content as $asma): ?>
                                <div class="transform hover:scale-105 transition duration-200" data-number="<?php echo $isSearchMode ? (isset($asma['data']['id']) ? $asma['data']['id'] : '') : (isset($asma['id']) ? $asma['id'] : ''); ?>">
                                    <?php 
                                    $asmaData = $isSearchMode ? $asma['data'] : $asma;
                                    if (isset($renderer) && method_exists($renderer, 'renderAsmaulHusna')) {
                                        echo $renderer->renderAsmaulHusna(['data' => $asmaData], ['layout' => 'card', 'show_copy' => false]); 
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                            echo 'Tidak ada nama yang cocok dengan pencarian "' . htmlspecialchars($search) . '". Coba gunakan kata kunci yang berbeda.';
                        } else {
                            echo 'Data Asmaul Husna tidak dapat dimuat. Silakan coba lagi nanti.';
                        }
                        ?>
                    </p>
                    <?php if ($isSearchMode): ?>
                        <a href="asmaul-husna.php" 
                           class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-list mr-2"></i>
                            Lihat Semua Nama
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

        <!-- Statistics -->
        <div class="mt-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-amber-600"><?php echo $totalItems; ?></div>
                    <div class="text-sm text-gray-600"><?php echo $isSearchMode ? 'Hasil Ditemukan' : 'Nama Indah'; ?></div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-orange-600">∞</div>
                    <div class="text-sm text-gray-600">Makna Mendalam</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-yellow-600"><?php echo $view === 'grid' ? 'Grid' : 'List'; ?></div>
                    <div class="text-sm text-gray-600">Mode Tampilan</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-red-600">1</div>
                    <div class="text-sm text-gray-600">Allah SWT</div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/islamic-content.js"></script>
    <script>
        // View toggle functionality
        function toggleView(newView) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('view', newView);
            window.location.href = currentUrl.toString();
        }
        
        // Scroll to specific number range
        function scrollToNumber(number) {
            const element = document.querySelector(`[data-number="${number}"]`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                element.classList.add('ring-2', 'ring-amber-400', 'ring-opacity-75');
                setTimeout(() => {
                    element.classList.remove('ring-2', 'ring-amber-400', 'ring-opacity-75');
                }, 2000);
            }
        }
        
        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('asmaul-husna-search');
            if (searchInput) {
                let searchTimeout;
                
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.value.length >= 2 || this.value.length === 0) {
                            performLiveSearch(this.value);
                        }
                    }, 500);
                });
            }
        });
        
        // Live search without page reload
        function performLiveSearch(query) {
            if (!query) {
                // If empty, reload to show all content
                window.location.href = 'asmaul-husna.php?view=<?php echo htmlspecialchars($view); ?>';
                return;
            }
            
            // Show loading indicator
            const content = document.getElementById('asmaul-husna-content');
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-amber-600"></i><p class="mt-2 text-gray-600">Mencari...</p></div>';
            
            // Perform search via AJAX (simplified version)
            const searchUrl = new URL(window.location);
            searchUrl.searchParams.set('search', query);
            
            // For now, redirect to search results
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
                        document.getElementById('asmaul-husna-search').focus();
                        break;
                    case 'g':
                        e.preventDefault();
                        toggleView('grid');
                        break;
                    case 'l':
                        e.preventDefault();
                        toggleView('list');
                        break;
                }
            }
        });
        
        // Add search suggestions
        function showSearchSuggestions() {
            const suggestions = ['Rahman', 'Rahim', 'Malik', 'Quddus', 'Salam', 'Ghafur', 'Shakur', 'Sabur'];
            const searchInput = document.getElementById('asmaul-husna-search');
            const randomSuggestion = suggestions[Math.floor(Math.random() * suggestions.length)];
            searchInput.value = randomSuggestion;
            searchInput.form.submit();
        }
        
        // Clear search
        function clearSearch() {
            window.location.href = 'asmaul-husna.php?view=<?php echo htmlspecialchars($view); ?>';
        }
    </script>
</body>
</html>