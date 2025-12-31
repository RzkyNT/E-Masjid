<?php
/**
 * Al-Quran Parameter Handler
 * For Masjid Al-Muhajirin Information System
 * 
 * This module handles parameter processing and validation for Al-Quran API endpoints
 * Requirements: 7.1, 7.3
 */

require_once __DIR__ . '/alquran_validation.php';

class AlQuranParameterHandler {
    
    /**
     * Process and validate GET parameters for Al-Quran API
     * Requirements: 7.1, 7.3
     * 
     * @param array $get_params $_GET parameters
     * @return array Processed parameters with validation results
     */
    public static function processGetParameters($get_params) {
        $result = [
            'valid' => true,
            'errors' => [],
            'params' => [],
            'action' => 'surat' // default action
        ];
        
        // Process action parameter
        $action = isset($get_params['action']) ? AlQuranValidator::sanitizeStringInput($get_params['action']) : 'surat';
        $valid_actions = ['surat', 'page', 'juz', 'juz_info', 'tema', 'tema_all', 'cache_stats', 'cache_clean'];
        
        if (!in_array($action, $valid_actions)) {
            $result['errors'][] = 'Invalid action parameter. Valid actions: ' . implode(', ', $valid_actions);
            $result['valid'] = false;
            return $result;
        }
        
        $result['action'] = $action;
        
        // Process parameters based on action
        switch ($action) {
            case 'surat':
                $result = self::processSuratParameters($get_params, $result);
                break;
                
            case 'page':
                $result = self::processPageParameters($get_params, $result);
                break;
                
            case 'juz':
            case 'juz_info':
                $result = self::processJuzParameters($get_params, $result);
                break;
                
            case 'tema':
                $result = self::processTemaParameters($get_params, $result);
                break;
                
            case 'tema_all':
            case 'cache_stats':
            case 'cache_clean':
                // These actions don't require additional parameters
                break;
        }
        
        return $result;
    }
    
    /**
     * Process surat-related parameters
     * Requirements: 1.4, 1.5, 7.1, 7.3
     */
    private static function processSuratParameters($get_params, $result) {
        // Surat parameter (required)
        $surat = isset($get_params['surat']) ? AlQuranValidator::sanitizeNumericInput($get_params['surat']) : null;
        if ($surat === null) {
            $result['errors'][] = 'Parameter surat diperlukan dan harus berupa angka';
            $result['valid'] = false;
            return $result;
        }
        
        $surat_validation = AlQuranValidator::validateSurat($surat);
        if (!$surat_validation['valid']) {
            $result['errors'][] = $surat_validation['message'];
            $result['valid'] = false;
            return $result;
        }
        
        $result['params']['surat'] = $surat_validation['value'];
        
        // Ayat parameter (optional, default 1)
        $ayat = isset($get_params['ayat']) ? AlQuranValidator::sanitizeNumericInput($get_params['ayat']) : 1;
        if ($ayat !== null) {
            $ayat_validation = AlQuranValidator::validateAyat($ayat, $surat_validation['value']);
            if (!$ayat_validation['valid']) {
                $result['errors'][] = $ayat_validation['message'];
                $result['valid'] = false;
                return $result;
            }
            $result['params']['ayat'] = $ayat_validation['value'];
        } else {
            $result['params']['ayat'] = 1;
        }
        
        // Check for range or length mode
        $ayat_end = isset($get_params['ayat_end']) ? AlQuranValidator::sanitizeNumericInput($get_params['ayat_end']) : null;
        $panjang = isset($get_params['panjang']) ? AlQuranValidator::sanitizeNumericInput($get_params['panjang']) : null;
        
        if ($ayat_end !== null) {
            // Range mode
            $range_validation = AlQuranValidator::validateAyatRange($result['params']['ayat'], $ayat_end, $surat_validation['value']);
            if (!$range_validation['valid']) {
                $result['errors'][] = $range_validation['message'];
                $result['valid'] = false;
                return $result;
            }
            $result['params']['ayat_end'] = $range_validation['end'];
            $result['params']['mode'] = 'range';
        } else {
            // Length mode
            if ($panjang === null) {
                $panjang = 1; // default
            }
            
            $panjang_validation = AlQuranValidator::validatePanjang($panjang);
            if (!$panjang_validation['valid']) {
                $result['errors'][] = $panjang_validation['message'];
                $result['valid'] = false;
                return $result;
            }
            $result['params']['panjang'] = $panjang_validation['value'];
            $result['params']['mode'] = 'length';
        }
        
        return $result;
    }
    
