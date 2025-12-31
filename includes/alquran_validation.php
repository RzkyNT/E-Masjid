<?php
/**
 * Al-Quran Input Validation and Parameter Handling
 * For Masjid Al-Muhajirin Information System
 * 
 * This module provides comprehensive validation functions for Al-Quran parameters
 * including surat, ayat, page, juz, and tema with proper error messages and
 * boundary checking as specified in requirements 1.4, 1.5, 2.2, 3.3, 4.3, 7.1, 7.3
 */

class AlQuranValidator {
    
    // Constants for validation ranges
    const MIN_SURAT = 1;
    const MAX_SURAT = 114;
    const MIN_PAGE = 1;
    const MAX_PAGE = 604;
    const MIN_JUZ = 1;
    const MAX_JUZ = 30;
    const MIN_TEMA = 1;
    const MAX_TEMA = 1121;
    const MIN_AYAT = 1;
    const MAX_PANJANG = 30; // Maximum ayat to retrieve at once
    
    // Surat information with ayat counts and types for validation
    private static $surat_info = [
        1 => ['name' => 'Al-Fatihah', 'ayat_count' => 7, 'type' => 'makkah'],
        2 => ['name' => 'Al-Baqarah', 'ayat_count' => 286, 'type' => 'madinah'],
        3 => ['name' => 'Ali \'Imran', 'ayat_count' => 200, 'type' => 'madinah'],
        4 => ['name' => 'An-Nisa\'', 'ayat_count' => 176, 'type' => 'madinah'],
        5 => ['name' => 'Al-Ma\'idah', 'ayat_count' => 120, 'type' => 'madinah'],
        6 => ['name' => 'Al-An\'am', 'ayat_count' => 165, 'type' => 'makkah'],
        7 => ['name' => 'Al-A\'raf', 'ayat_count' => 206, 'type' => 'makkah'],
        8 => ['name' => 'Al-Anfal', 'ayat_count' => 75, 'type' => 'madinah'],
        9 => ['name' => 'At-Taubah', 'ayat_count' => 129, 'type' => 'madinah'],
        10 => ['name' => 'Yunus', 'ayat_count' => 109, 'type' => 'makkah'],
        11 => ['name' => 'Hud', 'ayat_count' => 123, 'type' => 'makkah'],
        12 => ['name' => 'Yusuf', 'ayat_count' => 111, 'type' => 'makkah'],
        13 => ['name' => 'Ar-Ra\'d', 'ayat_count' => 43, 'type' => 'madinah'],
        14 => ['name' => 'Ibrahim', 'ayat_count' => 52, 'type' => 'makkah'],
        15 => ['name' => 'Al-Hijr', 'ayat_count' => 99, 'type' => 'makkah'],
        16 => ['name' => 'An-Nahl', 'ayat_count' => 128, 'type' => 'makkah'],
        17 => ['name' => 'Al-Isra\'', 'ayat_count' => 111, 'type' => 'makkah'],
        18 => ['name' => 'Al-Kahf', 'ayat_count' => 110, 'type' => 'makkah'],
        19 => ['name' => 'Maryam', 'ayat_count' => 98, 'type' => 'makkah'],
        20 => ['name' => 'Taha', 'ayat_count' => 135, 'type' => 'makkah'],
        21 => ['name' => 'Al-Anbiya\'', 'ayat_count' => 112, 'type' => 'makkah'],
        22 => ['name' => 'Al-Hajj', 'ayat_count' => 78, 'type' => 'madinah'],
        23 => ['name' => 'Al-Mu\'minun', 'ayat_count' => 118, 'type' => 'makkah'],
        24 => ['name' => 'An-Nur', 'ayat_count' => 64, 'type' => 'madinah'],
        25 => ['name' => 'Al-Furqan', 'ayat_count' => 77, 'type' => 'makkah'],
        26 => ['name' => 'Ash-Shu\'ara\'', 'ayat_count' => 227, 'type' => 'makkah'],
        27 => ['name' => 'An-Naml', 'ayat_count' => 93, 'type' => 'makkah'],
        28 => ['name' => 'Al-Qasas', 'ayat_count' => 88, 'type' => 'makkah'],
        29 => ['name' => 'Al-\'Ankabut', 'ayat_count' => 69, 'type' => 'makkah'],
        30 => ['name' => 'Ar-Rum', 'ayat_count' => 60, 'type' => 'makkah'],
        31 => ['name' => 'Luqman', 'ayat_count' => 34, 'type' => 'makkah'],
        32 => ['name' => 'As-Sajdah', 'ayat_count' => 30, 'type' => 'makkah'],
        33 => ['name' => 'Al-Ahzab', 'ayat_count' => 73, 'type' => 'madinah'],
        34 => ['name' => 'Saba\'', 'ayat_count' => 54, 'type' => 'makkah'],
        35 => ['name' => 'Fatir', 'ayat_count' => 45, 'type' => 'makkah'],
        36 => ['name' => 'Ya-Sin', 'ayat_count' => 83, 'type' => 'makkah'],
        37 => ['name' => 'As-Saffat', 'ayat_count' => 182, 'type' => 'makkah'],
        38 => ['name' => 'Sad', 'ayat_count' => 88, 'type' => 'makkah'],
        39 => ['name' => 'Az-Zumar', 'ayat_count' => 75, 'type' => 'makkah'],
        40 => ['name' => 'Ghafir', 'ayat_count' => 85, 'type' => 'makkah'],
        41 => ['name' => 'Fussilat', 'ayat_count' => 54, 'type' => 'makkah'],
        42 => ['name' => 'Ash-Shura', 'ayat_count' => 53, 'type' => 'makkah'],
        43 => ['name' => 'Az-Zukhruf', 'ayat_count' => 89, 'type' => 'makkah'],
        44 => ['name' => 'Ad-Dukhan', 'ayat_count' => 59, 'type' => 'makkah'],
        45 => ['name' => 'Al-Jathiyah', 'ayat_count' => 37, 'type' => 'makkah'],
        46 => ['name' => 'Al-Ahqaf', 'ayat_count' => 35, 'type' => 'makkah'],
        47 => ['name' => 'Muhammad', 'ayat_count' => 38, 'type' => 'madinah'],
        48 => ['name' => 'Al-Fath', 'ayat_count' => 29, 'type' => 'madinah'],
        49 => ['name' => 'Al-Hujurat', 'ayat_count' => 18, 'type' => 'madinah'],
        50 => ['name' => 'Qaf', 'ayat_count' => 45, 'type' => 'makkah'],
        51 => ['name' => 'Adh-Dhariyat', 'ayat_count' => 60, 'type' => 'makkah'],
        52 => ['name' => 'At-Tur', 'ayat_count' => 49, 'type' => 'makkah'],
        53 => ['name' => 'An-Najm', 'ayat_count' => 62, 'type' => 'makkah'],
        54 => ['name' => 'Al-Qamar', 'ayat_count' => 55, 'type' => 'makkah'],
        55 => ['name' => 'Ar-Rahman', 'ayat_count' => 78, 'type' => 'madinah'],
        56 => ['name' => 'Al-Waqi\'ah', 'ayat_count' => 96, 'type' => 'makkah'],
        57 => ['name' => 'Al-Hadid', 'ayat_count' => 29, 'type' => 'madinah'],
        58 => ['name' => 'Al-Mujadilah', 'ayat_count' => 22, 'type' => 'madinah'],
        59 => ['name' => 'Al-Hashr', 'ayat_count' => 24, 'type' => 'madinah'],
        60 => ['name' => 'Al-Mumtahanah', 'ayat_count' => 13, 'type' => 'madinah'],
        61 => ['name' => 'As-Saff', 'ayat_count' => 14, 'type' => 'madinah'],
        62 => ['name' => 'Al-Jumu\'ah', 'ayat_count' => 11, 'type' => 'madinah'],
        63 => ['name' => 'Al-Munafiqun', 'ayat_count' => 11, 'type' => 'madinah'],
        64 => ['name' => 'At-Taghabun', 'ayat_count' => 18, 'type' => 'madinah'],
        65 => ['name' => 'At-Talaq', 'ayat_count' => 12, 'type' => 'madinah'],
        66 => ['name' => 'At-Tahrim', 'ayat_count' => 12, 'type' => 'madinah'],
        67 => ['name' => 'Al-Mulk', 'ayat_count' => 30, 'type' => 'makkah'],
        68 => ['name' => 'Al-Qalam', 'ayat_count' => 52, 'type' => 'makkah'],
        69 => ['name' => 'Al-Haqqah', 'ayat_count' => 52, 'type' => 'makkah'],
        70 => ['name' => 'Al-Ma\'arij', 'ayat_count' => 44, 'type' => 'makkah'],
        71 => ['name' => 'Nuh', 'ayat_count' => 28, 'type' => 'makkah'],
        72 => ['name' => 'Al-Jinn', 'ayat_count' => 28, 'type' => 'makkah'],
        73 => ['name' => 'Al-Muzzammil', 'ayat_count' => 20, 'type' => 'makkah'],
        74 => ['name' => 'Al-Muddaththir', 'ayat_count' => 56, 'type' => 'makkah'],
        75 => ['name' => 'Al-Qiyamah', 'ayat_count' => 40, 'type' => 'makkah'],
        76 => ['name' => 'Al-Insan', 'ayat_count' => 31, 'type' => 'madinah'],
        77 => ['name' => 'Al-Mursalat', 'ayat_count' => 50, 'type' => 'makkah'],
        78 => ['name' => 'An-Naba\'', 'ayat_count' => 40, 'type' => 'makkah'],
        79 => ['name' => 'An-Nazi\'at', 'ayat_count' => 46, 'type' => 'makkah'],
        80 => ['name' => '\'Abasa', 'ayat_count' => 42, 'type' => 'makkah'],
        81 => ['name' => 'At-Takwir', 'ayat_count' => 29, 'type' => 'makkah'],
        82 => ['name' => 'Al-Infitar', 'ayat_count' => 19, 'type' => 'makkah'],
        83 => ['name' => 'Al-Mutaffifin', 'ayat_count' => 36, 'type' => 'makkah'],
        84 => ['name' => 'Al-Inshiqaq', 'ayat_count' => 25, 'type' => 'makkah'],
        85 => ['name' => 'Al-Buruj', 'ayat_count' => 22, 'type' => 'makkah'],
        86 => ['name' => 'At-Tariq', 'ayat_count' => 17, 'type' => 'makkah'],
        87 => ['name' => 'Al-A\'la', 'ayat_count' => 19, 'type' => 'makkah'],
        88 => ['name' => 'Al-Ghashiyah', 'ayat_count' => 26, 'type' => 'makkah'],
        89 => ['name' => 'Al-Fajr', 'ayat_count' => 30, 'type' => 'makkah'],
        90 => ['name' => 'Al-Balad', 'ayat_count' => 20, 'type' => 'makkah'],
        91 => ['name' => 'Ash-Shams', 'ayat_count' => 15, 'type' => 'makkah'],
        92 => ['name' => 'Al-Layl', 'ayat_count' => 21, 'type' => 'makkah'],
        93 => ['name' => 'Ad-Duha', 'ayat_count' => 11, 'type' => 'makkah'],
        94 => ['name' => 'Ash-Sharh', 'ayat_count' => 8, 'type' => 'makkah'],
        95 => ['name' => 'At-Tin', 'ayat_count' => 8, 'type' => 'makkah'],
        96 => ['name' => 'Al-\'Alaq', 'ayat_count' => 19, 'type' => 'makkah'],
        97 => ['name' => 'Al-Qadr', 'ayat_count' => 5, 'type' => 'makkah'],
        98 => ['name' => 'Al-Bayyinah', 'ayat_count' => 8, 'type' => 'madinah'],
        99 => ['name' => 'Az-Zalzalah', 'ayat_count' => 8, 'type' => 'madinah'],
        100 => ['name' => 'Al-\'Adiyat', 'ayat_count' => 11, 'type' => 'makkah'],
        101 => ['name' => 'Al-Qari\'ah', 'ayat_count' => 11, 'type' => 'makkah'],
        102 => ['name' => 'At-Takathur', 'ayat_count' => 8, 'type' => 'makkah'],
        103 => ['name' => 'Al-\'Asr', 'ayat_count' => 3, 'type' => 'makkah'],
        104 => ['name' => 'Al-Humazah', 'ayat_count' => 9, 'type' => 'makkah'],
        105 => ['name' => 'Al-Fil', 'ayat_count' => 5, 'type' => 'makkah'],
        106 => ['name' => 'Quraysh', 'ayat_count' => 4, 'type' => 'makkah'],
        107 => ['name' => 'Al-Ma\'un', 'ayat_count' => 7, 'type' => 'makkah'],
        108 => ['name' => 'Al-Kawthar', 'ayat_count' => 3, 'type' => 'makkah'],
        109 => ['name' => 'Al-Kafirun', 'ayat_count' => 6, 'type' => 'makkah'],
        110 => ['name' => 'An-Nasr', 'ayat_count' => 3, 'type' => 'madinah'],
        111 => ['name' => 'Al-Masad', 'ayat_count' => 5, 'type' => 'makkah'],
        112 => ['name' => 'Al-Ikhlas', 'ayat_count' => 4, 'type' => 'makkah'],
        113 => ['name' => 'Al-Falaq', 'ayat_count' => 5, 'type' => 'makkah'],
        114 => ['name' => 'An-Nas', 'ayat_count' => 6, 'type' => 'makkah']
    ];
    
