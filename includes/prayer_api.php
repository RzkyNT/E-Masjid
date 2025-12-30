<?php
/**
 * Prayer Time API Handler
 * Using MyQuran API for accurate prayer times
 */

class PrayerTimeAPI {
    private $base_url = 'https://api.myquran.com/v2/sholat/jadwal';
    private $city_id = 1203; // Bekasi
    private $cache_duration = 3600; // 1 hour cache
    
    /**
     * Get today's prayer times
     */
    public function getTodayPrayerTimes($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $cache_key = "prayer_today_{$date}";
        $cached_data = $this->getFromCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = "{$this->base_url}/{$this->city_id}/{$date}";
        $response = $this->makeRequest($url);
        
        if ($response && $response['status']) {
            $prayer_data = $this->formatTodayData($response['data']);
            $this->saveToCache($cache_key, $prayer_data, $this->cache_duration);
            return $prayer_data;
        }
        
        return $this->getFallbackData();
    }
    
    /**
     * Get monthly prayer times
     */
    public function getMonthlyPrayerTimes($year = null, $month = null) {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('n');
        
        $cache_key = "prayer_monthly_{$year}_{$month}";
        $cached_data = $this->getFromCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = "{$this->base_url}/{$this->city_id}/{$year}/{$month}";
        $response = $this->makeRequest($url);
        
        if ($response && $response['status']) {
            $prayer_data = $this->formatMonthlyData($response['data']);
            // Cache monthly data for longer (24 hours)
            $this->saveToCache($cache_key, $prayer_data, 86400);
            return $prayer_data;
        }
        
        return $this->getFallbackMonthlyData($year, $month);
    }
    
