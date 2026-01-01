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
                
                // Start background audio download
                $this->scheduleAudioDownload($response['data']);
                
                // Process audio URLs
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
     * Process audio URLs to use local files if available
     */
    private function processAudioUrls($surat_data) {
        if (!isset($surat_data['ayat']) || !is_array($surat_data['ayat'])) {
            return $surat_data;
        }
        
        $base_url = $this->getBaseUrl();
        
        foreach ($surat_data['ayat'] as &$ayat) {
            if (isset($ayat['audio'][$this->qari_id])) {
                $surat_id = $surat_data['nomor'];
                $ayat_id = $ayat['nomorAyat'];
                $local_file = $this->audio_dir . "surat_{$surat_id}_ayat_{$ayat_id}.mp3";
                
                // If local file exists, use it
                if (file_exists($local_file)) {
                    $ayat['audio'][$this->qari_id] = $base_url . "/assets/audio/quran/surat_{$surat_id}_ayat_{$ayat_id}.mp3";
                    $ayat['audio_local'] = true;
                } else {
                    $ayat['audio_local'] = false;
                }
            }
        }
        
        return $surat_data;
    }
    
    /**
     * Schedule audio download in background
     */
    private function scheduleAudioDownload($surat_data) {
        if (!isset($surat_data['ayat']) || !is_array($surat_data['ayat'])) {
            return;
        }
        
        // Create download queue file
        $queue_file = $this->cache_dir . 'download_queue.json';
        $queue = [];
        
        if (file_exists($queue_file)) {
            $queue = json_decode(file_get_contents($queue_file), true) ?: [];
        }
        
        $surat_id = $surat_data['nomor'];
        
        foreach ($surat_data['ayat'] as $ayat) {
            if (isset($ayat['audio'][$this->qari_id])) {
                $ayat_id = $ayat['nomorAyat'];
                $local_file = $this->audio_dir . "surat_{$surat_id}_ayat_{$ayat_id}.mp3";
                
                // Only add to queue if file doesn't exist
                if (!file_exists($local_file)) {
                    $queue[] = [
                        'surat_id' => $surat_id,
                        'ayat_id' => $ayat_id,
                        'url' => $ayat['audio'][$this->qari_id],
                        'local_file' => $local_file,
                        'added_at' => time()
                    ];
                }
            }
        }
        
        // Save updated queue
        file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT), LOCK_EX);
        
        // Start background download process
        $this->startBackgroundDownload();
    }
    
    /**
     * Start background download process
     */
    private function startBackgroundDownload() {
        // Use a simple file lock to prevent multiple download processes
        $lock_file = $this->cache_dir . 'download.lock';
        
        if (file_exists($lock_file)) {
            $lock_time = filemtime($lock_file);
            // If lock is older than 5 minutes, remove it (process might have died)
            if (time() - $lock_time > 300) {
                unlink($lock_file);
            } else {
                return; // Download already in progress
            }
        }
        
        // Create lock file
        touch($lock_file);
        
        // Process download queue
        $this->processDownloadQueue();
        
        // Remove lock file
        if (file_exists($lock_file)) {
            unlink($lock_file);
        }
    }
    
    /**
     * Process download queue
     */
    private function processDownloadQueue() {
        $queue_file = $this->cache_dir . 'download_queue.json';
        
        if (!file_exists($queue_file)) {
            return;
        }
        
        $queue = json_decode(file_get_contents($queue_file), true);
        if (!$queue || !is_array($queue)) {
            return;
        }
        
        $processed = [];
        $remaining = [];
        $max_downloads = 5; // Limit concurrent downloads
        $downloaded = 0;
        
        foreach ($queue as $item) {
            if ($downloaded >= $max_downloads) {
                $remaining[] = $item;
                continue;
            }
            
            try {
                if ($this->downloadAudioFile($item['url'], $item['local_file'])) {
                    $this->logActivity("Downloaded audio: surat {$item['surat_id']} ayat {$item['ayat_id']}");
                    $downloaded++;
                } else {
                    // Keep in queue for retry
                    $remaining[] = $item;
                }
            } catch (Exception $e) {
                $this->logError("Failed to download audio: " . $e->getMessage());
                $remaining[] = $item;
            }
            
            // Small delay between downloads
            usleep(500000); // 0.5 second
        }
        
        // Update queue with remaining items
        file_put_contents($queue_file, json_encode($remaining, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    /**
     * Download audio file
     */
    private function downloadAudioFile($url, $local_file) {
        if (file_exists($local_file)) {
            return true; // Already exists
        }
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; Masjid-Al-Muhajirin/1.0)',
                    'Accept: audio/mpeg, audio/*'
                ]
            ]
        ]);
        
        $audio_data = @file_get_contents($url, false, $context);
        
        if ($audio_data === false) {
            return false;
        }
        
        // Verify it's actually an audio file (check for MP3 header)
        if (substr($audio_data, 0, 3) !== 'ID3' && substr($audio_data, 0, 2) !== "\xFF\xFB") {
            return false;
        }
        
        return file_put_contents($local_file, $audio_data, LOCK_EX) !== false;
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
            // Return download queue status
            $queue_file = __DIR__ . '/cache/equran_v2/download_queue.json';
            $queue = [];
            if (file_exists($queue_file)) {
                $queue = json_decode(file_get_contents($queue_file), true) ?: [];
            }
            echo json_encode([
                'code' => 200,
                'message' => 'Download status',
                'data' => [
                    'queue_count' => count($queue),
                    'in_progress' => file_exists(__DIR__ . '/cache/equran_v2/download.lock')
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