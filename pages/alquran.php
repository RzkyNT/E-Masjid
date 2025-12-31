<?php
/**
 * Al-Quran Main Page
 * For Masjid Al-Muhajirin Information System
 * 
 * Main page for displaying Al-Quran with various navigation modes:
 * - Per Surat dan Ayat
 * - Per Halaman Mushaf
 * - Per Juz
 * - Per Tema
 * 
 * Requirements: 6.1, 6.2, 6.4
 */

// Include required files
require_once __DIR__ . '/../includes/alquran_api.php';
require_once __DIR__ . '/../includes/alquran_validation.php';
require_once __DIR__ . '/../includes/alquran_parameter_handler.php';

// Get and validate parameters with enhanced URL parameter handling
$mode = $_GET['mode'] ?? 'surat';
$error_message = '';
$quran_data = null;
$context_info = [];

// Enhanced parameter handling with range support
$surat = (int)($_GET['surat'] ?? 1);
$ayat = (int)($_GET['ayat'] ?? 1);
$panjang = (int)($_GET['panjang'] ?? 5);
$page = (int)($_GET['page'] ?? 1);
$juz = (int)($_GET['juz'] ?? 1);
$tema_id = (int)($_GET['tema'] ?? 1);

// Handle range parameter for ayat (e.g., "1-5")
$range = $_GET['range'] ?? '';
if (!empty($range) && strpos($range, '-') !== false) {
    $range_parts = explode('-', $range);
    if (count($range_parts) === 2) {
        $ayat_start = (int)$range_parts[0];
        $ayat_end = (int)$range_parts[1];
        if ($ayat_start > 0 && $ayat_end > $ayat_start) {
            $ayat = $ayat_start;
            $panjang = $ayat_end - $ayat_start + 1;
        }
    }
}

// Set dynamic page information based on mode and parameters
switch ($mode) {
    case 'surat':
        $surat_info = AlQuranValidator::getSuratInfo($surat);
        $page_title = "Al-Quran - Surat {$surat_info['name']} Ayat {$ayat}";
        $page_description = "Baca Surat {$surat_info['name']} ayat {$ayat} sampai " . ($ayat + $panjang - 1) . " dengan terjemahan Indonesia";
        break;
    case 'page':
        $page_title = "Al-Quran - Halaman {$page}";
        $page_description = "Baca Al-Quran halaman {$page} dari 604 halaman mushaf standar";
        break;
    case 'juz':
        $page_title = "Al-Quran - Juz {$juz}";
        $page_description = "Baca Al-Quran Juz {$juz} dari 30 juz dengan terjemahan Indonesia";
        break;
    case 'tema':
        $page_title = "Al-Quran - Tema ID {$tema_id}";
        $page_description = "Baca ayat-ayat Al-Quran berdasarkan tema tertentu";
        break;
    default:
        $page_title = 'Al-Quran Digital';
        $page_description = 'Baca Al-Quran digital dengan berbagai metode navigasi - per surat, halaman, juz, atau tema';
}

// Enhanced breadcrumb navigation based on mode and parameters
$breadcrumb = [
    ['title' => 'Al-Quran', 'url' => 'alquran.php']
];

// Add specific breadcrumb based on current mode
switch ($mode) {
    case 'surat':
        $surat_info = AlQuranValidator::getSuratInfo($surat);
        $breadcrumb[] = ['title' => 'Per Surat', 'url' => 'alquran.php?mode=surat'];
        $breadcrumb[] = ['title' => $surat_info['name'], 'url' => "alquran.php?mode=surat&surat={$surat}"];
        if ($ayat > 1 || $panjang != 5) {
            $breadcrumb[] = ['title' => "Ayat {$ayat}-" . ($ayat + $panjang - 1), 'url' => ''];
        }
        break;
    case 'page':
        $breadcrumb[] = ['title' => 'Per Halaman', 'url' => 'alquran.php?mode=page'];
        $breadcrumb[] = ['title' => "Halaman {$page}", 'url' => ''];
        break;
    case 'juz':
        $breadcrumb[] = ['title' => 'Per Juz', 'url' => 'alquran.php?mode=juz'];
        $breadcrumb[] = ['title' => "Juz {$juz}", 'url' => ''];
        break;
    case 'tema':
        $breadcrumb[] = ['title' => 'Per Tema', 'url' => 'alquran.php?mode=tema'];
        $breadcrumb[] = ['title' => "Tema {$tema_id}", 'url' => ''];
        break;
}

