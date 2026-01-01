<?php
/**
 * Hadits Page
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays various hadits collections including Arbain, Bulughul Maram, and various narrators
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();

// Handle parameters
$mode = $_GET['mode'] ?? 'index'; // Default to index (collection list)
$collection = $_GET['collection'] ?? 'arbain';
$nomor = isset($_GET['nomor']) ? (int)$_GET['nomor'] : 1;
$slug = $_GET['slug'] ?? 'bukhari';
$action = $_GET['action'] ?? 'view';

// Initialize variables
$hadits_data = null;
$error_message = '';
$context_info = [];
$collections_list = null;

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
    switch ($mode) {
        case 'index':
            // Show collections list
            $collections_list = $available_collections;
            $context_info = [
                'mode' => 'index',
                'total_collections' => count($available_collections)
            ];
            break;
            
        case 'collection':
            // Show specific collection content
            switch ($action) {
                case 'random':
                    $hadits_data = $api->getRandomHadits();
                    $context_info = [
                        'mode' => 'random',
                        'collection' => 'Hadits Acak'
                    ];
                    break;
                    
                case 'view':
                default:
                    switch ($collection) {
                        case 'arbain':
                            if ($nomor < 1 || $nomor > 42) {
                                $nomor = 1;
                            }
                            $hadits_data = $api->getHaditsArbain($nomor);
                            $context_info = [
                                'mode' => 'arbain',
                                'collection' => 'Hadits Arbain',
                                'nomor' => $nomor,
                                'total' => 42,
                                'description' => '42 Hadits Pilihan'
                            ];
                            break;
                            
                        case 'bulughul_maram':
                            if ($nomor < 1 || $nomor > 1597) {
                                $nomor = 1;
                            }
                            $hadits_data = $api->getHaditsBulughulMaram($nomor);
                            $context_info = [
                                'mode' => 'bulughul_maram',
                                'collection' => 'Bulughul Maram',
                                'nomor' => $nomor,
                                'total' => 1597,
                                'description' => 'Karya Ibnu Hajar Al-Asqalani'
                            ];
                            break;
                            
                        case 'perawi':
                            $hadits_data = $api->getHaditsPerawi($slug, $nomor);
                            $context_info = [
                                'mode' => 'perawi',
                                'collection' => 'Hadits ' . ucfirst($slug),
                                'nomor' => $nomor,
                                'slug' => $slug,
                                'description' => 'Hadits dari ' . ucfirst($slug)
                            ];
                            break;
                    }
                    break;
            }
            break;
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Hadits page error: " . $e->getMessage());
}

// Set page title
$page_title = 'Hadits - Masjid Al-Muhajirin';
if (!empty($context_info['collection'])) {
    $page_title = $context_info['collection'] . ' - Hadits - Masjid Al-Muhajirin';
}

// Set breadcrumb based on mode
$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Hadits', 'url' => 'hadits.php']
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
                        <i class="fas fa-book-open text-green-600 mr-3"></i>
                        <?php 
                        if ($mode === 'index') {
                            echo "Daftar Koleksi Hadits";
                        } else {
                            echo "Hadits";
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600">
                        <?php 
                        if ($mode === 'index') {
                            echo "Klik pada koleksi untuk membaca hadits dari sumber tersebut";
                        } else {
                            echo "Kumpulan hadits dari berbagai sumber untuk mempelajari sunnah Rasulullah SAW";
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
                <!-- Collections Index List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-list text-green-600 mr-2"></i>
                            Daftar Koleksi Hadits
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Klik pada koleksi untuk membaca hadits dari sumber tersebut</p>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($collections_list as $key => $collection_info): ?>
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
                                            <a href="?mode=collection&collection=perawi&slug=<?php echo $slug; ?>&nomor=1" 
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
                                <a href="?mode=collection&collection=<?php echo $key; ?>&nomor=1" 
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
                        
                        <!-- Random Hadits Option -->
                        <a href="?mode=collection&action=random" 
                           class="block p-4 hover:bg-amber-50 transition duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="bg-amber-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-amber-700 transition duration-200">
                                        <i class="fas fa-random"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition duration-200">
                                            Hadits Acak
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Inspirasi harian dari berbagai koleksi hadits
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
                            <div class="text-sm text-gray-600">Hadits Acak</div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Collection Navigation (for collection mode) -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Navigasi Koleksi</h2>
                        <a href="hadits.php" 
                           class="text-sm text-green-600 hover:text-green-700 font-medium">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Hadits Arbain -->
                        <a href="?mode=collection&collection=arbain&nomor=1" 
                           class="block p-4 bg-green-50 hover:bg-green-100 rounded-lg border-2 <?php echo $collection === 'arbain' ? 'border-green-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-star text-green-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Hadits Arbain</h3>
                                <p class="text-sm text-gray-600 mt-1">42 Hadits Pilihan</p>
                            </div>
                        </a>
                        
                        <!-- Bulughul Maram -->
                        <a href="?mode=collection&collection=bulughul_maram&nomor=1" 
                           class="block p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border-2 <?php echo $collection === 'bulughul_maram' ? 'border-blue-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-book text-blue-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Bulughul Maram</h3>
                                <p class="text-sm text-gray-600 mt-1">1597 Hadits Hukum</p>
                            </div>
                        </a>
                        
                        <!-- Hadits Perawi -->
                        <div class="p-4 bg-purple-50 rounded-lg border-2 <?php echo $collection === 'perawi' ? 'border-purple-500' : 'border-transparent'; ?>">
                            <div class="text-center mb-3">
                                <i class="fas fa-users text-purple-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Hadits Perawi</h3>
                                <p class="text-sm text-gray-600 mt-1">Berbagai Perawi</p>
                            </div>
                            <select onchange="window.location.href='?mode=collection&collection=perawi&slug=' + this.value + '&nomor=1'" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">Pilih Perawi</option>
                                <option value="bukhari" <?php echo $slug === 'bukhari' ? 'selected' : ''; ?>>Bukhari</option>
                                <option value="ahmad" <?php echo $slug === 'ahmad' ? 'selected' : ''; ?>>Ahmad</option>
                                <option value="muslim" <?php echo $slug === 'muslim' ? 'selected' : ''; ?>>Muslim</option>
                                <option value="tirmidzi" <?php echo $slug === 'tirmidzi' ? 'selected' : ''; ?>>Tirmidzi</option>
                                <option value="abudaud" <?php echo $slug === 'abudaud' ? 'selected' : ''; ?>>Abu Daud</option>
                                <option value="nasai" <?php echo $slug === 'nasai' ? 'selected' : ''; ?>>Nasai</option>
                                <option value="ibnumajah" <?php echo $slug === 'ibnumajah' ? 'selected' : ''; ?>>Ibnu Majah</option>
                            </select>
                        </div>
                        
                        <!-- Random Hadits -->
                        <a href="?mode=collection&action=random" 
                           class="block p-4 bg-amber-50 hover:bg-amber-100 rounded-lg border-2 <?php echo $action === 'random' ? 'border-amber-500' : 'border-transparent'; ?> transition duration-200">
                            <div class="text-center">
                                <i class="fas fa-random text-amber-600 text-2xl mb-2"></i>
                                <h3 class="font-semibold text-gray-900">Hadits Acak</h3>
                                <p class="text-sm text-gray-600 mt-1">Inspirasi Harian</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Context Information -->
                <?php if (!empty($context_info) && empty($error_message)): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-green-600 mr-3"></i>
                            <div>
                                <h3 class="text-green-800 font-medium">
                                    <?php echo htmlspecialchars($context_info['collection']); ?>
                                </h3>
                                <?php if (isset($context_info['nomor']) && isset($context_info['total'])): ?>
                                    <p class="text-green-700 text-sm">
                                        Hadits <?php echo $context_info['nomor']; ?> dari <?php echo $context_info['total']; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (isset($context_info['description'])): ?>
                                    <p class="text-green-600 text-xs mt-1">
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
                            <!-- Previous Button -->
                            <?php
                            $prevNomor = $nomor - 1;
                            $nextNomor = $nomor + 1;
                            $maxNomor = $context_info['total'] ?? 1;
                            ?>
                            
                            <div class="flex items-center gap-2">
                                <?php if ($prevNomor >= 1): ?>
                                    <a href="?mode=collection&collection=<?php echo $collection; ?>&nomor=<?php echo $prevNomor; ?><?php echo $collection === 'perawi' ? '&slug=' . $slug : ''; ?>" 
                                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200 flex items-center">
                                        <i class="fas fa-chevron-left mr-2"></i>
                                        Sebelumnya
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($nextNomor <= $maxNomor): ?>
                                    <a href="?mode=collection&collection=<?php echo $collection; ?>&nomor=<?php echo $nextNomor; ?><?php echo $collection === 'perawi' ? '&slug=' . $slug : ''; ?>" 
                                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 flex items-center">
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
                            
                            <!-- Random Button -->
                            <a href="?mode=collection&action=random" 
                               class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-random mr-2"></i>
                                Acak
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Hadits Content -->
                <?php if ($hadits_data && empty($error_message)): ?>
                    <div id="hadits-content">
                        <?php echo $renderer->renderHadits($hadits_data); ?>
                    </div>
                <?php elseif (empty($hadits_data) && $mode === 'collection'): ?>
                    <!-- Welcome State for Collection Mode -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <i class="fas fa-book-open text-green-600 text-6xl mb-6"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Koleksi Hadits</h2>
                        <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                            Pilih salah satu koleksi hadits di atas untuk mulai membaca. Tersedia Hadits Arbain, 
                            Bulughul Maram, hadits dari berbagai perawi, dan fitur hadits acak untuk inspirasi harian.
                        </p>
                        <a href="?mode=collection&collection=arbain&nomor=1" 
                           class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-star mr-2"></i>
                            Mulai dengan Hadits Arbain
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
        function jumpToNumber(nomor) {
            const collection = '<?php echo $collection; ?>';
            const slug = '<?php echo $slug; ?>';
            
            let url = `?mode=collection&collection=${collection}&nomor=${nomor}`;
            if (collection === 'perawi') {
                url += `&slug=${slug}`;
            }
            
            window.location.href = url;
        }
        
        // Auto-refresh for random hadits
        <?php if ($action === 'random'): ?>
        setInterval(() => {
            const refreshBtn = document.createElement('button');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Hadits Baru';
            refreshBtn.className = 'fixed bottom-6 right-6 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg shadow-lg transition duration-200 z-50';
            refreshBtn.onclick = () => window.location.href = '?mode=collection&action=random';
            
            if (!document.querySelector('.fixed.bottom-6.right-6')) {
                document.body.appendChild(refreshBtn);
            }
        }, 30000); // Show refresh button after 30 seconds
        <?php endif; ?>
    </script>
</body>
</html>