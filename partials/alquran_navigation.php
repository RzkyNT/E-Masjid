<?php
/**
 * Al-Quran Navigation Component
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides navigation controls for different Al-Quran reading modes:
 * - Surat and Ayat selection
 * - Page navigation (1-604)
 * - Juz navigation (1-30)
 * - Tema search interface
 * - Previous/Next navigation buttons
 * 
 * Requirements: 6.1, 6.2, 6.3
 */

require_once __DIR__ . '/../includes/alquran_validation.php';

// Get current navigation parameters
$current_mode = $_GET['mode'] ?? 'surat';
$current_surat = (int)($_GET['surat'] ?? 1);
$current_ayat = (int)($_GET['ayat'] ?? 1);
$current_page = (int)($_GET['page'] ?? 1);
$current_juz = (int)($_GET['juz'] ?? 1);
$current_tema = (int)($_GET['tema'] ?? 1);
$current_panjang = (int)($_GET['panjang'] ?? 5);

// Get all surat information for dropdown
$all_surat = AlQuranValidator::getAllSuratInfo();
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Mode Selection -->
        <div class="flex flex-wrap gap-2">
            <button onclick="switchMode('surat')" 
                    class="mode-btn <?php echo $current_mode === 'surat' ? 'active' : ''; ?> px-4 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-book mr-2"></i>Per Surat
            </button>
            <button onclick="switchMode('page')" 
                    class="mode-btn <?php echo $current_mode === 'page' ? 'active' : ''; ?> px-4 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-file-alt mr-2"></i>Per Halaman
            </button>
            <button onclick="switchMode('juz')" 
                    class="mode-btn <?php echo $current_mode === 'juz' ? 'active' : ''; ?> px-4 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-bookmark mr-2"></i>Per Juz
            </button>
            <button onclick="switchMode('tema')" 
                    class="mode-btn <?php echo $current_mode === 'tema' ? 'active' : ''; ?> px-4 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-search mr-2"></i>Per Tema
            </button>
        </div>

        <!-- Quick Navigation -->
        <div class="flex items-center gap-2">
            <button onclick="navigatePrevious()" 
                    id="prev-btn"
                    class="nav-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                    title="Sebelumnya">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="navigateNext()" 
                    id="next-btn"
                    class="nav-btn px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                    title="Selanjutnya">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Navigation Forms -->
    <div class="mt-6">
        <!-- Surat Mode Navigation -->
        <div id="surat-navigation" class="navigation-form <?php echo $current_mode === 'surat' ? 'active' : 'hidden'; ?>">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="mode" value="surat">
                
                <!-- Surat Selection -->
                <div>
                    <label for="surat" class="block text-sm font-medium text-gray-700 mb-2">Pilih Surat</label>
                    <select name="surat" id="surat" onchange="updateAyatOptions()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <?php foreach ($all_surat as $num => $info): ?>
                            <option value="<?php echo $num; ?>" <?php echo $current_surat === $num ? 'selected' : ''; ?>>
                                <?php echo $num; ?>. <?php echo htmlspecialchars($info['name']); ?> (<?php echo $info['ayat_count']; ?> ayat)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ayat Selection -->
                <div>
                    <label for="ayat" class="block text-sm font-medium text-gray-700 mb-2">Mulai dari Ayat</label>
                    <input type="number" name="ayat" id="ayat" value="<?php echo $current_ayat; ?>" 
                           min="1" max="<?php echo $all_surat[$current_surat]['ayat_count']; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Length Selection -->
                <div>
                    <label for="panjang" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Ayat</label>
                    <select name="panjang" id="panjang" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $current_panjang === $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> ayat
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-search mr-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Page Mode Navigation -->
        <div id="page-navigation" class="navigation-form <?php echo $current_mode === 'page' ? 'active' : 'hidden'; ?>">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="mode" value="page">
                
                <!-- Page Selection -->
                <div>
                    <label for="page" class="block text-sm font-medium text-gray-700 mb-2">Halaman Mushaf</label>
                    <input type="number" name="page" id="page" value="<?php echo $current_page; ?>" 
                           min="1" max="604" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="1-604">
                </div>

                <!-- Page Range Slider -->
                <div>
                    <label for="page-slider" class="block text-sm font-medium text-gray-700 mb-2">Atau gunakan slider</label>
                    <input type="range" id="page-slider" min="1" max="604" value="<?php echo $current_page; ?>" 
                           oninput="updatePageInput(this.value)"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-search mr-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Juz Mode Navigation -->
        <div id="juz-navigation" class="navigation-form <?php echo $current_mode === 'juz' ? 'active' : 'hidden'; ?>">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="mode" value="juz">
                
                <!-- Juz Selection -->
                <div>
                    <label for="juz" class="block text-sm font-medium text-gray-700 mb-2">Pilih Juz</label>
                    <select name="juz" id="juz" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $current_juz === $i ? 'selected' : ''; ?>>
                                Juz <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Juz Grid Selection -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atau pilih dari grid</label>
                    <div class="grid grid-cols-6 gap-2">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <button type="button" onclick="selectJuz(<?php echo $i; ?>)" 
                                    class="juz-btn <?php echo $current_juz === $i ? 'selected' : ''; ?> px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-green-50 hover:border-green-300 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-3 flex justify-center mt-4">
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-search mr-2"></i>Tampilkan Juz
                    </button>
                </div>
            </form>
        </div>

        <!-- Tema Mode Navigation -->
        <div id="tema-navigation" class="navigation-form <?php echo $current_mode === 'tema' ? 'active' : 'hidden'; ?>">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="mode" value="tema">
                
                <!-- Tema ID Input -->
                <div>
                    <label for="tema" class="block text-sm font-medium text-gray-700 mb-2">ID Tema</label>
                    <input type="number" name="tema" id="tema" value="<?php echo $current_tema; ?>" 
                           min="1" max="1121" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="1-1121">
                </div>

                <!-- Tema Search -->
                <div>
                    <label for="tema-search" class="block text-sm font-medium text-gray-700 mb-2">Cari Tema</label>
                    <div class="relative">
                        <input type="text" id="tema-search" 
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Ketik untuk mencari tema..."
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div id="tema-search-results" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                        <!-- Results will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-search mr-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Navigation JavaScript -->
<script>
// Current navigation state
let currentMode = '<?php echo $current_mode; ?>';
let currentSurat = <?php echo $current_surat; ?>;
let currentAyat = <?php echo $current_ayat; ?>;
let currentPage = <?php echo $current_page; ?>;
let currentJuz = <?php echo $current_juz; ?>;
let currentTema = <?php echo $current_tema; ?>;

