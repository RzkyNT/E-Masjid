<?php
/**
 * EQuran.id v2.0 API Integration with Local Caching
 * For Masjid Al-Muhajirin Information System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';

class EQuranV2API {
    private $base_url = 'https://equran.id/api/v2';
    private $cache_dir;
    private $audio_dir;
    private $cache_duration = 86400 * 7; // 7 days for Al-Quran data
    private $qari_id = '05'; // Misyari Rasyid Al-Afasy
    
    public function __construct() {
        $this->cache_dir = __DIR__ . '/cache/equran_v2/';
        $this->audio_dir = __DIR__ . '/../assets/audio/quran/';
        
        // Ensure directories exist
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
        if (!is_dir($this->audio_dir)) {
            mkdir($this->audio_dir, 0755, true);
        }
    }
    
    /**
     * Get list of all surat
     */
    public function getSuratList() {
        $cache_file = $this->cache_dir . 'surat_list.json';
        
        // Check cache first
        if ($this->isCacheValid($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data) {
                $this->logActivity("Cache hit for surat list");
                return $this->formatResponse(true, $data['data'], 'Data loaded from cache');
            }
        }
        
        // Fetch from API
        try {
            $url = $this->base_url . '/surat';
            $response = $this->makeAPIRequest($url);
            
            if ($response && $response['code'] === 200) {
                // Save to cache
                file_put_contents($cache_file, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
                $this->logActivity("Surat list fetched from API and cached");
                
                return $this->formatResponse(true, $response['data'], 'Data loaded from API');
            }
        } catch (Exception $e) {
            $this->logError("Failed to fetch surat list: " . $e->getMessage());
        }
        
        return $this->formatResponse(false, [], 'Failed to load surat list');
    }
    
    /**
     * Get surat detail with ayat
     */
    public function getSuratDetail($surat_id) {
        if (!is_numeric($surat_id) || $surat_id < 1 || $surat_id > 114) {
            return $this->formatResponse(false, [], 'Invalid surat ID');
        }
        
        $cache_file = $this->cache_dir . "surat_{$surat_id}.json";
        
        // Check cache first
        if ($this->isCacheValid($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data) {
                $this->logActivity("Cache hit for surat {$surat_id}");
                
                // Process audio URLs to use local files if available
                $data = $this->processAudioUrls($data);
                
                return $this->formatResponse(true, $data['data'], 'Data loaded from cache');
            }
        }
        
        // Fetch from API
        try {
            $url = $this->base_url . "/surat/{$surat_id}";
            $response = $this->makeAPIRequest($url);
            
            if ($response && $response['code'] === 200) {
                // Save to cache
                file_put_contents($cache_file, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
                $this->logActivity("Surat {$surat_id} fetched from API and cached");
                
                // Process audio URLs (now just returns CDN URLs)
                $response['data'] = $this->processAudioUrls($response['data']);
                
                return $this->formatResponse(true, $response['data'], 'Data loaded from API');
            }
        } catch (Exception $e) {
            $this->logError("Failed to fetch surat {$surat_id}: " . $e->getMessage());
        }
        
        return $this->formatResponse(false, [], 'Failed to load surat detail');
    }
    
    /**
     * Get tafsir for a surat
     */
    public function getTafsir($surat_id) {
        if (!is_numeric($surat_id) || $surat_id < 1 || $surat_id > 114) {
            return $this->formatResponse(false, [], 'Invalid surat ID');
        }
        
        $cache_file = $this->cache_dir . "tafsir_{$surat_id}.json";
        
        // Check cache first
        if ($this->isCacheValid($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data) {
                $this->logActivity("Cache hit for tafsir {$surat_id}");
                return $this->formatResponse(true, $data['data'], 'Tafsir loaded from cache');
            }
        }
        
        // Fetch from API
        try {
            $url = $this->base_url . "/tafsir/{$surat_id}";
            $response = $this->makeAPIRequest($url);
            
            if ($response && $response['code'] === 200) {
                // Save to cache
                file_put_contents($cache_file, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
                $this->logActivity("Tafsir {$surat_id} fetched from API and cached");
                
                return $this->formatResponse(true, $response['data'], 'Tafsir loaded from API');
            }
        } catch (Exception $e) {
            $this->logError("Failed to fetch tafsir {$surat_id}: " . $e->getMessage());
        }
        
        return $this->formatResponse(false, [], 'Failed to load tafsir');
    }
    
    /**
     * Process audio URLs - now just returns CDN URLs directly
     */
    private function processAudioUrls($surat_data) {
        // No longer processing for local files
        // Audio is streamed directly from CDN
        return $surat_data;
    }
    
    /**
     * Removed audio download functionality
     * Audio is now streamed directly from CDN
     */
    private function scheduleAudioDownload($surat_data) {
        // No longer downloading audio to server
        // Audio is streamed directly from CDN
    }
    
    private function startBackgroundDownload() {
        // No longer downloading audio to server
    }
    
    private function processDownloadQueue() {
        // No longer downloading audio to server
    }
    
    private function downloadAudioFile($url, $local_file) {
        // No longer downloading audio to server
        return false;
    }
    
    /**
     * Make API request
     */
    private function makeAPIRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; Masjid-Al-Muhajirin/1.0)',
                    'Accept: application/json'
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Failed to fetch data from: {$url}");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Check if cache is valid
     */
    private function isCacheValid($cache_file) {
        return file_exists($cache_file) && 
               (time() - filemtime($cache_file)) < $this->cache_duration;
    }
    
    /**
     * Get base URL for the application
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        // Remove /api from path
        $path = str_replace('/api', '', $path);
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Format API response
     */
    private function formatResponse($success, $data, $message = '') {
        return [
            'code' => $success ? 200 : 500,
            'message' => $message,
            'data' => $data
        ];
    }
    
    /**
     * Logging functions
     */
    private function logActivity($message) {
        $log_file = __DIR__ . '/../logs/equran_v2_activity.log';
        $this->writeLog($log_file, 'INFO', $message);
    }
    
    private function logError($message) {
        $log_file = __DIR__ . '/../logs/equran_v2_error.log';
        $this->writeLog($log_file, 'ERROR', $message);
    }
    
    private function writeLog($log_file, $level, $message) {
        try {
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $log_message = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
            
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("EQuran v2 logging error: " . $e->getMessage());
        }
    }
}

// Handle API requests
try {
    $api = new EQuranV2API();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'surat_list':
            echo json_encode($api->getSuratList());
            break;
            
        case 'surat_detail':
            $surat_id = $_GET['surat_id'] ?? 0;
            echo json_encode($api->getSuratDetail($surat_id));
            break;
            
        case 'tafsir':
            $surat_id = $_GET['surat_id'] ?? 0;
            echo json_encode($api->getTafsir($surat_id));
            break;
            
        case 'download_status':
            // Audio download feature removed
            // Audio is now streamed directly from CDN
            echo json_encode([
                'code' => 200,
                'message' => 'Audio streaming from CDN',
                'data' => [
                    'streaming' => true,
                    'cdn_enabled' => true
                ]
            ]);
            break;
            
        default:
            echo json_encode([
                'code' => 400,
                'message' => 'Invalid action',
                'data' => []
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>