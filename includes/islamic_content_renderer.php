<?php
/**
 * Islamic Content Renderer
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides rendering functions for Islamic content (Hadits, Doa, Asmaul Husna)
 * with proper Arabic text formatting and consistent UI design
 * 
 * Requirements: 6.1, 6.2, 6.3, 6.4
 */

class IslamicContentRenderer {
    
    /**
     * Render Hadits content
     * Requirements: 1.6, 6.1, 6.4
     */
    public function renderHadits(array $hadits, array $options = []): string {
        if (!isset($hadits['data'])) {
            return $this->renderError('Data hadits tidak valid');
        }
        
        $data = $hadits['data'];
        $showCopyButton = $options['show_copy'] ?? true;
        $showShareButton = $options['show_share'] ?? true;
        $showSource = $options['show_source'] ?? true;
        
        $html = '<div class="hadits-container bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">';
        
        // Header with hadits number and source
        if (isset($data['nomor']) || isset($hadits['request']['id'])) {
            $nomor = $data['nomor'] ?? $hadits['request']['id'];
            $source = $this->getHaditsSource($hadits['request']['path'] ?? '');
            
            $html .= '<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">';
            $html .= '<div class="flex items-center gap-3">';
            $html .= '<div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold shadow-sm">' . htmlspecialchars($nomor) . '</div>';
            
            if ($showSource && $source) {
                $html .= '<div class="text-sm text-gray-600">';
                $html .= '<div class="font-medium text-gray-800">' . htmlspecialchars($source['name']) . '</div>';
                $html .= '<div class="text-xs text-gray-500">' . htmlspecialchars($source['description']) . '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            
            // Action buttons
            if ($showCopyButton || $showShareButton) {
                $html .= '<div class="flex items-center gap-2">';
                
                if ($showCopyButton) {
                    $html .= '<button onclick="copyIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Salin hadits lengkap" aria-label="Salin hadits">';
                    $html .= '<i class="fas fa-copy mr-1"></i>Salin</button>';
                }
                
                if ($showShareButton) {
                    $html .= '<button onclick="shareIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Bagikan hadits" aria-label="Bagikan hadits">';
                    $html .= '<i class="fas fa-share-alt mr-1"></i>Bagikan</button>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        // Title if available
        if (isset($data['judul']) && !empty($data['judul'])) {
            $html .= '<h3 class="text-lg font-semibold text-gray-900 mb-4">' . htmlspecialchars($data['judul']) . '</h3>';
        }
        
        // Arabic text
        if (isset($data['arab']) && !empty($data['arab'])) {
            $html .= $this->renderArabicText($data['arab'], 'hadits');
        }
        
        // Indonesian translation
        if (isset($data['indo']) && !empty($data['indo'])) {
            $html .= $this->renderTranslation($data['indo'], 'Terjemahan');
        }
        
        // Narrator information
        if (isset($data['perawi']) && !empty($data['perawi'])) {
            $html .= '<div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">';
            $html .= '<span class="text-xs uppercase tracking-wide text-blue-700 font-medium block mb-1">Perawi:</span>';
            $html .= '<div class="text-blue-800 text-sm">' . htmlspecialchars($data['perawi']) . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Doa content
     * Requirements: 2.3, 6.1, 6.4
     */
    public function renderDoa(array $doa, array $options = []): string {
        if (!isset($doa['data'])) {
            return $this->renderError('Data doa tidak valid');
        }
        
        $data = $doa['data'];
        $showCopyButton = $options['show_copy'] ?? true;
        $showShareButton = $options['show_share'] ?? true;
        $showSource = $options['show_source'] ?? true;
        
        $html = '<div class="doa-container bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">';
        
        // Header with doa ID and source
        if (isset($doa['request']['id'])) {
            $id = $doa['request']['id'];
            
            $html .= '<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">';
            $html .= '<div class="flex items-center gap-3">';
            $html .= '<div class="bg-purple-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold shadow-sm">' . htmlspecialchars($id) . '</div>';
            
            if ($showSource && isset($data['source'])) {
                $html .= '<div class="text-sm text-gray-600">';
                $html .= '<div class="font-medium text-gray-800">Doa ' . ucfirst(htmlspecialchars($data['source'])) . '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            
            // Action buttons
            if ($showCopyButton || $showShareButton) {
                $html .= '<div class="flex items-center gap-2">';
                
                if ($showCopyButton) {
                    $html .= '<button onclick="copyIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Salin doa lengkap" aria-label="Salin doa">';
                    $html .= '<i class="fas fa-copy mr-1"></i>Salin</button>';
                }
                
                if ($showShareButton) {
                    $html .= '<button onclick="shareIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Bagikan doa" aria-label="Bagikan doa">';
                    $html .= '<i class="fas fa-share-alt mr-1"></i>Bagikan</button>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        // Title
        if (isset($data['judul']) && !empty($data['judul'])) {
            $html .= '<h3 class="text-lg font-semibold text-gray-900 mb-4">' . htmlspecialchars($data['judul']) . '</h3>';
        }
        
        // Arabic text
        if (isset($data['arab']) && !empty($data['arab'])) {
            $html .= $this->renderArabicText($data['arab'], 'doa');
        }
        
        // Indonesian translation
        if (isset($data['indo']) && !empty($data['indo'])) {
            $html .= $this->renderTranslation($data['indo'], 'Artinya');
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Asmaul Husna content
     * Requirements: 3.3, 6.1, 6.4
     */
    public function renderAsmaulHusna(array $asma, array $options = []): string {
        if (!isset($asma['data'])) {
            return $this->renderError('Data Asmaul Husna tidak valid');
        }
        
        $data = $asma['data'];
        $showCopyButton = $options['show_copy'] ?? true;
        $showShareButton = $options['show_share'] ?? true;
        $layout = $options['layout'] ?? 'card'; // card or list
        
        if ($layout === 'list') {
            return $this->renderAsmaulHusnaList($data, $options);
        }
        
        $html = '<div class="asma-container bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">';
        
        // Header with number
        if (isset($data['id'])) {
            $html .= '<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">';
            $html .= '<div class="flex items-center gap-3">';
            $html .= '<div class="bg-amber-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-sm font-bold shadow-sm">' . htmlspecialchars($data['id']) . '</div>';
            $html .= '<div class="text-sm text-gray-600">';
            $html .= '<div class="font-medium text-gray-800">Asmaul Husna</div>';
            $html .= '<div class="text-xs text-gray-500">Nama ke-' . htmlspecialchars($data['id']) . ' dari 99</div>';
            $html .= '</div>';
            $html .= '</div>';
            
            // Action buttons
            if ($showCopyButton || $showShareButton) {
                $html .= '<div class="flex items-center gap-2">';
                
                if ($showCopyButton) {
                    $html .= '<button onclick="copyIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Salin nama lengkap" aria-label="Salin nama">';
                    $html .= '<i class="fas fa-copy mr-1"></i>Salin</button>';
                }
                
                if ($showShareButton) {
                    $html .= '<button onclick="shareIslamicContent(this)" ';
                    $html .= 'class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg text-sm transition duration-200 font-medium" ';
                    $html .= 'title="Bagikan nama" aria-label="Bagikan nama">';
                    $html .= '<i class="fas fa-share-alt mr-1"></i>Bagikan</button>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        // Arabic text
        if (isset($data['arab']) && !empty($data['arab'])) {
            $html .= $this->renderArabicText($data['arab'], 'asma');
        }
        
        // Latin transliteration
        if (isset($data['latin']) && !empty($data['latin'])) {
            $html .= '<div class="latin-text text-gray-700 italic mb-4 text-lg leading-relaxed" dir="ltr" lang="id">';
            $html .= '<span class="text-xs uppercase tracking-wide text-gray-500 font-normal block mb-1">Transliterasi:</span>';
            $html .= htmlspecialchars($data['latin']);
            $html .= '</div>';
        }
        
        // Indonesian meaning
        if (isset($data['indo']) && !empty($data['indo'])) {
            $html .= $this->renderTranslation($data['indo'], 'Artinya');
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Asmaul Husna in list format
     */
    private function renderAsmaulHusnaList(array $data, array $options = []): string {
        $html = '<div class="asma-list-item flex items-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 mb-3 hover:bg-gray-50 transition duration-200">';
        
        // Number
        if (isset($data['id'])) {
            $html .= '<div class="bg-amber-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold shadow-sm mr-4">';
            $html .= htmlspecialchars($data['id']);
            $html .= '</div>';
        }
        
        // Content
        $html .= '<div class="flex-1">';
        
        // Arabic text
        if (isset($data['arab'])) {
            $html .= '<div class="arabic-text text-right text-xl font-arabic mb-1" dir="rtl" lang="ar">';
            $html .= htmlspecialchars($data['arab']);
            $html .= '</div>';
        }
        
        // Latin and Indonesian
        $html .= '<div class="flex flex-col sm:flex-row sm:items-center gap-2">';
        
        if (isset($data['latin'])) {
            $html .= '<div class="text-gray-700 italic text-sm">' . htmlspecialchars($data['latin']) . '</div>';
        }
        
        if (isset($data['indo'])) {
            $html .= '<div class="text-gray-600 text-sm">' . htmlspecialchars($data['indo']) . '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Action buttons (compact)
        if ($options['show_copy'] ?? true) {
            $html .= '<button onclick="copyIslamicContent(this)" ';
            $html .= 'class="p-2 text-gray-400 hover:text-gray-600 transition duration-200" ';
            $html .= 'title="Salin nama" aria-label="Salin nama">';
            $html .= '<i class="fas fa-copy"></i></button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Arabic text with proper formatting
     * Requirements: 6.1
     */
    public function renderArabicText(string $text, string $type = 'general'): string {
        $fontSize = $this->getArabicFontSize($type);
        $lineHeight = $this->getArabicLineHeight($type);
        
        $html = '<div class="arabic-text text-right leading-loose mb-6 font-arabic transition-all duration-200" ';
        $html .= 'dir="rtl" lang="ar" ';
        $html .= 'style="font-size: ' . $fontSize . '; line-height: ' . $lineHeight . ';">';
        $html .= htmlspecialchars($text);
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render translation text
     * Requirements: 6.1
     */
    public function renderTranslation(string $text, string $label = 'Terjemahan'): string {
        $html = '<div class="translation-text text-gray-800 leading-relaxed mb-4">';
        $html .= '<span class="text-xs uppercase tracking-wide text-gray-500 font-medium block mb-2">' . htmlspecialchars($label) . ':</span>';
        $html .= '<div class="text-base">' . htmlspecialchars($text) . '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render source information
     * Requirements: 6.4
     */
    public function renderSource(array $source): string {
        if (empty($source)) {
            return '';
        }
        
        $html = '<div class="source-info mt-4 p-3 bg-gray-50 border-l-4 border-gray-400 rounded-r-lg">';
        $html .= '<span class="text-xs uppercase tracking-wide text-gray-600 font-medium block mb-1">Sumber:</span>';
        
        if (isset($source['name'])) {
            $html .= '<div class="text-gray-700 text-sm font-medium">' . htmlspecialchars($source['name']) . '</div>';
        }
        
        if (isset($source['description'])) {
            $html .= '<div class="text-gray-600 text-xs mt-1">' . htmlspecialchars($source['description']) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render error message
     */
    public function renderError(string $message): string {
        $html = '<div class="error-container bg-red-50 border border-red-200 rounded-lg p-4 mb-6">';
        $html .= '<div class="flex items-center">';
        $html .= '<i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>';
        $html .= '<div class="text-red-800">' . htmlspecialchars($message) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get Arabic font size based on content type
     */
    private function getArabicFontSize(string $type): string {
        $sizes = [
            'hadits' => '2rem',
            'doa' => '1.8rem',
            'asma' => '2.5rem',
            'general' => '2rem'
        ];
        
        return $sizes[$type] ?? $sizes['general'];
    }
    
    /**
     * Get Arabic line height based on content type
     */
    private function getArabicLineHeight(string $type): string {
        $heights = [
            'hadits' => '2.5',
            'doa' => '2.3',
            'asma' => '2.8',
            'general' => '2.5'
        ];
        
        return $heights[$type] ?? $heights['general'];
    }
    
    /**
     * Get hadits source information from API path
     */
    private function getHaditsSource(string $path): ?array {
        $sources = [
            '/hadits/arbain/' => [
                'name' => 'Hadits Arbain',
                'description' => '42 Hadits Pilihan'
            ],
            '/hadits/bm/' => [
                'name' => 'Bulughul Maram',
                'description' => 'Karya Ibnu Hajar Al-Asqalani'
            ],
            '/hadits/bukhari/' => [
                'name' => 'Shahih Bukhari',
                'description' => 'Imam Bukhari'
            ],
            '/hadits/ahmad/' => [
                'name' => 'Musnad Ahmad',
                'description' => 'Imam Ahmad bin Hanbal'
            ]
        ];
        
        foreach ($sources as $pattern => $info) {
            if (strpos($path, $pattern) !== false) {
                return $info;
            }
        }
        
        return null;
    }
}
?>