try {
    // Initialize API
    $api = getAlQuranAPI();
    
    // Enhanced routing logic with better error handling
    switch ($mode) {
        case 'surat':
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('surat', [
                'surat' => $surat,
                'ayat' => $ayat,
                'panjang' => $panjang
            ]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get data with range support
            if (!empty($range)) {
                $quran_data = $api->getAyatByRange($surat, $ayat, $ayat + $panjang - 1);
            } else {
                $quran_data = $api->getAyatBySurat($surat, $ayat, $panjang);
            }
            
            // Set context info
            $surat_info = AlQuranValidator::getSuratInfo($surat);
            $context_info = [
                'mode' => 'surat',
                'surat_number' => $surat,
                'surat_name' => $surat_info['name'],
                'ayat_start' => $ayat,
                'ayat_count' => $panjang,
                'total_ayat' => $surat_info['ayat_count'],
                'range' => !empty($range) ? $range : null
            ];
            break;
            
        case 'page':
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('page', ['page' => $page]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get data
            $quran_data = $api->getAyatByPage($page);
            
            // Set context info
            $context_info = [
                'mode' => 'page',
                'page_number' => $page,
                'total_pages' => 604
            ];
            break;
            
        case 'juz':
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('juz', ['juz' => $juz]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get juz info and ayat data
            $juz_info = $api->getJuzInfo($juz);
            $quran_data = $api->getAyatByJuz($juz);
            
            // Set context info
            $context_info = [
                'mode' => 'juz',
                'juz_number' => $juz,
                'juz_info' => $juz_info,
                'total_juz' => 30
            ];
            break;
            
        case 'tema':
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('tema', ['tema_id' => $tema_id]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get data
            $quran_data = $api->getTemaById($tema_id);
            
            // Set context info with tema name if available
            $context_info = [
                'mode' => 'tema',
                'tema_id' => $tema_id,
                'total_tema' => 1121
            ];
            
            // Try to get tema name from data
            if (isset($quran_data['data']['nama'])) {
                $context_info['tema_name'] = $quran_data['data']['nama'];
                $context_info['tema_description'] = $quran_data['data']['deskripsi'] ?? '';
                
                // Update breadcrumb with actual tema name
                $breadcrumb[count($breadcrumb) - 1]['title'] = $quran_data['data']['nama'];
            }
            break;
            
        default:
            throw new Exception('Mode navigasi tidak valid. Mode yang tersedia: surat, page, juz, tema');
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    
    // Log error for debugging
    error_log("Al-Quran Page Error: " . $e->getMessage() . " - Mode: {$mode}, Parameters: " . json_encode($_GET));
}

// Include header after all variables are set
include __DIR__ . '/../partials/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header with Enhanced Context -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            <i class="fas fa-quran-book text-green-600 mr-3"></i>
            <?php 
            // Dynamic page title based on context
            if (!empty($context_info)) {
                switch ($context_info['mode']) {
                    case 'surat':
                        echo "Surat " . htmlspecialchars($context_info['surat_name']);
                        break;
                    case 'page':
                        echo "Halaman " . $context_info['page_number'];
                        break;
                    case 'juz':
                        echo "Juz " . $context_info['juz_number'];
                        break;
                    case 'tema':
                        echo isset($context_info['tema_name']) ? htmlspecialchars($context_info['tema_name']) : "Tema " . $context_info['tema_id'];
                        break;
                    default:
                        echo "Al-Quran Digital";
                }
            } else {
                echo "Al-Quran Digital";
            }
            ?>
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            <?php 
            // Dynamic description based on context
            if (!empty($context_info)) {
                switch ($context_info['mode']) {
                    case 'surat':
                        echo "Ayat " . $context_info['ayat_start'] . " - " . ($context_info['ayat_start'] + $context_info['ayat_count'] - 1) . 
                             " dari " . $context_info['total_ayat'] . " ayat";
                        break;
                    case 'page':
                        echo "Halaman " . $context_info['page_number'] . " dari " . $context_info['total_pages'] . " halaman mushaf";
                        break;
                    case 'juz':
                        echo "Juz " . $context_info['juz_number'] . " dari " . $context_info['total_juz'] . " juz";
                        break;
                    case 'tema':
                        if (isset($context_info['tema_description']) && !empty($context_info['tema_description'])) {
                            echo htmlspecialchars($context_info['tema_description']);
                        } else {
                            echo "Ayat-ayat berdasarkan tema tertentu";
                        }
                        break;
                    default:
                        echo "Baca Al-Quran dengan berbagai metode navigasi - per surat, halaman mushaf, juz, atau tema";
                }
            } else {
                echo "Baca Al-Quran dengan berbagai metode navigasi - per surat, halaman mushaf, juz, atau tema";
            }
            ?>
        </p>
        
        <!-- Quick Navigation Links -->
        <?php if (!empty($context_info)): ?>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <?php if ($context_info['mode'] !== 'surat'): ?>
                    <a href="alquran.php?mode=surat" 
                       class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition duration-200">
                        <i class="fas fa-book mr-1"></i>Per Surat
                    </a>
                <?php endif; ?>
                
                <?php if ($context_info['mode'] !== 'page'): ?>
                    <a href="alquran.php?mode=page" 
                       class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition duration-200">
                        <i class="fas fa-file-alt mr-1"></i>Per Halaman
                    </a>
                <?php endif; ?>
                
                <?php if ($context_info['mode'] !== 'juz'): ?>
                    <a href="alquran.php?mode=juz" 
                       class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition duration-200">
                        <i class="fas fa-bookmark mr-1"></i>Per Juz
                    </a>
                <?php endif; ?>
                
                <?php if ($context_info['mode'] !== 'tema'): ?>
                    <a href="alquran.php?mode=tema" 
                       class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition duration-200">
                        <i class="fas fa-search mr-1"></i>Per Tema
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navigation Component -->
    <?php include __DIR__ . '/../partials/alquran_navigation.php'; ?>

    <!-- Error Message with Enhanced Styling -->
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-red-800 font-medium text-lg mb-2">Terjadi Kesalahan</h3>
                    <p class="text-red-700 mb-4"><?php echo htmlspecialchars($error_message); ?></p>
                    
                    <!-- Suggested Actions -->
                    <div class="flex flex-wrap gap-2">
                        <a href="alquran.php" 
                           class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-800 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-home mr-2"></i>Kembali ke Beranda
                        </a>
                        <button onclick="window.location.reload()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-refresh mr-2"></i>Muat Ulang
                        </button>
                        <button onclick="window.history.back()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Al-Quran Display Component -->
    <?php include __DIR__ . '/../partials/alquran_display.php'; ?>
    
    <!-- URL Sharing and Bookmarking -->
    <?php if (!empty($context_info) && empty($error_message)): ?>
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Bagikan atau Simpan</h3>
                    <p class="text-sm text-gray-600">Bagikan halaman ini atau simpan sebagai bookmark untuk akses cepat</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button onclick="copyCurrentURL()" 
                            class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md text-sm font-medium transition duration-200">
                        <i class="fas fa-link mr-2"></i>Salin Link
                    </button>
                    <button onclick="shareCurrentPage()" 
                            class="inline-flex items-center px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-md text-sm font-medium transition duration-200">
                        <i class="fas fa-share-alt mr-2"></i>Bagikan
                    </button>
                    <button onclick="bookmarkPage()" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-md text-sm font-medium transition duration-200">
                        <i class="fas fa-bookmark mr-2"></i>Bookmark
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Enhanced JavaScript for URL handling and sharing -->
<script>
/**
 * Copy current page URL to clipboard
 */
function copyCurrentURL() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        showNotification('Link berhasil disalin ke clipboard', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('Link berhasil disalin ke clipboard', 'success');
        } catch (err) {
            showNotification('Gagal menyalin link', 'error');
        }
        
        document.body.removeChild(textArea);
    });
}

/**
 * Share current page using Web Share API or fallback
 */
function shareCurrentPage() {
    const title = document.title;
    const url = window.location.href;
    const text = document.querySelector('meta[name="description"]')?.content || title;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).then(() => {
            showNotification('Halaman berhasil dibagikan', 'success');
        }).catch(() => {
            // User cancelled or error occurred
        });
    } else {
        // Fallback to copy URL
        copyCurrentURL();
    }
}

