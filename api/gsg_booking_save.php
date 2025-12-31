<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../config/config.php';

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Ambil data dari POST
    $dates = $_POST['dates'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Validasi input
    if (empty($dates) || empty($nama) || empty($no_telp) || empty($alamat) || empty($keterangan)) {
        echo json_encode([
            'success' => false,
            'message' => 'Semua field harus diisi'
        ]);
        exit;
    }
    
    // Parse tanggal (format: "2024-01-15,2024-01-16,2024-01-17")
    $dateArray = explode(',', $dates);
    
    // Validasi tanggal
    foreach ($dateArray as $date) {
        $date = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode([
                'success' => false,
                'message' => 'Format tanggal tidak valid'
            ]);
            exit;
        }
        
        // Cek apakah tanggal sudah dikonfirmasi (bukan pending)
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM gsg_bookings 
            WHERE booking_date = ? AND status = 'confirmed'
        ");
        $checkStmt->execute([$date]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => "Tanggal $date sudah dikonfirmasi untuk acara lain"
            ]);
            exit;
        }
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert booking untuk setiap tanggal dengan status 'pending'
    $insertStmt = $pdo->prepare("
        INSERT INTO gsg_bookings (booking_date, nama, no_telp, alamat, keterangan, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    foreach ($dateArray as $date) {
        $date = trim($date);
        
        // Cek apakah sudah ada booking pending untuk tanggal ini dari user yang sama
        $existingStmt = $pdo->prepare("
            SELECT COUNT(*) FROM gsg_bookings 
            WHERE booking_date = ? AND no_telp = ? AND status = 'pending'
        ");
        $existingStmt->execute([$date, $no_telp]);
        
        // Jika belum ada, insert baru
        if ($existingStmt->fetchColumn() == 0) {
            $insertStmt->execute([$date, $nama, $no_telp, $alamat, $keterangan]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Response sukses
    echo json_encode([
        'success' => true,
        'message' => 'Booking request berhasil disimpan dengan status menunggu konfirmasi',
        'booking_dates' => $dateArray
    ]);
    
} catch (PDOException $e) {
    // Rollback jika ada error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback jika ada error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>