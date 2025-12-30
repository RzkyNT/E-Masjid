<?php
/**
 * Secure File Upload Handler
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides secure file upload functionality with validation and security checks
 */

require_once dirname(__DIR__) . '/config/config.php';

class SecureUploadHandler {
    
    // Allowed file types and their MIME types
    const ALLOWED_TYPES = [
        'image' => [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp']
        ],
        'document' => [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain']
        ],
        'video' => [
            'mp4' => ['video/mp4'],
            'webm' => ['video/webm'],
            'ogg' => ['video/ogg']
        ]
    ];
    
    // Maximum file sizes (in bytes)
    const MAX_SIZES = [
        'image' => 5242880,    // 5MB
        'document' => 10485760, // 10MB
        'video' => 52428800    // 50MB
    ];
    
    // Upload directories
    const UPLOAD_DIRS = [
        'articles' => 'assets/uploads/articles/',
        'gallery' => 'assets/uploads/gallery/',
        'documents' => 'assets/uploads/documents/',
        'settings' => 'assets/uploads/settings/'
    ];
    
    private $upload_dir;
    private $allowed_category;
    private $errors = [];
    
    public function __construct($category = 'articles') {
        $this->allowed_category = $category;
        $base_upload_dir = self::UPLOAD_DIRS[$category] ?? self::UPLOAD_DIRS['articles'];
        
        // Find the project root directory
        $project_root = $this->findProjectRoot();
        $this->upload_dir = $project_root . $base_upload_dir;
        
        // Create upload directory if it doesn't exist
        $this->createUploadDirectory();
    }
    
    /**
     * Find the project root directory
     */
    private function findProjectRoot() {
        $current_dir = __DIR__;
        
        // Look for config directory to identify project root
        while ($current_dir !== dirname($current_dir)) {
            if (is_dir($current_dir . '/config') && file_exists($current_dir . '/config/config.php')) {
                return $current_dir . '/';
            }
            $current_dir = dirname($current_dir);
        }
        
        // Fallback: assume we're in includes/ directory
        return dirname(__DIR__) . '/';
    }
    
    /**
     * Upload single file
     */
    public function uploadFile($file, $custom_name = null) {
        $this->errors = [];
        
        // Validate file input
        if (!$this->validateFileInput($file)) {
            return false;
        }
        
        // Get file info
        $file_info = $this->getFileInfo($file, $custom_name);
        
        // Validate file
        if (!$this->validateFile($file, $file_info)) {
            return false;
        }
        
        // Move uploaded file
        $destination = $this->upload_dir . $file_info['filename'];
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Gagal memindahkan file ke direktori tujuan.';
            return false;
        }
        
        // Set proper permissions
        chmod($destination, 0644);
        
        // Generate thumbnail for images
        if ($file_info['category'] === 'image') {
            $this->generateThumbnail($destination, $file_info);
        }
        