// Surat information for JavaScript
const suratInfo = <?php echo json_encode($all_surat); ?>;

/**
 * Switch navigation mode
 */
function switchMode(mode) {
    // Update active mode button
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Hide all navigation forms
    document.querySelectorAll('.navigation-form').forEach(form => {
        form.classList.add('hidden');
        form.classList.remove('active');
    });
    
    // Show selected navigation form
    const targetForm = document.getElementById(mode + '-navigation');
    if (targetForm) {
        targetForm.classList.remove('hidden');
        targetForm.classList.add('active');
    }
    
    currentMode = mode;
    updateNavigationButtons();
}

/**
 * Update ayat options based on selected surat
 */
function updateAyatOptions() {
    const suratSelect = document.getElementById('surat');
    const ayatInput = document.getElementById('ayat');
    
    if (suratSelect && ayatInput) {
        const selectedSurat = parseInt(suratSelect.value);
        const maxAyat = suratInfo[selectedSurat].ayat_count;
        
        ayatInput.max = maxAyat;
        
        // Reset ayat to 1 if current value exceeds max
        if (parseInt(ayatInput.value) > maxAyat) {
            ayatInput.value = 1;
        }
    }
}

/**
 * Update page input from slider
 */
function updatePageInput(value) {
    const pageInput = document.getElementById('page');
    if (pageInput) {
        pageInput.value = value;
    }
}

