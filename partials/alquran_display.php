<?php
/**
 * Al-Quran Display Component
 * For Masjid Al-Muhajirin Information System
 * 
 * Component for displaying Al-Quran text with proper Arabic formatting,
 * context information, font size controls, and copy functionality.
 * 
 * Requirements: 6.4, 8.1, 8.3
 * 
 * Expected variables:
 * - $quran_data: Array containing Al-Quran data from API
 * - $context_info: Array containing context information (mode, surat, page, etc.)
 * - $error_message: String containing error message if any
 */

// Ensure required variables are set
$quran_data = $quran_data ?? null;
$context_info = $context_info ?? [];
$error_message = $error_message ?? '';
?>

<!-- Font Size and Display Controls -->
<?php if (!empty($context_info) && empty($error_message)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <!-- Context Information -->
            <div class="flex items-center">
                <i class="fas fa-info-circle text-green-600 mr-3"></i>
                <div>
                    <?php if ($context_info['mode'] === 'surat'): ?>
                        <h3 class="text-green-800 font-medium">
                            Surat <?php echo htmlspecialchars($context_info['surat_name'] ?? ''); ?> (<?php echo $context_info['surat_number'] ?? ''; ?>)
                        </h3>
                        <p class="text-green-700 text-sm">
                            Ayat <?php echo $context_info['ayat_start'] ?? ''; ?> - <?php echo ($context_info['ayat_start'] ?? 0) + ($context_info['ayat_count'] ?? 0) - 1; ?> 
                            dari <?php echo $context_info['total_ayat'] ?? ''; ?> ayat
                        </p>
                    <?php elseif ($context_info['mode'] === 'page'): ?>
                        <h3 class="text-green-800 font-medium">
                            Halaman <?php echo $context_info['page_number'] ?? ''; ?>
                        </h3>
                        <p class="text-green-700 text-sm">
                            dari <?php echo $context_info['total_pages'] ?? 604; ?> halaman mushaf
                        </p>
                    <?php elseif ($context_info['mode'] === 'juz'): ?>
                        <h3 class="text-green-800 font-medium">
                            Juz <?php echo $context_info['juz_number'] ?? ''; ?>
                        </h3>
                        <p class="text-green-700 text-sm">
                            dari <?php echo $context_info['total_juz'] ?? 30; ?> juz
                        </p>
                        <?php if (isset($context_info['juz_info'])): ?>
                            <p class="text-green-600 text-xs mt-1">
                                <?php 
                                $juz_info = $context_info['juz_info'];
                                if (isset($juz_info['surat_mulai']) && isset($juz_info['surat_selesai'])) {
                                    echo "Dari " . htmlspecialchars($juz_info['surat_mulai']['nama'] ?? '') . 
                                         " ayat " . ($juz_info['surat_mulai']['ayat'] ?? '') .
                                         " sampai " . htmlspecialchars($juz_info['surat_selesai']['nama'] ?? '') . 
                                         " ayat " . ($juz_info['surat_selesai']['ayat'] ?? '');
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                    <?php elseif ($context_info['mode'] === 'tema'): ?>
                        <h3 class="text-green-800 font-medium">
                            <?php 
                            if (isset($context_info['tema_name'])) {
                                echo htmlspecialchars($context_info['tema_name']);
                            } else {
                                echo "Tema ID: " . ($context_info['tema_id'] ?? '');
                            }
                            ?>
                        </h3>
                        <p class="text-green-700 text-sm">
                            dari <?php echo $context_info['total_tema'] ?? 1121; ?> tema tersedia
                        </p>
                        <?php if (isset($context_info['tema_description'])): ?>
                            <p class="text-green-600 text-xs mt-1">
                                <?php echo htmlspecialchars($context_info['tema_description']); ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Font Size Controls -->
            <div class="flex items-center gap-2 bg-white rounded-lg px-3 py-2 shadow-sm">
                <span class="text-sm text-green-700 font-medium">Ukuran Font:</span>
                <button onclick="changeFontSize('decrease')" 
                        class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm transition duration-200 font-medium"
                        title="Perkecil font"
                        aria-label="Perkecil ukuran font">
                    <i class="fas fa-minus"></i>
                </button>
                <span id="font-size-indicator" class="text-sm text-green-700 min-w-[3rem] text-center">100%</span>
                <button onclick="changeFontSize('increase')" 
                        class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded text-sm transition duration-200 font-medium"
                        title="Perbesar font"
                        aria-label="Perbesar ukuran font">
                    <i class="fas fa-plus"></i>
                </button>
                <button onclick="resetFontSize()" 
                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded text-sm transition duration-200 ml-2"
                        title="Reset ukuran font"
                        aria-label="Reset ukuran font ke default">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Auto Scroll Floating Button - Clean Collapsible Design -->
<div id="auto-scroll-floating" class="fixed bottom-6 right-6 z-50 transition-all duration-300 ease-in-out">
    <!-- Main Auto Scroll Button (Always Visible) -->
    <button id="auto-scroll-main-btn" 
            class="w-16 h-16 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-lg transition-all duration-200 ease-in-out flex items-center justify-center relative group"
            title="Auto Scroll"
            aria-label="Toggle auto scroll">
        <i id="auto-scroll-icon" class="fas fa-play text-xl group-hover:scale-110 transition-transform duration-200"></i>
        
        <!-- Expand Indicator -->
        <div class="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <i class="fas fa-chevron-up"></i>
        </div>
    </button>
    
    <!-- Collapsible Controls (Hidden by default) -->
    <div id="auto-scroll-controls-stack" class="absolute bottom-20 right-0 opacity-0 invisible transform translate-y-4 transition-all duration-300 ease-in-out">
        <!-- Speed Decrease Button -->
        <button id="speed-decrease-floating" 
                class="w-12 h-12 bg-orange-600 hover:bg-orange-700 text-white rounded-full shadow-lg transition-all duration-200 ease-in-out flex items-center justify-center mb-3"
                title="Perlambat scroll"
                aria-label="Decrease scroll speed">
            <i class="fas fa-minus text-sm"></i>
        </button>
        
        <!-- Speed Increase Button -->
        <button id="speed-increase-floating" 
                class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-all duration-200 ease-in-out flex items-center justify-center mb-3"
                title="Percepat scroll"
                aria-label="Increase scroll speed">
            <i class="fas fa-plus text-sm"></i>
        </button>
        
        <!-- Display Options Button -->
        <button id="display-options-btn" 
                class="w-12 h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-full shadow-lg transition-all duration-200 ease-in-out flex items-center justify-center"
                title="Pengaturan tampilan"
                aria-label="Toggle display options">
            <i class="fas fa-eye text-sm"></i>
        </button>
    </div>
    
    <!-- Speed Indicator -->
    <div id="speed-indicator-floating" class="absolute -left-20 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white px-3 py-2 rounded text-xs font-medium opacity-0 invisible transition-all duration-300">
        <span id="speed-text">Sedang</span>
    </div>
    
    <!-- Display Options Panel -->
    <div id="display-options-panel" class="absolute bottom-0 right-16 bg-white rounded-lg shadow-lg border border-gray-200 p-3 min-w-[180px] opacity-0 invisible transform translate-x-2 transition-all duration-300 ease-in-out">
        <h4 class="text-xs font-medium text-gray-700 mb-3 uppercase tracking-wide">Tampilan</h4>
        
        <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="show-transliteration" checked class="w-4 h-4 text-purple-600 rounded">
                <span class="text-sm text-gray-700">Transliterasi</span>
            </label>
            
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="show-translation" checked class="w-4 h-4 text-purple-600 rounded">
                <span class="text-sm text-gray-700">Terjemahan</span>
            </label>
            
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="show-tafsir" checked class="w-4 h-4 text-purple-600 rounded">
                <span class="text-sm text-gray-700">Tafsir/Catatan</span>
            </label>
        </div>
        
        <div class="border-t border-gray-200 pt-3 mt-3">
            <button id="reset-display-options" 
                    class="w-full px-3 py-2 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-all duration-200 flex items-center justify-center gap-1"
                    title="Reset tampilan"
                    aria-label="Reset display options">
                <i class="fas fa-undo text-xs"></i>
                Reset
            </button>
        </div>
    </div>
</div>

<!-- Al-Quran Content Display -->
<?php if ($quran_data && empty($error_message)): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <?php if (isset($quran_data['data']) && is_array($quran_data['data']) && !empty($quran_data['data'])): ?>
            <div id="quran-content" class="divide-y divide-gray-100">
                <?php foreach ($quran_data['data'] as $index => $ayat): ?>
                    <div class="ayat-container p-6 hover:bg-gray-50 transition duration-200" 
                         data-ayat-number="<?php echo htmlspecialchars($ayat['ayah'] ?? $ayat['nomor'] ?? ''); ?>"
                         data-surat-number="<?php echo htmlspecialchars($ayat['surah'] ?? $ayat['nomor_surat'] ?? ''); ?>">
                        
                        <!-- Ayat Header -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
                            <div class="flex items-center gap-3">
                                <!-- Ayat Number Badge -->
                                <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold shadow-sm">
                                    <?php echo htmlspecialchars($ayat['ayah'] ?? $ayat['nomor'] ?? ''); ?>
                                </div>
                                
                                <!-- Ayat Metadata -->
                                <div class="text-sm text-gray-600">
                                    <?php if (isset($ayat['nama_surat']) || isset($context_info['surat_name'])): ?>
                                        <div class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($ayat['nama_surat'] ?? $context_info['surat_name'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <?php if (isset($ayat['juz'])): ?>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">
                                                Juz <?php echo htmlspecialchars($ayat['juz']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($ayat['page']) || isset($ayat['halaman'])): ?>
                                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded">
                                                Hal. <?php echo htmlspecialchars($ayat['page'] ?? $ayat['halaman']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($ayat['surah']) || isset($ayat['nomor_surat'])): ?>
                                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                Surat <?php echo htmlspecialchars($ayat['surah'] ?? $ayat['nomor_surat']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <button onclick="copyAyat(this)" 
                                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition duration-200 font-medium"
                                        title="Salin ayat lengkap"
                                        aria-label="Salin ayat">
                                    <i class="fas fa-copy mr-1"></i>Salin
                                </button>
                                <button onclick="shareAyat(this)" 
                                        class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg text-sm transition duration-200 font-medium"
                                        title="Bagikan ayat"
                                        aria-label="Bagikan ayat">
                                    <i class="fas fa-share-alt mr-1"></i>Bagikan
                                </button>
                            </div>
                        </div>
                        
                        <!-- Arabic Text -->
                        <?php if (isset($ayat['arab']) && !empty($ayat['arab'])): ?>
                            <div class="arabic-text text-right leading-loose mb-6 font-arabic transition-all duration-200" 
                                 dir="rtl" lang="ar"
                                 style="font-size: 2rem; line-height: 2.5;">
                                <?php echo htmlspecialchars($ayat['arab']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Latin Transliteration -->
                        <?php if (isset($ayat['latin']) && !empty($ayat['latin'])): ?>
                            <div class="latin-text text-gray-700 italic mb-4 text-lg leading-relaxed transliteration-content" 
                                 dir="ltr" lang="id">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-normal block mb-1">Transliterasi:</span>
                                <?php echo htmlspecialchars($ayat['latin']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Indonesian Translation -->
                        <?php if (isset($ayat['text']) || isset($ayat['arti'])): ?>
                            <div class="translation-text text-gray-800 leading-relaxed translation-content">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-medium block mb-2">Terjemahan:</span>
                                <div class="text-base translation-text">
                                    <?php echo htmlspecialchars($ayat['text'] ?? $ayat['arti']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Notes/Tafsir -->
                        <?php if ((isset($ayat['notes']) && !empty($ayat['notes'])) || (isset($ayat['tafsir']) && !empty($ayat['tafsir']))): ?>
                            <div class="tafsir-text mt-4 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg tafsir-content">
                                <span class="text-xs uppercase tracking-wide text-amber-700 font-medium block mb-2">Catatan:</span>
                                <div class="text-amber-800 leading-relaxed tafsir-text">
                                    <?php echo htmlspecialchars($ayat['notes'] ?? $ayat['tafsir']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Summary Information -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-600 gap-2">
                    <div>
                        Menampilkan <?php echo count($quran_data['data']); ?> ayat
                        <?php if (isset($context_info['mode']) && $context_info['mode'] === 'surat'): ?>
                            dari Surat <?php echo htmlspecialchars($context_info['surat_name'] ?? ''); ?>
                        <?php elseif (isset($context_info['mode']) && $context_info['mode'] === 'page'): ?>
                            dari Halaman <?php echo $context_info['page_number'] ?? ''; ?>
                        <?php elseif (isset($context_info['mode']) && $context_info['mode'] === 'juz'): ?>
                            dari Juz <?php echo $context_info['juz_number'] ?? ''; ?>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs">
                        <i class="fas fa-info-circle mr-1"></i>
                        Gunakan tombol salin untuk menyalin ayat
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- No Data Found -->
            <div class="text-center py-16">
                <i class="fas fa-book-open text-gray-400 text-5xl mb-6"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-3">Data tidak ditemukan</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Tidak ada data Al-Quran yang ditemukan untuk parameter yang diberikan. 
                    Silakan periksa kembali input Anda.
                </p>
                <button onclick="window.location.reload()" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-refresh mr-2"></i>Muat Ulang
                </button>
            </div>
        <?php endif; ?>
    </div>
    
<?php elseif (empty($error_message)): ?>
    <!-- Welcome/Default State -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <i class="fas fa-quran-book text-green-600 text-6xl mb-8"></i>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Selamat Datang di Al-Quran Digital</h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto text-lg leading-relaxed">
            Pilih metode navigasi di atas untuk mulai membaca Al-Quran. Anda dapat membaca per surat, 
            per halaman mushaf, per juz, atau mencari berdasarkan tema tertentu.
        </p>
        
        <!-- Navigation Options -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div class="bg-green-50 p-6 rounded-xl hover:bg-green-100 transition duration-200 cursor-pointer"
                 onclick="document.querySelector('select[name=mode]').value='surat'; document.querySelector('select[name=mode]').dispatchEvent(new Event('change'));">
                <i class="fas fa-book text-green-600 text-3xl mb-4"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Per Surat</h3>
                <p class="text-sm text-gray-600">Baca berdasarkan surat dan ayat tertentu</p>
            </div>
            <div class="bg-blue-50 p-6 rounded-xl hover:bg-blue-100 transition duration-200 cursor-pointer"
                 onclick="document.querySelector('select[name=mode]').value='page'; document.querySelector('select[name=mode]').dispatchEvent(new Event('change'));">
                <i class="fas fa-file-alt text-blue-600 text-3xl mb-4"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Per Halaman</h3>
                <p class="text-sm text-gray-600">Ikuti halaman mushaf standar</p>
            </div>
            <div class="bg-purple-50 p-6 rounded-xl hover:bg-purple-100 transition duration-200 cursor-pointer"
                 onclick="document.querySelector('select[name=mode]').value='juz'; document.querySelector('select[name=mode]').dispatchEvent(new Event('change'));">
                <i class="fas fa-bookmark text-purple-600 text-3xl mb-4"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Per Juz</h3>
                <p class="text-sm text-gray-600">Baca berdasarkan pembagian juz</p>
            </div>
            <div class="bg-amber-50 p-6 rounded-xl hover:bg-amber-100 transition duration-200 cursor-pointer"
                 onclick="document.querySelector('select[name=mode]').value='tema'; document.querySelector('select[name=mode]').dispatchEvent(new Event('change'));">
                <i class="fas fa-search text-amber-600 text-3xl mb-4"></i>
                <h3 class="font-semibold text-gray-900 mb-2">Per Tema</h3>
                <p class="text-sm text-gray-600">Cari berdasarkan topik tertentu</p>
            </div>
        </div>
        
        <!-- Quick Tips -->
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Tips Penggunaan:</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex items-start gap-3">
                    <i class="fas fa-lightbulb text-yellow-500 mt-1"></i>
                    <div>
                        <strong>Font Size:</strong> Gunakan tombol + dan - untuk mengatur ukuran font Arab
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-copy text-blue-500 mt-1"></i>
                    <div>
                        <strong>Copy Ayat:</strong> Klik tombol "Salin" untuk menyalin ayat lengkap
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-mobile-alt text-green-500 mt-1"></i>
                    <div>
                        <strong>Mobile Friendly:</strong> Interface responsif untuk semua perangkat
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-keyboard text-purple-500 mt-1"></i>
                    <div>
                        <strong>Keyboard:</strong> Gunakan Tab untuk navigasi dengan keyboard
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript for Font Size Control and Copy Functionality -->
<script>
let currentFontSize = 1; // 1 = 100%

// Base font size (rem) per jenis teks
const FONT_BASE = {
    arabic: 2.4,
    translation: 1.4,
    latin: 1.2,
    tafsir: 1.1
};

const LINE_HEIGHT = {
    arabic: 2.8,
    translation: 1.8,
    latin: 1.7,
    tafsir: 1.6
};

/**
 * Change font size
 * @param {string} action - 'increase' or 'decrease'
 */
function changeFontSize(action) {
    const indicator = document.getElementById('font-size-indicator');

    if (action === 'increase' && currentFontSize < 2) {
        currentFontSize += 0.1;
    } else if (action === 'decrease' && currentFontSize > 0.6) {
        currentFontSize -= 0.1;
    }

    applyFontSize();

    if (indicator) {
        indicator.textContent = Math.round(currentFontSize * 100) + '%';
    }

    localStorage.setItem('alquran_font_size', currentFontSize);
}

/**
 * Apply font size to each text type
 */
function applyFontSize() {
    document.querySelectorAll('.arabic-text').forEach(el => {
        el.style.fontSize = `${FONT_BASE.arabic * currentFontSize}rem`;
        el.style.lineHeight = LINE_HEIGHT.arabic;
    });

    document.querySelectorAll('.translation-text').forEach(el => {
        el.style.fontSize = `${FONT_BASE.translation * currentFontSize}rem`;
        el.style.lineHeight = LINE_HEIGHT.translation;
    });

    document.querySelectorAll('.latin-text').forEach(el => {
        el.style.fontSize = `${FONT_BASE.latin * currentFontSize}rem`;
        el.style.lineHeight = LINE_HEIGHT.latin;
    });

    document.querySelectorAll('.tafsir-text').forEach(el => {
        el.style.fontSize = `${FONT_BASE.tafsir * currentFontSize}rem`;
        el.style.lineHeight = LINE_HEIGHT.tafsir;
    });
}

/**
 * Reset font size
 */
function resetFontSize() {
    currentFontSize = 1;
    applyFontSize();

    const indicator = document.getElementById('font-size-indicator');
    if (indicator) {
        indicator.textContent = '100%';
    }

    localStorage.removeItem('alquran_font_size');
}

/**
 * Load saved font size on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    const savedSize = localStorage.getItem('alquran_font_size');
    if (savedSize) {
        currentFontSize = parseFloat(savedSize);
        applyFontSize();
    }
});


/**
 * Copy ayat text to clipboard
 * @param {HTMLElement} button - The copy button element
 */
function copyAyat(button) {
    const ayatContainer = button.closest('.ayat-container');
    const ayatNumber = ayatContainer.dataset.ayatNumber || '';
    const suratNumber = ayatContainer.dataset.suratNumber || '';
    
    const arabicText = ayatContainer.querySelector('.arabic-text')?.textContent?.trim() || '';
    const latinText = ayatContainer.querySelector('.latin-text')?.textContent?.replace('Transliterasi:', '').trim() || '';
    const translationText = ayatContainer.querySelector('.translation-text')?.textContent?.replace('Terjemahan:', '').trim() || '';
    const suratName = ayatContainer.querySelector('.font-medium')?.textContent?.trim() || '';
    
    // Format the text for copying  
    let fullText = '';
    if (arabicText) {
        fullText += arabicText + '\n\n';
    }
    if (latinText) {
        fullText += latinText + '\n\n';
    }
    if (translationText) {
        fullText += translationText + '\n\n';
    }
    if (suratName && ayatNumber) {
        fullText += `(${suratName}, Ayat ${ayatNumber})`;
    } else if (ayatNumber) {
        fullText += `(Ayat ${ayatNumber})`;
    }
    
    // Copy to clipboard
    navigator.clipboard.writeText(fullText).then(() => {
        showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Tersalin', 2000);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = fullText;
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Tersalin', 2000);
        } catch (err) {
            showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        }
        
        document.body.removeChild(textArea);
    });
}

/**
 * Share ayat using Web Share API or fallback to copy
 * @param {HTMLElement} button - The share button element
 */
function shareAyat(button) {
    const ayatContainer = button.closest('.ayat-container');
    const ayatNumber = ayatContainer.dataset.ayatNumber || '';
    const arabicText = ayatContainer.querySelector('.arabic-text')?.textContent?.trim() || '';
    const translationText = ayatContainer.querySelector('.translation-text')?.textContent?.replace('Terjemahan:', '').trim() || '';
    const suratName = ayatContainer.querySelector('.font-medium')?.textContent?.trim() || '';
    
    const shareText = `${arabicText}\n\n${translationText}\n\n(${suratName || 'Al-Quran'}, Ayat ${ayatNumber})`;
    const shareTitle = `${suratName || 'Al-Quran'} Ayat ${ayatNumber}`;
    
    if (navigator.share) {
        navigator.share({
            title: shareTitle,
            text: shareText,
            url: window.location.href
        }).then(() => {
            showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Dibagikan', 1500);
        }).catch(() => {
            // User cancelled or error occurred
        });
    } else {
        // Fallback to copy
        navigator.clipboard.writeText(shareText).then(() => {
            showButtonFeedback(button, 'success', '<i class="fas fa-copy mr-1"></i>Tersalin', 2000);
        }).catch(() => {
            showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        });
    }
}

/**
 * Show feedback on button click
 * @param {HTMLElement} button - Button element
 * @param {string} type - 'success' or 'error'
 * @param {string} message - Message to show
 * @param {number} duration - Duration in milliseconds
 */
function showButtonFeedback(button, type, message, duration) {
    const originalText = button.innerHTML;
    const originalClasses = button.className;
    
    button.innerHTML = message;
    
    if (type === 'success') {
        button.className = button.className.replace(/bg-\w+-\d+/, 'bg-green-100').replace(/text-\w+-\d+/, 'text-green-600');
    } else if (type === 'error') {
        button.className = button.className.replace(/bg-\w+-\d+/, 'bg-red-100').replace(/text-\w+-\d+/, 'text-red-600');
    }
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.className = originalClasses;
    }, duration);
}

// Load saved font size preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedFontSize = localStorage.getItem('alquran_font_size');
    if (savedFontSize) {
        currentFontSize = parseFloat(savedFontSize);
        const arabicTexts = document.querySelectorAll('.arabic-text');
        const indicator = document.getElementById('font-size-indicator');
        
        arabicTexts.forEach(text => {
            text.style.fontSize = `${currentFontSize * 2}rem`;
            text.style.lineHeight = `${Math.max(2.2, currentFontSize * 2.5)}`;
        });
        
        if (indicator) {
            indicator.textContent = Math.round(currentFontSize * 100) + '%';
        }
    }
});

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case '=':
            case '+':
                e.preventDefault();
                changeFontSize('increase');
                break;
            case '-':
                e.preventDefault();
                changeFontSize('decrease');
                break;
            case '0':
                e.preventDefault();
                resetFontSize();
                break;
        }
    }
});

