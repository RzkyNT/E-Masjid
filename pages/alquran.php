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

// Set page information
$page_title = 'Al-Quran Digital';
$page_description = 'Baca Al-Quran digital dengan berbagai metode navigasi - per surat, halaman, juz, atau tema';

// Set breadcrumb
$breadcrumb = [
    ['title' => 'Al-Quran', 'url' => '#']
];

// Include required files
require_once __DIR__ . '/../includes/alquran_api.php';
require_once __DIR__ . '/../includes/alquran_validation.php';
require_once __DIR__ . '/../includes/alquran_parameter_handler.php';

// Include header
include __DIR__ . '/../partials/header.php';

// Get and validate parameters
$mode = $_GET['mode'] ?? 'surat';
$error_message = '';
$quran_data = null;
$context_info = [];

try {
    // Initialize API
    $api = getAlQuranAPI();
    
    // Process based on mode
    switch ($mode) {
        case 'surat':
            $surat = (int)($_GET['surat'] ?? 1);
            $ayat = (int)($_GET['ayat'] ?? 1);
            $panjang = (int)($_GET['panjang'] ?? 5);
            
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('surat', [
                'surat' => $surat,
                'ayat' => $ayat,
                'panjang' => $panjang
            ]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get data
            $quran_data = $api->getAyatBySurat($surat, $ayat, $panjang);
            
            // Set context info
            $surat_info = AlQuranValidator::getSuratInfo($surat);
            $context_info = [
                'mode' => 'surat',
                'surat_number' => $surat,
                'surat_name' => $surat_info['name'],
                'ayat_start' => $ayat,
                'ayat_count' => $panjang,
                'total_ayat' => $surat_info['ayat_count']
            ];
            break;
            
        case 'page':
            $page = (int)($_GET['page'] ?? 1);
            
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
            $juz = (int)($_GET['juz'] ?? 1);
            
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
            $tema_id = (int)($_GET['tema'] ?? 1);
            
            // Validate parameters
            $validation = AlQuranValidator::validateParameters('tema', ['tema_id' => $tema_id]);
            
            if (!$validation['valid']) {
                throw new Exception(implode(', ', $validation['errors']));
            }
            
            // Get data
            $quran_data = $api->getTemaById($tema_id);
            
            // Set context info
            $context_info = [
                'mode' => 'tema',
                'tema_id' => $tema_id,
                'total_tema' => 1121
            ];
            break;
            
        default:
            throw new Exception('Mode navigasi tidak valid');
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            <i class="fas fa-quran-book text-green-600 mr-3"></i>
            Al-Quran Digital
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Baca Al-Quran dengan berbagai metode navigasi - per surat, halaman mushaf, juz, atau tema
        </p>
    </div>

    <!-- Navigation Component -->
    <?php include __DIR__ . '/../partials/alquran_navigation.php'; ?>

    <!-- Error Message -->
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-medium">Terjadi Kesalahan</h3>
                    <p class="text-red-700 mt-1"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Al-Quran Display Component -->
    <?php include __DIR__ . '/../partials/alquran_display.php'; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>