/**
 * Add page to bookmarks (browser bookmark)
 */
function bookmarkPage() {
    const title = document.title;
    const url = window.location.href;
    
    if (window.sidebar && window.sidebar.addPanel) {
        // Firefox
        window.sidebar.addPanel(title, url, '');
    } else if (window.external && ('AddFavorite' in window.external)) {
        // Internet Explorer
        window.external.AddFavorite(url, title);
    } else if (window.opera && window.print) {
        // Opera
        const elem = document.createElement('a');
        elem.setAttribute('href', url);
        elem.setAttribute('title', title);
        elem.setAttribute('rel', 'sidebar');
        elem.click();
    } else {
        // Other browsers - show instruction
        showNotification('Tekan Ctrl+D (Windows) atau Cmd+D (Mac) untuk menambahkan bookmark', 'info', 5000);
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
    
    // Set colors based on type
    switch (type) {
        case 'success':
            notification.classList.add('bg-green-500', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-red-500', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-500', 'text-white');
            break;
        default:
            notification.classList.add('bg-blue-500', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'}-circle mr-3"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Enhanced URL parameter handling
document.addEventListener('DOMContentLoaded', function() {
    // Update browser history for better navigation
    const currentURL = new URL(window.location);
    const mode = currentURL.searchParams.get('mode') || 'surat';
    
    // Update page title dynamically if needed
    const pageTitle = document.querySelector('h1');
    if (pageTitle && window.history.replaceState) {
        const newTitle = pageTitle.textContent.trim() + ' - <?php echo htmlspecialchars($settings['site_name'] ?? 'Masjid Al-Muhajirin'); ?>';
        document.title = newTitle;
        window.history.replaceState({mode: mode}, newTitle, currentURL.toString());
    }
    
    // Add keyboard shortcuts for navigation
    document.addEventListener('keydown', function(e) {
        if (e.altKey) {
            switch(e.key) {
                case '1':
                    e.preventDefault();
                    window.location.href = 'alquran.php?mode=surat';
                    break;
                case '2':
                    e.preventDefault();
                    window.location.href = 'alquran.php?mode=page';
                    break;
                case '3':
                    e.preventDefault();
                    window.location.href = 'alquran.php?mode=juz';
                    break;
                case '4':
                    e.preventDefault();
                    window.location.href = 'alquran.php?mode=tema';
                    break;
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>