<?php
// Simple error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'masjid_bimbel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Sistem Informasi Masjid Jami Al-Muhajirin');
define('APP_URL', 'http://localhost/test/lms/bimbel');
define('UPLOAD_PATH', 'assets/uploads/');

// Base URL variable for easier use in templates
$base_url = APP_URL;

// Security Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');

// Database Connection with better error handling
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // For debugging - show the actual error
    die("Database connection failed: " . $e->getMessage());
}

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Timezone setting
date_default_timezone_set('Asia/Jakarta');

// Function to safely get file modification time for cache busting
function getFileVersion($filepath) {
    $fullPath = __DIR__ . '/../' . $filepath;
    if (file_exists($fullPath)) {
        return filemtime($fullPath);
    }
    return time(); // Fallback to current time
}
?>