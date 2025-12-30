<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once '../config/config.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['endpoint'])) {
        throw new Exception('Invalid subscription data');
    }
    
    $endpoint = $input['endpoint'];
    $p256dh = $input['keys']['p256dh'] ?? '';
    $auth = $input['keys']['auth'] ?? '';
    
    // Create push_subscriptions table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            endpoint TEXT NOT NULL,
            p256dh_key VARCHAR(255),
            auth_key VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_endpoint (endpoint(255))
        )
    ");
    
    // Insert or update subscription
    $stmt = $pdo->prepare("
        INSERT INTO push_subscriptions (endpoint, p256dh_key, auth_key) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        p256dh_key = VALUES(p256dh_key), 
        auth_key = VALUES(auth_key),
        updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([$endpoint, $p256dh, $auth]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Subscription saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save subscription: ' . $e->getMessage()
    ]);
}
?>