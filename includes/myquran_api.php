<?php
/**
 * MyQuran API Integration Class
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides integration with MyQuran API v2 for Hadits, Doa, and Asmaul Husna content
 * Includes caching, rate limiting, and error handling
 * 
 * Requirements: 4.1, 4.2, 4.4, 4.5, 4.8
 */

class MyQuranAPI {
    private $baseUrl = 'https://api.myquran.com/v2';
    private $cache;
    private $rateLimiter;
    private $timeout = 10; // seconds
    
    public function __construct() {
        $this->cache = new MyQuranCacheManager();
        $this->rateLimiter = new APIRateLimiter();
    }
    
    /**
     * Get Hadits Arbain by number (1-42)
     * Requirements: 1.3, 4.1
     */
    public function getHaditsArbain(int $nomor): array {
        if ($nomor < 1 || $nomor > 42) {
            throw new InvalidArgumentException("Nomor hadits arbain harus antara 1-42");
        }
        
        $cacheKey = "hadits_arbain_{$nomor}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/hadits/arbain/{$nomor}";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response);
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch hadits arbain #{$nomor}");
    }
    
    /**
     * Get Hadits Bulughul Maram by number (1-1597)
     * Requirements: 1.4, 4.1
     */
    public function getHaditsBulughulMaram(int $nomor): array {
        if ($nomor < 1 || $nomor > 1597) {
            throw new InvalidArgumentException("Nomor hadits bulughul maram harus antara 1-1597");
        }
        
        $cacheKey = "hadits_bm_{$nomor}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/hadits/bm/{$nomor}";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response);
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch hadits bulughul maram #{$nomor}");
    }
    
    /**
     * Get Hadits from specific narrator
     * Requirements: 1.5, 4.1
     */
    public function getHaditsPerawi(string $slug, int $nomor): array {
        if (empty($slug) || $nomor < 1) {
            throw new InvalidArgumentException("Slug perawi dan nomor hadits harus valid");
        }
        
        $cacheKey = "hadits_{$slug}_{$nomor}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/hadits/{$slug}/{$nomor}";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response);
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch hadits {$slug} #{$nomor}");
    }
    
    /**
     * Get random Hadits from various sources
     * Requirements: 1.7, 4.1
     */
    public function getRandomHadits(): array {
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        // Try different random endpoints
        $endpoints = [
            '/hadits/bm/acak',
            '/hadits/perawi/acak'
        ];
        
        $url = $this->baseUrl . $endpoints[array_rand($endpoints)];
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch random hadits");
    }
    
    /**
     * Get Doa by ID (1-108)
     * Requirements: 2.2, 4.1
     */
    public function getDoa(int $id): array {
        if ($id < 1 || $id > 108) {
            throw new InvalidArgumentException("ID doa harus antara 1-108");
        }
        
        $cacheKey = "doa_{$id}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/doa/{$id}";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response);
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch doa #{$id}");
    }
    
    /**
     * Get random Doa
     * Requirements: 2.5, 4.1
     */
    public function getRandomDoa(): array {
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/doa/acak";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch random doa");
    }
    
    /**
     * Get Doa sources list
     * Requirements: 2.4, 4.1
     */
    public function getDoaSumber(): array {
        $cacheKey = "doa_sumber";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/doa/sumber";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response, 86400 * 7); // Cache for 7 days
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch doa sources");
    }
    
    /**
     * Get Asmaul Husna by number (1-99)
     * Requirements: 3.2, 4.1
     */
    public function getAsmaulHusna(int $nomor): array {
        if ($nomor < 1 || $nomor > 99) {
            throw new InvalidArgumentException("Nomor Asmaul Husna harus antara 1-99");
        }
        
        $cacheKey = "husna_{$nomor}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/husna/{$nomor}";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response);
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch Asmaul Husna #{$nomor}");
    }
    
    /**
     * Get random Asmaul Husna
     * Requirements: 3.4, 4.1
     */
    public function getRandomAsmaulHusna(): array {
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/husna/acak";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch random Asmaul Husna");
    }
    
    /**
     * Get all Asmaul Husna (1-99)
     * Requirements: 3.2, 4.1
     */
    public function getAllAsmaulHusna(): array {
        $cacheKey = "husna_all";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }
        
        $url = "{$this->baseUrl}/husna/semua";
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['status']) && $response['status'] === true) {
            $this->cache->set($cacheKey, $response, 86400 * 7); // Cache for 7 days
            $this->rateLimiter->recordRequest();
            return $response;
        }
        
        throw new Exception("Failed to fetch all Asmaul Husna");
    }
    
    /**
     * Make HTTP request to API
     * Requirements: 4.8
     */
    private function makeRequest(string $url): ?array {
        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'method' => 'GET',
                'header' => [
                    'User-Agent: Masjid-Al-Muhajirin/1.0',
                    'Accept: application/json'
                ]
            ]
        ]);
        
        try {
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                error_log("MyQuran API request failed: {$url}");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("MyQuran API invalid JSON response: {$url}");
                return null;
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("MyQuran API exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get API usage statistics
     * Requirements: 4.5
     */
    public function getUsageStats(): array {
        return [
            'remaining_requests' => $this->rateLimiter->getRemainingRequests(),
            'reset_time' => $this->rateLimiter->getResetTime(),
            'cache_stats' => $this->cache->getStats()
        ];
    }
}

/**
 * Cache Manager for MyQuran API responses
 * Requirements: 4.2, 4.7
 */
class MyQuranCacheManager {
    private $cacheDir;
    private $defaultTTL = 86400; // 24 hours
    
    public function __construct() {
        $this->cacheDir = __DIR__ . '/../cache/myquran/';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data
     * Requirements: 4.2
     */
    public function get(string $key): ?array {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        if (!$data || !isset($data['expires']) || !isset($data['content'])) {
            return null;
        }
        
        if (time() > $data['expires']) {
            unlink($filename);
            return null;
        }
        
        return $data['content'];
    }
    
    /**
     * Set cached data
     * Requirements: 4.2
     */
    public function set(string $key, array $data, ?int $ttl = null): bool {
        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getCacheFilename($key);
        
        $cacheData = [
            'expires' => time() + $ttl,
            'content' => $data,
            'created' => time()
        ];
        
        return file_put_contents($filename, json_encode($cacheData)) !== false;
    }
    
    /**
     * Delete cached data
     * Requirements: 4.7
     */
    public function delete(string $key): bool {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     * Requirements: 4.7
     */
    public function clear(): bool {
        $files = glob($this->cacheDir . '*.json');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Check if cache key is expired
     * Requirements: 4.7
     */
    public function isExpired(string $key): bool {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return true;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        if (!$data || !isset($data['expires'])) {
            return true;
        }
        
        return time() > $data['expires'];
    }
    
    /**
     * Get cache statistics
     * Requirements: 4.5
     */
    public function getStats(): array {
        $files = glob($this->cacheDir . '*.json');
        $totalSize = 0;
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['expires']) && time() > $data['expires']) {
                $expiredCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'expired_files' => $expiredCount,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Get cache filename for key
     */
    private function getCacheFilename(string $key): string {
        return $this->cacheDir . md5($key) . '.json';
    }
}

/**
 * Rate Limiter for API requests
 * Requirements: 4.4
 */
class APIRateLimiter {
    private $maxRequests = 200; // Increased from 100 to 200
    private $timeWindow = 3600; // 1 hour
    private $storageFile;
    
    public function __construct() {
        $this->storageFile = __DIR__ . '/../cache/api_rate_limit.json';
        
        // Create cache directory if it doesn't exist
        $cacheDir = dirname($this->storageFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }
    
    /**
     * Check if request can be made
     * Requirements: 4.4
     */
    public function canMakeRequest(): bool {
        $data = $this->loadData();
        $currentTime = time();
        
        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->timeWindow;
        });
        
        return count($data['requests']) < $this->maxRequests;
    }
    
    /**
     * Record a request
     * Requirements: 4.4
     */
    public function recordRequest(): void {
        $data = $this->loadData();
        $data['requests'][] = time();
        $this->saveData($data);
    }
    
    /**
     * Get remaining requests
     * Requirements: 4.4
     */
    public function getRemainingRequests(): int {
        $data = $this->loadData();
        $currentTime = time();
        
        // Clean old requests
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->timeWindow;
        });
        
        return max(0, $this->maxRequests - count($data['requests']));
    }
    
    /**
     * Get reset time
     * Requirements: 4.4
     */
    public function getResetTime(): int {
        $data = $this->loadData();
        
        if (empty($data['requests'])) {
            return time();
        }
        
        $oldestRequest = min($data['requests']);
        return $oldestRequest + $this->timeWindow;
    }
    
    /**
     * Load rate limit data
     */
    private function loadData(): array {
        if (!file_exists($this->storageFile)) {
            return ['requests' => []];
        }
        
        $data = json_decode(file_get_contents($this->storageFile), true);
        return $data ?: ['requests' => []];
    }
    
    /**
     * Save rate limit data
     */
    private function saveData(array $data): void {
        file_put_contents($this->storageFile, json_encode($data));
    }
}
?>