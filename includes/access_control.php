<?php
/**
 * Access Control Functions
 * For Masjid Al-Muhajirin Information System
 */

require_once dirname(__DIR__) . '/config/auth.php';

/**
 * Role-based access control definitions
 */
class AccessControl {
    
    // Define role hierarchy (higher number = more permissions)
    const ROLE_HIERARCHY = [
        'viewer' => 1,
        'admin_bimbel' => 2,
        'admin_masjid' => 3
    ];
    
    // Define resource permissions
    const PERMISSIONS = [
        'dashboard' => [
            'admin_masjid' => ['read'],
            'admin_bimbel' => ['read'],
            'viewer' => ['read']
        ],
        'user_management' => [
            'admin_masjid' => ['create', 'read', 'update', 'delete']
        ],
        'masjid_content' => [
            'admin_masjid' => ['create', 'read', 'update', 'delete'],
            'admin_bimbel' => ['read']
        ],
        'bimbel_management' => [
            'admin_bimbel' => ['create', 'read', 'update', 'delete'],
            'admin_masjid' => ['read']
        ],
        'bimbel_financial' => [
            'admin_bimbel' => ['create', 'read', 'update', 'delete'],
            'admin_masjid' => ['read']
        ],
        'reports' => [
            'admin_masjid' => ['read', 'export'],
            'admin_bimbel' => ['read', 'export'],
            'viewer' => ['read']
        ],
        'system_settings' => [
            'admin_masjid' => ['read', 'update']
        ]
    ];
    
    /**
     * Check if user has permission for resource and action
     */
    public static function hasPermission($user_role, $resource, $action = 'read') {
        if (!isset(self::PERMISSIONS[$resource])) {
            return false;
        }
        
        $resource_permissions = self::PERMISSIONS[$resource];
        
        if (!isset($resource_permissions[$user_role])) {
            return false;
        }
        
        return in_array($action, $resource_permissions[$user_role]);
    }
    
    /**
     * Check if user role has higher or equal hierarchy level
     */
    public static function hasRoleLevel($user_role, $required_role) {
        $user_level = self::ROLE_HIERARCHY[$user_role] ?? 0;
        $required_level = self::ROLE_HIERARCHY[$required_role] ?? 0;
        
        return $user_level >= $required_level;
    }
    
    /**
     * Get allowed actions for user role on resource
     */
    public static function getAllowedActions($user_role, $resource) {
        if (!isset(self::PERMISSIONS[$resource][$user_role])) {
            return [];
        }
        
        return self::PERMISSIONS[$resource][$user_role];
    }
    
    /**
     * Get all resources accessible by user role
     */
    public static function getAccessibleResources($user_role) {
        $accessible = [];
        
        foreach (self::PERMISSIONS as $resource => $roles) {
            if (isset($roles[$user_role])) {
                $accessible[$resource] = $roles[$user_role];
            }
        }
        
        return $accessible;
    }
}

/**
 * Page-specific access control
 */
class PageAccess {
    
    // Define page access requirements
    const PAGE_REQUIREMENTS = [
        '/admin/dashboard.php' => ['role' => ['admin_masjid', 'admin_bimbel', 'viewer']],
        '/admin/users.php' => ['permission' => ['user_management', 'read']],
        '/admin/bimbel/dashboard.php' => ['permission' => ['bimbel_management', 'read']],
        '/admin/bimbel/siswa.php' => ['permission' => ['bimbel_management', 'read']],
        '/admin/bimbel/mentor.php' => ['permission' => ['bimbel_management', 'read']],
        '/admin/bimbel/absensi_siswa.php' => ['permission' => ['bimbel_management', 'create']],
        '/admin/bimbel/absensi_mentor.php' => ['permission' => ['bimbel_management', 'create']],
        '/admin/bimbel/keuangan.php' => ['permission' => ['bimbel_financial', 'read']],
        '/admin/bimbel/laporan.php' => ['permission' => ['reports', 'read']],
        '/admin/masjid/berita.php' => ['permission' => ['masjid_content', 'read']],
        '/admin/masjid/galeri.php' => ['permission' => ['masjid_content', 'read']],
        '/admin/masjid/pengaturan.php' => ['permission' => ['system_settings', 'read']],
        '/admin/reports.php' => ['permission' => ['reports', 'read']]
    ];
    
