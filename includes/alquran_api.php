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