    /**
     * Make HTTP request with error handling
     */
    private function makeRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; MasjidApp/1.0)',
                    'Accept: application/json'
                ]
            ]
        ]);
        
        try {
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                error_log("Prayer API request failed: $url");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Prayer API JSON decode error: " . json_last_error_msg());
                return null;
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Prayer API exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Format today's prayer data
     */
    private function formatTodayData($data) {
        $jadwal = $data['jadwal'];
        
        return [
            'location' => $data['lokasi'] . ', ' . $data['daerah'],
            'date' => $jadwal['date'],
            'formatted_date' => $jadwal['tanggal'],
            'times' => [
                'imsak' => $jadwal['imsak'],
                'fajr' => $jadwal['subuh'],
                'sunrise' => $jadwal['terbit'],
                'dhuha' => $jadwal['dhuha'],
                'dhuhr' => $jadwal['dzuhur'],
                'asr' => $jadwal['ashar'],
                'maghrib' => $jadwal['maghrib'],
                'isha' => $jadwal['isya']
            ]
        ];
    }
    
    /**
     * Format monthly prayer data
     */
    private function formatMonthlyData($data) {
        $formatted = [
            'location' => $data['lokasi'] . ', ' . $data['daerah'],
            'schedule' => []
        ];
        
        foreach ($data['jadwal'] as $day) {
            $formatted['schedule'][$day['date']] = [
                'formatted_date' => $day['tanggal'],
                'times' => [
                    'imsak' => $day['imsak'],
                    'fajr' => $day['subuh'],
                    'sunrise' => $day['terbit'],
                    'dhuha' => $day['dhuha'],
                    'dhuhr' => $day['dzuhur'],
                    'asr' => $day['ashar'],
                    'maghrib' => $day['maghrib'],
                    'isha' => $day['isya']
                ]
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get fallback data when API fails
     */
    private function getFallbackData() {
        return [
            'location' => 'KAB. BEKASI, JAWA BARAT',
            'date' => date('Y-m-d'),
            'formatted_date' => date('l, d/m/Y'),
            'times' => [
                'imsak' => '04:06',
                'fajr' => '04:16',
                'sunrise' => '05:36',
                'dhuha' => '06:05',
                'dhuhr' => '11:57',
                'asr' => '15:23',
                'maghrib' => '18:12',
                'isha' => '19:27'
            ],
            'fallback' => true
        ];
    }
    
    /**
     * Get fallback monthly data
     */
    private function getFallbackMonthlyData($year, $month) {
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $schedule = [];
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $schedule[$date] = [
                'formatted_date' => date('l, d/m/Y', strtotime($date)),
                'times' => [
                    'imsak' => '04:06',
                    'fajr' => '04:16',
                    'sunrise' => '05:36',
                    'dhuha' => '06:05',
                    'dhuhr' => '11:57',
                    'asr' => '15:23',
                    'maghrib' => '18:12',
                    'isha' => '19:27'
                ]
            ];
        }
        
        return [
            'location' => 'KAB. BEKASI, JAWA BARAT',
            'schedule' => $schedule,
            'fallback' => true
        ];
    }
    
    /**
     * Simple file-based caching
     */
    private function getFromCache($key) {
        $cache_file = $this->getCacheFile($key);
        
        if (!file_exists($cache_file)) {
            return null;
        }
        
        $cache_data = json_decode(file_get_contents($cache_file), true);
        
        if (!$cache_data || $cache_data['expires'] < time()) {
            @unlink($cache_file);
            return null;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Save data to cache
     */
    private function saveToCache($key, $data, $duration) {
        $cache_dir = 'cache';
        if (!is_dir($cache_dir)) {
            @mkdir($cache_dir, 0755, true);
        }
        
        $cache_file = $this->getCacheFile($key);
        $cache_data = [
            'data' => $data,
            'expires' => time() + $duration
        ];
        
        @file_put_contents($cache_file, json_encode($cache_data), LOCK_EX);
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        return 'cache/prayer_' . md5($key) . '.json';
    }
    
    /**
     * Calculate next prayer time
     */
    public function getNextPrayer($prayer_times) {
        $now = new DateTime();
        $current_time = $now->format('H:i');
        
        $prayers = [
            'Imsak' => $prayer_times['imsak'],
            'Subuh' => $prayer_times['fajr'],
            'Terbit' => $prayer_times['sunrise'],
            'Dhuha' => $prayer_times['dhuha'],
            'Dzuhur' => $prayer_times['dhuhr'],
            'Ashar' => $prayer_times['asr'],
            'Maghrib' => $prayer_times['maghrib'],
            'Isya' => $prayer_times['isha']
        ];
        
        foreach ($prayers as $name => $time) {
            if ($time > $current_time) {
                return [
                    'name' => $name,
                    'time' => $time,
                    'countdown' => $this->calculateCountdown($time)
                ];
            }
        }
        
        // If no prayer found today, next is Imsak tomorrow
        return [
            'name' => 'Imsak',
            'time' => $prayer_times['imsak'],
            'countdown' => $this->calculateCountdown($prayer_times['imsak'], true)
        ];
    }
    
    /**
     * Calculate countdown to prayer time
     */
    private function calculateCountdown($prayer_time, $tomorrow = false) {
        $now = new DateTime();
        $prayer = new DateTime();
        
        list($hours, $minutes) = explode(':', $prayer_time);
        $prayer->setTime($hours, $minutes);
        
        if ($tomorrow) {
            $prayer->add(new DateInterval('P1D'));
        }
        
        $diff = $prayer->diff($now);
        
        if ($diff->h > 0) {
            return $diff->h . ' jam ' . $diff->i . ' menit';
        } else {
            return $diff->i . ' menit';
        }
    }
    
    /**
     * Clean old cache files
     */
    public function cleanCache() {
        $cache_dir = 'cache';
        if (!is_dir($cache_dir)) {
            return;
        }
        
        $files = glob($cache_dir . '/prayer_*.json');
        foreach ($files as $file) {
            $cache_data = json_decode(file_get_contents($file), true);
            if ($cache_data && $cache_data['expires'] < time()) {
                @unlink($file);
            }
        }
    }
}
?>