// Auto Scroll Floating Button Functionality - Clean Collapsible Design
document.addEventListener('DOMContentLoaded', function() {
    const floatingButton = document.getElementById('auto-scroll-floating');
    const mainButton = document.getElementById('auto-scroll-main-btn');
    const controlsStack = document.getElementById('auto-scroll-controls-stack');
    
    if (!floatingButton || !mainButton) {
        return; // Exit if elements not found
    }
    
    let controlsVisible = false;
    let hoverTimeout;
    
    // Show controls on hover or click
    function showControls() {
        if (controlsStack) {
            controlsStack.classList.remove('opacity-0', 'invisible', 'translate-y-4');
            controlsStack.classList.add('opacity-100', 'visible', 'translate-y-0');
        }
        controlsVisible = true;
    }
    
    // Hide controls
    function hideControls() {
        if (controlsStack) {
            controlsStack.classList.remove('opacity-100', 'visible', 'translate-y-0');
            controlsStack.classList.add('opacity-0', 'invisible', 'translate-y-4');
        }
        controlsVisible = false;
    }
    
    // Mouse enter - show controls with delay
    floatingButton.addEventListener('mouseenter', function() {
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(showControls, 300); // 300ms delay
    });
    
    // Mouse leave - hide controls with delay
    floatingButton.addEventListener('mouseleave', function() {
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(hideControls, 500); // 500ms delay
    });
    
    // Click main button - toggle controls
    mainButton.addEventListener('click', function(e) {
        // Don't prevent the auto scroll toggle
        // Just toggle controls visibility
        if (controlsVisible) {
            hideControls();
        } else {
            showControls();
        }
    });
    
    // Keep controls visible when hovering over them
    if (controlsStack) {
        controlsStack.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
        });
        
        controlsStack.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(hideControls, 300);
        });
    }
    
    // Hide controls when clicking outside
    document.addEventListener('click', function(e) {
        if (!floatingButton.contains(e.target) && controlsVisible) {
            hideControls();
        }
    });
    
    // Initialize button visibility based on page content
    initializeButtonVisibility();
    
    function initializeButtonVisibility() {
        // Show button only if there's Al-Quran content
        const quranContent = document.getElementById('quran-content');
        if (quranContent && quranContent.children.length > 0) {
            floatingButton.style.display = 'block';
        } else {
            floatingButton.style.display = 'none';
        }
    }
});
</script>

