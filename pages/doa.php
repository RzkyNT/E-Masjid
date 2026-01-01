<?php
/**
 * Doa Page
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays collection of Islamic prayers (doa) from various sources
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();

// Handle parameters
$mode = $_GET['mode'] ?? 'index'; // Default to index (doa list)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$action = $_GET['action'] ?? 'view';
$source = $_GET['source'] ?? '';

// Initialize variables
$doa_data = null;
$error_message = '';
$context_info = [];
$doa_list = null;
$sources_list = [];

// Define doa categories for index
$doa_categories = [
    'harian' => [
        'name' => 'Doa Harian',
        'description' => 'Doa-doa untuk aktivitas sehari-hari',
        'range' => '1-30',
        'icon' => 'fas fa-sun',
        'color' => 'yellow'
    ],
    'ibadah' => [
        'name' => 'Doa Ibadah',
        'description' => 'Doa-doa untuk berbagai ibadah',
        'range' => '31-60',
        'icon' => 'fas fa-pray',
        'color' => 'green'
    ],
    'perlindungan' => [
        'name' => 'Doa Perlindungan',
        'description' => 'Doa memohon perlindungan Allah',
        'range' => '61-90',
        'icon' => 'fas fa-shield-alt',
        'color' => 'blue'
    ],
    'khusus' => [
        'name' => 'Doa Khusus',
        'description' => 'Doa untuk situasi tertentu',
        'range' => '91-108',
        'icon' => 'fas fa-star',
        'color' => 'purple'
    ]
];

try {
    // Get sources list for navigation
    $sources_response = $api->getDoaSumber();
    if (isset($sources_response['data'])) {
        $sources_list = $sources_response['data'];
    }
    
    switch ($mode) {
        case 'index':
            // Show doa categories list
            $doa_list = $doa_categories;
            $context_info = [
                'mode' => 'index',
                'total_doa' => 108,
                'total_categories' => count($doa_categories)
            ];
            break;
            
        case 'collection':
            // Show specific doa content
            switch ($action) {
                case 'random':
                    $doa_data = $api->getRandomDoa();
                    $context_info = [
                        'mode' => 'random',
                        'collection' => 'Doa Acak'
                    ];
                    break;
                    
                case 'view':
                default:
                    if ($id < 1 || $id > 108) {
                        $id = 1;
                    }
                    $doa_data = $api->getDoa($id);
                    $context_info = [
                        'mode' => 'view',
                        'collection' => 'Koleksi Doa',
                        'id' => $id,
                        'total' => 108,
                        'description' => 'Doa-doa pilihan dari berbagai sumber'
                    ];
                    break;
            }
            break;
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Doa page error: " . $e->getMessage());
}

// Set page title
$page_title = 'Doa-Doa - Masjid Al-Muhajirin';
if (!empty($context_info['collection'])) {
    $page_title = $context_info['collection'] . ' - Doa-Doa - Masjid Al-Muhajirin';
}

// Set breadcrumb based on mode
$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Doa-Doa', 'url' => 'doa.php']
];

if ($mode === 'collection' && !empty($context_info['collection'])) {
    $breadcrumb[] = ['title' => $context_info['collection'], 'url' => ''];
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
                        if ($mode === 'index') {
                            echo "Daftar Koleksi Doa";
                        } else {
                            echo "Doa-Doa";
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($mode === 'index') {
                            echo "Klik pada kategori untuk membaca doa-doa dari kategori tersebut";
                        } else {
                            echo "Kumpulan doa-doa pilihan dari Al-Quran, Hadits, dan sumber-sumber terpercaya";
                        }
                        ?>
                    </p>
                </div>
                
                <?php if ($mode !== 'index'): ?>
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

        <!-- Content based on mode -->
        <?php if (empty($error_message)): ?>
            <?php if ($mode === 'index'): ?>
                <!-- Doa Categories Index List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-list text-purple-600 mr-2"></i>
                            Daftar Kategori Doa
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Klik pada kategori untuk membaca doa-doa dari kategori tersebut</p>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($doa_list as $key => $category): ?>
                            <a href="?mode=collection&category=<?php echo $key; ?>&id=<?php echo explode('-', $category['range'])[0]; ?>" 
                               class="block p-4 hover:bg-purple-50 transition duration-200 group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-<?php echo $category['color']; ?>-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-<?php echo $category['color']; ?>-700 transition duration-200">
                                            <i class="<?php echo $category['icon']; ?>"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition duration-200">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($category['description']); ?> • Doa <?php echo $category['range']; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-gray-400 group-hover:text-purple-600 transition duration-200">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        
                        <!-- Browse All Doa -->
                        <a href="?mode=collection&id=1" 
                           class="block p-4 hover:bg-blue-50 transition duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-blue-700 transition duration-200">
                                        <i class="fas fa-list-ol"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition duration-200">
                                            Telusuri Semua Doa
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Jelajahi 108 doa secara berurutan
                                        </p>
                                    </div>
                                </div>
                                <div class="text-gray-400 group-hover:text-blue-600 transition duration-200">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Random Doa Option -->
                        <a href="?mode=collection&action=random" 
                           class="block p-4 hover:bg-amber-50 transition duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="bg-amber-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-amber-700 transition duration-200">
                                        <i class="fas fa-random"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition duration-200">
                                            Doa Acak
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Inspirasi harian dari berbagai doa
                                        </p>
                                    </div>
                                </div>
                                <div class="text-gray-400 group-hover:text-amber-600 transition duration-200">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="mt-6 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-purple-600">108</div>
                            <div class="text-sm text-gray-600">Total Doa</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">30</div>
                            <div class="text-sm text-gray-600">Doa Harian</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">30</div>
                            <div class="text-sm text-gray-600">Doa Ibadah</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">48</div>
                            <div class="text-sm text-gray-600">Doa Khusus</div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Collection Mode Content -->
                <!-- Navigation Options -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Navigasi Doa</h2>
                        <a href="doa.php" 
                           class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Browse by Number -->
                        <div class="p-4 bg-purple-50 rounded-lg border-2 <?php echo $action === 'view' ? 'border-purple-500' : 'border-transparent'; ?>">
                            <div class="text-center mb-3">
                                <i class="fas fa-list-ol text-purple-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Telusuri Doa</h3>
                                <p class="text-sm text-gray-600 mt-1">1-108 Doa Pilihan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" 
                                       id="doa-number" 
                                       min="1" 
                                       max="108" 
                                       value="<?php echo $id; ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm text-center"
                                       placeholder="1-108">
                                <button onclick="goToDoa()" 
                                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm transition duration-200">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Filter by Source -->
                        <?php if (!empty($sources_list)): ?>
                        <div class="p-4 bg-blue-50 rounded-lg border-2 border-transparent">
                            <div class="text-center mb-3">
                                <i class="fas fa-filter text-blue-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Filter Sumber</h3>
                                <p class="text-sm text-gray-600 mt-1">Berdasarkan Kategori</p>
                            </div>
                            <select onchange="filterBySource(this.value)" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">Semua Sumber</option>
                                <?php foreach ($sources_list as $src): ?>
                                    <option value="<?php echo htmlspecialchars($src); ?>" <?php echo $source === $src ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(htmlspecialchars($src)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Random Doa -->
                        <a href="?mode=collection&action=random" 
                           class="block p-4 bg-amber-50 hover:bg-amber-100 rounded-lg border-2 <?php echo $action === 'random' ? 'border-amber-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-random text-amber-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Doa Acak</h3>
                                <p class="text-sm text-gray-600 mt-1">Inspirasi Harian</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Context Information -->
                <?php if (!empty($context_info) && empty($error_message)): ?>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-purple-600 mr-3"></i>
                            <div>
                                <h3 class="text-purple-800 font-medium">
                                    <?php echo htmlspecialchars($context_info['collection']); ?>
                                </h3>
                                <?php if (isset($context_info['id']) && isset($context_info['total'])): ?>
                                    <p class="text-purple-700 text-sm">
                                        Doa <?php echo $context_info['id']; ?> dari <?php echo $context_info['total']; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($context_info['description'])): ?>
                                    <p class="text-purple-600 text-xs mt-1">
                                        <?php echo htmlspecialchars($context_info['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Navigation Controls -->
                <?php if (!empty($context_info) && $action !== 'random' && empty($error_message)): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <!-- Previous/Next Buttons -->
                            <?php
                            $prevId = $id - 1;
                            $nextId = $id + 1;
                            $maxId = $context_info['total'] ?? 108;
                            ?>
                            
                            <div class="flex items-center gap-2">
                                <?php if ($prevId >= 1): ?>
                                    <a href="?mode=collection&id=<?php echo $prevId; ?>" 
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 flex items-center">
                                        <i class="fas fa-chevron-left mr-2"></i>
                                        Sebelumnya
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($nextId <= $maxId): ?>
                                    <a href="?mode=collection&id=<?php echo $nextId; ?>" 
                                       class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200 flex items-center">
                                        Selanjutnya
                                        <i class="fas fa-chevron-right ml-2"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Jump to Number -->
                            <div class="flex items-center gap-2">
                                <label for="jump-number" class="text-sm text-gray-600">Loncat ke:</label>
                                <input type="number" 
                                       id="jump-number" 
                                       min="1" 
                                       max="<?php echo $maxId; ?>" 
                                       value="<?php echo $id; ?>"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-center"
                                       onchange="jumpToNumber(this.value)">
                                <span class="text-sm text-gray-500">/ <?php echo $maxId; ?></span>
                            </div>
                            
                            <!-- Random Button -->
                            <a href="?mode=collection&action=random" 
                               class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-random mr-2"></i>
                                Acak
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Doa Content -->
                <?php if ($doa_data && empty($error_message)): ?>
                    <div id="doa-content">
                        <?php echo $renderer->renderDoa($doa_data); ?>
                    </div>
                <?php elseif (empty($doa_data) && $mode === 'collection'): ?>
                    <!-- Welcome State for Collection Mode -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <i class="fas fa-hands text-purple-600 text-6xl mb-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Selamat Datang di Koleksi Doa</h2>
                        <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                            Jelajahi koleksi doa-doa pilihan dari berbagai sumber terpercaya. Tersedia 108 doa 
                            yang dapat membantu dalam berbagai situasi kehidupan sehari-hari.
                        </p>
                        <a href="?mode=collection&id=1" 
                           class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-play mr-2"></i>
                            Mulai dengan Doa Pertama
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/islamic-content.js"></script>
    <script>
        function goToDoa() {
            const number = document.getElementById('doa-number').value;
            if (number >= 1 && number <= 108) {
                window.location.href = `?mode=collection&id=${number}`;
            }
        }
        
        function jumpToNumber(id) {
            if (id >= 1 && id <= 108) {
                window.location.href = `?mode=collection&id=${id}`;
            }
        }
        
        function filterBySource(source) {
            if (source) {
                // This would need additional API implementation for filtering
                console.log('Filter by source:', source);
                // For now, just show a message
                alert('Fitur filter berdasarkan sumber akan segera tersedia');
            }
        }
        
        // Auto-refresh for random doa
        <?php if ($action === 'random'): ?>
        setInterval(() => {
            const refreshBtn = document.createElement('button');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Doa Baru';
            refreshBtn.className = 'fixed bottom-6 right-6 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg shadow-lg transition duration-200 z-50';
            refreshBtn.onclick = () => window.location.href = '?mode=collection&action=random';
            
            if (!document.querySelector('.fixed.bottom-6.right-6')) {
                document.body.appendChild(refreshBtn);
            }
        }, 30000); // Show refresh button after 30 seconds
        <?php endif; ?>
        
        // Enter key support for doa number input
        document.getElementById('doa-number')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                goToDoa();
            }
        });
    </script>
</body>
</html>