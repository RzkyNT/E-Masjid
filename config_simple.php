<?php
// Very simple config for testing
define('DB_HOST', 'localhost');
define('DB_NAME', 'masjid_bimbel');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected successfully!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Start session
session_start();

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>