<?php
/**
 * Session Check Middleware
 * For Masjid Al-Muhajirin Information System
 * 
 * This file should be included at the top of admin pages that require authentication
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/auth.php';

/**
 * Check if user is logged in and redirect if not
 */
function checkSession($redirect_url = null) {
    if (!isLoggedIn()) {
        if (!$redirect_url) {
            // Determine redirect URL based on current location
            $current_dir = dirname($_SERVER['PHP_SELF']);
            if (strpos($current_dir, '/admin/bimbel') !== false) {
                $redirect_url = '../login.php';
            } elseif (strpos($current_dir, '/admin') !== false) {
                $redirect_url = 'login.php';
            } else {
                $redirect_url = '/admin/login.php';
            }
        }
        
        // Store the current URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        header("Location: $redirect_url");
        exit();
    }
    
    return true;
}

/**
 * Check if user has required role
 */
function checkRole($required_roles, $redirect_url = null) {
    checkSession($redirect_url);
    
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Convert single role to array
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    if (!in_array($user['role'], $required_roles)) {
        // Log unauthorized access attempt
        logSecurityEvent('UNAUTHORIZED_ACCESS', "User {$user['username']} attempted to access restricted area");
        
        // Show access denied page
        http_response_code(403);
        include dirname(__DIR__) . '/includes/access_denied.php';
        exit();
    }
    
    return true;
}

/**
 * Check if user has permission for specific resource and action
 */
function checkPermission($resource, $action = 'read', $redirect_url = null) {
    checkSession($redirect_url);
    
    if (!hasPermission($resource, $action)) {
        $user = getCurrentUser();
        logSecurityEvent('PERMISSION_DENIED', "User {$user['username']} denied access to $resource:$action");
        
        http_response_code(403);
        include dirname(__DIR__) . '/includes/access_denied.php';
        exit();
    }
    
    return true;
}

/**
 * Auto-check session for admin pages
 * This function is called automatically when this file is included
 */
function autoCheckSession() {
    // Only auto-check for admin pages
    $current_path = $_SERVER['PHP_SELF'];
    
    // Skip session check for login and logout pages
    if (strpos($current_path, 'login.php') !== false || 
        strpos($current_path, 'logout.php') !== false) {
        return;
    }
    
    // Check if this is an admin page
    if (strpos($current_path, '/admin/') !== false) {
        checkSession();
        
        // Update last activity for session timeout
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Get breadcrumb navigation based on current page
 */
function getBreadcrumb() {
    $current_path = $_SERVER['PHP_SELF'];
    $breadcrumb = [];
    
    // Base breadcrumb
    $breadcrumb[] = ['title' => 'Dashboard', 'url' => '/admin/dashboard.php'];
    
    // Add specific breadcrumbs based on path
    if (strpos($current_path, '/admin/bimbel/') !== false) {
        $breadcrumb[] = ['title' => 'Bimbel', 'url' => '/admin/bimbel/dashboard.php'];
        
        if (strpos($current_path, 'siswa.php') !== false) {
            $breadcrumb[] = ['title' => 'Manajemen Siswa', 'url' => ''];
        } elseif (strpos($current_path, 'mentor.php') !== false) {
            $breadcrumb[] = ['title' => 'Manajemen Mentor', 'url' => ''];
        } elseif (strpos($current_path, 'absensi') !== false) {
            $breadcrumb[] = ['title' => 'Absensi', 'url' => ''];
        } elseif (strpos($current_path, 'keuangan.php') !== false) {
            $breadcrumb[] = ['title' => 'Keuangan', 'url' => ''];
        } elseif (strpos($current_path, 'laporan.php') !== false) {
            $breadcrumb[] = ['title' => 'Laporan', 'url' => ''];
        }
    } elseif (strpos($current_path, 'users.php') !== false) {
        $breadcrumb[] = ['title' => 'Manajemen Pengguna', 'url' => ''];
    } elseif (strpos($current_path, 'reports.php') !== false) {
        $breadcrumb[] = ['title' => 'Laporan', 'url' => ''];
    }
    
    return $breadcrumb;
}

/**
 * Generate navigation menu based on user permissions
 */
function getNavigationMenu() {
    $user = getCurrentUser();
    if (!$user) {
        return [];
    }
    
    $menu = [];
    
    // Dashboard (always available for logged in users)
    $menu[] = [
        'title' => 'Dashboard',
        'url' => '/admin/dashboard.php',
        'icon' => 'fas fa-home',
        'active' => strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false
    ];
    
    // Website Management (for admin_masjid)
    if (hasPermission('masjid_content')) {
        $menu[] = [
            'title' => 'Website Masjid',
            'url' => '/admin/masjid/',
            'icon' => 'fas fa-globe',
            'active' => strpos($_SERVER['PHP_SELF'], '/admin/masjid/') !== false,
            'submenu' => [
                ['title' => 'Kelola Berita', 'url' => '/admin/masjid/berita.php'],
                ['title' => 'Kelola Galeri', 'url' => '/admin/masjid/galeri.php'],
                ['title' => 'Pengaturan', 'url' => '/admin/masjid/pengaturan.php']
            ]
        ];
    }
    
    // Bimbel Management
    if (hasPermission('bimbel_management')) {
        $menu[] = [
            'title' => 'Sistem Bimbel',
            'url' => '/admin/bimbel/dashboard.php',
            'icon' => 'fas fa-graduation-cap',
            'active' => strpos($_SERVER['PHP_SELF'], '/admin/bimbel/') !== false,
            'submenu' => [
                ['title' => 'Dashboard Bimbel', 'url' => '/admin/bimbel/dashboard.php'],
                ['title' => 'Manajemen Siswa', 'url' => '/admin/bimbel/siswa.php'],
                ['title' => 'Manajemen Mentor', 'url' => '/admin/bimbel/mentor.php'],
                ['title' => 'Absensi Siswa', 'url' => '/admin/bimbel/absensi_siswa.php'],
                ['title' => 'Absensi Mentor', 'url' => '/admin/bimbel/absensi_mentor.php'],
                ['title' => 'Keuangan', 'url' => '/admin/bimbel/keuangan.php'],
                ['title' => 'Laporan', 'url' => '/admin/bimbel/laporan.php']
            ]
        ];
    }
    
    // User Management (admin_masjid only)
    if (hasPermission('user_management')) {
        $menu[] = [
            'title' => 'Kelola Pengguna',
            'url' => '/admin/users.php',
            'icon' => 'fas fa-users',
            'active' => strpos($_SERVER['PHP_SELF'], 'users.php') !== false
        ];
    }
    
    // Reports (all roles can access)
    if (hasPermission('reports')) {
        $menu[] = [
            'title' => 'Laporan',
            'url' => '/admin/reports.php',
            'icon' => 'fas fa-chart-bar',
            'active' => strpos($_SERVER['PHP_SELF'], 'reports.php') !== false
        ];
    }
    
    return $menu;
}

/**
 * Check for session timeout and show warning
 */
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $last_activity = $_SESSION['last_activity'] ?? time();
    $time_left = SESSION_TIMEOUT - (time() - $last_activity);
    
    // If less than 5 minutes left, show warning
    if ($time_left < 300 && $time_left > 0) {
        return [
            'warning' => true,
            'time_left' => $time_left,
            'message' => 'Sesi Anda akan berakhir dalam ' . ceil($time_left / 60) . ' menit.'
        ];
    }
    
    return ['warning' => false, 'time_left' => $time_left];
}

// Auto-check session when this file is included
autoCheckSession();
?>