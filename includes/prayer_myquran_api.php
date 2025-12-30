<?php
/**
 * MyQuran API Helper Functions
 * API Documentation: https://api.myquran.com/v2/sholat/jadwal/{city_id}/{year}/{month}/{day}
 */

// Bekasi City ID = 1203
define('BEKASI_CITY_ID', 1203);

/**
 * Get today's prayer schedule
 */
function getTodayPrayerSchedule() {
    $today = date('Y/m/d');
    $url = "https://api.myquran.com/v2/sholat/jadwal/" . BEKASI_CITY_ID . "/{$today}";
    
    return fetchPrayerData($url);
}

/**
 * Get monthly prayer schedule
 */
function getMonthlyPrayerSchedule($year = null, $month = null) {
    if (!$year) $year = date('Y');
    if (!$month) $month = date('m');
    
    $url = "https://api.myquran.com/v2/sholat/jadwal/" . BEKASI_CITY_ID . "/{$year}/{$month}";
    
    return fetchPrayerData($url);
}

/**
 * Get specific date prayer schedule
 */
function getDatePrayerSchedule($date) {
    $formatted_date = date('Y/m/d', strtotime($date));
    $url = "https://api.myquran.com/v2/sholat/jadwal/" . BEKASI_CITY_ID . "/{$formatted_date}";
    
    return fetchPrayerData($url);
}

/**
 * Fetch data from MyQuran API with caching
 */
function fetchPrayerData($url) {
    // Create cache key from URL
    $cache_key = 'prayer_' . md5($url);
    $cache_file = __DIR__ . '/../api/cache/' . $cache_key . '.json';
    
    // Check if cache exists and is less than 1 hour old
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
        $cached_data = file_get_contents($cache_file);
        return json_decode($cached_data, true);
    }
    
    // Fetch from API
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Masjid Al-Muhajirin Website'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to fetch data from API');
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !$data['status']) {
            throw new Exception('Invalid API response');
        }
        
        // Save to cache
        if (!is_dir(dirname($cache_file))) {
            mkdir(dirname($cache_file), 0755, true);
        }
        file_put_contents($cache_file, $response);
        
        return $data;
        
    } catch (Exception $e) {
        error_log("Prayer API Error: " . $e->getMessage());
        
        // Return fallback data if API fails
        return getFallbackPrayerData();
    }
}

/**
 * Get fallback prayer data when API is unavailable
 */
function getFallbackPrayerData() {
    require_once __DIR__ . '/../config/site_defaults.php';
    
    $fallback_times = getFallbackPrayerTimes();
    $location = getSiteSetting('location_name');
    
    return [
        'status' => false,
        'data' => [
            'lokasi' => 'KAB. BEKASI',
            'daerah' => 'JAWA BARAT',
            'jadwal' => [
                'tanggal' => date('l, d/m/Y'),
                'imsak' => $fallback_times['imsak'],
                'subuh' => $fallback_times['fajr'],
                'terbit' => $fallback_times['sunrise'],
                'dhuha' => $fallback_times['dhuha'],
                'dzuhur' => $fallback_times['dhuhr'],
                'ashar' => $fallback_times['asr'],
                'maghrib' => $fallback_times['maghrib'],
                'isya' => $fallback_times['isha'],
                'date' => date('Y-m-d')
            ]
        ],
        'fallback' => true
    ];
}

/**
 * Format prayer time for display
 */
function formatPrayerTime($time) {
    return date('H:i', strtotime($time));
}

/**
 * Get next prayer time
 */
function getNextPrayer($schedule) {
    if (!isset($schedule['jadwal'])) return null;
    
    $current_time = date('H:i');
    $prayers = [
        'Subuh' => $schedule['jadwal']['subuh'],
        'Dzuhur' => $schedule['jadwal']['dzuhur'],
        'Ashar' => $schedule['jadwal']['ashar'],
        'Maghrib' => $schedule['jadwal']['maghrib'],
        'Isya' => $schedule['jadwal']['isya']
    ];
    
    foreach ($prayers as $name => $time) {
        if ($current_time < $time) {
            return [
                'name' => $name,
                'time' => $time,
                'formatted_time' => formatPrayerTime($time)
            ];
        }
    }
    
    // If all prayers have passed, return tomorrow's Subuh
    return [
        'name' => 'Subuh',
        'time' => $schedule['jadwal']['subuh'],
        'formatted_time' => formatPrayerTime($schedule['jadwal']['subuh']),
        'tomorrow' => true
    ];
}

/**
 * Check if it's prayer time (within 5 minutes)
 */
function isPrayerTime($schedule) {
    if (!isset($schedule['jadwal'])) return false;
    
    $current_time = time();
    $prayers = [
        'subuh' => $schedule['jadwal']['subuh'],
        'dzuhur' => $schedule['jadwal']['dzuhur'],
        'ashar' => $schedule['jadwal']['ashar'],
        'maghrib' => $schedule['jadwal']['maghrib'],
        'isya' => $schedule['jadwal']['isya']
    ];
    
    foreach ($prayers as $name => $time) {
        $prayer_timestamp = strtotime(date('Y-m-d') . ' ' . $time);
        $diff = abs($current_time - $prayer_timestamp);
        
        if ($diff <= 300) { // 5 minutes
            return [
                'name' => ucfirst($name),
                'time' => $time,
                'is_now' => $diff <= 60 // within 1 minute
            ];
        }
    }
    
    return false;
}
?>