    /**
     * Process page-related parameters
     * Requirements: 2.2, 7.1, 7.3
     */
    private static function processPageParameters($get_params, $result) {
        $page = isset($get_params['page']) ? AlQuranValidator::sanitizeNumericInput($get_params['page']) : null;
        if ($page === null) {
            $result['errors'][] = 'Parameter page diperlukan dan harus berupa angka';
            $result['valid'] = false;
            return $result;
        }
        
        $page_validation = AlQuranValidator::validatePage($page);
        if (!$page_validation['valid']) {
            $result['errors'][] = $page_validation['message'];
            $result['valid'] = false;
            return $result;
        }
        
        $result['params']['page'] = $page_validation['value'];
        return $result;
    }
    
    /**
     * Process juz-related parameters
     * Requirements: 3.3, 7.1, 7.3
     */
    private static function processJuzParameters($get_params, $result) {
        $juz = isset($get_params['juz']) ? AlQuranValidator::sanitizeNumericInput($get_params['juz']) : null;
        if ($juz === null) {
            $result['errors'][] = 'Parameter juz diperlukan dan harus berupa angka';
            $result['valid'] = false;
            return $result;
        }
        
        $juz_validation = AlQuranValidator::validateJuz($juz);
        if (!$juz_validation['valid']) {
            $result['errors'][] = $juz_validation['message'];
            $result['valid'] = false;
            return $result;
        }
        
        $result['params']['juz'] = $juz_validation['value'];
        return $result;
    }
    
    /**
     * Process tema-related parameters
     * Requirements: 4.3, 7.1, 7.3
     */
    private static function processTemaParameters($get_params, $result) {
        $tema_id = isset($get_params['tema_id']) ? AlQuranValidator::sanitizeNumericInput($get_params['tema_id']) : null;
        if ($tema_id === null) {
            $result['errors'][] = 'Parameter tema_id diperlukan dan harus berupa angka';
            $result['valid'] = false;
            return $result;
        }
        
        $tema_validation = AlQuranValidator::validateTema($tema_id);
        if (!$tema_validation['valid']) {
            $result['errors'][] = $tema_validation['message'];
            $result['valid'] = false;
            return $result;
        }
        
        $result['params']['tema_id'] = $tema_validation['value'];
        return $result;
    }
    
    /**
     * Generate error response for invalid parameters
     * Requirements: 7.1, 7.2
     * 
     * @param array $errors Array of error messages
     * @param string $action Action that was attempted
     * @return array Error response structure
     */
    public static function generateErrorResponse($errors, $action = '') {
        return [
            'success' => false,
            'error' => 'Invalid parameters: ' . implode('; ', $errors),
            'errors' => $errors,
            'action' => $action,
            'timestamp' => time(),
            'help' => self::getParameterHelp($action)
        ];
    }
    
    /**
     * Get parameter help for different actions
     * Requirements: 7.1
     * 
     * @param string $action Action name
     * @return array Help information
     */
    public static function getParameterHelp($action) {
        $help = [
            'surat' => [
                'required' => ['surat (1-114)'],
                'optional' => ['ayat (default: 1)', 'panjang (1-30, default: 1) OR ayat_end (for range)'],
                'examples' => [
                    '?action=surat&surat=2&ayat=1&panjang=5',
                    '?action=surat&surat=2&ayat=1&ayat_end=10'
                ]
            ],
            'page' => [
                'required' => ['page (1-604)'],
                'optional' => [],
                'examples' => ['?action=page&page=1']
            ],
            'juz' => [
                'required' => ['juz (1-30)'],
                'optional' => [],
                'examples' => ['?action=juz&juz=1']
            ],
            'juz_info' => [
                'required' => ['juz (1-30)'],
                'optional' => [],
                'examples' => ['?action=juz_info&juz=1']
            ],
            'tema' => [
                'required' => ['tema_id (1-1121)'],
                'optional' => [],
                'examples' => ['?action=tema&tema_id=1']
            ],
            'tema_all' => [
                'required' => [],
                'optional' => [],
                'examples' => ['?action=tema_all']
            ]
        ];
        
        return isset($help[$action]) ? $help[$action] : [
            'available_actions' => array_keys($help)
        ];
    }
}

// Helper function for backward compatibility
function processAlQuranParameters($get_params) {
    return AlQuranParameterHandler::processGetParameters($get_params);
}

function generateAlQuranErrorResponse($errors, $action = '') {
    return AlQuranParameterHandler::generateErrorResponse($errors, $action);
}

?>