<!-- Enhanced Arabic Font Styles -->
<style>
/* Import Arabic fonts */
@import url('https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@400;700&display=swap');

.font-arabic {
    font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', 'Al Bayan', 'Geeza Pro', serif;
    font-feature-settings: 'liga' 1, 'dlig' 1, 'calt' 1;
    text-rendering: optimizeLegibility;
}

.arabic-text {
    word-spacing: 0.2em;
    letter-spacing: 0.05em;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Responsive font sizes */
@media (max-width: 640px) {
    .arabic-text {
        font-size: 1.5rem !important;
        line-height: 2.2 !important;
    }
}

@media (min-width: 641px) and (max-width: 768px) {
    .arabic-text {
        font-size: 1.75rem !important;
        line-height: 2.3 !important;
    }
}

/* Hover effects */
.ayat-container:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Print styles */
@media print {
    .ayat-container {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .arabic-text {
        font-size: 14pt !important;
        line-height: 1.8 !important;
    }
    
    button {
        display: none !important;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .arabic-text {
        text-shadow: none;
        font-weight: 600;
    }
}

/* Auto Scroll Floating Button Styles - Clean Collapsible Design */
#auto-scroll-floating {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#auto-scroll-floating button {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* Main button enhanced styling */
#auto-scroll-main-btn {
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

#auto-scroll-main-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
}

#auto-scroll-main-btn:active {
    transform: scale(0.95);
}

/* Controls stack styling */
#auto-scroll-controls-stack {
    backdrop-filter: blur(10px);
}

