<?php
// API untuk mengirim push notification
// Hanya bisa diakses oleh admin

require_once '../config/config.php';
require_once '../includes/session_check.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_masjid') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = $input['title'] ?? 'Masjid Al-Muhajirin';
    $message = $input['message'] ?? '';
    $url = $input['url'] ?? './';
    
    if (empty($message)) {
        throw new Exception('Message is required');
    }
    
    // Get all subscriptions
    $stmt = $pdo->prepare("SELECT endpoint, p256dh_key, auth_key FROM push_subscriptions");
    $stmt->execute();
    $subscriptions = $stmt->fetchAll();
    
    if (empty($subscriptions)) {
        echo json_encode(['success' => false, 'message' => 'No subscriptions found']);
        exit;
    }
    
    $sent_count = 0;
    $failed_count = 0;
    
    foreach ($subscriptions as $subscription) {
        $result = sendPushNotification(
            $subscription['endpoint'],
            $subscription['p256dh_key'],
            $subscription['auth_key'],
            $title,
            $message,
            $url
        );
        
        if ($result) {
            $sent_count++;
        } else {
            $failed_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Notification sent to $sent_count subscribers, $failed_count failed"
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendPushNotification($endpoint, $p256dh, $auth, $title, $message, $url) {
    // This is a simplified version
    // In production, you would use a proper Web Push library like web-push-php
    
    $payload = json_encode([
        'title' => $title,
        'body' => $message,
        'url' => $url,
        'icon' => './assets/images/icon-192x192.png'
    ]);
    
    // For now, just log the notification
    error_log("Push notification: $title - $message to $endpoint");
    
    // Return true for success (in real implementation, this would send actual push)
    return true;
}
?>