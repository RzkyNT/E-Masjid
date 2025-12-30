<?php
/**
 * Prayer Times API Endpoint
 * For Masjid Al-Muhajirin Information System
 * Now using MyQuran API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/prayer_myquran_api.php';

// Get request parameters
$action = $_GET['action'] ?? 'today';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

try {
    switch ($action) {
        case 'today':
            $result = getTodayPrayerSchedule();
            break;
            
        case 'monthly':
            $result = getMonthlyPrayerSchedule($year, $month);
            break;
            
        case 'date':
            $result = getDatePrayerSchedule($date);
            break;
            
        case 'next_prayer':
            $today_data = getTodayPrayerSchedule();
            if ($today_data && $today_data['status']) {
                $next_prayer = getNextPrayer($today_data['data']);
                echo json_encode([
                    'success' => true,
                    'data' => $next_prayer,
                    'timestamp' => time()
                ]);
            } else {
                throw new Exception('Failed to get prayer data');
            }
            exit;
            
        default:
            throw new Exception('Invalid action');
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => time()
        ]);
    } else {
        throw new Exception('Failed to fetch prayer data');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'fallback' => getFallbackPrayerData(),
        'timestamp' => time()
    ]);
}
?>