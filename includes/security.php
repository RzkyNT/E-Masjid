<?php
/**
 * Professional Security System
 * Masjid Al-Muhajirin Information System
 */

class Security {
    private static $blocked_ips = [];
    private static $rate_limit = [];
    private static $session_started = false;
    
    /**
     * Initialize security system
     */
    public static function init() {
        // Start secure session
        self::startSecureSession();
        
        // Check for blocked IPs
        self::checkBlockedIP();
        
        // Rate limiting
        self::checkRateLimit();
        
        // Input sanitization
        self::sanitizeInputs();
        
        // Security headers
        self::setSecurityHeaders();
        
        // Check for common attacks
        self::checkForAttacks();
    }
    
    /**
     * Start secure session
     */
    private static function startSecureSession() {
        if (self::$session_started) {
            return;
        }
        
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Generate secure session name
        session_name('MASJID_SESSION_' . substr(md5($_SERVER['HTTP_HOST'] ?? 'localhost'), 0, 8));
        
        session_start();
        self::$session_started = true;
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Set security headers
     */
    private static function setSecurityHeaders() {
        if (headers_sent()) {
            return;
        }
        
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;";
        header("Content-Security-Policy: $csp");
        
        // HSTS (if HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Remove server information
        header_remove('Server');
        header_remove('X-Powered-By');
    }
    
    /**
     * Check for blocked IPs
     */
    private static function checkBlockedIP() {
        $ip = self::getClientIP();
        $blocked_file = __DIR__ . '/../logs/blocked_ips.json';
        
        if (file_exists($blocked_file)) {
            $blocked_data = json_decode(file_get_contents($blocked_file), true);
            
            if (isset($blocked_data[$ip])) {
                $block_info = $blocked_data[$ip];
                
                // Check if block has expired
                if (time() < $block_info['expires']) {
                    ErrorHandler::logSecurityEvent('BLOCKED_IP_ACCESS', [
                        'ip' => $ip,
                        'reason' => $block_info['reason'],
                        'expires' => date('Y-m-d H:i:s', $block_info['expires'])
                    ]);
                    
                    http_response_code(403);
                    header('Location: /error.php?code=403');
                    exit;
                } else {
                    // Remove expired block
                    unset($blocked_data[$ip]);
                    file_put_contents($blocked_file, json_encode($blocked_data));
                }
            }
        }
    }
    
    /**
     * Rate limiting
     */
    private static function checkRateLimit() {
        $ip = self::getClientIP();
        $rate_file = __DIR__ . '/../logs/rate_limit.json';
        $max_requests = 100; // requests per minute
        $window = 60; // seconds
        
        $rate_data = [];
        if (file_exists($rate_file)) {
            $rate_data = json_decode(file_get_contents($rate_file), true) ?: [];
        }
        
        $current_time = time();
        $window_start = $current_time - $window;
        
        // Clean old entries
        if (isset($rate_data[$ip])) {
            $rate_data[$ip] = array_filter($rate_data[$ip], function($timestamp) use ($window_start) {
                return $timestamp > $window_start;
            });
        } else {
            $rate_data[$ip] = [];
        }
        
        // Check rate limit
        if (count($rate_data[$ip]) >= $max_requests) {
            ErrorHandler::logSecurityEvent('RATE_LIMIT_EXCEEDED', [
                'ip' => $ip,
                'requests' => count($rate_data[$ip]),
                'limit' => $max_requests
            ]);
            
            // Block IP for 1 hour
            self::blockIP($ip, 'Rate limit exceeded', 3600);
            
            http_response_code(429);
            header('Location: /error.php?code=429');
            exit;
        }
        
        // Add current request
        $rate_data[$ip][] = $current_time;
        
        // Save rate data
        file_put_contents($rate_file, json_encode($rate_data));
    }
    
    /**
     * Sanitize all inputs
     */
    private static function sanitizeInputs() {
        // Sanitize GET parameters
        if (!empty($_GET)) {
            $_GET = self::sanitizeArray($_GET);
        }
        
        // Sanitize POST parameters
        if (!empty($_POST)) {
            $_POST = self::sanitizeArray($_POST);
        }
        
        // Sanitize COOKIE parameters
        if (!empty($_COOKIE)) {
            $_COOKIE = self::sanitizeArray($_COOKIE);
        }
    }
    
    /**
     * Sanitize array recursively
     */
    private static function sanitizeArray($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sanitizeArray($value);
            } else {
                $array[$key] = self::sanitizeString($value);
            }
        }
        return $array;
    }
    
    /**
     * Sanitize string
     */
    private static function sanitizeString($string) {
        // Remove null bytes
        $string = str_replace("\0", '', $string);
        
        // Basic XSS prevention
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        
        return $string;
    }
    
    /**
     * Check for common attacks
     */
    private static function checkForAttacks() {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // SQL Injection patterns
        $sql_patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\'|\"|;|--|\#|\*|\|)/i'
        ];
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $query_string) || preg_match($pattern, $request_uri)) {
                ErrorHandler::logSecurityEvent('SQL_INJECTION_ATTEMPT', [
                    'pattern' => $pattern,
                    'uri' => $request_uri,
                    'query' => $query_string
                ]);
                
                self::blockIP(self::getClientIP(), 'SQL injection attempt', 7200);
                http_response_code(403);
                header('Location: /error.php?code=403');
                exit;
            }
        }
        
        // XSS patterns
        $xss_patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i'
        ];
        
        foreach ($xss_patterns as $pattern) {
            if (preg_match($pattern, $query_string) || preg_match($pattern, $request_uri)) {
                ErrorHandler::logSecurityEvent('XSS_ATTEMPT', [
                    'pattern' => $pattern,
                    'uri' => $request_uri,
                    'query' => $query_string
                ]);
                
                self::blockIP(self::getClientIP(), 'XSS attempt', 3600);
                http_response_code(403);
                header('Location: /error.php?code=403');
                exit;
            }
        }
        
        // Path traversal
        if (preg_match('/\.\.[\/\\\\]/', $request_uri)) {
            ErrorHandler::logSecurityEvent('PATH_TRAVERSAL_ATTEMPT', [
                'uri' => $request_uri
            ]);
            
            self::blockIP(self::getClientIP(), 'Path traversal attempt', 3600);
            http_response_code(403);
            header('Location: /error.php?code=403');
            exit;
        }
        
        // Suspicious user agents
        $suspicious_agents = [
            'sqlmap',
            'nikto',
            'nessus',
            'openvas',
            'vega',
            'w3af',
            'paros',
            'webscarab',
            'burp'
        ];
        
        foreach ($suspicious_agents as $agent) {
            if (stripos($user_agent, $agent) !== false) {
                ErrorHandler::logSecurityEvent('SUSPICIOUS_USER_AGENT', [
                    'user_agent' => $user_agent
                ]);
                
                self::blockIP(self::getClientIP(), 'Suspicious user agent', 1800);
                http_response_code(403);
                header('Location: /error.php?code=403');
                exit;
            }
        }
    }
    
    /**
     * Block IP address
     */
    public static function blockIP($ip, $reason, $duration = 3600) {
        $blocked_file = __DIR__ . '/../logs/blocked_ips.json';
        $blocked_data = [];
        
        if (file_exists($blocked_file)) {
            $blocked_data = json_decode(file_get_contents($blocked_file), true) ?: [];
        }
        
        $blocked_data[$ip] = [
            'reason' => $reason,
            'blocked_at' => time(),
            'expires' => time() + $duration,
            'duration' => $duration
        ];
        
        file_put_contents($blocked_file, json_encode($blocked_data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Clean old security logs
     */
    public static function cleanSecurityLogs($days = 30) {
        $files = [
            __DIR__ . '/../logs/blocked_ips.json',
            __DIR__ . '/../logs/rate_limit.json'
        ];
        
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true) ?: [];
                $cleaned_data = [];
                
                foreach ($data as $key => $value) {
                    if (isset($value['blocked_at']) && $value['blocked_at'] > $cutoff) {
                        $cleaned_data[$key] = $value;
                    }
                }
                
                file_put_contents($file, json_encode($cleaned_data, JSON_PRETTY_PRINT));
            }
        }
    }
}

// Initialize security system
Security::init();
?>