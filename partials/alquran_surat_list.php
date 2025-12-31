<?php
/**
 * Al-Quran Surat List Component
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays list of all 114 surat with search functionality
 * 
 * Expected variables:
 * - $surat_list: Array containing all surat information
 * - $search_query: Current search query (if any)
 */

// Ensure required variables are set
$surat_list = $surat_list ?? AlQuranValidator::getAllSuratInfo();
$search_query = $search_query ?? '';
?>

<!-- Advanced Search Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-search text-green-600 mr-2"></i>
            Pencarian Al-Quran
        </h2>
        <button onclick="toggleAdvancedSearch()" 
                class="text-sm text-green-600 hover:text-green-700 font-medium">
            <i class="fas fa-cog mr-1"></i>Pencarian Lanjutan
        </button>
    </div>
    
    <!-- Basic Search -->
    <form method="GET" action="alquran.php" class="mb-4">
        <input type="hidden" name="mode" value="search">
        <div class="flex gap-2">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       placeholder="Cari surat, ayat, transliterasi, atau terjemahan..."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       required>
            </div>
            <button type="submit" 
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
        </div>
    </form>
    
    <!-- Advanced Search (Hidden by default) -->
    <div id="advanced-search" class="hidden border-t pt-4">
        <form method="GET" action="alquran.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="mode" value="search">
            
            <div>
                <label for="search_advanced" class="block text-sm font-medium text-gray-700 mb-2">Kata Kunci</label>
                <input type="text" 
                       name="search" 
                       id="search_advanced"
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       placeholder="Masukkan kata kunci..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       required>
            </div>
            
            <div>
                <label for="search_type" class="block text-sm font-medium text-gray-700 mb-2">Cari dalam</label>
                <select name="search_type" id="search_type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="all">Semua</option>
                    <option value="surat">Nama Surat</option>
                    <option value="transliterasi">Transliterasi</option>
                    <option value="terjemahan">Terjemahan</option>
                    <option value="ayat">Teks Arab</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-search mr-2"></i>Cari Lanjutan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Filter -->
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm font-medium text-gray-700">Filter Cepat:</span>
        <button onclick="filterSurat('all')" 
                class="filter-btn active px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition duration-200">
            Semua (114)
        </button>
        <button onclick="filterSurat('makkah')" 
                class="filter-btn px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition duration-200">
            Makkiyah
        </button>
        <button onclick="filterSurat('madinah')" 
                class="filter-btn px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition duration-200">
            Madaniyah
        </button>
        <button onclick="filterSurat('short')" 
                class="filter-btn px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition duration-200">
            Pendek (&lt;20 ayat)
        </button>
        <button onclick="filterSurat('long')" 
                class="filter-btn px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition duration-200">
            Panjang (&gt;100 ayat)
        </button>
    </div>
</div>

