<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../config/config.php';

try {
    // Query untuk mengambil tanggal yang sudah dikonfirmasi
    $stmt = $pdo->prepare("
        SELECT booking_date, COUNT(*) as booking_count
        FROM gsg_bookings 
        WHERE status = 'confirmed'
        AND booking_date >= CURDATE()
        GROUP BY booking_date
        ORDER BY booking_date ASC
    ");
    
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data untuk FullCalendar
    $bookedDates = [];
    foreach ($bookings as $booking) {
        $bookedDates[] = $booking['booking_date'];
    }
    
    // Response sukses
    echo json_encode([
        'success' => true,
        'booked_dates' => $bookedDates,
        'bookings_detail' => $bookings
    ]);
    
} catch (PDOException $e) {
    // Response error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Response error umum
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>