/**
 * Select juz from grid
 */
function selectJuz(juzNumber) {
    // Update juz select
    const juzSelect = document.getElementById('juz');
    if (juzSelect) {
        juzSelect.value = juzNumber;
    }
    
    // Update grid button states
    document.querySelectorAll('.juz-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    currentJuz = juzNumber;
}

/**
 * Navigate to previous content
 */
function navigatePrevious() {
    const prevBtn = document.getElementById('prev-btn');
    if (prevBtn && prevBtn.disabled) return;
    
    let url = new URL(window.location);
    
    switch (currentMode) {
        case 'surat':
            if (currentAyat > 1) {
                url.searchParams.set('ayat', currentAyat - 1);
            } else if (currentSurat > 1) {
                const prevSurat = currentSurat - 1;
                url.searchParams.set('surat', prevSurat);
                url.searchParams.set('ayat', suratInfo[prevSurat].ayat_count);
            }
            break;
            
        case 'page':
            if (currentPage > 1) {
                url.searchParams.set('page', currentPage - 1);
            }
            break;
            
        case 'juz':
            if (currentJuz > 1) {
                url.searchParams.set('juz', currentJuz - 1);
            }
            break;
            
        case 'tema':
            if (currentTema > 1) {
                url.searchParams.set('tema', currentTema - 1);
            }
            break;
    }
    
    window.location.href = url.toString();
}

/**
 * Navigate to next content
 */
function navigateNext() {
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn && nextBtn.disabled) return;
    
    let url = new URL(window.location);
    
    switch (currentMode) {
        case 'surat':
            const maxAyat = suratInfo[currentSurat].ayat_count;
            if (currentAyat < maxAyat) {
                url.searchParams.set('ayat', currentAyat + 1);
            } else if (currentSurat < 114) {
                url.searchParams.set('surat', currentSurat + 1);
                url.searchParams.set('ayat', 1);
            }
            break;
            
        case 'page':
            if (currentPage < 604) {
                url.searchParams.set('page', currentPage + 1);
            }
            break;
            
        case 'juz':
            if (currentJuz < 30) {
                url.searchParams.set('juz', currentJuz + 1);
            }
            break;
            
        case 'tema':
            if (currentTema < 1121) {
                url.searchParams.set('tema', currentTema + 1);
            }
            break;
    }
    
    window.location.href = url.toString();
}

// Tema search functionality
let temaData = [];
let searchTimeout = null;
let selectedResultIndex = -1;

/**
 * Load all tema data for search functionality
 */
async function loadTemaData() {
    try {
        const response = await fetch('/api/alquran.php?action=get_all_tema');
        const result = await response.json();
        
        if (result.success && result.data && result.data.data) {
            temaData = result.data.data;
        }
    } catch (error) {
        console.error('Failed to load tema data:', error);
    }
}

/**
 * Search tema by name
 */
function searchTema(query) {
    if (!query || query.length < 2) {
        hideSearchResults();
        return;
    }
    
    const results = temaData.filter(tema => 
        tema.nama.toLowerCase().includes(query.toLowerCase()) ||
        (tema.deskripsi && tema.deskripsi.toLowerCase().includes(query.toLowerCase()))
    ).slice(0, 10); // Limit to 10 results
    
    displaySearchResults(results);
}

/**
 * Display tema search results
 */
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('tema-search-results');
    selectedResultIndex = -1;
    
    if (!results || results.length === 0) {
        resultsContainer.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">Tidak ada tema ditemukan</div>';
        resultsContainer.classList.remove('hidden');
        return;
    }
    
    const html = results.map((tema, index) => `
        <div class="tema-result px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
             data-index="${index}"
             data-tema-id="${tema.id}"
             data-tema-name="${tema.nama.replace(/"/g, '&quot;')}"
             onclick="selectTema(${tema.id}, '${tema.nama.replace(/'/g, "\\'")}')">
            <div class="font-medium text-sm text-gray-900">${tema.nama}</div>
            ${tema.deskripsi ? `<div class="text-xs text-gray-500 mt-1">${tema.deskripsi}</div>` : ''}
        </div>
    `).join('');
    
    resultsContainer.innerHTML = html;
    resultsContainer.classList.remove('hidden');
}