<!-- Surat List -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-list text-green-600 mr-2"></i>
            Daftar Surat Al-Quran
        </h2>
        <p class="text-sm text-gray-600 mt-1">Klik pada surat untuk membaca seluruh isi surat</p>
    </div>
    
    <div id="surat-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1">
        <?php foreach ($surat_list as $number => $info): ?>
            <a href="alquran.php?mode=surat&surat=<?php echo $number; ?>" 
               class="surat-item block p-4 hover:bg-green-50 border-b border-gray-100 transition duration-200 group"
               data-surat="<?php echo $number; ?>"
               data-name="<?php echo strtolower($info['name']); ?>"
               data-ayat-count="<?php echo $info['ayat_count']; ?>"
               data-type="<?php echo isset($info['type']) ? $info['type'] : 'makkah'; ?>">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Surat Number -->
                        <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold mr-3 group-hover:bg-green-700 transition duration-200">
                            <?php echo $number; ?>
                        </div>
                        
                        <!-- Surat Info -->
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition duration-200">
                                <?php echo htmlspecialchars($info['name']); ?>
                            </h3>
                            <p class="text-sm text-gray-600">
                                <?php echo $info['ayat_count']; ?> ayat
                                <?php if (isset($info['type'])): ?>
                                    • <?php echo ucfirst($info['type']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Arrow Icon -->
                    <div class="text-gray-400 group-hover:text-green-600 transition duration-200">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <!-- Arabic Name (if available) -->
                <?php if (isset($info['arabic_name'])): ?>
                    <div class="mt-2 text-right">
                        <span class="text-lg font-arabic text-gray-700"><?php echo htmlspecialchars($info['arabic_name']); ?></span>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- No Results Message (Hidden by default) -->
    <div id="no-results" class="hidden text-center py-12">
        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada surat ditemukan</h3>
        <p class="text-gray-600">Coba ubah filter atau kata kunci pencarian</p>
        <button onclick="filterSurat('all')" 
                class="mt-4 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
            Tampilkan Semua Surat
        </button>
    </div>
</div>

<!-- Statistics -->
<div class="mt-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div>
            <div class="text-2xl font-bold text-green-600">114</div>
            <div class="text-sm text-gray-600">Total Surat</div>
        </div>
        <div>
            <div class="text-2xl font-bold text-blue-600">6,236</div>
            <div class="text-sm text-gray-600">Total Ayat</div>
        </div>
        <div>
            <div class="text-2xl font-bold text-purple-600">30</div>
            <div class="text-sm text-gray-600">Total Juz</div>
        </div>
        <div>
            <div class="text-2xl font-bold text-orange-600">604</div>
            <div class="text-sm text-gray-600">Total Halaman</div>
        </div>
    </div>
</div>

<!-- JavaScript for Search and Filter -->
<script>
/**
 * Toggle advanced search visibility
 */
function toggleAdvancedSearch() {
    const advancedSearch = document.getElementById('advanced-search');
    const isHidden = advancedSearch.classList.contains('hidden');
    
    if (isHidden) {
        advancedSearch.classList.remove('hidden');
        advancedSearch.classList.add('animate-fadeIn');
    } else {
        advancedSearch.classList.add('hidden');
        advancedSearch.classList.remove('animate-fadeIn');
    }
}

/**
 * Filter surat by category
 */
function filterSurat(type) {
    const suratItems = document.querySelectorAll('.surat-item');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const noResults = document.getElementById('no-results');
    let visibleCount = 0;
    
    // Update active filter button
    filterBtns.forEach(btn => {
        btn.classList.remove('active', 'bg-green-100', 'text-green-700');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    event.target.classList.add('active', 'bg-green-100', 'text-green-700');
    event.target.classList.remove('bg-gray-100', 'text-gray-700');
    
    // Filter surat items
    suratItems.forEach(item => {
        let shouldShow = false;
        
        switch (type) {
            case 'all':
                shouldShow = true;
                break;
            case 'makkah':
                shouldShow = item.dataset.type === 'makkah';
                break;
            case 'madinah':
                shouldShow = item.dataset.type === 'madinah';
                break;
            case 'short':
                shouldShow = parseInt(item.dataset.ayatCount) < 20;
                break;
            case 'long':
                shouldShow = parseInt(item.dataset.ayatCount) > 100;
                break;
        }
        
        if (shouldShow) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    if (visibleCount === 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
}

/**
 * Search surat by name with fuzzy matching
 */
function searchSurat(query) {
    const suratItems = document.querySelectorAll('.surat-item');
    const noResults = document.getElementById('no-results');
    const queryLower = query.toLowerCase();
    let visibleCount = 0;
    
    suratItems.forEach(item => {
        const suratName = item.dataset.name;
        const suratNumber = item.dataset.surat;
        
        // Calculate fuzzy score
        const score = calculateFuzzyScore(queryLower, suratName, suratNumber);
        
        // Only show results with score above threshold
        const shouldShow = score >= 30; // Match the PHP threshold
        
        if (shouldShow) {
            item.style.display = 'block';
            // Add visual indicator for match quality
            if (score >= 80) {
                item.style.order = '1'; // High relevance
                item.classList.add('high-relevance');
                item.classList.remove('medium-relevance', 'low-relevance');
            } else if (score >= 60) {
                item.style.order = '2'; // Medium relevance
                item.classList.add('medium-relevance');
                item.classList.remove('high-relevance', 'low-relevance');
            } else {
                item.style.order = '3'; // Low relevance
                item.classList.add('low-relevance');
                item.classList.remove('high-relevance', 'medium-relevance');
            }
            visibleCount++;
        } else {
            item.style.display = 'none';
            item.classList.remove('high-relevance', 'medium-relevance', 'low-relevance');
        }
    });
    
    // Show/hide no results message
    if (visibleCount === 0 && query.length > 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
}

/**
 * Calculate fuzzy matching score (JavaScript version)
 */
function calculateFuzzyScore(query, suratName, suratNumber) {
    if (!query || query.length === 0) return 0;
    
    const cleanQuery = cleanSuratName(query);
    const cleanSurat = cleanSuratName(suratName);
    
    // Exact match
    if (cleanQuery === cleanSurat) return 100;
    
    // Number match
    if (query === suratNumber) return 95;
    
    // Substring match
    if (cleanSurat.includes(cleanQuery)) {
        const position = cleanSurat.indexOf(cleanQuery);
        return 90 - (position * 2);
    }
    
    // Starts with match
    if (cleanSurat.startsWith(cleanQuery)) {
        return 85;
    }
    
    // Word boundary match
    const words = cleanSurat.split(' ');
    for (let word of words) {
        if (word.startsWith(cleanQuery)) {
            return 75;
        }
    }
    
    // Only proceed with expensive operations if query is long enough
    if (cleanQuery.length < 3) {
        return 0;
    }
    
    // Character-based similarity (only for longer queries)
    if (cleanQuery.length >= 4) {
        const charScore = calculateCharacterScore(cleanQuery, cleanSurat);
        if (charScore > 60) {
            return charScore * 0.3;
        }
    }
    
    return 0;
}

/**
 * Clean surat name (JavaScript version)
 */
function cleanSuratName(name) {
    if (!name) return '';
    
    let cleaned = name.toLowerCase().trim();
    
    // Remove common prefixes
    const prefixes = ['al-', 'an-', 'ar-', 'as-', 'at-', 'az-', 'ash-', 'ad-'];
    for (let prefix of prefixes) {
        if (cleaned.startsWith(prefix)) {
            cleaned = cleaned.substring(prefix.length);
            break;
        }
    }
    
    // Remove special characters
    cleaned = cleaned.replace(/['`\-]/g, '');
    
    // Normalize character variations
    const replacements = {
        'aa': 'a', 'ii': 'i', 'uu': 'u',
        'kh': 'h', 'gh': 'g', 'sh': 's',
        'th': 't', 'dh': 'd', 'zh': 'z'
    };
    
    for (let [from, to] of Object.entries(replacements)) {
        cleaned = cleaned.replace(new RegExp(from, 'g'), to);
    }
    
    return cleaned.trim();
}

/**
 * Calculate character-based similarity
 */
function calculateCharacterScore(query, target) {
    if (!query || !target) return 0;
    
    const queryChars = query.split('');
    const targetChars = target.split('');
    
    let matches = 0;
    for (let char of queryChars) {
        if (targetChars.includes(char)) {
            matches++;
        }
    }
    
    return queryChars.length > 0 ? (matches / queryChars.length) * 100 : 0;
}

// Add live search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchSurat(this.value);
            }, 300);
        });
    }
    
    // Set search type from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const searchType = urlParams.get('search_type');
    if (searchType) {
        const searchTypeSelect = document.getElementById('search_type');
        if (searchTypeSelect) {
            searchTypeSelect.value = searchType;
        }
    }
});
</script>

<!-- Additional Styles -->
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

.filter-btn.active {
    transform: scale(1.05);
}

.surat-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Search relevance indicators */
.surat-item.high-relevance {
    border-left: 4px solid #10b981;
    background-color: #f0fdf4;
}

.surat-item.medium-relevance {
    border-left: 4px solid #f59e0b;
    background-color: #fffbeb;
}

.surat-item.low-relevance {
    border-left: 4px solid #6b7280;
    background-color: #f9fafb;
}

/* Arabic font for surat names */
.font-arabic {
    font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', 'Al Bayan', 'Geeza Pro', serif;
}

/* Responsive grid adjustments */
@media (max-width: 640px) {
    #surat-grid {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    #surat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>