<?php
/**
 * Al-Quran API Endpoint
 * For Masjid Al-Muhajirin Information System
 * Using MyQuran API v2
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/alquran_api.php';
require_once '../includes/alquran_validation.php';
require_once '../includes/alquran_parameter_handler.php';

// Process and validate parameters
$param_result = AlQuranParameterHandler::processGetParameters($_GET);

if (!$param_result['valid']) {
    http_response_code(400);
    echo json_encode(
        AlQuranParameterHandler::generateErrorResponse($param_result['errors'], $param_result['action']),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}

$action = $param_result['action'];
$params = $param_result['params'];

try {
    $api = new AlQuranAPI();
    $result = null;
    
    switch ($action) {
        case 'surat':
            if (isset($params['ayat_end'])) {
                // Range mode: surat/ayat_start-ayat_end
                $result = $api->getAyatByRange($params['surat'], $params['ayat'], $params['ayat_end']);
            } else {
                // Length mode: surat/ayat/panjang
                $result = $api->getAyatBySurat($params['surat'], $params['ayat'], $params['panjang']);
            }
            break;
            
        case 'page':
            $result = $api->getAyatByPage($params['page']);
            break;
            
        case 'juz':
            $result = $api->getAyatByJuz($params['juz']);
            break;
            
        case 'juz_info':
            $result = $api->getJuzInfo($params['juz']);
            break;
            
        case 'tema':
            $result = $api->getTemaById($params['tema_id']);
            break;
            
        case 'tema_all':
            $result = $api->getAllTema();
            break;
            
        case 'cache_stats':
            require_once '../includes/alquran_cache.php';
            $cache = new AlQuranCache();
            $result = [
                'status' => true,
                'data' => $cache->getStats()
            ];
            break;
            
        case 'cache_clean':
            require_once '../includes/alquran_cache.php';
            $cache = new AlQuranCache();
            $cleaned = $cache->cleanExpired();
            $result = [
                'status' => true,
                'data' => [
                    'message' => "Cleaned {$cleaned} expired cache files",
                    'cleaned_files' => $cleaned
                ]
            ];
            break;
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'action' => $action,
            'parameters' => $params,
            'data' => $result,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        throw new Exception('Failed to fetch Al-Quran data');
    }
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid input: ' . $e->getMessage(),
        'action' => $action,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'action' => $action,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}
?>