/**
 * Hide search results
 */
function hideSearchResults() {
    const resultsContainer = document.getElementById('tema-search-results');
    resultsContainer.classList.add('hidden');
    selectedResultIndex = -1;
}

/**
 * Select tema from search results
 */
function selectTema(temaId, temaNama) {
    const temaInput = document.getElementById('tema');
    const searchInput = document.getElementById('tema-search');
    
    if (temaInput) {
        temaInput.value = temaId;
    }
    
    if (searchInput) {
        searchInput.value = temaNama;
    }
    
    hideSearchResults();
    currentTema = temaId;
}

/**
 * Handle tema search input with keyboard navigation
 */
function handleTemaSearch(event) {
    const query = event.target.value;
    const resultsContainer = document.getElementById('tema-search-results');
    const results = resultsContainer.querySelectorAll('.tema-result');
    
    // Handle keyboard navigation
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (selectedResultIndex < results.length - 1) {
            selectedResultIndex++;
            updateSelectedResult(results);
        }
        return;
    }
    
    if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (selectedResultIndex > 0) {
            selectedResultIndex--;
            updateSelectedResult(results);
        }
        return;
    }
    
    if (event.key === 'Enter') {
        event.preventDefault();
        if (selectedResultIndex >= 0 && results[selectedResultIndex]) {
            const selectedResult = results[selectedResultIndex];
            const temaId = selectedResult.dataset.temaId;
            const temaNama = selectedResult.dataset.temaName;
            selectTema(parseInt(temaId), temaNama);
        }
        return;
    }
    
    if (event.key === 'Escape') {
        hideSearchResults();
        return;
    }
    
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Debounce search
    searchTimeout = setTimeout(() => {
        searchTema(query);
    }, 300);
}

/**
 * Update navigation button states based on current position
 */
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (!prevBtn || !nextBtn) return;
    
    let canGoPrev = true;
    let canGoNext = true;
    
    switch (currentMode) {
        case 'surat':
            canGoPrev = !(currentSurat === 1 && currentAyat === 1);
            canGoNext = !(currentSurat === 114 && currentAyat >= suratInfo[114].ayat_count);
            break;
            
        case 'page':
            canGoPrev = currentPage > 1;
            canGoNext = currentPage < 604;
            break;
            
        case 'juz':
            canGoPrev = currentJuz > 1;
            canGoNext = currentJuz < 30;
            break;
            
        case 'tema':
            canGoPrev = currentTema > 1;
            canGoNext = currentTema < 1121;
            break;
    }
    
    prevBtn.disabled = !canGoPrev;
    nextBtn.disabled = !canGoNext;
    
    if (!canGoPrev) {
        prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
        prevBtn.classList.remove('hover:bg-gray-200');
    } else {
        prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        prevBtn.classList.add('hover:bg-gray-200');
    }
    
    if (!canGoNext) {
        nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        nextBtn.classList.remove('hover:bg-gray-200');
    } else {
        nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        nextBtn.classList.add('hover:bg-gray-200');
    }
}

/**
 * Update selected result highlighting
 */
function updateSelectedResult(results) {
    results.forEach((result, index) => {
        if (index === selectedResultIndex) {
            result.classList.add('bg-green-50', 'border-green-200');
        } else {
            result.classList.remove('bg-green-50', 'border-green-200');
        }
    });
}

/**
 * Validate navigation inputs
 */
