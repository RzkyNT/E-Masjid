<?php
/**
 * Al-Quran Search Results Component
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays search results for Al-Quran content
 * 
 * Expected variables:
 * - $search_results: Array containing search results
 * - $search_query: Search query string
 * - $search_type: Search type (all, surat, ayat, etc.)
 */

// Ensure required variables are set
$search_results = $search_results ?? null;
$search_query = $search_query ?? '';
$search_type = $search_type ?? 'all';
?>

<!-- Search Results Header -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                <i class="fas fa-search text-green-600 mr-2"></i>
                Hasil Pencarian
            </h2>
            <p class="text-gray-600">
                Menampilkan hasil untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
                <?php if ($search_type !== 'all'): ?>
                    dalam <strong><?php echo ucfirst($search_type); ?></strong>
                <?php endif; ?>
            </p>
            <?php if (isset($search_results['total'])): ?>
                <p class="text-sm text-gray-500 mt-1">
                    Ditemukan <?php echo $search_results['total']; ?> hasil
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Search Again Button -->
        <div class="flex gap-2">
            <a href="alquran.php" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <button onclick="showSearchForm()" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition duration-200">
                <i class="fas fa-search mr-2"></i>Cari Lagi
            </button>
        </div>
    </div>
</div>

<!-- Search Form (Hidden by default) -->
<div id="search-form" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" action="alquran.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="mode" value="search">
        
        <div>
            <label for="search_new" class="block text-sm font-medium text-gray-700 mb-2">Kata Kunci Baru</label>
            <input type="text" 
                   name="search" 
                   id="search_new"
                   value="<?php echo htmlspecialchars($search_query); ?>"
                   placeholder="Masukkan kata kunci..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                   required>
        </div>
        
        <div>
            <label for="search_type_new" class="block text-sm font-medium text-gray-700 mb-2">Cari dalam</label>
            <select name="search_type" id="search_type_new" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="all" <?php echo $search_type === 'all' ? 'selected' : ''; ?>>Semua</option>
                <option value="surat" <?php echo $search_type === 'surat' ? 'selected' : ''; ?>>Nama Surat</option>
                <option value="transliterasi" <?php echo $search_type === 'transliterasi' ? 'selected' : ''; ?>>Transliterasi</option>
                <option value="terjemahan" <?php echo $search_type === 'terjemahan' ? 'selected' : ''; ?>>Terjemahan</option>
                <option value="ayat" <?php echo $search_type === 'ayat' ? 'selected' : ''; ?>>Teks Arab</option>
            </select>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <button type="button" onclick="hideSearchForm()" 
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-md transition duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </form>
</div>

