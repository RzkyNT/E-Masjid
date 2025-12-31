<?php
/**
 * Al-Quran API Integration
 * For Masjid Al-Muhajirin Information System
 * Using MyQuran API v2
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/alquran_validation.php';

class AlQuranAPI {
    private $base_url = 'https://api.myquran.com/v2/quran';
    private $cache_dir;
    private $cache_duration = 86400; // 24 hours for Al-Quran data (static content)
    
    public function __construct() {
        $this->cache_dir = __DIR__ . '/../api/cache/';
        
        // Ensure cache directory exists
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Get ayat by surat with specified length
     * @param int $surat Surat number (1-114)
     * @param int $ayat Starting ayat number
     * @param int $panjang Number of ayat to retrieve
     * @return array|null
     */
    public function getAyatBySurat($surat, $ayat = 1, $panjang = 1) {
        // Validate input parameters using new validation system
        $surat_validation = AlQuranValidator::validateSurat($surat);
        if (!$surat_validation['valid']) {
            throw new InvalidArgumentException($surat_validation['message']);
        }
        
        $ayat_validation = AlQuranValidator::validateAyat($ayat, $surat_validation['value']);
        if (!$ayat_validation['valid']) {
            throw new InvalidArgumentException($ayat_validation['message']);
        }
        
        $panjang_validation = AlQuranValidator::validatePanjang($panjang);
        if (!$panjang_validation['valid']) {
            throw new InvalidArgumentException($panjang_validation['message']);
        }
        
        $endpoint = "/ayat/{$surat_validation['value']}/{$ayat_validation['value']}/{$panjang_validation['value']}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get complete surat (all ayat) - bypasses panjang validation
     * @param int $surat Surat number (1-114)
     * @return array|null
     */
    public function getCompleteSurat($surat) {
        // Validate only surat parameter
        $surat_validation = AlQuranValidator::validateSurat($surat);
        if (!$surat_validation['valid']) {
            throw new InvalidArgumentException($surat_validation['message']);
        }
        
        // Get surat info to determine total ayat count
        $surat_info = AlQuranValidator::getSuratInfo($surat);
        $total_ayat = $surat_info['ayat_count'];
        
        // If surat has 30 or fewer ayat, get all at once
        if ($total_ayat <= 30) {
            $endpoint = "/ayat/{$surat_validation['value']}/1/{$total_ayat}";
            return $this->fetchQuranData($endpoint);
        }
        
        // For longer surat, make multiple API calls and combine results
        $all_ayat = [];
        $batch_size = 30; // API maximum
        $current_ayat = 1;
        
        while ($current_ayat <= $total_ayat) {
            $remaining_ayat = $total_ayat - $current_ayat + 1;
            $batch_count = min($batch_size, $remaining_ayat);
            
            $endpoint = "/ayat/{$surat_validation['value']}/{$current_ayat}/{$batch_count}";
            $batch_result = $this->fetchQuranData($endpoint);
            
            if (!$batch_result || !isset($batch_result['data']) || !is_array($batch_result['data'])) {
                // If any batch fails, return what we have so far or null
                if (empty($all_ayat)) {
                    return null;
                }
                break;
            }
            
            // Merge the ayat from this batch
            $all_ayat = array_merge($all_ayat, $batch_result['data']);
            $current_ayat += $batch_count;
            
            // Small delay between requests to be respectful to the API
            usleep(100000); // 0.1 second delay
        }
        
        // Return combined result in the same format as single API call
        if (!empty($all_ayat)) {
            return [
                'status' => true,
                'data' => $all_ayat,
                'surat_info' => $surat_info,
                'total_ayat' => count($all_ayat),
                'batched' => true // Indicate this was retrieved in batches
            ];
        }
        
        return null;
    }
    
    /**
     * Get ayat by surat with range
     * @param int $surat Surat number (1-114)
     * @param int $ayat_start Starting ayat number
     * @param int $ayat_end Ending ayat number
     * @return array|null
     */
    public function getAyatByRange($surat, $ayat_start, $ayat_end) {
        // Validate input parameters using new validation system
        $surat_validation = AlQuranValidator::validateSurat($surat);
        if (!$surat_validation['valid']) {
            throw new InvalidArgumentException($surat_validation['message']);
        }
        
        $range_validation = AlQuranValidator::validateAyatRange($ayat_start, $ayat_end, $surat_validation['value']);
        if (!$range_validation['valid']) {
            throw new InvalidArgumentException($range_validation['message']);
        }
        
        $range = "{$range_validation['start']}-{$range_validation['end']}";
        $endpoint = "/ayat/{$surat_validation['value']}/{$range}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get ayat by page number
     * @param int $page Page number (1-604)
     * @return array|null
     */
    public function getAyatByPage($page) {
        $page_validation = AlQuranValidator::validatePage($page);
        if (!$page_validation['valid']) {
            throw new InvalidArgumentException($page_validation['message']);
        }
        
        $endpoint = "/ayat/page/{$page_validation['value']}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get ayat by juz number
     * @param int $juz Juz number (1-30)
     * @return array|null
     */
    public function getAyatByJuz($juz) {
        $juz_validation = AlQuranValidator::validateJuz($juz);
        if (!$juz_validation['valid']) {
            throw new InvalidArgumentException($juz_validation['message']);
        }
        
        $endpoint = "/ayat/juz/{$juz_validation['value']}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get juz information
     * @param int $juz Juz number (1-30)
     * @return array|null
     */
    public function getJuzInfo($juz) {
        $juz_validation = AlQuranValidator::validateJuz($juz);
        if (!$juz_validation['valid']) {
            throw new InvalidArgumentException($juz_validation['message']);
        }
        
        $endpoint = "/juz/{$juz_validation['value']}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get all tema (topics)
     * @return array|null
     */
    public function getAllTema() {
        $endpoint = "/tema/semua";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Get tema by ID
     * @param int $tema_id Tema ID (1-1121)
     * @return array|null
     */
    public function getTemaById($tema_id) {
        $tema_validation = AlQuranValidator::validateTema($tema_id);
        if (!$tema_validation['valid']) {
            throw new InvalidArgumentException($tema_validation['message']);
        }
        
        $endpoint = "/tema/{$tema_validation['value']}";
        return $this->fetchQuranData($endpoint);
    }
    
    /**
     * Search Al-Quran content
     * @param string $query Search query
     * @param string $type Search type: all, surat, ayat, transliterasi, terjemahan, catatan
     * @return array|null
     */
    public function searchQuran($query, $type = 'all') {
        if (empty($query) || strlen(trim($query)) < 2) {
            throw new InvalidArgumentException('Query pencarian minimal 2 karakter');
        }
        
        $query = trim($query);
        $valid_types = ['all', 'surat', 'ayat', 'transliterasi', 'terjemahan', 'catatan'];
        
        if (!in_array($type, $valid_types)) {
            $type = 'all';
        }
        
        // For now, we'll implement a basic search by getting all surat and filtering
        // In a real implementation, this would use a dedicated search API endpoint
        $results = [];
        
        try {
            // Search in surat names first
            if ($type === 'all' || $type === 'surat') {
                $surat_results = $this->searchInSuratNames($query);
                if (!empty($surat_results)) {
                    $results = array_merge($results, $surat_results);
                }
            }
            
            // Search in ayat content (limited search for performance)
            if ($type === 'all' || in_array($type, ['ayat', 'transliterasi', 'terjemahan'])) {
                $ayat_results = $this->searchInAyatContent($query, $type);
                if (!empty($ayat_results)) {
                    $results = array_merge($results, $ayat_results);
                }
            }
            
            return [
                'status' => true,
                'data' => $results,
                'query' => $query,
                'type' => $type,
                'total' => count($results)
            ];
            
        } catch (Exception $e) {
            $this->logError("Search error: " . $e->getMessage());
            return [
                'status' => false,
                'data' => [],
                'query' => $query,
                'type' => $type,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search in surat names with fuzzy matching
     * @param string $query Search query
     * @return array
     */
    private function searchInSuratNames($query) {
        $results = [];
        $all_surat = AlQuranValidator::getAllSuratInfo();
        
        foreach ($all_surat as $number => $info) {
            $score = $this->calculateFuzzyScore($query, $info['name'], $number);
            
            // Only include results with score above threshold
            if ($score >= 30) { // Increased threshold from 0 to 30
                $results[] = [
                    'type' => 'surat',
                    'surat_number' => $number,
                    'surat_name' => $info['name'],
                    'ayat_count' => $info['ayat_count'],
                    'match_type' => 'surat_name',
                    'score' => $score,
                    'relevance' => $this->getRelevanceLevel($score),
                    'url' => "alquran.php?mode=surat&surat={$number}"
                ];
            }
        }
        
        // Sort by score (highest first)
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Limit results to top 15 for better user experience
        return array_slice($results, 0, 15);
    }
    
    /**
     * Get relevance level based on score
     * @param float $score Matching score
     * @return string Relevance level
     */
    private function getRelevanceLevel($score) {
        if ($score >= 80) {
            return 'high';
        } elseif ($score >= 60) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Calculate fuzzy matching score for surat names
     * @param string $query Search query
     * @param string $suratName Surat name
     * @param int $suratNumber Surat number
     * @return float Score (0-100)
     */
    private function calculateFuzzyScore($query, $suratName, $suratNumber) {
        $query_lower = strtolower(trim($query));
        $surat_lower = strtolower($suratName);
        
        // If query is empty, return 0
        if (empty($query_lower)) {
            return 0;
        }
        
        // Exact match gets highest score
        if ($query_lower === $surat_lower) {
            return 100;
        }
        
        // Check if query matches surat number
        if ($query_lower === (string)$suratNumber) {
            return 95;
        }
        
        // Exact substring match gets high score
        if (strpos($surat_lower, $query_lower) !== false) {
            $position = strpos($surat_lower, $query_lower);
            // Earlier position gets higher score
            return 90 - ($position * 2);
        }
        
        // Remove common prefixes and try again
        $cleaned_surat = $this->cleanSuratName($surat_lower);
        $cleaned_query = $this->cleanSuratName($query_lower);
        
        // Exact match after cleaning
        if ($cleaned_query === $cleaned_surat) {
            return 85;
        }
        
        // Substring match after cleaning
        if (strpos($cleaned_surat, $cleaned_query) !== false) {
            $position = strpos($cleaned_surat, $cleaned_query);
            return 80 - ($position * 2);
        }
        
        // Check if cleaned query is at the start
        if (strpos($cleaned_surat, $cleaned_query) === 0) {
            return 75;
        }
        
        // Word boundary matching
        $words = explode(' ', $cleaned_surat);
        foreach ($words as $word) {
            if (strpos($word, $cleaned_query) === 0) {
                return 70;
            }
        }
        
        // Only proceed with expensive operations if query is long enough
        if (strlen($cleaned_query) < 3) {
            return 0;
        }
        
        // Levenshtein distance for similar strings (only for longer queries)
        $distance = levenshtein($cleaned_query, $cleaned_surat);
        $max_length = max(strlen($cleaned_query), strlen($cleaned_surat));
        
        if ($max_length > 0) {
            $similarity = (1 - ($distance / $max_length)) * 100;
            
            // Only return if similarity is above higher threshold
            if ($similarity > 70) {
                return $similarity * 0.6; // Reduce score for fuzzy matches
            }
        }
        
        // Character-by-character matching for very different strings (only for longer queries)
        if (strlen($cleaned_query) >= 4) {
            $char_score = $this->calculateCharacterScore($cleaned_query, $cleaned_surat);
            if ($char_score > 60) {
                return $char_score * 0.3; // Further reduce score for character matches
            }
        }
        
        return 0;
    }
    
    /**
     * Clean surat name by removing common prefixes and normalizing
     * @param string $name Surat name
     * @return string Cleaned name
     */
    private function cleanSuratName($name) {
        $name = strtolower(trim($name));
        
        // Remove common Arabic prefixes
        $prefixes = ['al-', 'an-', 'ar-', 'as-', 'at-', 'az-', 'ash-', 'ad-'];
        
        foreach ($prefixes as $prefix) {
            if (strpos($name, $prefix) === 0) {
                $name = substr($name, strlen($prefix));
                break;
            }
        }
        
        // Remove apostrophes and special characters
        $name = str_replace(['\'', '\'', '`', '-'], '', $name);
        
        // Normalize common character variations
        $replacements = [
            'aa' => 'a',
            'ii' => 'i',
            'uu' => 'u',
            'kh' => 'h',
            'gh' => 'g',
            'sh' => 's',
            'th' => 't',
            'dh' => 'd',
            'zh' => 'z'
        ];
        
        foreach ($replacements as $from => $to) {
            $name = str_replace($from, $to, $name);
        }
        
        return trim($name);
    }
    
    /**
     * Calculate character-based similarity score
     * @param string $query Search query
     * @param string $target Target string
     * @return float Score (0-100)
     */
    private function calculateCharacterScore($query, $target) {
        $query_chars = str_split($query);
        $target_chars = str_split($target);
        
        $matches = 0;
        $query_length = count($query_chars);
        
        foreach ($query_chars as $char) {
            if (in_array($char, $target_chars)) {
                $matches++;
            }
        }
        
        return $query_length > 0 ? ($matches / $query_length) * 100 : 0;
    }
    
    /**
     * Search in ayat content (limited implementation)
     * @param string $query Search query
     * @param string $type Search type
     * @return array
     */
    private function searchInAyatContent($query, $type) {
        // This is a simplified implementation
        // In a real application, you would use a proper search index
        $results = [];
        $query_lower = strtolower($query);
        
        // Search in popular surat first (Al-Fatihah, Al-Baqarah, etc.)
        $popular_surat = [1, 2, 3, 4, 5, 18, 36, 55, 67, 112, 113, 114];
        
        foreach ($popular_surat as $surat_number) {
            try {
                $surat_info = AlQuranValidator::getSuratInfo($surat_number);
                
                // Get first few ayat to search (limit for performance)
                $max_ayat = min(10, $surat_info['ayat_count']);
                $ayat_data = $this->getAyatBySurat($surat_number, 1, $max_ayat);
                
                if (isset($ayat_data['data']) && is_array($ayat_data['data'])) {
                    foreach ($ayat_data['data'] as $ayat) {
                        $match_found = false;
                        $match_field = '';
                        
                        // Search in different fields based on type
                        if ($type === 'all' || $type === 'transliterasi') {
                            if (isset($ayat['latin']) && strpos(strtolower($ayat['latin']), $query_lower) !== false) {
                                $match_found = true;
                                $match_field = 'transliterasi';
                            }
                        }
                        
                        if ($type === 'all' || $type === 'terjemahan') {
                            if (isset($ayat['text']) && strpos(strtolower($ayat['text']), $query_lower) !== false) {
                                $match_found = true;
                                $match_field = 'terjemahan';
                            }
                            if (isset($ayat['arti']) && strpos(strtolower($ayat['arti']), $query_lower) !== false) {
                                $match_found = true;
                                $match_field = 'terjemahan';
                            }
                        }
                        
                        if ($match_found) {
                            $results[] = [
                                'type' => 'ayat',
                                'surat_number' => $surat_number,
                                'surat_name' => $surat_info['name'],
                                'ayat_number' => $ayat['ayah'] ?? $ayat['nomor'] ?? '',
                                'arab' => $ayat['arab'] ?? '',
                                'latin' => $ayat['latin'] ?? '',
                                'text' => $ayat['text'] ?? $ayat['arti'] ?? '',
                                'match_type' => $match_field,
                                'url' => "alquran.php?mode=surat&surat={$surat_number}&ayat=" . ($ayat['ayah'] ?? $ayat['nomor'] ?? '') . "&panjang=1"
                            ];
                        }
                        
                        // Limit results for performance
                        if (count($results) >= 20) {
                            break 2;
                        }
                    }
                }
            } catch (Exception $e) {
                // Continue with next surat if one fails
                continue;
            }
        }
        
        return $results;
    }
    
    /**
     * Fetch data from MyQuran API with caching
     * @param string $endpoint API endpoint
     * @return array|null
     */
    public function fetchQuranData($endpoint) {
        $url = $this->base_url . $endpoint;
        $cache_key = 'quran_' . md5($url);
        $cache_file = $this->cache_dir . $cache_key . '.json';
        
        // Check if cache exists and is valid
        if ($this->isCacheValid($cache_file)) {
            try {
                $cached_data = file_get_contents($cache_file);
                $data = json_decode($cached_data, true);
                
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    $this->logActivity("Cache hit for endpoint: {$endpoint}");
                    return $data;
                }
            } catch (Exception $e) {
                $this->logError("Cache read error: " . $e->getMessage());
            }
        }
        
        // Fetch from API
        try {
            $data = $this->makeAPIRequest($url);
            
            if ($data) {
                // Save to cache
                $this->setCachedData($cache_file, $data);
                $this->logActivity("API call successful for endpoint: {$endpoint}");
                return $data;
            }
        } catch (Exception $e) {
            $this->logError("API request failed for {$endpoint}: " . $e->getMessage());
            
            // Try to use expired cache as fallback
            if (file_exists($cache_file)) {
                try {
                    $cached_data = file_get_contents($cache_file);
                    $data = json_decode($cached_data, true);
                    
                    if ($data && json_last_error() === JSON_ERROR_NONE) {
                        $this->logActivity("Using expired cache as fallback for endpoint: {$endpoint}");
                        $data['fallback'] = true;
                        return $data;
                    }
                } catch (Exception $cache_e) {
                    $this->logError("Fallback cache read error: " . $cache_e->getMessage());
                }
            }
        }
        
        return null;
    }
    
    /**
     * Make HTTP request to API
     * @param string $url Full API URL
     * @return array|null
     */
    private function makeAPIRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10, // 10 second timeout
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; Masjid-Al-Muhajirin/1.0)',
                    'Accept: application/json',
                    'Accept-Encoding: gzip, deflate'
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Failed to fetch data from API: {$url}");
        }
        
        // Check if response is gzipped and decompress if needed
        if (substr($response, 0, 2) === "\x1f\x8b") {
            $response = gzdecode($response);
            if ($response === false) {
                throw new Exception("Failed to decompress gzipped response");
            }
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API: " . json_last_error_msg());
        }
        
        if (!$data || !isset($data['status'])) {
            throw new Exception("API returned invalid response");
        }
        
        // Handle special case for juz endpoint which returns status:false but has data
        if ($data['status'] === false && isset($data['data']) && !empty($data['data'])) {
            // This is likely the juz info endpoint which has inconsistent status reporting
            return $data;
        }
        
        if ($data['status'] !== true && $data['status'] !== 1) {
            $message = isset($data['message']) ? $data['message'] : 'Unknown API error';
            throw new Exception("API returned error: " . $message);
        }
        
        return $data;
    }
    
    /**
     * Check if cache file is valid (exists and not expired)
     * @param string $cache_file Path to cache file
     * @return bool
     */
    private function isCacheValid($cache_file) {
        return file_exists($cache_file) && 
               (time() - filemtime($cache_file)) < $this->cache_duration;
    }
    
    /**
     * Save data to cache
     * @param string $cache_file Path to cache file
     * @param array $data Data to cache
     */
    private function setCachedData($cache_file, $data) {
        try {
            $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($cache_file, $json_data, LOCK_EX);
        } catch (Exception $e) {
            $this->logError("Cache write error: " . $e->getMessage());
        }
    }
    
    /**
     * Logging functions
     */
    private function logActivity($message) {
        $log_file = __DIR__ . '/../logs/alquran_activity.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] INFO: {$message}" . PHP_EOL;
        
        try {
            // Ensure logs directory exists
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Silently fail if logging fails
            error_log("Al-Quran logging error: " . $e->getMessage());
        }
    }
    
    private function logError($message) {
        $log_file = __DIR__ . '/../logs/alquran_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        try {
            // Ensure logs directory exists
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Fallback to PHP error log
            error_log("Al-Quran API Error: {$message}");
        }
    }
}

// Helper functions for backward compatibility and ease of use
function getAlQuranAPI() {
    static $api = null;
    if ($api === null) {
        $api = new AlQuranAPI();
    }
    return $api;
}

/**
 * Quick access functions
 */
function getAyatBySurat($surat, $ayat = 1, $panjang = 1) {
    return getAlQuranAPI()->getAyatBySurat($surat, $ayat, $panjang);
}

function getAyatByRange($surat, $ayat_start, $ayat_end) {
    return getAlQuranAPI()->getAyatByRange($surat, $ayat_start, $ayat_end);
}

function getAyatByPage($page) {
    return getAlQuranAPI()->getAyatByPage($page);
}

function getAyatByJuz($juz) {
    return getAlQuranAPI()->getAyatByJuz($juz);
}

function getJuzInfo($juz) {
    return getAlQuranAPI()->getJuzInfo($juz);
}

function getAllTema() {
    return getAlQuranAPI()->getAllTema();
}

function getTemaById($tema_id) {
    return getAlQuranAPI()->getTemaById($tema_id);
}
?>