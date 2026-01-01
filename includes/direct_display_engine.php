<?php
/**
 * Direct Display Engine for Islamic Content
 * Manages immediate content display without mode selection
 */

interface DirectDisplayInterface {
    public function getDefaultContent(): array;
    public function renderContentList(array $content, array $options = []): string;
    public function getPaginatedContent(int $page, int $limit): array;
}

class DirectDisplayEngine {
    private $api;
    private $renderer;
    
    public function __construct($api, $renderer) {
        $this->api = $api;
        $this->renderer = $renderer;
    }
    
    /**
     * Get display handler for specific content type
     */
    public function getDisplayHandler($contentType) {
        switch ($contentType) {
            case 'asmaul_husna':
                return new AsmaulHusnaDirectDisplay($this->api, $this->renderer);
            case 'doa':
                return new DoaDirectDisplay($this->api, $this->renderer);
            case 'hadits':
                return new HaditsDirectDisplay($this->api, $this->renderer);
            default:
                throw new Exception("Unsupported content type: {$contentType}");
        }
    }
}

/**
 * Asmaul Husna Direct Display Handler
 */
class AsmaulHusnaDirectDisplay implements DirectDisplayInterface {
    private $api;
    private $renderer;
    
    public function __construct($api, $renderer) {
        $this->api = $api;
        $this->renderer = $renderer;
    }
    
    public function getDefaultContent(): array {
        try {
            $allData = $this->api->getAllAsmaulHusna();
            return [
                'success' => true,
                'data' => $allData['data'] ?? [],
                'total' => 99,
                'content_type' => 'asmaul_husna'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ];
        }
    }
    
