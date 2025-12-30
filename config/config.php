<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'masjid_bimbel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Sistem Informasi Masjid Jami Al-Muhajirin');
define('APP_URL', 'http://localhost/masjid');
define('UPLOAD_PATH', 'assets/uploads/');

// Security Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
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
?>