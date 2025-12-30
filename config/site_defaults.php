<?php
/**
 * Site Default Configuration
 * This file contains default values that are used as fallbacks when database is not available
 */

// Default site settings
define('DEFAULT_SITE_SETTINGS', [
    'site_name' => 'Masjid Jami Al-Muhajirin',
    'site_description' => 'Website resmi Masjid Jami Al-Muhajirin',
    'masjid_address' => 'Q2X5+P3M, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara, Kota Bekasi',
    'contact_phone' => '021-12345678',
    'contact_email' => 'info@almuhajirin.com',
    'donation_account' => 'Bank Mandiri: 1234567890 a.n. DKM Al-Muhajirin',
    
    // Location information
    'location_name' => 'Bekasi Utara, Jawa Barat',
    'coordinates_lat' => '-6.2088',
    'coordinates_lng' => '107.0139',
    'timezone' => 'WIB (UTC+7)',
    'qibla_direction' => '295° dari Utara',
    
    // Schedule information
    'kajian_minggu' => '08:00-10:00 WIB',
    'kajian_rabu' => '20:00-21:30 WIB',
    'jumat_time' => '12:00 WIB',
    
    // Prayer time fallbacks
    'fallback_prayer_times' => [
        'imsak' => '04:20',
        'fajr' => '04:30',
        'sunrise' => '05:45',
        'dhuha' => '06:30',
        'dhuhr' => '12:15',
        'asr' => '15:30',
        'maghrib' => '18:45',
        'isha' => '20:00'
    ]
]);

/**
 * Get site setting with fallback
 */
function getSiteSetting($key, $default = null) {
    global $pdo;
    
    // Try to get from database first
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetchColumn();
            
            if ($result !== false) {
                return $result;
            }
        } catch (PDOException $e) {
            // Fall through to default
        }
    }
    
    // Use default from constants
    $defaults = DEFAULT_SITE_SETTINGS;
    return $defaults[$key] ?? $default;
}

/**
 * Get all site settings with fallbacks
 */
function getAllSiteSettings() {
    global $pdo;
    
    $settings = DEFAULT_SITE_SETTINGS;
    
    // Try to get from database and override defaults
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
            $stmt->execute();
            $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Override defaults with database values
            $settings = array_merge($settings, $db_settings);
        } catch (PDOException $e) {
            // Use defaults only
        }
    }
    
    return $settings;
}

/**
 * Get fallback prayer times
 */
function getFallbackPrayerTimes() {
    return DEFAULT_SITE_SETTINGS['fallback_prayer_times'];
}
?>