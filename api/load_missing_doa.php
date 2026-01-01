<?php
/**
 * Background Doa Loader
 * Loads missing doa data in batches to avoid rate limiting
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/myquran_api.php';

try {
    $api = new MyQuranAPI();
    $startId = isset($_GET['start']) ? (int)$_GET['start'] : 87;
    $endId = isset($_GET['end']) ? (int)$_GET['end'] : 108;
    $batchSize = isset($_GET['batch']) ? (int)$_GET['batch'] : 5;
    
    $loadedDoa = [];
    $errors = [];
    $processed = 0;
    
    // Load doa in small batches to avoid rate limiting
    for ($i = $startId; $i <= min($startId + $batchSize - 1, $endId); $i++) {
        try {
            $doaData = $api->getDoa($i);
            if (isset($doaData['data'])) {
                $doaData['data']['id'] = $i;
                $doaData['data']['category'] = getDoaCategory($i);
                $loadedDoa[] = $doaData['data'];
            } else {
                $errors[] = "Doa #$i: No data returned";
            }
            $processed++;
            
            // Small delay to avoid overwhelming the API
            usleep(100000); // 0.1 second delay
            
        } catch (Exception $e) {
            $errors[] = "Doa #$i: " . $e->getMessage();
        }
    }
    
    $response = [
        'success' => true,
        'data' => $loadedDoa,
        'processed' => $processed,
        'start_id' => $startId,
        'end_id' => min($startId + $batchSize - 1, $endId),
        'has_more' => (min($startId + $batchSize - 1, $endId)) < $endId,
        'next_start' => min($startId + $batchSize, $endId + 1),
        'errors' => $errors,
        'total_loaded' => count($loadedDoa)
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function getDoaCategory($id) {
    if ($id >= 1 && $id <= 30) return 'harian';
    if ($id >= 31 && $id <= 60) return 'ibadah';
    if ($id >= 61 && $id <= 90) return 'perlindungan';
    if ($id >= 91 && $id <= 108) return 'khusus';
    return 'lainnya';
}
?>