#auto-scroll-controls-stack button {
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Speed control buttons hover effects */
#speed-increase-floating:hover,
#speed-decrease-floating:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

#speed-increase-floating:active,
#speed-decrease-floating:active {
    transform: scale(0.95);
}

/* Display options button hover effects */
#display-options-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(147, 51, 234, 0.3);
}

#display-options-btn:active {
    transform: scale(0.95);
}

/* Speed indicator floating */
#speed-indicator-floating.show {
    opacity: 1;
    visibility: visible;
}

/* Display options panel */
#display-options-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}

/* Paused state styling */
#auto-scroll-main-btn.paused {
    background-color: #f59e0b !important;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
}

#auto-scroll-main-btn.paused:hover {
    background-color: #d97706 !important;
    box-shadow: 0 6px 25px rgba(245, 158, 11, 0.4);
}

/* Temporarily disabled state styling */
#auto-scroll-main-btn.temporarily-disabled {
    background-color: #f97316 !important;
    opacity: 0.7;
}

#auto-scroll-main-btn.temporarily-disabled:hover {
    background-color: #ea580c !important;
}

/* Content visibility controls */
.transliteration-content.hidden {
    display: none !important;
}

.translation-content.hidden {
    display: none !important;
}

.tafsir-content.hidden {
    display: none !important;
}