    /**
     * Validate surat number
     * Requirements: 1.4, 7.1
     * 
     * @param mixed $surat Surat number to validate
     * @return array Validation result with status and message
     */
    public static function validateSurat($surat) {
        // Sanitize input
        $surat = self::sanitizeNumericInput($surat);
        
        if ($surat === null) {
            return [
                'valid' => false,
                'message' => 'Nomor surat harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($surat < self::MIN_SURAT || $surat > self::MAX_SURAT) {
            return [
                'valid' => false,
                'message' => "Nomor surat harus antara " . self::MIN_SURAT . "-" . self::MAX_SURAT,
                'code' => 'OUT_OF_RANGE'
            ];
        }
        
        return [
            'valid' => true,
            'value' => $surat,
            'surat_name' => self::$surat_info[$surat]['name'],
            'ayat_count' => self::$surat_info[$surat]['ayat_count']
        ];
    }
    
    /**
     * Validate ayat number for a specific surat
     * Requirements: 1.5, 7.1
     * 
     * @param mixed $ayat Ayat number to validate
     * @param int $surat Surat number for context validation
     * @return array Validation result with status and message
     */
    public static function validateAyat($ayat, $surat = null) {
        // Sanitize input
        $ayat = self::sanitizeNumericInput($ayat);
        
        if ($ayat === null) {
            return [
                'valid' => false,
                'message' => 'Nomor ayat harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($ayat < self::MIN_AYAT) {
            return [
                'valid' => false,
                'message' => 'Nomor ayat harus lebih dari 0',
                'code' => 'BELOW_MINIMUM'
            ];
        }
        
        // If surat is provided, validate ayat against surat's ayat count
        if ($surat !== null) {
            $surat_validation = self::validateSurat($surat);
            if (!$surat_validation['valid']) {
                return [
                    'valid' => false,
                    'message' => 'Surat tidak valid: ' . $surat_validation['message'],
                    'code' => 'INVALID_SURAT'
                ];
            }
            
            $max_ayat = $surat_validation['ayat_count'];
            if ($ayat > $max_ayat) {
                return [
                    'valid' => false,
                    'message' => "Surat {$surat_validation['surat_name']} hanya memiliki {$max_ayat} ayat",
                    'code' => 'EXCEEDS_SURAT_LIMIT'
                ];
            }
        }
        
        return [
            'valid' => true,
            'value' => $ayat
        ];
    }
    
    /**
     * Validate page number
     * Requirements: 2.2, 7.1
     * 
     * @param mixed $page Page number to validate
     * @return array Validation result with status and message
     */
    public static function validatePage($page) {
        // Sanitize input
        $page = self::sanitizeNumericInput($page);
        
        if ($page === null) {
            return [
                'valid' => false,
                'message' => 'Nomor halaman harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($page < self::MIN_PAGE || $page > self::MAX_PAGE) {
            return [
                'valid' => false,
                'message' => "Nomor halaman harus antara " . self::MIN_PAGE . "-" . self::MAX_PAGE,
                'code' => 'OUT_OF_RANGE'
            ];
        }
        
        return [
            'valid' => true,
            'value' => $page
        ];
    }
    
    /**
     * Validate juz number
     * Requirements: 3.3, 7.1
     * 
     * @param mixed $juz Juz number to validate
     * @return array Validation result with status and message
     */
    public static function validateJuz($juz) {
        // Sanitize input
        $juz = self::sanitizeNumericInput($juz);
        
        if ($juz === null) {
            return [
                'valid' => false,
                'message' => 'Nomor juz harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($juz < self::MIN_JUZ || $juz > self::MAX_JUZ) {
            return [
                'valid' => false,
                'message' => "Nomor juz harus antara " . self::MIN_JUZ . "-" . self::MAX_JUZ,
                'code' => 'OUT_OF_RANGE'
            ];
        }
        
        return [
            'valid' => true,
            'value' => $juz
        ];
    }
    
    /**
     * Validate tema ID
     * Requirements: 4.3, 7.1
     * 
     * @param mixed $tema_id Tema ID to validate
     * @return array Validation result with status and message
     */
    public static function validateTema($tema_id) {
        // Sanitize input
        $tema_id = self::sanitizeNumericInput($tema_id);
        
        if ($tema_id === null) {
            return [
                'valid' => false,
                'message' => 'ID tema harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($tema_id < self::MIN_TEMA || $tema_id > self::MAX_TEMA) {
            return [
                'valid' => false,
                'message' => "ID tema harus antara " . self::MIN_TEMA . "-" . self::MAX_TEMA,
                'code' => 'OUT_OF_RANGE'
            ];
        }
        
        return [
            'valid' => true,
            'value' => $tema_id
        ];
    }
    
    /**
     * Validate panjang (length) parameter for ayat retrieval
     * Requirements: 7.3
     * 
     * @param mixed $panjang Length value to validate
     * @return array Validation result with status and message
     */
    public static function validatePanjang($panjang) {
        // Sanitize input
        $panjang = self::sanitizeNumericInput($panjang);
        
        if ($panjang === null) {
            return [
                'valid' => false,
                'message' => 'Panjang ayat harus berupa angka',
                'code' => 'INVALID_TYPE'
            ];
        }
        
        if ($panjang < 1 || $panjang > self::MAX_PANJANG) {
            return [
                'valid' => false,
                'message' => "Panjang ayat harus antara 1-" . self::MAX_PANJANG,
                'code' => 'OUT_OF_RANGE'
            ];
        }
        
        return [
            'valid' => true,
            'value' => $panjang
        ];
    }
    
    /**
     * Validate ayat range (start and end)
     * Requirements: 1.5, 7.3
     * 
     * @param mixed $ayat_start Starting ayat number
     * @param mixed $ayat_end Ending ayat number
     * @param int $surat Surat number for context validation
     * @return array Validation result with status and message
     */
    public static function validateAyatRange($ayat_start, $ayat_end, $surat = null) {
        // Validate start ayat
        $start_validation = self::validateAyat($ayat_start, $surat);
        if (!$start_validation['valid']) {
            return [
                'valid' => false,
                'message' => 'Ayat awal tidak valid: ' . $start_validation['message'],
                'code' => 'INVALID_START_AYAT'
            ];
        }
        
        // Validate end ayat
        $end_validation = self::validateAyat($ayat_end, $surat);
        if (!$end_validation['valid']) {
            return [
                'valid' => false,
                'message' => 'Ayat akhir tidak valid: ' . $end_validation['message'],
                'code' => 'INVALID_END_AYAT'
            ];
        }
        
        // Check if start is less than or equal to end
        if ($start_validation['value'] > $end_validation['value']) {
            return [
                'valid' => false,
                'message' => 'Ayat awal harus lebih kecil atau sama dengan ayat akhir',
                'code' => 'INVALID_RANGE'
            ];
        }
        
        // Check if range is not too large
        $range_size = $end_validation['value'] - $start_validation['value'] + 1;
        if ($range_size > self::MAX_PANJANG) {
            return [
                'valid' => false,
                'message' => "Rentang ayat terlalu besar (maksimal " . self::MAX_PANJANG . " ayat)",
                'code' => 'RANGE_TOO_LARGE'
            ];
        }
        
        return [
            'valid' => true,
            'start' => $start_validation['value'],
            'end' => $end_validation['value'],
            'count' => $range_size
        ];
    }
    
    /**
     * Sanitize and validate numeric input
     * Requirements: 7.3
     * 
     * @param mixed $input Input to sanitize
     * @return int|null Sanitized integer or null if invalid
     */
    public static function sanitizeNumericInput($input) {
        // Remove whitespace
        if (is_string($input)) {
            $input = trim($input);
        }
        
        // Check if empty
        if ($input === '' || $input === null) {
            return null;
        }
        
        // Check if numeric
        if (!is_numeric($input)) {
            return null;
        }
        
        // Convert to integer
        $value = (int)$input;
        
        // Check if conversion was successful (no decimal part lost)
        if ((string)$value !== (string)$input && (float)$input != $value) {
            return null;
        }
        
        return $value;
    }
    
    /**
     * Sanitize string input for tema search
     * Requirements: 7.3
     * 
     * @param mixed $input Input to sanitize
     * @return string|null Sanitized string or null if invalid
     */
    public static function sanitizeStringInput($input) {
        if (!is_string($input)) {
            return null;
        }
        
        // Remove excessive whitespace and trim
        $sanitized = trim(preg_replace('/\s+/', ' ', $input));
        
        // Check minimum length
        if (strlen($sanitized) < 1) {
            return null;
        }
        
        // Check maximum length
        if (strlen($sanitized) > 100) {
            return null;
        }
        
        // Remove potentially dangerous characters
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }
    
    /**
     * Validate complete parameter set for different modes
     * Requirements: 7.1, 7.3
     * 
     * @param string $mode Navigation mode (surat, page, juz, tema)
     * @param array $params Parameters to validate
     * @return array Validation result with status and messages
     */
    public static function validateParameters($mode, $params) {
        $result = [
            'valid' => true,
            'errors' => [],
            'validated_params' => []
        ];
        
        switch ($mode) {
            case 'surat':
                // Validate surat
                if (!isset($params['surat'])) {
                    $result['errors'][] = 'Parameter surat diperlukan';
                    $result['valid'] = false;
                } else {
                    $surat_validation = self::validateSurat($params['surat']);
                    if (!$surat_validation['valid']) {
                        $result['errors'][] = $surat_validation['message'];
                        $result['valid'] = false;
                    } else {
                        $result['validated_params']['surat'] = $surat_validation['value'];
                        
                        // Validate ayat if provided
                        if (isset($params['ayat'])) {
                            $ayat_validation = self::validateAyat($params['ayat'], $surat_validation['value']);
                            if (!$ayat_validation['valid']) {
                                $result['errors'][] = $ayat_validation['message'];
                                $result['valid'] = false;
                            } else {
                                $result['validated_params']['ayat'] = $ayat_validation['value'];
                            }
                        }
                        
                        // Validate range or panjang
                        if (isset($params['ayat_end'])) {
                            $ayat_start = $params['ayat'] ?? 1;
                            $range_validation = self::validateAyatRange($ayat_start, $params['ayat_end'], $surat_validation['value']);
                            if (!$range_validation['valid']) {
                                $result['errors'][] = $range_validation['message'];
                                $result['valid'] = false;
                            } else {
                                $result['validated_params']['ayat_end'] = $range_validation['end'];
                            }
                        } elseif (isset($params['panjang'])) {
                            $panjang_validation = self::validatePanjang($params['panjang']);
                            if (!$panjang_validation['valid']) {
                                $result['errors'][] = $panjang_validation['message'];
                                $result['valid'] = false;
                            } else {
                                $result['validated_params']['panjang'] = $panjang_validation['value'];
                            }
                        }
                    }
                }
                break;
                
            case 'page':
                if (!isset($params['page'])) {
                    $result['errors'][] = 'Parameter page diperlukan';
                    $result['valid'] = false;
                } else {
                    $page_validation = self::validatePage($params['page']);
                    if (!$page_validation['valid']) {
                        $result['errors'][] = $page_validation['message'];
                        $result['valid'] = false;
                    } else {
                        $result['validated_params']['page'] = $page_validation['value'];
                    }
                }
                break;
                
            case 'juz':
                if (!isset($params['juz'])) {
                    $result['errors'][] = 'Parameter juz diperlukan';
                    $result['valid'] = false;
                } else {
                    $juz_validation = self::validateJuz($params['juz']);
                    if (!$juz_validation['valid']) {
                        $result['errors'][] = $juz_validation['message'];
                        $result['valid'] = false;
                    } else {
                        $result['validated_params']['juz'] = $juz_validation['value'];
                    }
                }
                break;
                
            case 'tema':
                if (!isset($params['tema_id'])) {
                    $result['errors'][] = 'Parameter tema_id diperlukan';
                    $result['valid'] = false;
                } else {
                    $tema_validation = self::validateTema($params['tema_id']);
                    if (!$tema_validation['valid']) {
                        $result['errors'][] = $tema_validation['message'];
                        $result['valid'] = false;
                    } else {
                        $result['validated_params']['tema_id'] = $tema_validation['value'];
                    }
                }
                break;
                
            default:
                $result['errors'][] = 'Mode navigasi tidak valid. Gunakan: surat, page, juz, atau tema';
                $result['valid'] = false;
        }
        
        return $result;
    }
    
    /**
     * Get surat information by number
     * 
     * @param int $surat_number Surat number
     * @return array|null Surat information or null if not found
     */
    public static function getSuratInfo($surat_number) {
        return isset(self::$surat_info[$surat_number]) ? self::$surat_info[$surat_number] : null;
    }
    
    /**
     * Get all surat information
     * 
     * @return array All surat information
     */
    public static function getAllSuratInfo() {
        return self::$surat_info;
    }
}

// Helper functions for backward compatibility and ease of use

/**
 * Quick validation functions
 */
function validateAlQuranSurat($surat) {
    return AlQuranValidator::validateSurat($surat);
}

function validateAlQuranAyat($ayat, $surat = null) {
    return AlQuranValidator::validateAyat($ayat, $surat);
}

function validateAlQuranPage($page) {
    return AlQuranValidator::validatePage($page);
}

function validateAlQuranJuz($juz) {
    return AlQuranValidator::validateJuz($juz);
}

function validateAlQuranTema($tema_id) {
    return AlQuranValidator::validateTema($tema_id);
}

function validateAlQuranParameters($mode, $params) {
    return AlQuranValidator::validateParameters($mode, $params);
}

function sanitizeAlQuranInput($input) {
    return AlQuranValidator::sanitizeNumericInput($input);
}

?>