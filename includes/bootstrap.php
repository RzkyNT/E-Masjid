<?php
/**
 * Professional Bootstrap File
 * Masjid Al-Muhajirin Information System
 * 
 * This file initializes all professional systems:
 * - Error handling and logging
 * - Security system
 * - Performance monitoring
 * - Configuration management
 */

// Prevent direct access
if (!defined('MASJID_SYSTEM')) {
    define('MASJID_SYSTEM', true);
}

// Define system constants
define('SYSTEM_ROOT', dirname(__DIR__));
define('SYSTEM_VERSION', '2.0.0');
define('SYSTEM_NAME', 'Masjid Al-Muhajirin Information System');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include core systems
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/performance_monitor.php';

/**
 * Professional System Manager
 */
class SystemManager {
    private static $initialized = false;
    private static $config = [];
    
    /**
     * Initialize all systems
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        // Load configuration
        self::loadConfig();
        
        // Initialize systems
        ErrorHandler::init();
        Security::init();
        PerformanceMonitor::start();
        
        // Set up maintenance check
        self::checkMaintenanceMode();
        
        // Clean old logs periodically
        self::performMaintenance();
        
        self::$initialized = true;
        
        // Log system startup
        ErrorHandler::logAppError('System initialized successfully', [
            'version' => SYSTEM_VERSION,
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ], 'INFO');
    }
    
    /**
     * Load system configuration
     */
    private static function loadConfig() {
        $config_file = SYSTEM_ROOT . '/config/system.json';
        
        if (file_exists($config_file)) {
            self::$config = json_decode(file_get_contents($config_file), true) ?: [];
        }
        
        // Default configuration
        self::$config = array_merge([
            'maintenance_mode' => false,
            'debug_mode' => false,
            'log_retention_days' => 30,
            'performance_monitoring' => true,
            'security_monitoring' => true,
            'rate_limit_requests' => 100,
            'rate_limit_window' => 60,
            'admin_email' => 'admin@masjidalmuhajirin.com',
            'site_name' => 'Masjid Al-Muhajirin',
            'site_url' => 'https://masjidalmuhajirin.com'
        ], self::$config);
    }
    
    /**
     * Get configuration value
     */
    public static function getConfig($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Set configuration value
     */
    public static function setConfig($key, $value) {
        self::$config[$key] = $value;
        self::saveConfig();
    }
    
    /**
     * Save configuration
     */
    private static function saveConfig() {
        $config_file = SYSTEM_ROOT . '/config/system.json';
        $config_dir = dirname($config_file);
        
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }
        
        file_put_contents($config_file, json_encode(self::$config, JSON_PRETTY_PRINT));
    }
    
    /**
     * Check maintenance mode
     */
    private static function checkMaintenanceMode() {
        if (self::getConfig('maintenance_mode', false)) {
            // Allow admin access
            $admin_ips = self::getConfig('admin_ips', []);
            $client_ip = Security::getClientIP();
            
            if (!in_array($client_ip, $admin_ips)) {
                // Check if current page is maintenance page
                $current_page = basename($_SERVER['PHP_SELF']);
                if ($current_page !== 'maintenance.php') {
                    header('Location: /maintenance.php');
                    exit;
                }
            }
        }
    }
    
    /**
     * Perform system maintenance
     */
    private static function performMaintenance() {
        // Run maintenance tasks randomly (1% chance)
        if (rand(1, 100) === 1) {
            $retention_days = self::getConfig('log_retention_days', 30);
            
            // Clean old logs
            ErrorHandler::cleanOldLogs($retention_days);
            Security::cleanSecurityLogs($retention_days);
            
            // Clean old cache files
            self::cleanOldCache();
            
            ErrorHandler::logAppError('Automatic maintenance completed', [
                'retention_days' => $retention_days
            ], 'INFO');
        }
    }
    
    /**
     * Clean old cache files
     */
    private static function cleanOldCache() {
        $cache_dirs = [
            SYSTEM_ROOT . '/cache',
            SYSTEM_ROOT . '/api/cache'
        ];
        
        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($cache_dir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                $cutoff = time() - (7 * 24 * 60 * 60); // 7 days
                
                foreach ($files as $file) {
                    if ($file->isFile() && $file->getMTime() < $cutoff) {
                        unlink($file->getRealPath());
                    }
                }
            }
        }
    }
    
    /**
     * Get system status
     */
    public static function getSystemStatus() {
        $status = [
            'system_name' => SYSTEM_NAME,
            'version' => SYSTEM_VERSION,
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s'),
            'uptime' => self::getServerUptime(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'disk_usage' => self::getDiskUsage(),
            'maintenance_mode' => self::getConfig('maintenance_mode', false),
            'debug_mode' => self::getConfig('debug_mode', false),
            'services' => [
                'error_handler' => class_exists('ErrorHandler'),
                'security' => class_exists('Security'),
                'performance_monitor' => class_exists('PerformanceMonitor')
            ]
        ];
        
        return $status;
    }
    
    /**
     * Get server uptime (if available)
     */
    private static function getServerUptime() {
        if (function_exists('sys_getloadavg') && file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];
            return gmdate('H:i:s', $uptime);
        }
        return 'Unknown';
    }
    
    /**
     * Get disk usage
     */
    private static function getDiskUsage() {
        $total = disk_total_space(SYSTEM_ROOT);
        $free = disk_free_space(SYSTEM_ROOT);
        $used = $total - $free;
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }
    
    /**
     * Enable maintenance mode
     */
    public static function enableMaintenanceMode($admin_ips = []) {
        self::setConfig('maintenance_mode', true);
        self::setConfig('admin_ips', $admin_ips);
        
        ErrorHandler::logAppError('Maintenance mode enabled', [
            'admin_ips' => $admin_ips
        ], 'INFO');
    }
    
    /**
     * Disable maintenance mode
     */
    public static function disableMaintenanceMode() {
        self::setConfig('maintenance_mode', false);
        
        ErrorHandler::logAppError('Maintenance mode disabled', [], 'INFO');
    }
    
    /**
     * Generate system report
     */
    public static function generateSystemReport() {
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'system_status' => self::getSystemStatus(),
            'performance_report' => PerformanceMonitor::generateReport(7),
            'recent_errors' => self::getRecentErrors(50),
            'security_events' => self::getRecentSecurityEvents(50)
        ];
        
        return $report;
    }
    
    /**
     * Get recent errors
     */
    private static function getRecentErrors($limit = 50) {
        $log_files = glob(SYSTEM_ROOT . '/logs/*error*.log');
        $errors = [];
        
        foreach ($log_files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $errors = array_merge($errors, array_slice($lines, -$limit));
        }
        
        return array_slice($errors, -$limit);
    }
    
    /**
     * Get recent security events
     */
    private static function getRecentSecurityEvents($limit = 50) {
        $log_file = SYSTEM_ROOT . '/logs/security_' . date('Y-m-d') . '.log';
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            return array_slice($lines, -$limit);
        }
        
        return [];
    }
}

// Initialize system
SystemManager::init();

// Add performance checkpoint
PerformanceMonitor::checkpoint('bootstrap_complete');
?>