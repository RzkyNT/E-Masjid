<?php
/**
 * Friday Schedule CRUD API
 * Handles Create, Read, Update, Delete operations for Friday schedules
 */

require_once '../config/config.php';
require_once '../config/auth.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Check if user is authenticated and has permission
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get current user and check permissions
$current_user = getCurrentUser();

// Handle POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get request data (support both JSON and form data)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If JSON decode failed, try form data
if ($data === null) {
    $data = $_POST;
}

$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'create':
        case 'add':
            if (!hasPermission('masjid_content', 'create')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            // Validate required fields
            $required_fields = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
            $errors = [];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = "Field ini wajib diisi";
                }
            }
            
            // Validate that the date is a Friday
            if (!empty($data['friday_date'])) {
                $date = new DateTime($data['friday_date']);
                if ($date->format('N') != 5) {
                    $errors['friday_date'] = 'Tanggal yang dipilih harus hari Jumat';
                }
                
                // Check if date is in the past
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                $date->setTime(0, 0, 0);
                
                if ($date < $today) {
                    $errors['friday_date'] = 'Tanggal tidak boleh di masa lalu';
                }
            }
            
            // Validate prayer time format
            if (!empty($data['prayer_time'])) {
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['prayer_time'])) {
                    $errors['prayer_time'] = 'Format waktu tidak valid (HH:MM)';
                }
            }
            
            // Validate name lengths
            if (!empty($data['imam_name']) && strlen($data['imam_name']) < 2) {
                $errors['imam_name'] = 'Nama imam minimal 2 karakter';
            }
            
            if (!empty($data['khotib_name']) && strlen($data['khotib_name']) < 2) {
                $errors['khotib_name'] = 'Nama khotib minimal 2 karakter';
            }
            
            if (!empty($data['khutbah_theme']) && strlen($data['khutbah_theme']) < 5) {
                $errors['khutbah_theme'] = 'Tema khutbah minimal 5 karakter';
            }
            
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            
            // Insert new schedule
            $stmt = $pdo->prepare("
                INSERT INTO friday_schedules 
                (friday_date, prayer_time, imam_name, khotib_name, khutbah_theme, khutbah_description, location, special_notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['friday_date'],
                $data['prayer_time'],
                $data['imam_name'],
                $data['khotib_name'],
                $data['khutbah_theme'],
                $data['khutbah_description'] ?? '',
                $data['location'] ?? 'Masjid Al-Muhajirin',
                $data['special_notes'] ?? '',
                $current_user['id']
            ]);
            
            logActivity($current_user['id'], 'friday_schedule_created', 'Created Friday schedule via modal', [
                'friday_date' => $data['friday_date'],
                'theme' => $data['khutbah_theme']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Jadwal Jumat berhasil ditambahkan',
                'schedule_id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'update':
        case 'edit':
            if (!hasPermission('masjid_content', 'update')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            $event_id = $data['id'] ?? $data['event_id'] ?? '';
            if (empty($event_id)) {
                echo json_encode(['success' => false, 'message' => 'Event ID is required']);
                exit;
            }
            
            // Validate required fields
            $required_fields = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
            $errors = [];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = "Field ini wajib diisi";
                }
            }
            
            // Validate that the date is a Friday
            if (!empty($data['friday_date'])) {
                $date = new DateTime($data['friday_date']);
                if ($date->format('N') != 5) {
                    $errors['friday_date'] = 'Tanggal yang dipilih harus hari Jumat';
                }
            }
            
            // Validate prayer time format
            if (!empty($data['prayer_time'])) {
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['prayer_time'])) {
                    $errors['prayer_time'] = 'Format waktu tidak valid (HH:MM)';
                }
            }
            
            // Validate name lengths
            if (!empty($data['imam_name']) && strlen($data['imam_name']) < 2) {
                $errors['imam_name'] = 'Nama imam minimal 2 karakter';
            }
            
            if (!empty($data['khotib_name']) && strlen($data['khotib_name']) < 2) {
                $errors['khotib_name'] = 'Nama khotib minimal 2 karakter';
            }
            
            if (!empty($data['khutbah_theme']) && strlen($data['khutbah_theme']) < 5) {
                $errors['khutbah_theme'] = 'Tema khutbah minimal 5 karakter';
            }
            
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            
            // Update schedule
            $stmt = $pdo->prepare("
                UPDATE friday_schedules 
                SET friday_date = ?, prayer_time = ?, imam_name = ?, khotib_name = ?, 
                    khutbah_theme = ?, khutbah_description = ?, location = ?, special_notes = ?, 
                    status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['friday_date'],
                $data['prayer_time'],
                $data['imam_name'],
                $data['khotib_name'],
                $data['khutbah_theme'],
                $data['khutbah_description'] ?? '',
                $data['location'] ?? 'Masjid Al-Muhajirin',
                $data['special_notes'] ?? '',
                $data['status'] ?? 'scheduled',
                $event_id
            ]);
            
            if ($stmt->rowCount() > 0) {
                logActivity($current_user['id'], 'friday_schedule_updated', 'Updated Friday schedule via modal', [
                    'schedule_id' => $event_id,
                    'friday_date' => $data['friday_date']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Jadwal Jumat berhasil diperbarui'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan atau tidak ada perubahan'
                ]);
            }
            break;
            
        case 'delete':
            if (!hasPermission('masjid_content', 'delete')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            $event_id = $data['id'] ?? $data['event_id'] ?? '';
            if (empty($event_id)) {
                echo json_encode(['success' => false, 'message' => 'Event ID is required']);
                exit;
            }
            
            // Get schedule info before deletion for logging
            $stmt = $pdo->prepare("SELECT friday_date, khutbah_theme FROM friday_schedules WHERE id = ?");
            $stmt->execute([$event_id]);
            $schedule_info = $stmt->fetch();
            
            // Delete schedule
            $stmt = $pdo->prepare("DELETE FROM friday_schedules WHERE id = ?");
            $stmt->execute([$event_id]);
            
            if ($stmt->rowCount() > 0) {
                logActivity($current_user['id'], 'friday_schedule_deleted', 'Deleted Friday schedule via modal', [
                    'schedule_id' => $event_id,
                    'friday_date' => $schedule_info['friday_date'] ?? 'unknown',
                    'theme' => $schedule_info['khutbah_theme'] ?? 'unknown'
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Jadwal Jumat berhasil dihapus'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ]);
            }
            break;
            
        case 'update_date':
            if (!hasPermission('masjid_content', 'update')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            
            $event_id = $data['event_id'] ?? $data['id'] ?? '';
            $new_date = $data['new_date'] ?? $data['friday_date'] ?? '';
            
            if (empty($event_id) || empty($new_date)) {
                echo json_encode(['success' => false, 'message' => 'Event ID and new date are required']);
                exit;
            }
            
            // Validate that the new date is a Friday
            $date = new DateTime($new_date);
            if ($date->format('N') != 5) {
                echo json_encode(['success' => false, 'message' => 'Tanggal baru harus hari Jumat']);
                exit;
            }
            
            // Update only the date
            $stmt = $pdo->prepare("
                UPDATE friday_schedules 
                SET friday_date = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([$new_date, $event_id]);
            
            if ($stmt->rowCount() > 0) {
                logActivity($current_user['id'], 'friday_schedule_date_updated', 'Updated Friday schedule date via drag & drop', [
                    'schedule_id' => $event_id,
                    'new_date' => $new_date
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tanggal jadwal berhasil diperbarui'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan atau tanggal sama'
                ]);
            }
            break;
            
        case 'get_schedule':
            // Get single schedule details
            $event_id = $data['event_id'] ?? $data['id'] ?? '';
            if (empty($event_id)) {
                echo json_encode(['success' => false, 'message' => 'Event ID is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT * FROM friday_schedules 
                WHERE id = ?
            ");
            $stmt->execute([$event_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($schedule) {
                echo json_encode([
                    'success' => true,
                    'schedule' => $schedule
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Friday schedule CRUD error: " . $e->getMessage());
    
    // Handle specific database errors
    if ($e->getCode() == 23000) {
        if (strpos($e->getMessage(), 'unique_friday_date') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Jadwal untuk tanggal tersebut sudah ada'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan database: data duplikat'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan database'
        ]);
    }
} catch (Exception $e) {
    error_log("Friday schedule CRUD error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem'
    ]);
}
?>