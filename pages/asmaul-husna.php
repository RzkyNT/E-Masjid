<?php
/**
 * Asmaul Husna Page
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays the 99 beautiful names of Allah (Asmaul Husna)
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.7, 3.8
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();

// Handle parameters
$mode = $_GET['mode'] ?? 'index'; // Default to index (names list)
$nomor = isset($_GET['nomor']) ? (int)$_GET['nomor'] : 1;
$action = $_GET['action'] ?? 'view';
$view = $_GET['view'] ?? 'card'; // card or list
$search = $_GET['search'] ?? '';

// Initialize variables
$asma_data = null;
$all_asma_data = null;
$error_message = '';
$context_info = [];
$asma_categories = null;

// Define Asmaul Husna categories for index
$asma_display_options = [
    'single' => [
        'name' => 'Lihat Satu Nama',
        'description' => 'Fokus pada satu nama dengan penjelasan lengkap',
        'icon' => 'fas fa-eye',
        'color' => 'amber'
    ],
    'all' => [
        'name' => 'Semua Nama',
        'description' => '99 nama indah Allah dalam satu halaman',
        'icon' => 'fas fa-list',
        'color' => 'blue'
    ],
    'search' => [
        'name' => 'Cari Nama',
        'description' => 'Cari berdasarkan arti atau makna',
        'icon' => 'fas fa-search',
        'color' => 'green'
    ],
    'random' => [
        'name' => 'Nama Acak',
        'description' => 'Renungan harian dengan nama acak',
        'icon' => 'fas fa-random',
        'color' => 'purple'
    ]
];

try {
    switch ($mode) {
        case 'index':
            // Show display options list
            $asma_categories = $asma_display_options;
            $context_info = [
                'mode' => 'index',
                'total_names' => 99,
                'total_options' => count($asma_display_options)
            ];
            break;
            
        case 'collection':
            // Show specific asmaul husna content
            switch ($action) {
                case 'random':
                    $asma_data = $api->getRandomAsmaulHusna();
                    $context_info = [
                        'mode' => 'random',
                        'collection' => 'Asmaul Husna Acak'
                    ];
                    break;
                    
                case 'all':
                    $all_asma_data = $api->getAllAsmaulHusna();
                    $context_info = [
                        'mode' => 'all',
                        'collection' => 'Semua Asmaul Husna',
                        'total' => 99,
                        'description' => '99 Nama Indah Allah SWT'
                    ];
                    break;
                    
                case 'view':
                default:
                    if ($nomor < 1 || $nomor > 99) {
                        $nomor = 1;
                    }
                    $asma_data = $api->getAsmaulHusna($nomor);
                    $context_info = [
                        'mode' => 'view',
                        'collection' => 'Asmaul Husna',
                        'nomor' => $nomor,
                        'total' => 99,
                        'description' => '99 Nama Indah Allah SWT'
                    ];
                    break;
            }
            break;
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Asmaul Husna page error: " . $e->getMessage());
}

// Set page title
$page_title = 'Asmaul Husna - Masjid Al-Muhajirin';
if (!empty($context_info['collection'])) {
    $page_title = $context_info['collection'] . ' - Asmaul Husna - Masjid Al-Muhajirin';
}

// Set breadcrumb based on mode
$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Asmaul Husna', 'url' => 'asmaul-husna.php']
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
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <?php foreach ($breadcrumb as $index => $item): ?>
                    <li class="<?php echo $index === 0 ? 'inline-flex items-center' : ''; ?>">
                        <?php if ($index > 0): ?>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['url'])): ?>
                            <a href="<?php echo htmlspecialchars($item['url']); ?>" 
                               class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <?php if ($index === 0): ?>
                                    <i class="fas fa-home mr-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        <?php else: ?>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($index > 0): ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        <i class="fas fa-star-and-crescent text-amber-600 mr-3"></i>
                        <?php 
                        if ($mode === 'index') {
                            echo "Daftar Asmaul Husna";
                        } else {
                            echo "Asmaul Husna";
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($mode === 'index') {
                            echo "Klik pada pilihan untuk menjelajahi 99 nama indah Allah SWT";
                        } else {
                            echo "99 Nama Indah Allah SWT - \"Dialah Allah yang memiliki Asmaul Husna\" (QS. Thaha: 8)";
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
                <!-- Asmaul Husna Display Options Index -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-list text-amber-600 mr-2"></i>
                            Pilihan Tampilan Asmaul Husna
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Klik pada pilihan untuk menjelajahi 99 nama indah Allah SWT</p>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($asma_categories as $key => $option): ?>
                            <?php
                            $url = '';
                            switch ($key) {
                                case 'single':
                                    $url = '?mode=collection&nomor=1';
                                    break;
                                case 'all':
                                    $url = '?mode=collection&action=all&view=card';
                                    break;
                                case 'search':
                                    $url = '?mode=collection&action=all&view=list';
                                    break;
                                case 'random':
                                    $url = '?mode=collection&action=random';
                                    break;
                            }
                            ?>
                            <a href="<?php echo $url; ?>" 
                               class="block p-4 hover:bg-amber-50 transition duration-200 group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-<?php echo $option['color']; ?>-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-<?php echo $option['color']; ?>-700 transition duration-200">
                                            <i class="<?php echo $option['icon']; ?>"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition duration-200">
                                                <?php echo htmlspecialchars($option['name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($option['description']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-gray-400 group-hover:text-amber-600 transition duration-200">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Quick Access Numbers -->
                <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Akses Cepat</h3>
                    <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-9 gap-2">
                        <?php for ($i = 1; $i <= 99; $i += 11): ?>
                            <a href="?mode=collection&nomor=<?php echo $i; ?>" 
                               class="block p-3 text-center bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 hover:border-amber-300 transition duration-200 group">
                                <div class="text-amber-600 group-hover:text-amber-700 font-semibold">
                                    <?php echo $i; ?>
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    <?php echo $i === 1 ? 'Ar-Rahman' : ($i === 12 ? 'Al-Quddus' : ($i === 23 ? 'Al-Hakeem' : ($i === 34 ? 'Ash-Shabur' : ($i === 45 ? 'Al-Hakam' : ($i === 56 ? 'Al-Mubdi' : ($i === 67 ? 'Al-Ahad' : ($i === 78 ? 'Ar-Rashid' : ($i === 89 ? 'Al-Badi' : 'As-Sabur')))))))); ?>
                                </div>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="mt-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-amber-600">99</div>
                            <div class="text-sm text-gray-600">Nama Indah</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-orange-600">∞</div>
                            <div class="text-sm text-gray-600">Makna Mendalam</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600">4</div>
                            <div class="text-sm text-gray-600">Cara Tampilan</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600">1</div>
                            <div class="text-sm text-gray-600">Allah SWT</div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Collection Mode Content -->
                <!-- Navigation Options -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Navigasi Asmaul Husna</h2>
                        <a href="asmaul-husna.php" 
                           class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Single Name View -->
                        <div class="p-4 bg-amber-50 rounded-lg border-2 <?php echo $action === 'view' ? 'border-amber-500' : 'border-transparent'; ?>">
                            <div class="text-center mb-3">
                                <i class="fas fa-eye text-amber-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Lihat Satu Nama</h3>
                                <p class="text-sm text-gray-600 mt-1">Fokus pada satu nama</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" 
                                       id="asma-number" 
                                       min="1" 
                                       max="99" 
                                       value="<?php echo $nomor; ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm text-center"
                                       placeholder="1-99">
                                <button onclick="goToAsma()" 
                                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-sm transition duration-200">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- All Names View -->
                        <a href="?mode=collection&action=all&view=<?php echo $view; ?>" 
                           class="block p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border-2 <?php echo $action === 'all' ? 'border-blue-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-list text-blue-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Semua Nama</h3>
                                <p class="text-sm text-gray-600 mt-1">99 Nama Lengkap</p>
                            </div>
                        </a>
                        
                        <!-- Search Names -->
                        <div class="p-4 bg-green-50 rounded-lg border-2 border-transparent">
                            <div class="text-center mb-3">
                                <i class="fas fa-search text-green-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Cari Nama</h3>
                                <p class="text-sm text-gray-600 mt-1">Berdasarkan arti</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       id="search-input" 
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                                       placeholder="Cari arti...">
                                <button onclick="searchAsma()" 
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm transition duration-200">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Random Name -->
                        <a href="?mode=collection&action=random" 
                           class="block p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border-2 <?php echo $action === 'random' ? 'border-purple-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-random text-purple-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Nama Acak</h3>
                                <p class="text-sm text-gray-600 mt-1">Renungan Harian</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- View Toggle for All Names -->
                <?php if ($action === 'all'): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Tampilan</h3>
                            <div class="flex items-center gap-2">
                                <a href="?mode=collection&action=all&view=card" 
                                   class="px-3 py-2 <?php echo $view === 'card' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-th-large mr-1"></i>Kartu
                                </a>
                                <a href="?mode=collection&action=all&view=list" 
                                   class="px-3 py-2 <?php echo $view === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-list mr-1"></i>Daftar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Context Information -->
                <?php if (!empty($context_info) && empty($error_message)): ?>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-amber-600 mr-3"></i>
                            <div>
                                <h3 class="text-amber-800 font-medium">
                                    <?php echo htmlspecialchars($context_info['collection']); ?>
                                </h3>
                                <?php if (isset($context_info['nomor']) && isset($context_info['total'])): ?>
                                    <p class="text-amber-700 text-sm">
                                        Nama ke-<?php echo $context_info['nomor']; ?> dari <?php echo $context_info['total']; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($context_info['description'])): ?>
                                    <p class="text-amber-600 text-xs mt-1">
                                        <?php echo htmlspecialchars($context_info['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Navigation Controls for Single View -->
                <?php if (!empty($context_info) && $action === 'view' && empty($error_message)): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <!-- Previous/Next Buttons -->
                            <?php
                            $prevNomor = $nomor - 1;
                            $nextNomor = $nomor + 1;
                            $maxNomor = $context_info['total'] ?? 99;
                            ?>
                            
                            <div class="flex items-center gap-2">
                                <?php if ($prevNomor >= 1): ?>
                                    <a href="?mode=collection&nomor=<?php echo $prevNomor; ?>" 
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 flex items-center">
                                        <i class="fas fa-chevron-left mr-2"></i>
                                        Sebelumnya
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($nextNomor <= $maxNomor): ?>
                                    <a href="?mode=collection&nomor=<?php echo $nextNomor; ?>" 
                                       class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200 flex items-center">
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
                                       max="<?php echo $maxNomor; ?>" 
                                       value="<?php echo $nomor; ?>"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-center"
                                       onchange="jumpToNumber(this.value)">
                                <span class="text-sm text-gray-500">/ <?php echo $maxNomor; ?></span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <a href="?mode=collection&action=all" 
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-list mr-2"></i>
                                    Semua
                                </a>
                                <a href="?mode=collection&action=random" 
                                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-random mr-2"></i>
                                    Acak
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Content Display -->
                <?php if ($asma_data && empty($error_message)): ?>
                    <!-- Single Asmaul Husna -->
                    <div id="asma-content">
                        <?php echo $renderer->renderAsmaulHusna($asma_data, ['layout' => 'card']); ?>
                    </div>
                <?php elseif ($all_asma_data && empty($error_message)): ?>
                    <!-- All Asmaul Husna -->
                    <div id="all-asma-content">
                        <?php if (isset($all_asma_data['data']) && is_array($all_asma_data['data'])): ?>
                            <?php if ($view === 'list'): ?>
                                <!-- List View -->
                                <div class="space-y-3">
                                    <?php foreach ($all_asma_data['data'] as $asma): ?>
                                        <?php echo $renderer->renderAsmaulHusna(['data' => $asma], ['layout' => 'list', 'show_copy' => false]); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- Card View -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($all_asma_data['data'] as $asma): ?>
                                        <div class="transform hover:scale-105 transition duration-200">
                                            <?php echo $renderer->renderAsmaulHusna(['data' => $asma], ['layout' => 'card', 'show_copy' => false]); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">Data Asmaul Husna tidak dapat dimuat dengan benar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif (empty($asma_data) && empty($all_asma_data) && $mode === 'collection'): ?>
                    <!-- Welcome State for Collection Mode -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <i class="fas fa-star-and-crescent text-amber-600 text-6xl mb-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Cara Menjelajahi</h2>
                        <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                            Jelajahi 99 nama indah Allah SWT. Setiap nama memiliki makna dan hikmah yang mendalam 
                            untuk memperkuat iman dan mendekatkan diri kepada Allah.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="?mode=collection&nomor=1" 
                               class="inline-flex items-center px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-play mr-2"></i>
                                Mulai dari Nama Pertama
                            </a>
                            <a href="?mode=collection&action=all" 
                               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-list mr-2"></i>
                                Lihat Semua Nama
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/islamic-content.js"></script>
    <script>
        function goToAsma() {
            const number = document.getElementById('asma-number').value;
            if (number >= 1 && number <= 99) {
                window.location.href = `?mode=collection&nomor=${number}`;
            }
        }
        
        function jumpToNumber(nomor) {
            if (nomor >= 1 && nomor <= 99) {
                window.location.href = `?mode=collection&nomor=${nomor}`;
            }
        }
        
        function searchAsma() {
            const query = document.getElementById('search-input').value.trim();
            if (query) {
                // For now, redirect to all view with search parameter
                window.location.href = `?mode=collection&action=all&search=${encodeURIComponent(query)}`;
            }
        }
        
        // Auto-refresh for random asma
        <?php if ($action === 'random'): ?>
        setInterval(() => {
            const refreshBtn = document.createElement('button');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Nama Baru';
            refreshBtn.className = 'fixed bottom-6 right-6 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg shadow-lg transition duration-200 z-50';
            refreshBtn.onclick = () => window.location.href = '?mode=collection&action=random';
            
            if (!document.querySelector('.fixed.bottom-6.right-6')) {
                document.body.appendChild(refreshBtn);
            }
        }, 30000); // Show refresh button after 30 seconds
        <?php endif; ?>
        
        // Enter key support for inputs
        document.getElementById('asma-number')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                goToAsma();
            }
        });
        
        document.getElementById('search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchAsma();
            }
        });
        
        // Search functionality for all view
        <?php if ($action === 'all' && !empty($search)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const searchTerm = '<?php echo addslashes($search); ?>'.toLowerCase();
            const containers = document.querySelectorAll('#all-asma-content .asma-container, #all-asma-content .asma-list-item');
            
            containers.forEach(container => {
                const text = container.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    container.style.display = '';
                    container.classList.add('bg-yellow-50', 'border-yellow-300');
                } else {
                    container.style.display = 'none';
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>