/* Smooth transitions for content visibility */
.transliteration-content,
.translation-content,
.tafsir-content {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.transliteration-content.hiding,
.translation-content.hiding,
.tafsir-content.hiding {
    opacity: 0;
    transform: translateY(-10px);
}

/* Speed and direction button states */
.speed-btn.active,
.direction-btn.active {
    background-color: #10b981 !important;
    color: white !important;
    border-color: #059669 !important;
}

.speed-btn:not(.active):hover,
.direction-btn:not(.active):hover {
    background-color: #f3f4f6;
    transform: translateY(-1px);
}

/* Keyboard tooltip */
#keyboard-tooltip.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

#keyboard-tooltip kbd {
    font-family: monospace;
    font-size: 0.75rem;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
}

/* Pulse animation for active state */
@keyframes pulse-green {
    0%, 100% {
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    }
    50% {
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.6), 0 0 0 10px rgba(16, 185, 129, 0.1);
    }
}

#auto-scroll-main-btn.active {
    animation: pulse-green 2s infinite;
}

/* Staggered animation for controls */
#auto-scroll-controls-stack.opacity-100 button:nth-child(1) {
    animation: slideInUp 0.3s ease-out 0.1s both;
}

#auto-scroll-controls-stack.opacity-100 button:nth-child(2) {
    animation: slideInUp 0.3s ease-out 0.2s both;
}

