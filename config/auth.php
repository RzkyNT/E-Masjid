<?php
require_once 'config.php';

/**
 * Authentication and Authorization Functions
 * For Masjid Al-Muhajirin Information System
 */

/**
 * Hash password using PHP's password_hash function
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Login user with username and password
 */
function login($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Username atau password tidak valid'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}

/**
 * Logout user and destroy session
 */
function logout() {
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        logout();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Get current logged in user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Check if user has specific role
 */
function hasRole($required_role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    if (is_array($required_role)) {
        return in_array($user['role'], $required_role);
    }
    
    return $user['role'] === $required_role;
}

/**
 * Check if user has permission for specific resource
 */
function hasPermission($resource, $action = 'read') {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    $role = $user['role'];
    
    switch ($resource) {
        case 'user_management':
            return $role === 'admin_masjid' && in_array($action, ['create', 'read', 'update', 'delete']);
            
        case 'bimbel_management':
            if ($role === 'admin_bimbel') {
                return in_array($action, ['create', 'read', 'update', 'delete']);
            } elseif ($role === 'admin_masjid') {
                return $action === 'read';
            }
            return false;
            
        case 'masjid_content':
            if ($role === 'admin_masjid') {
                return in_array($action, ['create', 'read', 'update', 'delete']);
            } elseif ($role === 'admin_bimbel') {
                return $action === 'read';
            }
            return false;
            
        case 'reports':
            if (in_array($role, ['admin_masjid', 'admin_bimbel'])) {
                return in_array($action, ['read', 'export']);
            } elseif ($role === 'viewer') {
                return $action === 'read';
            }
            return false;
            
        case 'financial_detail':
            if ($role === 'admin_bimbel') {
                return in_array($action, ['create', 'read', 'update', 'delete']);
            } elseif ($role === 'admin_masjid') {
                return $action === 'read';
            }
            return false;
            
        default:
            return false;
    }
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin($redirect_url = null) {
    if (!isLoggedIn()) {
        if (!$redirect_url) {
            $redirect_url = APP_URL . '/admin/login.php';
        }
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require specific role - show access denied if insufficient permission
 */
function requireRole($required_role, $redirect_url = null) {
    if (!isLoggedIn()) {
        if (!$redirect_url) {
            $redirect_url = APP_URL . '/admin/login.php';
        }
        header("Location: $redirect_url");
        exit();
    }
    
    if (!hasRole($required_role)) {
        http_response_code(403);
        include 'includes/access_denied.php';
        exit();
    }
}

/**
 * Require specific permission for resource
 */
function requirePermission($resource, $action = 'read', $redirect_url = null) {
    if (!isLoggedIn()) {
        if (!$redirect_url) {
            $redirect_url = APP_URL . '/admin/login.php';
        }
        header("Location: $redirect_url");
        exit();
    }
    
    if (!hasPermission($resource, $action)) {
        http_response_code(403);
        include 'includes/access_denied.php';
        exit();
    }
}

/**
 * Get user dashboard URL based on role
 */
function getDashboardUrl($role = null) {
    if (!$role) {
        $user = getCurrentUser();
        $role = $user ? $user['role'] : null;
    }
    
    switch ($role) {
        case 'admin_masjid':
            return APP_URL . '/admin/dashboard.php';
        case 'admin_bimbel':
            return APP_URL . '/admin/bimbel/dashboard.php';
        case 'viewer':
            return APP_URL . '/admin/reports.php';
        default:
            return APP_URL . '/admin/login.php';
    }
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random password
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = '') {
    $user = getCurrentUser();
    $user_id = $user ? $user['id'] : null;
    $username = $user ? $user['username'] : 'anonymous';
    
    $log_entry = date('Y-m-d H:i:s') . " - $event - User: $username (ID: $user_id) - IP: " . $_SERVER['REMOTE_ADDR'] . " - $details" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents('logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log general activity events
 */
function logActivity($action, $details = '') {
    $user = getCurrentUser();
    $user_id = $user ? $user['id'] : null;
    $username = $user ? $user['username'] : 'anonymous';
    
    $log_entry = date('Y-m-d H:i:s') . " - $action - User: $username (ID: $user_id) - IP: " . $_SERVER['REMOTE_ADDR'] . " - $details" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents('logs/activity.log', $log_entry, FILE_APPEND | LOCK_EX);
}
?>