    /**
     * Check access for current page
     */
    public static function checkPageAccess($page_path = null) {
        if (!$page_path) {
            $page_path = $_SERVER['PHP_SELF'];
        }
        
        // Normalize path
        $page_path = str_replace('\\', '/', $page_path);
        
        if (!isset(self::PAGE_REQUIREMENTS[$page_path])) {
            // No specific requirements, allow access for logged in users
            return requireLogin();
        }
        
        $requirements = self::PAGE_REQUIREMENTS[$page_path];
        $user = getCurrentUser();
        
        if (!$user) {
            requireLogin();
            return false;
        }
        
        // Check role requirement
        if (isset($requirements['role'])) {
            if (!in_array($user['role'], $requirements['role'])) {
                http_response_code(403);
                include dirname(__DIR__) . '/includes/access_denied.php';
                exit();
            }
        }
        
        // Check permission requirement
        if (isset($requirements['permission'])) {
            $resource = $requirements['permission'][0];
            $action = $requirements['permission'][1] ?? 'read';
            
            if (!AccessControl::hasPermission($user['role'], $resource, $action)) {
                http_response_code(403);
                include dirname(__DIR__) . '/includes/access_denied.php';
                exit();
            }
        }
        
        return true;
    }
}

/**
 * CSRF Protection
 */
class CSRFProtection {
    
    /**
     * Generate and return CSRF token
     */
    public static function generateToken() {
        return generateCSRFToken();
    }
    
    /**
     * Verify CSRF token from request
     */
    public static function verifyToken($token = null) {
        if (!$token) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        }
        
        return verifyCSRFToken($token);
    }
    
    /**
     * Require valid CSRF token or exit
     */
    public static function requireToken($token = null) {
        if (!self::verifyToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
        
        return true;
    }
    
    /**
     * Generate hidden input field with CSRF token
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
    }
}

/**
 * Rate Limiting for login attempts
 */
class RateLimit {
    
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_TIME = 900; // 15 minutes
    
    /**
     * Check if IP is rate limited
     */
    public static function isLimited($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $attempts_file = 'logs/login_attempts_' . md5($ip) . '.json';
        
        if (!file_exists($attempts_file)) {
            return false;
        }
        
        $attempts = json_decode(file_get_contents($attempts_file), true);
        
        if (!$attempts) {
            return false;
        }
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($timestamp) {
            return (time() - $timestamp) < self::LOCKOUT_TIME;
        });
        
        return count($attempts) >= self::MAX_ATTEMPTS;
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordAttempt($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Create logs directory if it doesn't exist
        if (!file_exists('logs')) {
            mkdir('logs', 0755, true);
        }
        
        $attempts_file = 'logs/login_attempts_' . md5($ip) . '.json';
        
        $attempts = [];
        if (file_exists($attempts_file)) {
            $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
        }
        
        // Add current attempt
        $attempts[] = time();
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($timestamp) {
            return (time() - $timestamp) < self::LOCKOUT_TIME;
        });
        
        file_put_contents($attempts_file, json_encode(array_values($attempts)));
    }
    
    /**
     * Clear attempts for IP (on successful login)
     */
    public static function clearAttempts($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $attempts_file = 'logs/login_attempts_' . md5($ip) . '.json';
        
        if (file_exists($attempts_file)) {
            unlink($attempts_file);
        }
    }
    
    /**
     * Get remaining lockout time
     */
    public static function getLockoutTime($ip = null) {
        if (!$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $attempts_file = 'logs/login_attempts_' . md5($ip) . '.json';
        
        if (!file_exists($attempts_file)) {
            return 0;
        }
        
        $attempts = json_decode(file_get_contents($attempts_file), true);
        
        if (!$attempts || count($attempts) < self::MAX_ATTEMPTS) {
            return 0;
        }
        
        $oldest_attempt = min($attempts);
        $lockout_end = $oldest_attempt + self::LOCKOUT_TIME;
        
        return max(0, $lockout_end - time());
    }
}
?>