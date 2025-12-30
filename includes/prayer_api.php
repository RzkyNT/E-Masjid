<?php
/**
 * Prayer Times API Integration
 * For Masjid Al-Muhajirin Information System
 */

require_once __DIR__ . '/../config/site_defaults.php';

class PrayerTimeAPI {
    private $api_url = 'https://api.myquran.com/v2/sholat/jadwal/1204/';
    private $fallback_times;
    
    public function __construct() {
        $this->fallback_times = getFallbackPrayerTimes();
    }
    
    /**
     * Get today's prayer times
     */
    public function getTodayPrayerTimes() {
        $today = date('Y/m/d');
        
        try {
            // Try to get from API
            $response = $this->makeAPIRequest($today);
            
            if ($response && isset($response['data']['jadwal'])) {
                $jadwal = $response['data']['jadwal'];
                
                return [
                    'times' => [
                        'imsak' => $jadwal['imsak'] ?? $this->fallback_times['imsak'],
                        'fajr' => $jadwal['subuh'] ?? $this->fallback_times['fajr'],
                        'sunrise' => $jadwal['terbit'] ?? $this->fallback_times['sunrise'],
                        'dhuha' => $jadwal['dhuha'] ?? $this->fallback_times['dhuha'],
                        'dhuhr' => $jadwal['dzuhur'] ?? $this->fallback_times['dhuhr'],
                        'asr' => $jadwal['ashar'] ?? $this->fallback_times['asr'],
                        'maghrib' => $jadwal['maghrib'] ?? $this->fallback_times['maghrib'],
                        'isha' => $jadwal['isya'] ?? $this->fallback_times['isha']
                    ],
                    'location' => $response['data']['lokasi'] ?? getSiteSetting('location_name'),
                    'formatted_date' => date('l, d F Y')
                ];
            }
        } catch (Exception $e) {
            // Log error but continue with fallback
            error_log("Prayer API Error: " . $e->getMessage());
        }
        
        // Return fallback times
        return [
            'times' => $this->fallback_times,
            'location' => getSiteSetting('location_name'),
            'formatted_date' => date('l, d F Y'),
            'fallback' => true
        ];
    }
    
    /**
     * Get monthly prayer times
     */
    public function getMonthlyPrayerTimes($year, $month) {
        try {
            $monthly_times = [];
            $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
            
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf('%04d/%02d/%02d', $year, $month, $day);
                $response = $this->makeAPIRequest($date);
                
                if ($response && isset($response['data']['jadwal'])) {
                    $jadwal = $response['data']['jadwal'];
                    $monthly_times[sprintf('%04d-%02d-%02d', $year, $month, $day)] = [
                        'imsak' => $jadwal['imsak'] ?? $this->fallback_times['imsak'],
                        'fajr' => $jadwal['subuh'] ?? $this->fallback_times['fajr'],
                        'sunrise' => $jadwal['terbit'] ?? $this->fallback_times['sunrise'],
                        'dhuha' => $jadwal['dhuha'] ?? $this->fallback_times['dhuha'],
                        'dhuhr' => $jadwal['dzuhur'] ?? $this->fallback_times['dhuhr'],
                        'asr' => $jadwal['ashar'] ?? $this->fallback_times['asr'],
                        'maghrib' => $jadwal['maghrib'] ?? $this->fallback_times['maghrib'],
                        'isha' => $jadwal['isya'] ?? $this->fallback_times['isha']
                    ];
                } else {
                    // Use fallback for this day
                    $monthly_times[sprintf('%04d-%02d-%02d', $year, $month, $day)] = $this->fallback_times;
                }
            }
            
            return $monthly_times;
        } catch (Exception $e) {
            error_log("Monthly Prayer API Error: " . $e->getMessage());
            
            // Return fallback for entire month
            $monthly_times = [];
            $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
            
            for ($day = 1; $day <= $days_in_month; $day++) {
                $monthly_times[sprintf('%04d-%02d-%02d', $year, $month, $day)] = $this->fallback_times;
            }
            
            return $monthly_times;
        }
    }
    
    /**
     * Make API request with timeout and error handling
     */
    private function makeAPIRequest($date) {
        $url = $this->api_url . $date;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5, // 5 second timeout
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; Masjid-Al-Muhajirin/1.0)',
                    'Accept: application/json'
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $data;
    }
}

// Static class for backward compatibility
class PrayerTimesAPI {
    public static function getTodayPrayerTimes() {
        $api = new PrayerTimeAPI();
        return $api->getTodayPrayerTimes();
    }
    
    public static function getMonthlyPrayerTimes($year, $month) {
        $api = new PrayerTimeAPI();
        return $api->getMonthlyPrayerTimes($year, $month);
    }
}
?>