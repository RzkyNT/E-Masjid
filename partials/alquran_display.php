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
                            <div class="latin-text text-gray-700 italic mb-4 text-lg leading-relaxed">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-normal block mb-1">Transliterasi:</span>
                                <?php echo htmlspecialchars($ayat['latin']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Indonesian Translation -->
                        <?php if (isset($ayat['text']) || isset($ayat['arti'])): ?>
                            <div class="translation-text text-gray-800 leading-relaxed">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-medium block mb-2">Terjemahan:</span>
                                <div class="text-base translation-text">
                                    <?php echo htmlspecialchars($ayat['text'] ?? $ayat['arti']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Notes/Tafsir -->
                        <?php if ((isset($ayat['notes']) && !empty($ayat['notes'])) || (isset($ayat['tafsir']) && !empty($ayat['tafsir']))): ?>
                            <div class="tafsir-text mt-4 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg">
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