function validateNavigationInputs() {
    const currentForm = document.querySelector('.navigation-form.active');
    if (!currentForm) return true;
    
    switch (currentMode) {
        case 'surat':
            const surat = parseInt(document.getElementById('surat').value);
            const ayat = parseInt(document.getElementById('ayat').value);
            const panjang = parseInt(document.getElementById('panjang').value);
            
            if (surat < 1 || surat > 114) {
                alert('Nomor surat harus antara 1-114');
                return false;
            }
            
            const maxAyat = suratInfo[surat].ayat_count;
            if (ayat < 1 || ayat > maxAyat) {
                alert(`Nomor ayat harus antara 1-${maxAyat} untuk surat ini`);
                return false;
            }
            
            if (panjang < 1 || panjang > 30) {
                alert('Jumlah ayat harus antara 1-30');
                return false;
            }
            break;
            
        case 'page':
            const page = parseInt(document.getElementById('page').value);
            if (page < 1 || page > 604) {
                alert('Nomor halaman harus antara 1-604');
                return false;
            }
            break;
            
        case 'juz':
            const juz = parseInt(document.getElementById('juz').value);
            if (juz < 1 || juz > 30) {
                alert('Nomor juz harus antara 1-30');
                return false;
            }
            break;
            
        case 'tema':
            const tema = parseInt(document.getElementById('tema').value);
            if (tema < 1 || tema > 1121) {
                alert('ID tema harus antara 1-1121');
                return false;
            }
            break;
    }
    
    return true;
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set initial mode button state
    switchMode(currentMode);
    
    // Update navigation button states
    updateNavigationButtons();
    
    // Initialize page slider
    const pageSlider = document.getElementById('page-slider');
    if (pageSlider) {
        pageSlider.addEventListener('input', function() {
            updatePageInput(this.value);
        });
    }
    
    // Initialize tema search
    const temaSearchInput = document.getElementById('tema-search');
    if (temaSearchInput) {
        temaSearchInput.addEventListener('input', handleTemaSearch);
        temaSearchInput.addEventListener('keydown', handleTemaSearch);
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#tema-search') && !event.target.closest('#tema-search-results')) {
                hideSearchResults();
            }
        });
        
        // Load tema data for search
        loadTemaData();
    }
    
    // Add form validation to all forms
    document.querySelectorAll('.navigation-form form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateNavigationInputs()) {
                event.preventDefault();
            }
        });
    });
});
</script>

<!-- Navigation Styles -->
<style>
.mode-btn {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.mode-btn:hover {
    background-color: #e5e7eb;
    border-color: #9ca3af;
}

.mode-btn.active {
    background-color: #059669;
    color: white;
    border-color: #059669;
}

.juz-btn.selected {
    background-color: #dcfce7;
    border-color: #16a34a;
    color: #15803d;
}

.slider::-webkit-slider-thumb {
    appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #059669;
    cursor: pointer;
}

.slider::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #059669;
    cursor: pointer;
    border: none;
}

.navigation-form.hidden {
    display: none;
}

.navigation-form.active {
    display: block;
}

/* Tema search results styling */
#tema-search-results {
    z-index: 50;
}

.tema-result:hover {
    background-color: #f9fafb !important;
}

.tema-result.bg-green-50 {
    background-color: #f0fdf4 !important;
    border-color: #bbf7d0 !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .mode-btn {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
    
    .mode-btn i {
        display: none;
    }
    
    .grid.grid-cols-6 {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
    
    .juz-btn {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 640px) {
    .grid.grid-cols-1.md\\:grid-cols-4 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .grid.grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .flex.flex-col.lg\\:flex-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .grid.grid-cols-6 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* Loading state for tema search */
.tema-search-loading {
    padding: 0.75rem;
    text-align: center;
    color: #6b7280;
    font-size: 0.875rem;
}

.tema-search-loading::after {
    content: '';
    display: inline-block;
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    border-top-color: #059669;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Focus states for accessibility */
.mode-btn:focus,
.nav-btn:focus,
input:focus,
select:focus,
button:focus {
    outline: 2px solid #059669;
    outline-offset: 2px;
}

/* Improved button states */
.nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.nav-btn:disabled:hover {
    background-color: #f3f4f6;
}
</style>