<?php
/**
 * Islamic Content Sharing System
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides comprehensive sharing functionality for all Islamic content
 * with specific URLs and formatted content for social media
 */

class IslamicSharingSystem {
    private $baseUrl;
    private $siteName;
    
    public function __construct($baseUrl = null, $siteName = 'Masjid Al-Muhajirin') {
        $this->baseUrl = $baseUrl ?: $this->getCurrentBaseUrl();
        $this->siteName = $siteName;
    }
    
    /**
     * Generate sharing data for Al-Quran Surah
     */
    public function generateSurahSharing($surahNumber, $surahData) {
        $url = $this->baseUrl . "/alquran.php?surah=" . $surahNumber;
        $title = "Surah " . ($surahData['nama_latin'] ?? $surahNumber) . " - " . ($surahData['nama'] ?? '');
        $description = "Baca Surah " . ($surahData['nama_latin'] ?? $surahNumber) . 
                      " (" . ($surahData['jumlah_ayat'] ?? '') . " ayat) - " . 
                      ($surahData['arti'] ?? '') . " dari Al-Quran";
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#AlQuran #Surah #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->formatWhatsAppText($title, $description, $url),
            'telegram_text' => $this->formatTelegramText($title, $description, $url),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #AlQuran #Islam'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description)
        ];
    }
    
    /**
     * Generate sharing data for specific Ayah
     */
    public function generateAyahSharing($surahNumber, $ayahNumber, $ayahData, $surahData = null) {
        $url = $this->baseUrl . "/alquran.php?surah=" . $surahNumber . "&ayat=" . $ayahNumber;
        $surahName = $surahData['nama_latin'] ?? "Surah $surahNumber";
        $title = "QS. $surahName:$ayahNumber";
        
        $arabText = isset($ayahData['teks']) ? substr($ayahData['teks'], 0, 100) . '...' : '';
        $translation = isset($ayahData['terjemahan']) ? substr($ayahData['terjemahan'], 0, 150) . '...' : '';
        
        $description = "\"$arabText\"\n\nArtinya: \"$translation\"\n\n- QS. $surahName:$ayahNumber";
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#AlQuran #Ayat #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->formatWhatsAppText($title, $description, $url),
            'telegram_text' => $this->formatTelegramText($title, $description, $url),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #AlQuran #Ayat'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->formatCopyText($title, $arabText, $translation, $url)
        ];
    }
    
    /**
     * Generate sharing data for Hadits
     */
    public function generateHaditsSharing($collection, $number, $haditsData, $slug = null) {
        $collectionNames = [
            'arbain' => 'Hadits Arbain',
            'bulughul_maram' => 'Bulughul Maram',
            'perawi' => 'Hadits Perawi'
        ];
        
        $urlParams = "collection=$collection&nomor=$number";
        if ($slug) $urlParams .= "&slug=$slug";
        
        $url = $this->baseUrl . "/hadits.php?" . $urlParams;
        $collectionName = $collectionNames[$collection] ?? ucfirst($collection);
        $title = "$collectionName #$number";
        
        // Clean and prepare text data
        $translation = '';
        $narrator = '';
        
        if (isset($haditsData['arti']) && !empty($haditsData['arti'])) {
            $translation = $this->cleanTextForJson($haditsData['arti']);
            $translation = substr($translation, 0, 200);
            if (strlen($haditsData['arti']) > 200) $translation .= '...';
        } elseif (isset($haditsData['id']) && !empty($haditsData['id'])) {
            // Use 'id' field as translation if 'arti' is not available
            $translation = $this->cleanTextForJson($haditsData['id']);
            $translation = substr($translation, 0, 200);
            if (strlen($haditsData['id']) > 200) $translation .= '...';
        }
        
        if (isset($haditsData['perawi']) && !empty($haditsData['perawi'])) {
            $narrator = $this->cleanTextForJson($haditsData['perawi']);
        }
        
        $description = '';
        if (!empty($translation)) {
            $description .= $translation;
        }
        if (!empty($narrator)) {
            if (!empty($description)) $description .= "\n\n";
            $description .= "Diriwayatkan oleh: $narrator";
        }
        if (empty($description)) {
            $description = "Hadits dari koleksi $collectionName";
        }
        $description .= "\n\n- $title";
        
        // Clean the final description for JSON safety
        $description = $this->cleanTextForJson($description);
        
        return [
            'url' => $url,
            'title' => $this->cleanTextForJson($title),
            'description' => $description,
            'hashtags' => '#Hadits #Sunnah #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->cleanTextForJson($this->formatWhatsAppText($title, $description, $url)),
            'telegram_text' => $this->cleanTextForJson($this->formatTelegramText($title, $description, $url)),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #Hadits #Sunnah'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->cleanTextForJson($this->formatCopyText($title, '', $translation, $url, $narrator))
        ];
    }
    
    /**
     * Generate sharing data for Doa
     */
    public function generateDoaSharing($doaId, $doaData) {
        $url = $this->baseUrl . "/doa.php?id=" . $doaId;
        $title = isset($doaData['judul']) ? $this->cleanTextForJson($doaData['judul']) : "Doa #$doaId";
        
        $arabText = '';
        $translation = '';
        
        if (isset($doaData['arab']) && !empty($doaData['arab'])) {
            $arabText = $this->cleanTextForJson($doaData['arab']);
            $arabText = substr($arabText, 0, 100);
            if (strlen($doaData['arab']) > 100) $arabText .= '...';
        }
        
        if (isset($doaData['arti']) && !empty($doaData['arti'])) {
            $translation = $this->cleanTextForJson($doaData['arti']);
            $translation = substr($translation, 0, 150);
            if (strlen($doaData['arti']) > 150) $translation .= '...';
        }
        
        $description = '';
        if (!empty($arabText)) {
            $description .= $arabText;
        }
        if (!empty($translation)) {
            if (!empty($description)) $description .= "\n\n";
            $description .= "Artinya: $translation";
        }
        $description .= "\n\n- $title";
        
        // Clean the final description for JSON safety
        $description = $this->cleanTextForJson($description);
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#Doa #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->cleanTextForJson($this->formatWhatsAppText($title, $description, $url)),
            'telegram_text' => $this->cleanTextForJson($this->formatTelegramText($title, $description, $url)),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #Doa #Islam'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->cleanTextForJson($this->formatCopyText($title, $arabText, $translation, $url))
        ];
    }
    
    /**
     * Generate sharing data for Asmaul Husna
     */
    public function generateAsmaulHusnaSharing($asmaId, $asmaData) {
        $url = $this->baseUrl . "/asmaul-husna.php?id=" . $asmaId;
        
        $arabName = '';
        $latinName = '';
        $meaning = '';
        
        if (isset($asmaData['arab']) && !empty($asmaData['arab'])) {
            $arabName = $this->cleanTextForJson($asmaData['arab']);
        }
        
        if (isset($asmaData['latin']) && !empty($asmaData['latin'])) {
            $latinName = $this->cleanTextForJson($asmaData['latin']);
        }
        
        if (isset($asmaData['indo']) && !empty($asmaData['indo'])) {
            $meaning = $this->cleanTextForJson($asmaData['indo']);
        } elseif (isset($asmaData['arti']) && !empty($asmaData['arti'])) {
            $meaning = $this->cleanTextForJson($asmaData['arti']);
        }
        
        $title = "Asmaul Husna #$asmaId: $latinName";
        $description = "$arabName\n$latinName\n\nArtinya: $meaning\n\n- Asmaul Husna ke-$asmaId";
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#AsmaulHusna #NamaAllah #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->formatWhatsAppText($title, $description, $url),
            'telegram_text' => $this->formatTelegramText($title, $description, $url),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #AsmaulHusna #Islam'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->formatCopyText($title, $arabName, $meaning, $url)
        ];
    }
    
    /**
     * Generate sharing data for Tafsir
     * NOTE: tafsir.php belum diimplementasi - method ini untuk persiapan masa depan
     */
    /*
    public function generateTafsirSharing($surahNumber, $ayahNumber, $tafsirData, $surahData = null) {
        $url = $this->baseUrl . "/pages/tafsir.php?surah=" . $surahNumber . "&ayat=" . $ayahNumber;
        $surahName = $surahData['nama_latin'] ?? "Surah $surahNumber";
        $title = "Tafsir QS. $surahName:$ayahNumber";
        
        $tafsirText = isset($tafsirData['tafsir']) ? substr($tafsirData['tafsir'], 0, 200) . '...' : '';
        $description = "Tafsir ayat: \"$tafsirText\"\n\n- QS. $surahName:$ayahNumber";
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#Tafsir #AlQuran #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->formatWhatsAppText($title, $description, $url),
            'telegram_text' => $this->formatTelegramText($title, $description, $url),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #Tafsir #AlQuran'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->formatCopyText($title, '', $tafsirText, $url)
        ];
    }
    */
    
    /**
     * Generate sharing data for Dzikir
     * NOTE: dzikir.php belum diimplementasi - method ini untuk persiapan masa depan
     */
    /*
    public function generateDzikirSharing($dzikirId, $dzikirData) {
        $url = $this->baseUrl . "/pages/dzikir.php?id=" . $dzikirId;
        $title = isset($dzikirData['judul']) ? $dzikirData['judul'] : "Dzikir #$dzikirId";
        
        $arabText = isset($dzikirData['arab']) ? substr($dzikirData['arab'], 0, 100) . '...' : '';
        $translation = isset($dzikirData['arti']) ? substr($dzikirData['arti'], 0, 150) . '...' : '';
        $count = isset($dzikirData['jumlah']) ? " (dibaca {$dzikirData['jumlah']}x)" : '';
        
        $description = "\"$arabText\"\n\nArtinya: \"$translation\"$count\n\n- $title";
        
        return [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'hashtags' => '#Dzikir #Islam #MasjidAlMuhajirin',
            'whatsapp_text' => $this->formatWhatsAppText($title, $description, $url),
            'telegram_text' => $this->formatTelegramText($title, $description, $url),
            'facebook_url' => $this->generateFacebookUrl($url, $title, $description),
            'twitter_url' => $this->generateTwitterUrl($url, $title . ' #Dzikir #Islam'),
            'linkedin_url' => $this->generateLinkedInUrl($url, $title, $description),
            'copy_text' => $this->formatCopyText($title, $arabText, $translation, $url)
        ];
    }
    */
    
    /**
     * Format text for WhatsApp sharing
     */
    private function formatWhatsAppText($title, $description, $url) {
        return "*$title*\n\n$description\n\n🔗 Baca selengkapnya: $url\n\n📱 {$this->siteName}";
    }
    
    /**
     * Format text for Telegram sharing
     */
    private function formatTelegramText($title, $description, $url) {
        return "*$title*\n\n$description\n\n🔗 [Baca selengkapnya]($url)\n\n📱 {$this->siteName}";
    }
    
    /**
     * Format text for copying
     */
    private function formatCopyText($title, $arabText, $translation, $url, $narrator = null) {
        $text = "$title\n\n";
        if ($arabText) $text .= "$arabText\n\n";
        if ($translation) $text .= "Artinya: $translation\n\n";
        if ($narrator) $text .= "Diriwayatkan oleh: $narrator\n\n";
        $text .= "Sumber: $url\n\n{$this->siteName}";
        return $text;
    }
    
    /**
     * Clean text for JSON safety - removes problematic characters
     */
    private function cleanTextForJson($text) {
        if (empty($text)) return '';
        
        // Convert to UTF-8 if needed
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remove or replace characters that break JSON
        $text = str_replace(['"', "'", "\\", "\r", "\n", "\t"], ['', '', '', ' ', ' ', ' '], $text);
        
        // Remove control characters
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim and limit length
        $text = trim($text);
        if (strlen($text) > 200) {
            $text = substr($text, 0, 197) . '...';
        }
        
        return $text;
    }
    
    /**
     * Generate Facebook sharing URL
     */
    private function generateFacebookUrl($url, $title, $description) {
        return 'https://www.facebook.com/sharer/sharer.php?' . http_build_query([
            'u' => $url,
            'quote' => "$title\n\n$description"
        ]);
    }
    
    /**
     * Generate Twitter sharing URL
     */
    private function generateTwitterUrl($url, $text) {
        return 'https://twitter.com/intent/tweet?' . http_build_query([
            'url' => $url,
            'text' => $text,
            'via' => 'MasjidAlMuhajirin'
        ]);
    }
    
    /**
     * Generate LinkedIn sharing URL
     */
    private function generateLinkedInUrl($url, $title, $description) {
        return 'https://www.linkedin.com/sharing/share-offsite/?' . http_build_query([
            'url' => $url,
            'title' => $title,
            'summary' => $description
        ]);
    }
    
    /**
     * Get current base URL
     */
    private function getCurrentBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Generate sharing modal HTML
     */
    public function generateSharingModal($sharingData, $contentType = 'content') {
        return '
        <div id="sharing-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-share-alt text-blue-600 mr-2"></i>
                                Bagikan ' . ucfirst($contentType) . '
                            </h3>
                            <button onclick="closeSharingModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            <!-- WhatsApp -->
                            <a href="https://wa.me/?text=' . urlencode($sharingData['whatsapp_text']) . '" 
                               target="_blank" 
                               class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                                <i class="fab fa-whatsapp text-green-600 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">WhatsApp</span>
                            </a>
                            
                            <!-- Telegram -->
                            <a href="https://t.me/share/url?url=' . urlencode($sharingData['url']) . '&text=' . urlencode($sharingData['telegram_text']) . '" 
                               target="_blank" 
                               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                                <i class="fab fa-telegram text-blue-600 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">Telegram</span>
                            </a>
                            
                            <!-- Facebook -->
                            <a href="' . $sharingData['facebook_url'] . '" 
                               target="_blank" 
                               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                                <i class="fab fa-facebook text-blue-700 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">Facebook</span>
                            </a>
                            
                            <!-- Twitter -->
                            <a href="' . $sharingData['twitter_url'] . '" 
                               target="_blank" 
                               class="flex items-center p-3 bg-sky-50 hover:bg-sky-100 rounded-lg transition duration-200">
                                <i class="fab fa-twitter text-sky-600 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">Twitter</span>
                            </a>
                            
                            <!-- LinkedIn -->
                            <a href="' . $sharingData['linkedin_url'] . '" 
                               target="_blank" 
                               class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                                <i class="fab fa-linkedin text-blue-800 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">LinkedIn</span>
                            </a>
                            
                            <!-- Copy Link -->
                            <button onclick="copyToClipboard(\'' . $sharingData['url'] . '\')" 
                                    class="w-full flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-200">
                                <i class="fas fa-link text-gray-600 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">Salin Link</span>
                            </button>
                            
                            <!-- Copy Text -->
                            <button onclick="copyToClipboard(\'' . addslashes($sharingData['copy_text']) . '\')" 
                                    class="w-full flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-200">
                                <i class="fas fa-copy text-gray-600 text-xl mr-3"></i>
                                <span class="font-medium text-gray-900">Salin Teks</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Generate sharing button HTML
     */
    public function generateSharingButton($sharingData, $contentType = 'content', $buttonClass = 'btn-share') {
        return '
        <button onclick="openSharingModal(\'' . addslashes(json_encode($sharingData)) . '\', \'' . $contentType . '\')" 
                class="' . $buttonClass . ' text-gray-500 hover:text-blue-600 transition duration-200" 
                title="Bagikan ' . $contentType . '">
            <i class="fas fa-share-alt"></i>
        </button>';
    }
}