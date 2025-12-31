<?php
/**
 * Friday Schedule Events API
 * Provides event data for FullCalendar
 */

require_once '../config/config.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Get Friday schedules for a wider range (1 year back and 2 years forward)
    $start_date = date('Y-m-d', strtotime('-1 year'));
    $end_date = date('Y-m-d', strtotime('+2 years'));
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            friday_date,
            prayer_time,
            imam_name,
            khotib_name,
            khutbah_theme,
            khutbah_description,
            location,
            special_notes,
            status,
            CASE 
                WHEN friday_date = CURDATE() THEN 'today'
                WHEN friday_date > CURDATE() THEN 'upcoming'
                ELSE 'past'
            END as schedule_status
        FROM friday_schedules 
        WHERE friday_date BETWEEN ? AND ?
        ORDER BY friday_date ASC
    ");
    
    $stmt->execute([$start_date, $end_date]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $events = [];
    
    foreach ($schedules as $schedule) {
        // Determine event color based on status and date
        $color = '#10b981'; // Default green for scheduled
        $textColor = '#ffffff';
        
        if ($schedule['status'] === 'completed') {
            $color = '#6b7280'; // Gray for completed
        } elseif ($schedule['status'] === 'cancelled') {
            $color = '#ef4444'; // Red for cancelled
        } elseif ($schedule['schedule_status'] === 'today') {
            $color = '#3b82f6'; // Blue for today
        }
        
        // Create event object for FullCalendar
        $event = [
            'id' => $schedule['id'],
            'title' => 'Sholat Jumat',
            'start' => $schedule['friday_date'],
            'allDay' => true,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => $textColor,
            'extendedProps' => [
                'prayer_time' => date('H:i', strtotime($schedule['prayer_time'])),
                'imam_name' => $schedule['imam_name'],
                'khotib_name' => $schedule['khotib_name'],
                'khutbah_theme' => $schedule['khutbah_theme'],
                'khutbah_description' => $schedule['khutbah_description'],
                'location' => $schedule['location'],
                'special_notes' => $schedule['special_notes'],
                'status' => $schedule['status'],
                'schedule_status' => $schedule['schedule_status']
            ]
        ];
        
        // Add additional info to title for list view
        if (isset($_GET['view']) && $_GET['view'] === 'list') {
            $event['title'] = sprintf(
                'Sholat Jumat - %s | Imam: %s | Khotib: %s',
                date('H:i', strtotime($schedule['prayer_time'])),
                $schedule['imam_name'],
                $schedule['khotib_name']
            );
        }
        
        $events[] = $event;
    }
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'events' => $events,
        'total' => count($events)
    ]);
    
} catch (PDOException $e) {
    error_log("Friday schedule events API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'events' => []
    ]);
} catch (Exception $e) {
    error_log("Friday schedule events API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching events',
        'events' => []
    ]);
}
?>