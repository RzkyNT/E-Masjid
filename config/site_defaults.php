<?php
/**
 * Site Default Configuration
 * This file contains default values that are used as fallbacks when database is not available
 * Enhanced with comprehensive website settings for Masjid Jami Al-Muhajirin
 */

// Default site settings
define('DEFAULT_SITE_SETTINGS', [
    // Basic site information
    'site_name' => 'Masjid Jami Al-Muhajirin',
    'site_tagline' => 'Masjid yang Memakmurkan Umat',
    'site_description' => 'Website resmi Masjid Jami Al-Muhajirin - Pusat ibadah dan pendidikan Islam di Bekasi Utara',
    
    // Masjid information
    'masjid_name' => 'Masjid Jami Al-Muhajirin',
    'masjid_address' => 'Q2X5+P3M, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara, Kota Bekasi, Jawa Barat 17124',
    'masjid_coordinates' => '-6.2008,107.0082',
    'masjid_established' => '2010',
    
    // Contact information
    'contact_phone' => '021-88888888',
    'contact_whatsapp' => '6281234567890',
    'contact_email' => 'info@almuhajirin.com',
    
    // Social media
    'social_facebook' => 'https://facebook.com/almuhajirinbekasi',
    'social_instagram' => 'https://instagram.com/almuhajirinbekasi',
    'social_youtube' => 'https://youtube.com/@almuhajirinbekasi',
    
    // Donation information
    'donation_bank_mandiri' => '1234567890',
    'donation_bank_bca' => '0987654321',
    'donation_bank_bni' => '1122334455',
    'donation_account_name' => 'DKM Masjid Jami Al-Muhajirin',
    'donation_qris' => 'assets/images/qris-donation.png',
    
    // Masjid profile content
    'masjid_history' => 'Masjid Jami Al-Muhajirin didirikan pada tahun 2010 atas prakarsa warga sekitar yang ingin memiliki tempat ibadah yang representatif. Pembangunan masjid ini dilakukan secara gotong royong dengan dukungan penuh dari masyarakat setempat.',
    'masjid_vision' => 'Menjadi masjid yang memakmurkan umat melalui kegiatan ibadah, pendidikan, dan sosial yang berkelanjutan.',
    'masjid_mission' => 'Menyelenggarakan kegiatan ibadah yang khusyuk dan berjamaah|Memberikan pendidikan Islam yang berkualitas|Mengembangkan kegiatan sosial untuk kesejahteraan umat|Membangun ukhuwah islamiyah yang kuat',
    
    // DKM Structure
    'dkm_ketua' => 'H. Ahmad Suryadi',
    'dkm_wakil_ketua' => 'H. Muhammad Ridwan',
    'dkm_sekretaris' => 'Ustadz Ahmad Fauzi, Lc.',
    'dkm_bendahara' => 'Hj. Siti Aminah',
    'dkm_sie_ibadah' => 'Ustadz Muhammad Ali',
    'dkm_sie_pendidikan' => 'Ustadz Ahmad Fauzi, Lc.',
    'dkm_sie_sosial' => 'H. Bambang Sutrisno',
    
    // Facilities
    'facilities_list' => 'Ruang sholat utama (kapasitas 500 jamaah)|Tempat wudhu pria dan wanita|Ruang kajian dan aula serbaguna|Perpustakaan mini|Tempat parkir yang luas|Kantor DKM|Ruang bimbel Al-Muhajirin',
    
    // Location information
    'location_name' => 'Bekasi Utara, Jawa Barat',
    'coordinates_lat' => '-6.2008',
    'coordinates_lng' => '107.0082',
    'timezone' => 'WIB (UTC+7)',
    'qibla_direction' => '295° dari Utara',
    
    // Schedule information
    'kajian_minggu' => '08:00-10:00 WIB',
    'kajian_rabu' => '20:00-21:30 WIB',
    'kajian_jumat' => '19:30-21:00 WIB (setelah Maghrib)',
    'jumat_time' => '12:00 WIB',
    
    // Prayer time settings
    'prayer_api_enabled' => '1',
    'prayer_api_city' => 'bekasi',
    'prayer_manual_adjustment' => '0',
    
    // Website settings
    'site_maintenance' => '0',
    'site_analytics' => '',
    'site_logo' => 'assets/images/logo-masjid.png',
    'site_favicon' => 'assets/images/favicon.png',
    
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
    ],
    
    // Content defaults
    'homepage_hero_title' => 'Selamat Datang di Masjid Jami Al-Muhajirin',
    'homepage_hero_subtitle' => 'Masjid yang Memakmurkan Umat di Bekasi Utara',
    'homepage_welcome_text' => 'Assalamu\'alaikum warahmatullahi wabarakatuh. Selamat datang di website resmi Masjid Jami Al-Muhajirin. Mari bergabung bersama kami dalam memakmurkan masjid dan meningkatkan ketaqwaan kepada Allah SWT.',
    
    // Navigation menu items
    'nav_menu_items' => [
        'Beranda' => 'index.php',
        'Profil' => 'pages/profil.php',
        'Jadwal Sholat' => 'pages/jadwal_sholat.php',
        'Berita' => 'pages/berita.php',
        'Galeri' => 'pages/galeri.php',
        'Donasi' => 'pages/donasi.php',
        'Kontak' => 'pages/kontak.php'
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

/**
 * Get navigation menu items
 */
function getNavigationMenu() {
    return DEFAULT_SITE_SETTINGS['nav_menu_items'];
}

/**
 * Get masjid facilities as array
 */
function getMasjidFacilities() {
    $facilities_string = getSiteSetting('facilities_list');
    return explode('|', $facilities_string);
}

/**
 * Get masjid mission as array
 */
function getMasjidMission() {
    $mission_string = getSiteSetting('masjid_mission');
    return explode('|', $mission_string);
}

/**
 * Get donation accounts as array
 */
function getDonationAccounts() {
    return [
        'Bank Mandiri' => getSiteSetting('donation_bank_mandiri'),
        'Bank BCA' => getSiteSetting('donation_bank_bca'),
        'Bank BNI' => getSiteSetting('donation_bank_bni')
    ];
}

/**
 * Get social media links as array (legacy function)
 */
function getLegacySocialMediaLinks() {
    return [
        'facebook' => getSiteSetting('social_facebook'),
        'instagram' => getSiteSetting('social_instagram'),
        'youtube' => getSiteSetting('social_youtube')
    ];
}

/**
 * Format phone number for WhatsApp link (legacy function)
 */
function getLegacyWhatsAppLink($message = '') {
    $phone = getSiteSetting('contact_whatsapp');
    $encoded_message = urlencode($message);
    return "https://wa.me/{$phone}?text={$encoded_message}";
}

/**
 * Get Google Maps link for masjid location (legacy function)
 */
function getLegacyGoogleMapsLink() {
    $coordinates = getSiteSetting('masjid_coordinates');
    return "https://maps.google.com/maps?q={$coordinates}";
}
?>