#auto-scroll-controls-stack.opacity-100 button:nth-child(3) {
    animation: slideInUp 0.3s ease-out 0.3s both;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Focus styles for accessibility */
#auto-scroll-floating button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
    #auto-scroll-floating {
        bottom: 1rem;
        right: 1rem;
    }
    
    #auto-scroll-main-btn {
        width: 4rem;
        height: 4rem;
    }
    
    #auto-scroll-controls-stack button {
        width: 2.75rem;
        height: 2.75rem;
    }
    
    /* Larger touch targets for mobile */
    #auto-scroll-controls-stack {
        bottom: 5rem;
    }
}

/* Tablet responsive adjustments */
@media (min-width: 641px) and (max-width: 1024px) {
    #auto-scroll-floating {
        bottom: 1.5rem;
        right: 1.5rem;
    }
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    #auto-scroll-main-btn {
        border-width: 1px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    #auto-scroll-floating *,
    #auto-scroll-floating *::before,
    #auto-scroll-floating *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    #auto-scroll-main-btn.active {
        animation: none;
    }
    
    #auto-scroll-controls-stack.opacity-100 button {
        animation: none;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .ayat-container,
    button {
        transition: none !important;
    }
}

/* Focus styles for accessibility */
button:focus {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

.ayat-container:focus-within {
    outline: 2px solid #10B981;
    outline-offset: 2px;
    border-radius: 0.5rem;
}
</style>