        return [
            'success' => true,
            'filename' => $file_info['filename'],
            'original_name' => $file_info['original_name'],
            'file_path' => $destination,
            'relative_path' => (self::UPLOAD_DIRS[$this->allowed_category] ?? self::UPLOAD_DIRS['articles']) . $file_info['filename'],
            'file_size' => $file['size'],
            'file_type' => $file_info['category'],
            'mime_type' => $file['type']
        ];
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultipleFiles($files) {
        $results = [];
        $this->errors = [];
        
        // Handle different file input formats
        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files in single input
            $file_count = count($files['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $result = $this->uploadFile($file);
                if ($result) {
                    $results[] = $result;
                }
            }
        } else {
            // Multiple separate file inputs
            foreach ($files as $file) {
                $result = $this->uploadFile($file);
                if ($result) {
                    $results[] = $result;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Delete uploaded file
     */
    public function deleteFile($filename) {
        $file_path = $this->upload_dir . $filename;
        
        if (!file_exists($file_path)) {
            $this->errors[] = 'File tidak ditemukan.';
            return false;
        }
        
        // Delete main file
        if (!unlink($file_path)) {
            $this->errors[] = 'Gagal menghapus file.';
            return false;
        }
        
        // Delete thumbnail if exists
        $thumbnail_path = $this->upload_dir . 'thumbnails/' . $filename;
        if (file_exists($thumbnail_path)) {
            unlink($thumbnail_path);
        }
        
        return true;
    }
    
    /**
     * Get upload errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get last error message
     */
    public function getLastError() {
        return end($this->errors) ?: '';
    }
    
    /**
     * Validate file input
     */
    private function validateFileInput($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Parameter file tidak valid.';
            return false;
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'Tidak ada file yang dipilih.';
                return false;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File terlalu besar.';
                return false;
            default:
                $this->errors[] = 'Terjadi kesalahan saat upload.';
                return false;
        }
        
        return true;
    }
    
    /**
     * Get file information
     */
    private function getFileInfo($file, $custom_name = null) {
        $original_name = $file['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // Determine file category
        $category = $this->getFileCategory($extension);
        
        // Generate safe filename
        if ($custom_name) {
            $base_name = $this->sanitizeFilename($custom_name);
        } else {
            $base_name = $this->sanitizeFilename(pathinfo($original_name, PATHINFO_FILENAME));
        }
        
        // Add timestamp to prevent conflicts
        $filename = $base_name . '_' . time() . '.' . $extension;
        
        return [
            'original_name' => $original_name,
            'filename' => $filename,
            'extension' => $extension,
            'category' => $category
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file, $file_info) {
        $extension = $file_info['extension'];
        $category = $file_info['category'];
        
        // Check if extension is allowed
        if (!$this->isExtensionAllowed($extension)) {
            $this->errors[] = "Tipe file .$extension tidak diizinkan.";
            return false;
        }
        
        // Check MIME type
        if (!$this->isMimeTypeAllowed($file['type'], $extension)) {
            $this->errors[] = 'MIME type file tidak valid.';
            return false;
        }
        
        // Check file size
        if (!$this->isFileSizeAllowed($file['size'], $category)) {
            $max_size = $this->formatBytes(self::MAX_SIZES[$category]);
            $this->errors[] = "Ukuran file terlalu besar. Maksimal $max_size.";
            return false;
        }
        
        // Additional security checks
        if (!$this->performSecurityChecks($file)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if file extension is allowed
     */
    private function isExtensionAllowed($extension) {
        foreach (self::ALLOWED_TYPES as $category => $types) {
            if (array_key_exists($extension, $types)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if MIME type is allowed for extension
     */
    private function isMimeTypeAllowed($mime_type, $extension) {
        foreach (self::ALLOWED_TYPES as $category => $types) {
            if (isset($types[$extension]) && in_array($mime_type, $types[$extension])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if file size is within limits
     */
    private function isFileSizeAllowed($size, $category) {
        return $size <= (self::MAX_SIZES[$category] ?? self::MAX_SIZES['image']);
    }
    
    /**
     * Get file category based on extension
     */
    private function getFileCategory($extension) {
        foreach (self::ALLOWED_TYPES as $category => $types) {
            if (array_key_exists($extension, $types)) {
                return $category;
            }
        }
        return 'unknown';
    }
    
    /**
     * Perform additional security checks
     */
    private function performSecurityChecks($file) {
        // Check for PHP code in uploaded files
        $content = file_get_contents($file['tmp_name']);
        
        if (preg_match('/<\?php|<\?=|<script/i', $content)) {
            $this->errors[] = 'File mengandung kode yang tidak diizinkan.';
            return false;
        }
        
        // Check file signature for images
        if (strpos($file['type'], 'image/') === 0) {
            $image_info = @getimagesize($file['tmp_name']);
            if (!$image_info) {
                $this->errors[] = 'File gambar tidak valid.';
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename) {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Trim underscores from start and end
        $filename = trim($filename, '_');
        
        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'file';
        }
        
        return $filename;
    }
    
    /**
     * Create upload directory if it doesn't exist
     */
    private function createUploadDirectory() {
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
        
        // Create thumbnails directory for images
        $thumbnails_dir = $this->upload_dir . 'thumbnails/';
        if (!file_exists($thumbnails_dir)) {
            mkdir($thumbnails_dir, 0755, true);
        }
        
        // Create .htaccess for security
        $htaccess_path = $this->upload_dir . '.htaccess';
        if (!file_exists($htaccess_path)) {
            $htaccess_content = "# Prevent direct access to uploaded files\n";
            $htaccess_content .= "Options -Indexes\n";
            $htaccess_content .= "<Files *.php>\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</Files>\n";
            
            file_put_contents($htaccess_path, $htaccess_content);
        }
    }
    
    /**
     * Generate thumbnail for images
     */
    private function generateThumbnail($source_path, $file_info) {
        if ($file_info['category'] !== 'image') {
            return false;
        }
        
        $thumbnail_dir = $this->upload_dir . 'thumbnails/';
        $thumbnail_path = $thumbnail_dir . $file_info['filename'];
        
        // Get image dimensions
        $image_info = getimagesize($source_path);
        if (!$image_info) {
            return false;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        $type = $image_info[2];
        
        // Calculate thumbnail dimensions (max 300x300)
        $max_size = 300;
        if ($width > $height) {
            $new_width = $max_size;
            $new_height = ($height * $max_size) / $width;
        } else {
            $new_height = $max_size;
            $new_width = ($width * $max_size) / $height;
        }
        
        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($source_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($source_path);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Resize image
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumbnail_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumbnail_path);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumbnail_path);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return true;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

/**
 * Content Backup and Restore Functions
 */
class ContentBackup {
    
    private $backup_dir = 'backups/';
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        
        // Create backup directory
        if (!file_exists($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }
    
    /**
     * Create full content backup
     */
    public function createBackup($include_files = true) {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_name = "content_backup_$timestamp";
        $backup_path = $this->backup_dir . $backup_name;
        
        // Create backup directory
        mkdir($backup_path, 0755, true);
        
        try {
            // Backup database content
            $this->backupDatabase($backup_path);
            
            // Backup uploaded files
            if ($include_files) {
                $this->backupFiles($backup_path);
            }
            
            // Create backup info file
            $this->createBackupInfo($backup_path, $include_files);
            
            // Create ZIP archive
            $zip_path = $backup_path . '.zip';
            $this->createZipArchive($backup_path, $zip_path);
            
            // Remove temporary directory
            $this->removeDirectory($backup_path);
            
            return [
                'success' => true,
                'backup_file' => $zip_path,
                'backup_name' => $backup_name . '.zip',
                'size' => filesize($zip_path)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Restore content from backup
     */
    public function restoreBackup($backup_file) {
        if (!file_exists($backup_file)) {
            throw new Exception('File backup tidak ditemukan.');
        }
        
        $temp_dir = $this->backup_dir . 'temp_restore_' . time();
        
        try {
            // Extract ZIP file
            $this->extractZipArchive($backup_file, $temp_dir);
            
            // Restore database
            $this->restoreDatabase($temp_dir);
            
            // Restore files
            $this->restoreFiles($temp_dir);
            
            // Clean up
            $this->removeDirectory($temp_dir);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            // Clean up on error
            if (file_exists($temp_dir)) {
                $this->removeDirectory($temp_dir);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get list of available backups
     */
    public function getBackupList() {
        $backups = [];
        $files = glob($this->backup_dir . 'content_backup_*.zip');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'path' => $file,
                'size' => filesize($file),
                'created_at' => filemtime($file)
            ];
        }
        
        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });
        
        return $backups;
    }
    
    /**
     * Delete backup file
     */
    public function deleteBackup($filename) {
        $file_path = $this->backup_dir . $filename;
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        return unlink($file_path);
    }
    
    /**
     * Backup database content
     */
    private function backupDatabase($backup_path) {
        $tables = ['articles', 'gallery', 'contacts', 'settings'];
        $sql_content = '';
        
        foreach ($tables as $table) {
            // Get table structure
            $stmt = $this->pdo->query("SHOW CREATE TABLE $table");
            $row = $stmt->fetch();
            $sql_content .= "\n-- Table structure for $table\n";
            $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql_content .= $row['Create Table'] . ";\n\n";
            
            // Get table data
            $stmt = $this->pdo->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $sql_content .= "-- Data for table $table\n";
                $sql_content .= "INSERT INTO `$table` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $escaped_values = array_map(function($value) {
                        return $this->pdo->quote($value);
                    }, array_values($row));
                    $values[] = '(' . implode(', ', $escaped_values) . ')';
                }
                
                $sql_content .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        file_put_contents($backup_path . '/database.sql', $sql_content);
    }
    
    /**
     * Backup uploaded files
     */
    private function backupFiles($backup_path) {
        $upload_dirs = [
            'assets/uploads/articles/',
            'assets/uploads/gallery/',
            'assets/uploads/documents/',
            'assets/uploads/settings/'
        ];
        
        foreach ($upload_dirs as $dir) {
            if (file_exists($dir)) {
                $this->copyDirectory($dir, $backup_path . '/' . $dir);
            }
        }
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($backup_path, $include_files) {
        $info = [
            'created_at' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'include_files' => $include_files,
            'tables' => ['articles', 'gallery', 'contacts', 'settings']
        ];
        
        file_put_contents($backup_path . '/backup_info.json', json_encode($info, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create ZIP archive
     */
    private function createZipArchive($source_dir, $zip_path) {
        $zip = new ZipArchive();
        
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Gagal membuat file ZIP.');
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = substr($file_path, strlen($source_dir) + 1);
                $zip->addFile($file_path, $relative_path);
            }
        }
        
        $zip->close();
    }
    
    /**
     * Extract ZIP archive
     */
    private function extractZipArchive($zip_path, $extract_to) {
        $zip = new ZipArchive();
        
        if ($zip->open($zip_path) !== TRUE) {
            throw new Exception('Gagal membuka file ZIP.');
        }
        
        if (!$zip->extractTo($extract_to)) {
            throw new Exception('Gagal mengekstrak file ZIP.');
        }
        
        $zip->close();
    }
    
    /**
     * Restore database from backup
     */
    private function restoreDatabase($backup_path) {
        $sql_file = $backup_path . '/database.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception('File database backup tidak ditemukan.');
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Execute SQL statements
        $this->pdo->exec($sql_content);
    }
    
    /**
     * Restore files from backup
     */
    private function restoreFiles($backup_path) {
        $upload_dirs = [
            'assets/uploads/articles/',
            'assets/uploads/gallery/',
            'assets/uploads/documents/',
            'assets/uploads/settings/'
        ];
        
        foreach ($upload_dirs as $dir) {
            $backup_dir = $backup_path . '/' . $dir;
            if (file_exists($backup_dir)) {
                // Remove existing files
                if (file_exists($dir)) {
                    $this->removeDirectory($dir);
                }
                
                // Copy backup files
                $this->copyDirectory($backup_dir, $dir);
            }
        }
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination) {
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!file_exists($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item, $target);
            }
        }
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        return rmdir($dir);
    }
}
?>