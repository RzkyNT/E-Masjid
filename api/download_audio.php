<?php
/**
 * Audio Download Script for EQuran v2
 * Downloads audio files from Misyari Rasyid Al-Afasy
 */

set_time_limit(0); // Remove time limit for long downloads
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config/config.php';

class AudioDownloader {
    private $base_url = 'https://equran.id/api/v2';
    private $audio_dir;
    private $cache_dir;
    private $qari_id = '05'; // Misyari Rasyid Al-Afasy
    private $log_file;
    
    public function __construct() {
        $this->audio_dir = __DIR__ . '/../assets/audio/quran/';
        $this->cache_dir = __DIR__ . '/cache/equran_v2/';
        $this->log_file = __DIR__ . '/../logs/audio_download.log';
        
        // Ensure directories exist
        if (!is_dir($this->audio_dir)) {
            mkdir($this->audio_dir, 0755, true);
        }
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
        if (!is_dir(dirname($this->log_file))) {
            mkdir(dirname($this->log_file), 0755, true);
        }
    }
    
    /**
     * Download audio for specific surat
     */
    public function downloadSurat($surat_id) {
        $this->log("Starting download for surat {$surat_id}");
        
        try {
            // Get surat data
            $surat_data = $this->getSuratData($surat_id);
            if (!$surat_data) {
                $this->log("Failed to get surat data for surat {$surat_id}");
                return false;
            }
            
            $downloaded = 0;
            $failed = 0;
            $skipped = 0;
            
            foreach ($surat_data['ayat'] as $ayat) {
                if (!isset($ayat['audio'][$this->qari_id])) {
                    continue;
                }
                
                $ayat_id = $ayat['nomorAyat'];
                $audio_url = $ayat['audio'][$this->qari_id];
                $local_file = $this->audio_dir . "surat_{$surat_id}_ayat_{$ayat_id}.mp3";
                
                // Skip if file already exists
                if (file_exists($local_file) && filesize($local_file) > 1000) {
                    $skipped++;
                    continue;
                }
                
                $this->log("Downloading surat {$surat_id} ayat {$ayat_id}...");
                
                if ($this->downloadFile($audio_url, $local_file)) {
                    $downloaded++;
                    $this->log("✓ Downloaded surat {$surat_id} ayat {$ayat_id}");
                } else {
                    $failed++;
                    $this->log("✗ Failed to download surat {$surat_id} ayat {$ayat_id}");
                }
                
                // Small delay between downloads
                usleep(200000); // 0.2 second
            }
            
            $this->log("Surat {$surat_id} complete: {$downloaded} downloaded, {$skipped} skipped, {$failed} failed");
            return true;
            
        } catch (Exception $e) {
            $this->log("Error downloading surat {$surat_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Download audio for all surat
     */
    public function downloadAll($start_surat = 1, $end_surat = 114) {
        $this->log("Starting bulk download from surat {$start_surat} to {$end_surat}");
        
        $total_downloaded = 0;
        $total_failed = 0;
        $total_skipped = 0;
        
        for ($surat_id = $start_surat; $surat_id <= $end_surat; $surat_id++) {
            $this->log("Processing surat {$surat_id}...");
            
            try {
                $surat_data = $this->getSuratData($surat_id);
                if (!$surat_data) {
                    $this->log("Skipping surat {$surat_id} - no data");
                    continue;
                }
                
                foreach ($surat_data['ayat'] as $ayat) {
                    if (!isset($ayat['audio'][$this->qari_id])) {
                        continue;
                    }
                    
                    $ayat_id = $ayat['nomorAyat'];
                    $audio_url = $ayat['audio'][$this->qari_id];
                    $local_file = $this->audio_dir . "surat_{$surat_id}_ayat_{$ayat_id}.mp3";
                    
                    // Skip if file already exists
                    if (file_exists($local_file) && filesize($local_file) > 1000) {
                        $total_skipped++;
                        continue;
                    }
                    
                    if ($this->downloadFile($audio_url, $local_file)) {
                        $total_downloaded++;
                        echo "✓ Surat {$surat_id} Ayat {$ayat_id}\n";
                    } else {
                        $total_failed++;
                        echo "✗ Surat {$surat_id} Ayat {$ayat_id}\n";
                    }
                    
                    // Small delay
                    usleep(200000);
                }
                
                // Longer delay between surat
                sleep(1);
                
            } catch (Exception $e) {
                $this->log("Error processing surat {$surat_id}: " . $e->getMessage());
                continue;
            }
        }
        
        $this->log("Bulk download complete: {$total_downloaded} downloaded, {$total_skipped} skipped, {$total_failed} failed");
        
        return [
            'downloaded' => $total_downloaded,
            'skipped' => $total_skipped,
            'failed' => $total_failed
        ];
    }
    
    /**
     * Get surat data from cache or API
     */
    public function getSuratData($surat_id) {
        $cache_file = $this->cache_dir . "surat_{$surat_id}.json";
        
        // Try cache first
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data && isset($data['data'])) {
                return $data['data'];
            }
        }
        
        // Fetch from API
        try {
            $url = $this->base_url . "/surat/{$surat_id}";
            $response = $this->makeAPIRequest($url);
            
            if ($response && $response['code'] === 200) {
                // Save to cache
                file_put_contents($cache_file, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
                return $response['data'];
            }
        } catch (Exception $e) {
            $this->log("API request failed for surat {$surat_id}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Download a single file
     */
    public function downloadFile($url, $local_file) {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 60,
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
            
            // Verify it's actually an audio file
            if (strlen($audio_data) < 1000) {
                return false; // Too small to be a valid audio file
            }
            
            // Check for MP3 header or ID3 tag
            $header = substr($audio_data, 0, 10);
            if (strpos($header, 'ID3') !== 0 && 
                substr($header, 0, 2) !== "\xFF\xFB" && 
                substr($header, 0, 2) !== "\xFF\xFA") {
                return false;
            }
            
            return file_put_contents($local_file, $audio_data, LOCK_EX) !== false;
            
        } catch (Exception $e) {
            $this->log("Download error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Make API request
     */
    private function makeAPIRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
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
     * Get download statistics
     */
    public function getStats() {
        $total_files = 0;
        $downloaded_files = 0;
        $total_size = 0;
        
        // Count expected files (6236 ayat total)
        for ($surat = 1; $surat <= 114; $surat++) {
            $surat_info = $this->getSuratInfo($surat);
            if ($surat_info) {
                $total_files += $surat_info['ayat_count'];
            }
        }
        
        // Count downloaded files
        if (is_dir($this->audio_dir)) {
            $files = glob($this->audio_dir . 'surat_*_ayat_*.mp3');
            $downloaded_files = count($files);
            
            foreach ($files as $file) {
                $total_size += filesize($file);
            }
        }
        
        return [
            'total_expected' => $total_files,
            'downloaded' => $downloaded_files,
            'percentage' => $total_files > 0 ? round(($downloaded_files / $total_files) * 100, 2) : 0,
            'total_size_mb' => round($total_size / (1024 * 1024), 2)
        ];
    }
    
    /**
     * Get surat info (simplified)
     */
    private function getSuratInfo($surat_id) {
        $surat_info = [
            1 => ['name' => 'Al-Fatihah', 'ayat_count' => 7],
            2 => ['name' => 'Al-Baqarah', 'ayat_count' => 286],
            3 => ['name' => 'Ali \'Imran', 'ayat_count' => 200],
            // Add more as needed, or fetch from API
        ];
        
        // For now, use a simple calculation based on known total
        // In real implementation, you'd fetch this from the surat list API
        $ayat_counts = [7,286,200,176,120,165,206,75,129,109,123,111,43,52,99,128,111,110,98,135,112,78,118,64,77,227,93,88,69,60,34,30,73,54,45,83,182,88,75,85,54,53,89,59,37,35,38,29,18,45,60,49,62,55,78,96,29,22,24,13,14,11,11,18,12,12,30,52,52,44,28,28,20,56,40,31,50,40,46,42,29,19,36,25,22,17,19,26,30,20,15,21,11,8,8,19,5,8,8,11,11,8,3,9,5,4,7,3,6,3,5,4,5,6];
        
        if (isset($ayat_counts[$surat_id - 1])) {
            return [
                'name' => "Surat {$surat_id}",
                'ayat_count' => $ayat_counts[$surat_id - 1]
            ];
        }
        
        return null;
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
        
        // Also output to console if running from CLI
        if (php_sapi_name() === 'cli') {
            echo $log_message;
        }
    }
}

// Handle CLI or web requests
if (php_sapi_name() === 'cli') {
    // Command line interface
    $downloader = new AudioDownloader();
    
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'surat':
            $surat_id = (int)($argv[2] ?? 1);
            echo "Downloading surat {$surat_id}...\n";
            $downloader->downloadSurat($surat_id);
            break;
            
        case 'all':
            $start = (int)($argv[2] ?? 1);
            $end = (int)($argv[3] ?? 114);
            echo "Downloading all surat from {$start} to {$end}...\n";
            $result = $downloader->downloadAll($start, $end);
            echo "Complete! Downloaded: {$result['downloaded']}, Skipped: {$result['skipped']}, Failed: {$result['failed']}\n";
            break;
            
        case 'stats':
            $stats = $downloader->getStats();
            echo "Download Statistics:\n";
            echo "Expected files: {$stats['total_expected']}\n";
            echo "Downloaded: {$stats['downloaded']}\n";
            echo "Percentage: {$stats['percentage']}%\n";
            echo "Total size: {$stats['total_size_mb']} MB\n";
            break;
            
        default:
            echo "Usage:\n";
            echo "  php download_audio.php surat [surat_id]     - Download specific surat\n";
            echo "  php download_audio.php all [start] [end]   - Download range of surat\n";
            echo "  php download_audio.php stats               - Show download statistics\n";
    }
    
} else {
    // Web interface
    header('Content-Type: application/json');
    
    $downloader = new AudioDownloader();
    $action = $_GET['action'] ?? 'stats';
    
    switch ($action) {
        case 'stats':
            echo json_encode([
                'success' => true,
                'data' => $downloader->getStats()
            ]);
            break;
            
        case 'download_surat':
            $surat_id = (int)($_GET['surat_id'] ?? 0);
            if ($surat_id >= 1 && $surat_id <= 114) {
                $result = $downloader->downloadSurat($surat_id);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Download completed' : 'Download failed'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid surat ID'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}
?>