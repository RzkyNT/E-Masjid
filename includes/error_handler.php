<?php
/**
 * Professional Error Handler and Logger
 * Masjid Al-Muhajirin Information System
 */

class ErrorHandler {
    private static $log_dir = __DIR__ . '/../logs/';
    private static $max_log_size = 10485760; // 10MB
    private static $max_log_files = 5;
    
    /**
     * Initialize error handler
     */
    public static function init() {
        // Set error reporting based on environment
        if (self::isProduction()) {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        // Set custom error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
        
        // Ensure log directory exists
        self::ensureLogDirectory();
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_type = self::getErrorType($severity);
        $log_message = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error_type,
            $message,
            $file,
            $line
        );
        
        self::logError($log_message, 'php_errors');
        
        // Send to error page in production
        if (self::isProduction() && $severity & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
            self::redirectToErrorPage(500);
        }
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $log_message = sprintf(
            "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        self::logError($log_message, 'exceptions');
        
        if (self::isProduction()) {
            self::redirectToErrorPage(500);
        } else {
            echo "<pre>$log_message</pre>";
        }
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $log_message = sprintf(
                "[%s] Fatal Error: %s in %s on line %d",
                date('Y-m-d H:i:s'),
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            self::logError($log_message, 'fatal_errors');
            
            if (self::isProduction()) {
                self::redirectToErrorPage(500);
            }
        }
    }
    
    /**
     * Log custom application errors
     */
    public static function logAppError($message, $context = [], $level = 'ERROR') {
        $log_message = sprintf(
            "[%s] %s: %s",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        
        if (!empty($context)) {
            $log_message .= "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
        }
        
        self::logError($log_message, 'application');
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = []) {
        $log_message = sprintf(
            "[%s] SECURITY: %s\nIP: %s\nUser Agent: %s\nRequest URI: %s",
            date('Y-m-d H:i:s'),
            $event,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        );
        
        if (!empty($details)) {
            $log_message .= "\nDetails: " . json_encode($details, JSON_PRETTY_PRINT);
        }
        
        self::logError($log_message, 'security');
        
        // Send email alert for critical security events
        if (in_array($event, ['SQL_INJECTION_ATTEMPT', 'XSS_ATTEMPT', 'BRUTE_FORCE_ATTACK'])) {
            self::sendSecurityAlert($event, $log_message);
        }
    }
    
    /**
     * Log performance metrics
     */
    public static function logPerformance($page, $execution_time, $memory_usage, $db_queries = 0) {
        $log_message = sprintf(
            "[%s] PERFORMANCE: %s - Time: %.4fs, Memory: %s, Queries: %d",
            date('Y-m-d H:i:s'),
            $page,
            $execution_time,
            self::formatBytes($memory_usage),
            $db_queries
        );
        
        self::logError($log_message, 'performance');
    }
    
    /**
     * Write to log file
     */
    private static function logError($message, $type = 'general') {
        $log_file = self::$log_dir . $type . '_' . date('Y-m-d') . '.log';
        
        // Rotate log if too large
        if (file_exists($log_file) && filesize($log_file) > self::$max_log_size) {
            self::rotateLog($log_file);
        }
        
        file_put_contents($log_file, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotate log files
     */
    private static function rotateLog($log_file) {
        $base_name = pathinfo($log_file, PATHINFO_FILENAME);
        $extension = pathinfo($log_file, PATHINFO_EXTENSION);
        $dir = dirname($log_file);
        
        // Remove oldest log
        $oldest_log = $dir . '/' . $base_name . '_' . (self::$max_log_files - 1) . '.' . $extension;
        if (file_exists($oldest_log)) {
            unlink($oldest_log);
        }
        
        // Rotate existing logs
        for ($i = self::$max_log_files - 2; $i >= 0; $i--) {
            $old_log = $i === 0 ? $log_file : $dir . '/' . $base_name . '_' . $i . '.' . $extension;
            $new_log = $dir . '/' . $base_name . '_' . ($i + 1) . '.' . $extension;
            
            if (file_exists($old_log)) {
                rename($old_log, $new_log);
            }
        }
    }
    
    /**
     * Send security alert email
     */
    private static function sendSecurityAlert($event, $message) {
        // Implement email sending logic here
        // You can use PHPMailer or similar library
        
        $to = 'admin@masjidalmuhajirin.com';
        $subject = 'Security Alert: ' . $event;
        $body = "Security event detected:\n\n" . $message;
        
        // Basic mail function (replace with proper email library)
        @mail($to, $subject, $body);
    }
    
    /**
     * Redirect to error page
     */
    private static function redirectToErrorPage($code) {
        if (!headers_sent()) {
            http_response_code($code);
            header("Location: /error.php?code=$code");
            exit;
        }
    }
    
    /**
     * Check if running in production
     */
    private static function isProduction() {
        return !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'dev.local']);
    }
    
    /**
     * Get error type string
     */
    private static function getErrorType($type) {
        $types = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $types[$type] ?? 'Unknown Error';
    }
    
    /**
     * Format bytes to human readable
     */
    private static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Ensure log directory exists
     */
    private static function ensureLogDirectory() {
        if (!is_dir(self::$log_dir)) {
            mkdir(self::$log_dir, 0755, true);
        }
        
        // Create .htaccess to protect logs
        $htaccess_file = self::$log_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Require all denied\n");
        }
    }
    
    /**
     * Clean old logs (call this periodically)
     */
    public static function cleanOldLogs($days = 30) {
        $files = glob(self::$log_dir . '*.log*');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

// Initialize error handler
ErrorHandler::init();
?>