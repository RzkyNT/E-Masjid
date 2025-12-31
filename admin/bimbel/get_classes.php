<?php
/**
 * Get Classes by Level
 * AJAX endpoint to get available classes for a specific level
 */

require_once '../../config/config.php';
require_once '../../includes/session_check.php';
require_once '../../includes/bimbel_functions.php';

// Check authentication
$current_user = getCurrentUser();
if (!$current_user || !in_array($current_user['role'], ['admin_bimbel', 'admin_masjid', 'viewer'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get level parameter
$level = $_GET['level'] ?? '';

if (empty($level)) {
    http_response_code(400);
    echo json_encode(['error' => 'Level parameter is required']);
    exit;
}

// Validate level
if (!in_array($level, ['SD', 'SMP', 'SMA'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid level']);
    exit;
}

try {
    // Get classes for the specified level
    $classes = getClassesByLevel($level);
    
    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode($classes);
    
} catch (Exception $e) {
    error_log("Error getting classes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>