    public function renderContentList(array $content, array $options = []): string {
        $view = $options['view'] ?? 'grid';
        $showSearch = $options['show_search'] ?? true;
        $searchQuery = $options['search_query'] ?? '';
        
        $html = '';
        
        // Search bar
        if ($showSearch) {
            $html .= $this->renderSearchBar($searchQuery);
        }
        
        // Quick navigation
        $html .= $this->renderQuickNavigation();
        
        // Content grid/list
        if ($view === 'list') {
            $html .= '<div class="space-y-3" id="asmaul-husna-content">';
            foreach ($content as $asma) {
                $html .= $this->renderer->renderAsmaulHusna(['data' => $asma], ['layout' => 'list', 'show_copy' => false]);
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="asmaul-husna-content">';
            foreach ($content as $asma) {
                $html .= '<div class="transform hover:scale-105 transition duration-200">';
                $html .= $this->renderer->renderAsmaulHusna(['data' => $asma], ['layout' => 'card', 'show_copy' => false]);
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        return $html;
    }
    
    public function getPaginatedContent(int $page, int $limit): array {
        $allContent = $this->getDefaultContent();
        if (!$allContent['success']) {
            return $allContent;
        }
        
        $data = $allContent['data'];
        $total = count($data);
        $offset = ($page - 1) * $limit;
        
        return [
            'success' => true,
            'data' => array_slice($data, $offset, $limit),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    private function renderSearchBar($searchQuery = '') {
        return '
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-search text-amber-600 mr-2"></i>
                    Pencarian Asmaul Husna
                </h2>
                <div class="flex items-center gap-2">
                    <button onclick="toggleView(\'grid\')" id="grid-view-btn" 
                            class="px-3 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-th-large mr-1"></i>Grid
                    </button>
                    <button onclick="toggleView(\'list\')" id="list-view-btn"
                            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-list mr-1"></i>List
                    </button>
                </div>
            </div>
            
            <div class="flex gap-2 mb-4">
                <div class="flex-1">
                    <input type="text" 
                           id="asmaul-husna-search" 
                           value="' . htmlspecialchars($searchQuery) . '"
                           placeholder="Cari berdasarkan nama Arab, transliterasi, atau arti..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <button onclick="clearSearch()" 
                        class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Advanced filters -->
            <div class="flex flex-wrap gap-2">
                <select id="number-range-filter" onchange="applyFilters()" 
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Nomor</option>
                    <option value="1-25">1-25</option>
                    <option value="26-50">26-50</option>
                    <option value="51-75">51-75</option>
                    <option value="76-99">76-99</option>
                </select>
            </div>
        </div>';
    }
    
    private function renderQuickNavigation() {
        $html = '<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">';
        $html .= '<h3 class="text-lg font-semibold text-gray-900 mb-4">Navigasi Cepat</h3>';
        $html .= '<div class="grid grid-cols-5 md:grid-cols-10 gap-2">';
        
        for ($i = 1; $i <= 99; $i += 10) {
            $end = min($i + 9, 99);
            $html .= '<button onclick="scrollToNumber(' . $i . ')" 
                             class="p-2 text-center bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 hover:border-amber-300 transition duration-200 text-sm">
                        ' . $i . '-' . $end . '
                      </button>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
}

/**
 * Doa Direct Display Handler
 */
class DoaDirectDisplay implements DirectDisplayInterface {
    private $api;
    private $renderer;
    
    public function __construct($api, $renderer) {
        $this->api = $api;
        $this->renderer = $renderer;
    }
    
    public function getDefaultContent(): array {
        try {
            $allDoa = [];
            
            // Get all 108 doa
            for ($i = 1; $i <= 108; $i++) {
                try {
                    $doaData = $this->api->getDoa($i);
                    if (isset($doaData['data'])) {
                        $doaData['data']['id'] = $i;
                        $doaData['data']['category'] = $this->getDoaCategory($i);
                        $allDoa[] = $doaData['data'];
                    }
                } catch (Exception $e) {
                    // Skip individual errors
                    continue;
                }
            }
            
            return [
                'success' => true,
                'data' => $allDoa,
                'total' => count($allDoa),
                'content_type' => 'doa'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ];
        }
    }
    
    public function renderContentList(array $content, array $options = []): string {
        $showSearch = $options['show_search'] ?? true;
        $searchQuery = $options['search_query'] ?? '';
        $categoryFilter = $options['category_filter'] ?? '';
        
        $html = '';
        
        // Search bar
        if ($showSearch) {
            $html .= $this->renderSearchBar($searchQuery, $categoryFilter);
        }
        
        // Category statistics
        $html .= $this->renderCategoryStats($content);
        
        // Content list grouped by category
        $html .= $this->renderContentByCategory($content);
        
        return $html;
    }
    
    public function getPaginatedContent(int $page, int $limit): array {
        $allContent = $this->getDefaultContent();
        if (!$allContent['success']) {
            return $allContent;
        }
        
        $data = $allContent['data'];
        $total = count($data);
        $offset = ($page - 1) * $limit;
        
        return [
            'success' => true,
            'data' => array_slice($data, $offset, $limit),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    private function renderSearchBar($searchQuery = '', $categoryFilter = '') {
        return '
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-search text-purple-600 mr-2"></i>
                    Pencarian Doa
                </h2>
            </div>
            
            <div class="flex gap-2 mb-4">
                <div class="flex-1">
                    <input type="text" 
                           id="doa-search" 
                           value="' . htmlspecialchars($searchQuery) . '"
                           placeholder="Cari berdasarkan judul, isi doa, atau situasi..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <button onclick="clearSearch()" 
                        class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Category filters -->
            <div class="flex flex-wrap gap-2">
                <button onclick="filterByCategory(\'all\')" 
                        class="category-filter-btn active px-3 py-2 bg-purple-100 text-purple-700 rounded-full text-sm transition duration-200">
                    Semua (108)
                </button>
                <button onclick="filterByCategory(\'harian\')" 
                        class="category-filter-btn px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-sm transition duration-200">
                    Harian (1-30)
                </button>
                <button onclick="filterByCategory(\'ibadah\')" 
                        class="category-filter-btn px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-sm transition duration-200">
                    Ibadah (31-60)
                </button>
                <button onclick="filterByCategory(\'perlindungan\')" 
                        class="category-filter-btn px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-sm transition duration-200">
                    Perlindungan (61-90)
                </button>
                <button onclick="filterByCategory(\'khusus\')" 
                        class="category-filter-btn px-3 py-2 bg-gray-100 text-gray-700 rounded-full text-sm transition duration-200">
                    Khusus (91-108)
                </button>
            </div>
        </div>';
    }
    
    private function renderCategoryStats($content) {
        $categories = [
            'harian' => 0,
            'ibadah' => 0,
            'perlindungan' => 0,
            'khusus' => 0
        ];
        
        foreach ($content as $doa) {
            if (isset($doa['category']) && isset($categories[$doa['category']])) {
                $categories[$doa['category']]++;
            }
        }
        
        return '
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-yellow-600">' . $categories['harian'] . '</div>
                    <div class="text-sm text-gray-600">Doa Harian</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">' . $categories['ibadah'] . '</div>
                    <div class="text-sm text-gray-600">Doa Ibadah</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-blue-600">' . $categories['perlindungan'] . '</div>
                    <div class="text-sm text-gray-600">Doa Perlindungan</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">' . $categories['khusus'] . '</div>
                    <div class="text-sm text-gray-600">Doa Khusus</div>
                </div>
            </div>
        </div>';
    }
    
    private function renderContentByCategory($content) {
        $categorizedContent = [
            'harian' => [],
            'ibadah' => [],
            'perlindungan' => [],
            'khusus' => []
        ];
        
        foreach ($content as $doa) {
            if (isset($doa['category']) && isset($categorizedContent[$doa['category']])) {
                $categorizedContent[$doa['category']][] = $doa;
            }
        }
        
        $categoryNames = [
            'harian' => 'Doa Harian',
            'ibadah' => 'Doa Ibadah',
            'perlindungan' => 'Doa Perlindungan',
            'khusus' => 'Doa Khusus'
        ];
        
        $html = '<div id="doa-content">';
        
        foreach ($categorizedContent as $category => $doas) {
            if (empty($doas)) continue;
            
            $html .= '<div class="category-section mb-8" data-category="' . $category . '">';
            $html .= '<h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">';
            $html .= '<i class="fas fa-folder text-purple-600 mr-2"></i>';
            $html .= $categoryNames[$category] . ' (' . count($doas) . ')';
            $html .= '</h3>';
            
            $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            foreach ($doas as $doa) {
                $html .= '<div class="doa-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition duration-200" data-id="' . ($doa['id'] ?? '') . '">';
                $html .= '<div class="flex items-start justify-between mb-2">';
                $html .= '<h4 class="font-semibold text-gray-900">' . htmlspecialchars($doa['judul'] ?? 'Doa ' . ($doa['id'] ?? '')) . '</h4>';
                $html .= '<span class="text-sm text-purple-600 font-medium">#' . ($doa['id'] ?? '') . '</span>';
                $html .= '</div>';
                
                if (isset($doa['arab'])) {
                    $html .= '<div class="text-right mb-2 text-lg font-arabic text-gray-800">' . htmlspecialchars($doa['arab']) . '</div>';
                }
                
                if (isset($doa['arti'])) {
                    $html .= '<div class="text-sm text-gray-600 mb-2">' . htmlspecialchars(substr($doa['arti'], 0, 100)) . '...</div>';
                }
                
                $html .= '<div class="flex items-center justify-between mt-3">';
                $html .= '<span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">' . ucfirst($category) . '</span>';
                $html .= '<button onclick="viewDoaDetail(' . ($doa['id'] ?? '') . ')" class="text-purple-600 hover:text-purple-700 text-sm font-medium">';
                $html .= '<i class="fas fa-eye mr-1"></i>Lihat Detail';
                $html .= '</button>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function getDoaCategory($id) {
        if ($id >= 1 && $id <= 30) return 'harian';
        if ($id >= 31 && $id <= 60) return 'ibadah';
        if ($id >= 61 && $id <= 90) return 'perlindungan';
        if ($id >= 91 && $id <= 108) return 'khusus';
        return 'lainnya';
    }
}

/**
 * Hadits Direct Display Handler
 */
class HaditsDirectDisplay implements DirectDisplayInterface {
    private $api;
    private $renderer;
    
    public function __construct($api, $renderer) {
        $this->api = $api;
        $this->renderer = $renderer;
    }
    
    public function getDefaultContent(): array {
        // For hadits, we show collections list as default content
        $collections = [
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
        
        return [
            'success' => true,
            'data' => $collections,
            'total' => count($collections),
            'content_type' => 'hadits_collections'
        ];
    }
    
    public function renderContentList(array $content, array $options = []): string {
        $showSearch = $options['show_search'] ?? true;
        $searchQuery = $options['search_query'] ?? '';
        
        $html = '';
        
        // Search bar
        if ($showSearch) {
            $html .= $this->renderSearchBar($searchQuery);
        }
        
        // Collections list
        $html .= $this->renderCollectionsList($content);
        
        // Statistics
        $html .= $this->renderStatistics();
        
        return $html;
    }
    
    public function getPaginatedContent(int $page, int $limit): array {
        // For collections, pagination is not needed
        return $this->getDefaultContent();
    }
    
    private function renderSearchBar($searchQuery = '') {
        return '
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-search text-green-600 mr-2"></i>
                    Pencarian Hadits
                </h2>
            </div>
            
            <div class="flex gap-2 mb-4">
                <div class="flex-1">
                    <input type="text" 
                           id="hadits-search" 
                           value="' . htmlspecialchars($searchQuery) . '"
                           placeholder="Cari berdasarkan teks hadits, perawi, atau tema..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <button onclick="performHaditsSearch()" 
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
            
            <!-- Collection filters -->
            <div class="flex flex-wrap gap-2">
                <select id="collection-filter" onchange="applyCollectionFilter()" 
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Koleksi</option>
                    <option value="arbain">Hadits Arbain</option>
                    <option value="bulughul_maram">Bulughul Maram</option>
                    <option value="perawi">Hadits Perawi</option>
                </select>
                
                <select id="perawi-filter" onchange="applyPerawiFilter()" 
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Perawi</option>
                    <option value="bukhari">Bukhari</option>
                    <option value="muslim">Muslim</option>
                    <option value="ahmad">Ahmad</option>
                    <option value="tirmidzi">Tirmidzi</option>
                    <option value="abudaud">Abu Daud</option>
                    <option value="nasai">Nasai</option>
                    <option value="ibnumajah">Ibnu Majah</option>
                </select>
            </div>
        </div>';
    }
    
    private function renderCollectionsList($collections) {
        $html = '<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">';
        $html .= '<div class="px-6 py-4 border-b border-gray-200 bg-gray-50">';
        $html .= '<h2 class="text-lg font-semibold text-gray-900">';
        $html .= '<i class="fas fa-list text-green-600 mr-2"></i>';
        $html .= 'Daftar Koleksi Hadits';
        $html .= '</h2>';
        $html .= '<p class="text-sm text-gray-600 mt-1">Klik pada koleksi untuk membaca hadits dari sumber tersebut</p>';
        $html .= '</div>';
        
        $html .= '<div class="divide-y divide-gray-100">';
        
        foreach ($collections as $key => $collection) {
            if ($key === 'perawi') {
                // Special handling for perawi collections
                $html .= '<div class="p-4 hover:bg-green-50 transition duration-200">';
                $html .= '<div class="flex items-center justify-between mb-3">';
                $html .= '<div class="flex items-center">';
                $html .= '<div class="bg-' . $collection['color'] . '-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3">';
                $html .= '<i class="' . $collection['icon'] . '"></i>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<h3 class="font-semibold text-gray-900">' . htmlspecialchars($collection['name']) . '</h3>';
                $html .= '<p class="text-sm text-gray-600">' . htmlspecialchars($collection['description']) . '</p>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                
                // Sub-collections
                $html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 ml-13">';
                foreach ($collection['sub_collections'] as $slug => $name) {
                    $html .= '<a href="?collection=perawi&slug=' . $slug . '" ';
                    $html .= 'class="block p-3 bg-gray-50 hover:bg-purple-100 rounded-lg border border-gray-200 hover:border-purple-300 transition duration-200">';
                    $html .= '<div class="flex items-center justify-between">';
                    $html .= '<span class="text-sm font-medium text-gray-900">' . htmlspecialchars($name) . '</span>';
                    $html .= '<i class="fas fa-chevron-right text-gray-400 text-xs"></i>';
                    $html .= '</div>';
                    $html .= '</a>';
                }
                $html .= '</div>';
                $html .= '</div>';
            } else {
                // Regular collections
                $html .= '<a href="?collection=' . $key . '" ';
                $html .= 'class="block p-4 hover:bg-green-50 transition duration-200 group">';
                $html .= '<div class="flex items-center justify-between">';
                $html .= '<div class="flex items-center">';
                $html .= '<div class="bg-' . $collection['color'] . '-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm mr-3 group-hover:bg-' . $collection['color'] . '-700 transition duration-200">';
                $html .= '<i class="' . $collection['icon'] . '"></i>';
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<h3 class="font-semibold text-gray-900 group-hover:text-green-600 transition duration-200">';
                $html .= htmlspecialchars($collection['name']);
                $html .= '</h3>';
                $html .= '<p class="text-sm text-gray-600">';
                $html .= htmlspecialchars($collection['description']) . ' • ' . $collection['total'] . ' hadits';
                $html .= '</p>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="text-gray-400 group-hover:text-green-600 transition duration-200">';
                $html .= '<i class="fas fa-chevron-right"></i>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</a>';
            }
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderStatistics() {
        return '
        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
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
                    <div class="text-sm text-gray-600">Hadits Tersedia</div>
                </div>
            </div>
        </div>';
    }
}