<!-- Search Results -->
<?php if ($search_results && isset($search_results['data']) && !empty($search_results['data'])): ?>
    <div class="space-y-4">
        <?php foreach ($search_results['data'] as $index => $result): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-200">
                <?php if ($result['type'] === 'surat'): ?>
                    <!-- Surat Result -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center text-sm font-bold mr-4">
                                <?php echo $result['surat_number']; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    <?php echo htmlspecialchars($result['surat_name']); ?>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    <?php echo $result['ayat_count']; ?> ayat • Cocok dengan nama surat
                                    <?php if (isset($result['relevance'])): ?>
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full <?php 
                                            echo $result['relevance'] === 'high' ? 'bg-green-100 text-green-700' : 
                                                ($result['relevance'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'); 
                                        ?>">
                                            <?php echo ucfirst($result['relevance']); ?> relevance
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <a href="<?php echo htmlspecialchars($result['url']); ?>" 
                           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition duration-200">
                            <i class="fas fa-book-open mr-2"></i>Baca Surat
                        </a>
                    </div>
                    
                <?php elseif ($result['type'] === 'ayat'): ?>
                    <!-- Ayat Result -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold mr-3">
                                    <?php echo $result['ayat_number']; ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($result['surat_name']); ?> - Ayat <?php echo $result['ayat_number']; ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Cocok dalam: <?php echo ucfirst($result['match_type']); ?>
                                    </p>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($result['url']); ?>" 
                               class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition duration-200">
                                <i class="fas fa-external-link-alt mr-1"></i>Lihat
                            </a>
                        </div>
                        
                        <!-- Arabic Text -->
                        <?php if (!empty($result['arab'])): ?>
                            <div class="arabic-text text-right leading-loose font-arabic text-xl text-gray-800" dir="rtl" lang="ar">
                                <?php echo htmlspecialchars($result['arab']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Transliteration -->
                        <?php if (!empty($result['latin'])): ?>
                            <div class="text-gray-700 italic">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-medium">Transliterasi:</span>
                                <div class="mt-1"><?php echo highlightSearchTerm(htmlspecialchars($result['latin']), $search_query, $result['match_type'] === 'transliterasi'); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Translation -->
                        <?php if (!empty($result['text'])): ?>
                            <div class="text-gray-800">
                                <span class="text-xs uppercase tracking-wide text-gray-500 font-medium">Terjemahan:</span>
                                <div class="mt-1"><?php echo highlightSearchTerm(htmlspecialchars($result['text']), $search_query, $result['match_type'] === 'terjemahan'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Load More Button (if needed) -->
    <?php if (isset($search_results['total']) && $search_results['total'] > count($search_results['data'])): ?>
        <div class="text-center mt-8">
            <button onclick="loadMoreResults()" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition duration-200">
                <i class="fas fa-plus mr-2"></i>Muat Lebih Banyak
            </button>
            <p class="text-sm text-gray-500 mt-2">
                Menampilkan <?php echo count($search_results['data']); ?> dari <?php echo $search_results['total']; ?> hasil
            </p>
        </div>
    <?php endif; ?>
    
<?php elseif ($search_results && isset($search_results['data'])): ?>
    <!-- No Results Found -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-search text-gray-400 text-5xl mb-6"></i>
        <h3 class="text-xl font-medium text-gray-900 mb-3">Tidak ada hasil ditemukan</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Tidak ditemukan hasil untuk "<strong><?php echo htmlspecialchars($search_query); ?></strong>". 
            Coba gunakan kata kunci yang berbeda atau ubah jenis pencarian.
        </p>
        
        <!-- Suggestions -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-lg mx-auto">
            <h4 class="font-medium text-gray-900 mb-3">Saran Pencarian:</h4>
            <ul class="text-sm text-gray-600 space-y-1 text-left">
                <li>• Gunakan kata kunci yang lebih umum</li>
                <li>• Periksa ejaan kata kunci</li>
                <li>• Coba cari dalam kategori yang berbeda</li>
                <li>• Gunakan sinonim atau kata yang berkaitan</li>
            </ul>
        </div>
        
        <div class="flex flex-wrap justify-center gap-2">
            <button onclick="showSearchForm()" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition duration-200">
                <i class="fas fa-search mr-2"></i>Cari Lagi
            </button>
            <a href="alquran.php" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition duration-200">
                <i class="fas fa-list mr-2"></i>Lihat Semua Surat
            </a>
        </div>
    </div>
    
<?php else: ?>
    <!-- Error State -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
            <div>
                <h3 class="text-red-800 font-medium">Terjadi Kesalahan</h3>
                <p class="text-red-700 mt-1">Tidak dapat melakukan pencarian. Silakan coba lagi nanti.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * Helper function to highlight search terms
 */
function highlightSearchTerm($text, $searchTerm, $shouldHighlight = true) {
    if (!$shouldHighlight || empty($searchTerm)) {
        return $text;
    }
    
    $highlighted = preg_replace(
        '/(' . preg_quote($searchTerm, '/') . ')/i',
        '<mark class="bg-yellow-200 px-1 rounded">$1</mark>',
        $text
    );
    
    return $highlighted;
}
?>

<!-- JavaScript for Search Results -->
<script>
/**
 * Show search form
 */
function showSearchForm() {
    const searchForm = document.getElementById('search-form');
    searchForm.classList.remove('hidden');
    searchForm.classList.add('animate-fadeIn');
    
    // Focus on search input
    const searchInput = document.getElementById('search_new');
    if (searchInput) {
        searchInput.focus();
        searchInput.select();
    }
}

/**
 * Hide search form
 */
function hideSearchForm() {
    const searchForm = document.getElementById('search-form');
    searchForm.classList.add('hidden');
    searchForm.classList.remove('animate-fadeIn');
}

/**
 * Load more results (placeholder function)
 */
function loadMoreResults() {
    // This would typically make an AJAX request to load more results
    alert('Fitur "Muat Lebih Banyak" akan segera tersedia');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus search input if no results
    const noResults = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-12.text-center');
    if (noResults) {
        showSearchForm();
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

/* Arabic font */
.font-arabic {
    font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', 'Al Bayan', 'Geeza Pro', serif;
    font-feature-settings: 'liga' 1, 'dlig' 1, 'calt' 1;
    text-rendering: optimizeLegibility;
}

/* Highlight styles */
mark {
    background-color: #fef3c7;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-weight: 600;
}

/* Hover effects */
.bg-white:hover {
    transform: translateY(-1px);
}
</style>