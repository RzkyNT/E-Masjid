<?php
/**
 * Settings Loader
 * Centralized settings management for the entire website
 * Loads settings from database and provides fallbacks
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/site_defaults.php';

/**
 * Load all website settings from database with fallbacks
 * This function should be called at the beginning of each page
 */
function loadWebsiteSettings() {
    global $pdo;
    
    // Start with default settings
    $settings = DEFAULT_SITE_SETTINGS;
    
    // Try to get settings from database
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IS NOT NULL AND setting_value IS NOT NULL");
            $stmt->execute();
            $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Override defaults with database values
            if ($db_settings) {
                $settings = array_merge($settings, $db_settings);
            }
        } catch (PDOException $e) {
            // Log error but continue with defaults
            error_log("Settings loading error: " . $e->getMessage());
        }
    }
    
    return $settings;
}

/**
 * Get a specific setting value
 */
function getWebsiteSetting($key, $default = '') {
    global $website_settings;
    
    // Load settings if not already loaded
    if (!isset($website_settings)) {
        $website_settings = loadWebsiteSettings();
    }
    
    return $website_settings[$key] ?? $default;
}

/**
 * Get social media links with proper URLs
 */
function getSocialMediaLinks() {
    return [
        'facebook' => getWebsiteSetting('social_facebook'),
        'instagram' => getWebsiteSetting('social_instagram'),
        'youtube' => getWebsiteSetting('social_youtube'),
        'twitter' => getWebsiteSetting('social_twitter'),
        'telegram' => getWebsiteSetting('social_telegram')
    ];
}

/**
 * Get contact information
 */
function getContactInfo() {
    return [
        'phone' => getWebsiteSetting('contact_phone'),
        'email' => getWebsiteSetting('contact_email'),
        'whatsapp' => getWebsiteSetting('contact_whatsapp'),
        'address' => getWebsiteSetting('masjid_address')
    ];
}

/**
 * Get masjid profile information
 */
function getMasjidProfile() {
    return [
        'name' => getWebsiteSetting('masjid_name'),
        'history' => getWebsiteSetting('masjid_history'),
        'vision' => getWebsiteSetting('masjid_vision'),
        'mission' => getWebsiteSetting('masjid_mission'),
        'address' => getWebsiteSetting('masjid_address')
    ];
}

/**
 * Get donation settings
 */
function getDonationSettings() {
    return [
        'account' => getWebsiteSetting('donation_account'),
        'qr_code' => getWebsiteSetting('donation_qr_code'),
        'categories' => getWebsiteSetting('donation_categories'),
        'transparency_text' => getWebsiteSetting('donation_transparency_text')
    ];
}

/**
 * Get prayer settings
 */
function getPrayerSettings() {
    return [
        'api_enabled' => getWebsiteSetting('prayer_api_enabled', '1'),
        'api_url' => getWebsiteSetting('prayer_api_url'),
        'city' => getWebsiteSetting('prayer_location_city'),
        'country' => getWebsiteSetting('prayer_location_country'),
        'calculation_method' => getWebsiteSetting('prayer_calculation_method', '2')
    ];
}

/**
 * Format WhatsApp link with message
 */
function getWhatsAppLink($message = '') {
    $phone = getWebsiteSetting('contact_whatsapp');
    if (empty($phone)) {
        return '#';
    }
    
    $encoded_message = urlencode($message);
    return "https://wa.me/{$phone}" . ($message ? "?text={$encoded_message}" : '');
}

/**
 * Get Google Maps link
 */
function getGoogleMapsLink() {
    $coordinates = getWebsiteSetting('location_coordinates');
    if (empty($coordinates)) {
        return '#';
    }
    
    return "https://maps.google.com/maps?q={$coordinates}";
}

/**
 * Get site logo URL
 */
function getSiteLogo() {
    $logo = getWebsiteSetting('site_logo');
    if (empty($logo) || !file_exists($logo)) {
        return 'assets/images/logo-default.png'; // fallback logo
    }
    
    return $logo;
}

/**
 * Initialize settings for the current page
 * Call this function at the beginning of each page
 */
function initializePageSettings() {
    global $website_settings;
    
    // Load all settings
    $website_settings = loadWebsiteSettings();
    
    // Make settings available to the page
    return $website_settings;
}

// Auto-initialize settings when this file is included
if (!isset($website_settings)) {
    $website_settings = loadWebsiteSettings();
}

/**
 * Get DKM structure from settings
 */
function getDKMStructure() {
    return [
        'ketua' => [
            'name' => getWebsiteSetting('dkm_ketua', 'H. Ahmad Suryadi, S.Pd'),
            'position' => 'Ketua DKM',
            'description' => 'Memimpin dan mengkoordinasikan seluruh kegiatan masjid',
            'color' => 'green'
        ],
        'wakil_ketua' => [
            'name' => getWebsiteSetting('dkm_wakil_ketua', 'Drs. Muhammad Yusuf'),
            'position' => 'Wakil Ketua',
            'description' => 'Membantu ketua dalam menjalankan program masjid',
            'color' => 'blue'
        ],
        'sekretaris' => [
            'name' => getWebsiteSetting('dkm_sekretaris', 'Siti Aminah, S.Kom'),
            'position' => 'Sekretaris',
            'description' => 'Mengelola administrasi dan dokumentasi kegiatan',
            'color' => 'purple'
        ],
        'bendahara' => [
            'name' => getWebsiteSetting('dkm_bendahara', 'Abdul Rahman, S.E'),
            'position' => 'Bendahara',
            'description' => 'Mengelola keuangan dan aset masjid',
            'color' => 'orange'
        ],
        'sie_ibadah' => [
            'name' => getWebsiteSetting('dkm_sie_ibadah', 'Ustadz Faisal Hakim'),
            'position' => 'Koordinator Dakwah',
            'description' => 'Mengkoordinasikan kegiatan dakwah dan kajian',
            'color' => 'teal'
        ],
        'sie_pendidikan' => [
            'name' => getWebsiteSetting('dkm_sie_pendidikan', 'Hj. Fatimah, S.Pd.I'),
            'position' => 'Koordinator Pendidikan',
            'description' => 'Mengelola program bimbel dan pendidikan',
            'color' => 'pink'
        ]
    ];
}
?>