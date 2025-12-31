<?php
/**
 * Al-Quran Cache Management
 * For Masjid Al-Muhajirin Information System
 */

class AlQuranCache {
    private $cache_dir;
    private $default_duration = 86400; // 24 hours
    
    public function __construct() {
        $this->cache_dir = __DIR__ . '/../api/cache/';
        
        // Ensure cache directory exists
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     * @param string $key Cache key
     * @return array|null
     */
    public function get($key) {
        $cache_file = $this->getCacheFilePath($key);
        
        if (!$this->isValid($cache_file)) {
            return null;
        }
        
        try {
            $cached_data = file_get_contents($cache_file);
            $data = json_decode($cached_data, true);
            
            if ($data && json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        } catch (Exception $e) {
            $this->logError("Cache read error for key {$key}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Set cached data
     * @param string $key Cache key
     * @param array $data Data to cache
     * @param int $duration Cache duration in seconds (optional)
     * @return bool
     */
    public function set($key, $data, $duration = null) {
        if ($duration === null) {
            $duration = $this->default_duration;
        }
        
        $cache_file = $this->getCacheFilePath($key);
        
        try {
            // Add cache metadata
            $cache_data = [
                'data' => $data,
                'cached_at' => time(),
                'expires_at' => time() + $duration,
                'cache_key' => $key
            ];
            
            $json_data = json_encode($cache_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if (file_put_contents($cache_file, $json_data, LOCK_EX) !== false) {
                return true;
            }
        } catch (Exception $e) {
            $this->logError("Cache write error for key {$key}: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Delete cached data
     * @param string $key Cache key
     * @return bool
     */
    public function delete($key) {
        $cache_file = $this->getCacheFilePath($key);
        
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        
        return true;
    }
    
    /**
     * Check if cache is valid (exists and not expired)
     * @param string $cache_file Path to cache file
     * @return bool
     */
    public function isValid($cache_file) {
        if (!file_exists($cache_file)) {
            return false;
        }
        
        try {
            $cached_data = file_get_contents($cache_file);
            $cache_info = json_decode($cached_data, true);
            
            if ($cache_info && isset($cache_info['expires_at'])) {
                return time() < $cache_info['expires_at'];
            }
            
            // Fallback to file modification time
            return (time() - filemtime($cache_file)) < $this->default_duration;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Clean expired cache files
     * @return int Number of files cleaned
     */
    public function cleanExpired() {
        $cleaned = 0;
        
        try {
            $files = glob($this->cache_dir . 'quran_*.json');
            
            foreach ($files as $file) {
                if (!$this->isValid($file)) {
                    if (unlink($file)) {
                        $cleaned++;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logError("Cache cleanup error: " . $e->getMessage());
        }
        
        return $cleaned;
    }
    
    /**
     * Get cache statistics
     * @return array
     */
    public function getStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'valid_files' => 0,
            'expired_files' => 0,
            'cache_dir' => $this->cache_dir
        ];
        
        try {
            $files = glob($this->cache_dir . 'quran_*.json');
            $stats['total_files'] = count($files);
            
            foreach ($files as $file) {
                $stats['total_size'] += filesize($file);
                
                if ($this->isValid($file)) {
                    $stats['valid_files']++;
                } else {
                    $stats['expired_files']++;
                }
            }
            
            $stats['total_size_mb'] = round($stats['total_size'] / 1024 / 1024, 2);
        } catch (Exception $e) {
            $this->logError("Cache stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Clear all Al-Quran cache files
     * @return int Number of files cleared
     */
    public function clearAll() {
        $cleared = 0;
        
        try {
            $files = glob($this->cache_dir . 'quran_*.json');
            
            foreach ($files as $file) {
                if (unlink($file)) {
                    $cleared++;
                }
            }
        } catch (Exception $e) {
            $this->logError("Cache clear error: " . $e->getMessage());
        }
        
        return $cleared;
    }
    
    /**
     * Get cache file path for a key
     * @param string $key Cache key
     * @return string
     */
    private function getCacheFilePath($key) {
        $safe_key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cache_dir . 'quran_' . $safe_key . '.json';
    }
    
    /**
     * Generate cache key from URL or parameters
     * @param string $url_or_params URL or parameter string
     * @return string
     */
    public static function generateKey($url_or_params) {
        return md5($url_or_params);
    }
    
    /**
     * Log error message
     * @param string $message Error message
     */
    private function logError($message) {
        $log_file = __DIR__ . '/../logs/alquran_cache.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] CACHE ERROR: {$message}" . PHP_EOL;
        
        try {
            // Ensure logs directory exists
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Fallback to PHP error log
            error_log("Al-Quran Cache Error: {$message}");
        }
    }
}

// Helper functions for easy access
function getAlQuranCache() {
    static $cache = null;
    if ($cache === null) {
        $cache = new AlQuranCache();
    }
    return $cache;
}

/**
 * Quick cache functions
 */
function getCachedQuranData($key) {
    return getAlQuranCache()->get($key);
}

function setCachedQuranData($key, $data, $duration = null) {
    return getAlQuranCache()->set($key, $data, $duration);
}

function clearQuranCache() {
    return getAlQuranCache()->clearAll();
}

function cleanExpiredQuranCache() {
    return getAlQuranCache()->cleanExpired();
}

function getQuranCacheStats() {
    return getAlQuranCache()->getStats();
}
?>