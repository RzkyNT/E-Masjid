<?php
/**
 * Image Path Helper
 * Fixed: filesystem-safe & URL-safe
 */

/**
 * Get image path for <img src="">
 */
function getImagePath(string $stored_path, string $from_location = 'public'): string
{
    if (empty($stored_path)) {
        return '';
    }

    $stored_path = ltrim($stored_path, './');

    switch ($from_location) {
        case 'admin':
            // admin/masjid/berita.php
            return '../../' . $stored_path;

        case 'public':
            // pages/berita.php
            return '../' . $stored_path;

        case 'root':
        default:
            return $stored_path;
    }
}

/**
 * REAL file existence check (filesystem)
 * This function needs to work regardless of where it's called from
 */
function imageExists(string $stored_path): bool
{
    if (empty($stored_path)) {
        return false;
    }

    $stored_path = ltrim($stored_path, './');

    // Try different possible locations based on common calling contexts
    $possible_paths = [
        $stored_path,                    // From project root
        '../' . $stored_path,            // From subdirectory (pages/, admin/)
        '../../' . $stored_path,         // From deeper subdirectory (admin/masjid/)
        dirname(__DIR__) . '/' . $stored_path,  // From includes/ directory
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return true;
        }
    }

    return false;
}

/**
 * Absolute image URL (SEO, OG, email)
 */
function getImageUrl(string $stored_path): string
{
    if (empty($stored_path)) {
        return '';
    }

    $stored_path = ltrim($stored_path, './');

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];

    return $scheme . '://' . $host . '/' . $stored_path;
}
?>