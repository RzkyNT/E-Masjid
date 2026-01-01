<?php
/**
 * Professional Performance Monitor
 * Masjid Al-Muhajirin Information System
 */

class PerformanceMonitor {
    private static $start_time;
    private static $start_memory;
    private static $db_queries = 0;
    private static $checkpoints = [];
    
    /**
     * Start monitoring
     */
    public static function start() {
        self::$start_time = microtime(true);
        self::$start_memory = memory_get_usage(true);
        self::$checkpoints = [];
        
        // Register shutdown function to log performance
        register_shutdown_function([self::class, 'logPerformance']);
    }
    
    /**
     * Add checkpoint
     */
    public static function checkpoint($name) {
        self::$checkpoints[$name] = [
            'time' => microtime(true) - self::$start_time,
            'memory' => memory_get_usage(true) - self::$start_memory
        ];
    }
    
    /**
     * Increment database query counter
     */
    public static function incrementDBQueries() {
        self::$db_queries++;
    }
    
    /**
     * Get current performance metrics
     */
    public static function getMetrics() {
        return [
            'execution_time' => microtime(true) - self::$start_time,
            'memory_usage' => memory_get_usage(true) - self::$start_memory,
            'peak_memory' => memory_get_peak_usage(true),
            'db_queries' => self::$db_queries,
            'checkpoints' => self::$checkpoints
        ];
    }
    
    /**
     * Log performance metrics
     */
    public static function logPerformance() {
        if (!self::$start_time) {
            return;
        }
        
        $metrics = self::getMetrics();
        $page = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        // Log to performance log
        ErrorHandler::logPerformance(
            $page,
            $metrics['execution_time'],
            $metrics['memory_usage'],
            $metrics['db_queries']
        );
        
        // Alert if performance is poor
        if ($metrics['execution_time'] > 5.0) { // 5 seconds
            ErrorHandler::logAppError("Slow page load detected: {$page}", $metrics, 'WARNING');
        }
        
        if ($metrics['memory_usage'] > 50 * 1024 * 1024) { // 50MB
            ErrorHandler::logAppError("High memory usage detected: {$page}", $metrics, 'WARNING');
        }
        
        // Add performance header in development
        if (!self::isProduction()) {
            $perf_header = sprintf(
                'Time: %.4fs, Memory: %s, Queries: %d',
                $metrics['execution_time'],
                self::formatBytes($metrics['memory_usage']),
                $metrics['db_queries']
            );
            
            if (!headers_sent()) {
                header("X-Performance: $perf_header");
            }
        }
    }
    
    /**
     * Generate performance report
     */
    public static function generateReport($days = 7) {
        $log_dir = __DIR__ . '/../logs/';
        $report = [
            'period' => $days . ' days',
            'generated_at' => date('Y-m-d H:i:s'),
            'pages' => [],
            'summary' => [
                'total_requests' => 0,
                'avg_response_time' => 0,
                'avg_memory_usage' => 0,
                'slow_pages' => 0,
                'high_memory_pages' => 0
            ]
        ];
        
        // Read performance logs
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $log_file = $log_dir . "performance_$date.log";
            
            if (file_exists($log_file)) {
                $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                foreach ($lines as $line) {
                    if (preg_match('/\[([^\]]+)\] PERFORMANCE: (.+) - Time: ([0-9.]+)s, Memory: ([^,]+), Queries: (\d+)/', $line, $matches)) {
                        $page = $matches[2];
                        $time = (float)$matches[3];
                        $memory = $matches[4];
                        $queries = (int)$matches[5];
                        
                        if (!isset($report['pages'][$page])) {
                            $report['pages'][$page] = [
                                'requests' => 0,
                                'total_time' => 0,
                                'total_memory' => 0,
                                'total_queries' => 0,
                                'max_time' => 0,
                                'max_memory' => 0
                            ];
                        }
                        
                        $report['pages'][$page]['requests']++;
                        $report['pages'][$page]['total_time'] += $time;
                        $report['pages'][$page]['total_queries'] += $queries;
                        $report['pages'][$page]['max_time'] = max($report['pages'][$page]['max_time'], $time);
                        
                        $report['summary']['total_requests']++;
                        
                        if ($time > 3.0) {
                            $report['summary']['slow_pages']++;
                        }
                    }
                }
            }
        }
        
        // Calculate averages
        foreach ($report['pages'] as $page => &$data) {
            if ($data['requests'] > 0) {
                $data['avg_time'] = $data['total_time'] / $data['requests'];
                $data['avg_queries'] = $data['total_queries'] / $data['requests'];
            }
        }
        
        if ($report['summary']['total_requests'] > 0) {
            $total_time = array_sum(array_column($report['pages'], 'total_time'));
            $report['summary']['avg_response_time'] = $total_time / $report['summary']['total_requests'];
        }
        
        // Sort pages by average response time
        uasort($report['pages'], function($a, $b) {
            return $b['avg_time'] <=> $a['avg_time'];
        });
        
        return $report;
    }
    
    /**
     * Check if running in production
     */
    private static function isProduction() {
        return !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'dev.local']);
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
}

// Start monitoring
PerformanceMonitor::start();
?>