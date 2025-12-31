<?php
/**
 * Friday Schedule iCal Export
 * Exports Friday prayer schedules in iCalendar format
 */

require_once '../config/config.php';
require_once '../includes/settings_loader.php';

// Initialize settings
$settings = initializePageSettings();

try {
    // Get Friday schedules for the next 12 months
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+12 months'));
    
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
            created_at,
            updated_at
        FROM friday_schedules 
        WHERE friday_date BETWEEN ? AND ? AND status != 'cancelled'
        ORDER BY friday_date ASC
    ");
    
    $stmt->execute([$start_date, $end_date]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for iCal download
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="jadwal-jumat-' . date('Y-m') . '.ics"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Generate iCal content
    $ical_content = generateICalContent($schedules, $settings);
    
    echo $ical_content;
    
} catch (PDOException $e) {
    error_log("Friday schedule iCal export error: " . $e->getMessage());
    http_response_code(500);
    echo "Error: Unable to export calendar";
} catch (Exception $e) {
    error_log("Friday schedule iCal export error: " . $e->getMessage());
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}

/**
 * Generate iCalendar content
 */
function generateICalContent($schedules, $settings) {
    $site_name = $settings['site_name'] ?? 'Masjid Al-Muhajirin';
    $site_url = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '';
    
    // iCal header
    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//" . $site_name . "//Friday Prayer Schedule//ID\r\n";
    $ical .= "CALSCALE:GREGORIAN\r\n";
    $ical .= "METHOD:PUBLISH\r\n";
    $ical .= "X-WR-CALNAME:Jadwal Sholat Jumat - " . $site_name . "\r\n";
    $ical .= "X-WR-CALDESC:Jadwal sholat Jumat dengan imam, khotib, dan tema khutbah\r\n";
    $ical .= "X-WR-TIMEZONE:Asia/Jakarta\r\n";
    
    // Timezone definition
    $ical .= "BEGIN:VTIMEZONE\r\n";
    $ical .= "TZID:Asia/Jakarta\r\n";
    $ical .= "BEGIN:STANDARD\r\n";
    $ical .= "DTSTART:19700101T000000\r\n";
    $ical .= "TZOFFSETFROM:+0700\r\n";
    $ical .= "TZOFFSETTO:+0700\r\n";
    $ical .= "TZNAME:WIB\r\n";
    $ical .= "END:STANDARD\r\n";
    $ical .= "END:VTIMEZONE\r\n";
    
    // Add events
    foreach ($schedules as $schedule) {
        $ical .= generateEventContent($schedule, $site_name, $site_url);
    }
    
    // iCal footer
    $ical .= "END:VCALENDAR\r\n";
    
    return $ical;
}

/**
 * Generate individual event content
 */
function generateEventContent($schedule, $site_name, $site_url) {
    $event_id = 'friday-' . $schedule['id'] . '@' . $_SERVER['HTTP_HOST'];
    $date = new DateTime($schedule['friday_date']);
    $prayer_time = new DateTime($schedule['friday_date'] . ' ' . $schedule['prayer_time']);
    
    // Calculate end time (assume 1 hour duration)
    $end_time = clone $prayer_time;
    $end_time->add(new DateInterval('PT1H'));
    
    // Format dates for iCal
    $dtstart = $prayer_time->format('Ymd\THis');
    $dtend = $end_time->format('Ymd\THis');
    $dtstamp = (new DateTime())->format('Ymd\THis\Z');
    $created = (new DateTime($schedule['created_at']))->format('Ymd\THis\Z');
    $modified = (new DateTime($schedule['updated_at']))->format('Ymd\THis\Z');
    
    // Event title and description
    $summary = 'Sholat Jumat - ' . $site_name;
    $description = "Imam: " . $schedule['imam_name'] . "\\n";
    $description .= "Khotib: " . $schedule['khotib_name'] . "\\n";
    $description .= "Tema Khutbah: " . $schedule['khutbah_theme'] . "\\n";
    
    if (!empty($schedule['khutbah_description'])) {
        $description .= "\\nDeskripsi: " . str_replace(["\r\n", "\n", "\r"], "\\n", $schedule['khutbah_description']) . "\\n";
    }
    
    if (!empty($schedule['special_notes'])) {
        $description .= "\\nCatatan: " . str_replace(["\r\n", "\n", "\r"], "\\n", $schedule['special_notes']) . "\\n";
    }
    
    $description .= "\\nWaktu: " . $prayer_time->format('H:i') . " WIB";
    $description .= "\\nLokasi: " . $schedule['location'];
    
    // Location
    $location = $schedule['location'];
    
    // Build event
    $event = "BEGIN:VEVENT\r\n";
    $event .= "UID:" . $event_id . "\r\n";
    $event .= "DTSTAMP:" . $dtstamp . "\r\n";
    $event .= "CREATED:" . $created . "\r\n";
    $event .= "LAST-MODIFIED:" . $modified . "\r\n";
    $event .= "DTSTART;TZID=Asia/Jakarta:" . $dtstart . "\r\n";
    $event .= "DTEND;TZID=Asia/Jakarta:" . $dtend . "\r\n";
    $event .= "SUMMARY:" . escapeICalText($summary) . "\r\n";
    $event .= "DESCRIPTION:" . escapeICalText($description) . "\r\n";
    $event .= "LOCATION:" . escapeICalText($location) . "\r\n";
    $event .= "STATUS:" . ($schedule['status'] === 'cancelled' ? 'CANCELLED' : 'CONFIRMED') . "\r\n";
    $event .= "CATEGORIES:Religious,Prayer,Friday\r\n";
    
    // Add URL if available
    if (!empty($site_url)) {
        $event .= "URL:" . $site_url . "/pages/jadwal_jumat.php\r\n";
    }
    
    // Add alarm (reminder 30 minutes before)
    $event .= "BEGIN:VALARM\r\n";
    $event .= "TRIGGER:-PT30M\r\n";
    $event .= "ACTION:DISPLAY\r\n";
    $event .= "DESCRIPTION:Sholat Jumat dalam 30 menit\r\n";
    $event .= "END:VALARM\r\n";
    
    $event .= "END:VEVENT\r\n";
    
    return $event;
}

/**
 * Escape text for iCal format
 */
function escapeICalText($text) {
    // Escape special characters
    $text = str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', ''], $text);
    
    // Wrap long lines (75 characters max)
    return wordwrap($text, 73, "\r\n ", true);
}
?>