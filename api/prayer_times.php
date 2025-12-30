<?php
/**
 * Prayer Times API Endpoint
 * For Masjid Al-Muhajirin Information System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/prayer_api.php';

// Get request parameters
$action = $_GET['action'] ?? 'today';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

try {
    $api = new PrayerTimeAPI();
    
    switch ($action) {
        case 'today':
            $result = $api->getTodayPrayerTimes();
            break;
            
        case 'monthly':
            $result = $api->getMonthlyPrayerTimes($year, $month);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>