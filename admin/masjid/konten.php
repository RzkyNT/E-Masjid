<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';
require_once '../../includes/upload_handler.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'dashboard';

$success_message = '';
$error_message = '';

// Initialize handlers
$upload_handler = new SecureUploadHandler('documents');
$backup_handler = new ContentBackup();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        $post_action = $_POST['action'] ?? '';
        
        switch ($post_action) {
            case 'upload_file':
                handleFileUpload();
                break;
            case 'delete_file':
                handleFileDelete();
                break;
            case 'create_backup':
                handleCreateBackup();
                break;
            case 'restore_backup':
                handleRestoreBackup();
                break;
            case 'delete_backup':
                handleDeleteBackup();
                break;
            case 'update_static_content':
                handleUpdateStaticContent();
                break;
        }
    }
}

// Handle file upload
function handleFileUpload() {
    global $upload_handler, $success_message, $error_message;
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Tidak ada file yang dipilih atau terjadi kesalahan upload.';
        return;
    }
    
    $custom_name = $_POST['custom_name'] ?? null;
    $result = $upload_handler->uploadFile($_FILES['file'], $custom_name);
    
    if ($result) {
        $success_message = 'File berhasil diupload: ' . $result['filename'];
        
        // Log upload activity
        logActivity('file_upload', 'File uploaded: ' . $result['filename']);
    } else {
        $error_message = 'Gagal upload file: ' . $upload_handler->getLastError();
    }
}

// Handle file deletion
function handleFileDelete() {
    global $upload_handler, $success_message, $error_message;
    
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        $error_message = 'Nama file tidak valid.';
        return;
    }
    
    if ($upload_handler->deleteFile($filename)) {
        $success_message = 'File berhasil dihapus: ' . $filename;
        
        // Log delete activity
        logActivity('file_delete', 'File deleted: ' . $filename);
    } else {
        $error_message = 'Gagal menghapus file: ' . $upload_handler->getLastError();
    }
}

// Handle backup creation
function handleCreateBackup() {
    global $backup_handler, $success_message, $error_message;
    
    $include_files = isset($_POST['include_files']) && $_POST['include_files'] === '1';
    
    $result = $backup_handler->createBackup($include_files);
    
    if ($result['success']) {
        $success_message = 'Backup berhasil dibuat: ' . $result['backup_name'];
        
        // Log backup activity
        logActivity('backup_create', 'Backup created: ' . $result['backup_name']);
    } else {
        $error_message = 'Gagal membuat backup: ' . $result['error'];
    }
}

// Handle backup restoration
function handleRestoreBackup() {
    global $backup_handler, $success_message, $error_message;
    
    $backup_file = $_POST['backup_file'] ?? '';
    
    if (empty($backup_file)) {
        $error_message = 'File backup tidak valid.';
        return;
    }
    
    $result = $backup_handler->restoreBackup($backup_file);
    
    if ($result['success']) {
        $success_message = 'Backup berhasil direstore.';
        
        // Log restore activity
        logActivity('backup_restore', 'Backup restored: ' . basename($backup_file));
    } else {
        $error_message = 'Gagal restore backup: ' . $result['error'];
    }
}

// Handle backup deletion
function handleDeleteBackup() {
    global $backup_handler, $success_message, $error_message;
    
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        $error_message = 'Nama file backup tidak valid.';
        return;
    }
    
    if ($backup_handler->deleteBackup($filename)) {
        $success_message = 'Backup berhasil dihapus: ' . $filename;
        
        // Log delete activity
        logActivity('backup_delete', 'Backup deleted: ' . $filename);
    } else {
        $error_message = 'Gagal menghapus backup.';
    }
}

// Handle static content update
function handleUpdateStaticContent() {
    global $pdo, $success_message, $error_message;
    
    $content_key = $_POST['content_key'] ?? '';
    $content_value = $_POST['content_value'] ?? '';
    
    if (empty($content_key)) {
        $error_message = 'Kunci konten tidak valid.';
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, 'textarea', 'Static content') 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$content_key, $content_value]);
        
        $success_message = 'Konten berhasil diperbarui.';
        
        // Log content update
        logActivity('content_update', 'Static content updated: ' . $content_key);
        
    } catch (PDOException $e) {
        $error_message = 'Gagal memperbarui konten: ' . $e->getMessage();
    }
}

// Get uploaded files list
function getUploadedFiles($category = 'documents') {
    $upload_dir = SecureUploadHandler::UPLOAD_DIRS[$category] ?? SecureUploadHandler::UPLOAD_DIRS['documents'];
    $files = [];
    
    if (file_exists($upload_dir)) {
        $scan_files = array_diff(scandir($upload_dir), array('.', '..', 'thumbnails', '.htaccess'));
        
        foreach ($scan_files as $file) {
            $file_path = $upload_dir . $file;
            if (is_file($file_path)) {
                $files[] = [
                    'name' => $file,
                    'size' => filesize($file_path),
                    'modified' => filemtime($file_path),
                    'path' => $file_path
                ];
            }
        }
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
    }
    
    return $files;
}

// Get static content
function getStaticContent($key) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : '';
    } catch (PDOException $e) {
        return '';
    }
}

// Format file size
function formatFileSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Get data for current action
$uploaded_files = getUploadedFiles('documents');
$backup_list = $backup_handler->getBackupList();
$static_contents = [
    'homepage_hero' => 'Konten Hero Homepage',
    'about_content' => 'Konten Tentang Masjid',
    'contact_info' => 'Informasi Kontak',
    'footer_text' => 'Teks Footer'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Konten - Admin Masjid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="../dashboard.php" class="text-gray-600 hover:text-gray-900 mr-4">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Konten</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($current_user['username']) ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="mb-8">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('files')" id="tab-files" class="tab-button active">
                    <i class="fas fa-file-alt mr-2"></i>
                    Manajemen File
                </button>
                <button onclick="showTab('content')" id="tab-content" class="tab-button">
                    <i class="fas fa-edit mr-2"></i>
                    Konten Statis
                </button>
                <button onclick="showTab('backup')" id="tab-backup" class="tab-button">
                    <i class="fas fa-database mr-2"></i>
                    Backup & Restore
                </button>
            </nav>
        </div>

        <!-- File Management Tab -->
        <div id="content-files" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- File Upload -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-upload mr-2 text-blue-600"></i>
                        Upload File
                    </h3>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="upload_file">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih File
                            </label>
                            <input type="file" name="file" required 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">
                                Maksimal 10MB. Format: PDF, DOC, DOCX, TXT, JPG, PNG
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Custom (Opsional)
                            </label>
                            <input type="text" name="custom_name" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nama file custom">
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            Upload File
                        </button>
                    </form>
                </div>

                <!-- File List -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-folder mr-2 text-green-600"></i>
                        File yang Diupload
                    </h3>
                    
                    <?php if (empty($uploaded_files)): ?>
                        <p class="text-gray-500 text-center py-8">
                            <i class="fas fa-folder-open text-4xl mb-4 block"></i>
                            Belum ada file yang diupload
                        </p>
                    <?php else: ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($uploaded_files as $file): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($file['name']) ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?= formatFileSize($file['size']) ?> • 
                                            <?= date('d/m/Y H:i', $file['modified']) ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= htmlspecialchars($file['path']) ?>" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus file ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="action" value="delete_file">
                                            <input type="hidden" name="filename" value="<?= htmlspecialchars($file['name']) ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Static Content Tab -->
        <div id="content-content" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">
                    <i class="fas fa-edit mr-2 text-purple-600"></i>
                    Edit Konten Statis
                </h3>
                
                <div class="space-y-6">
                    <?php foreach ($static_contents as $key => $label): ?>
                        <div class="border-b border-gray-200 pb-6 last:border-b-0">
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="action" value="update_static_content">
                                <input type="hidden" name="content_key" value="<?= $key ?>">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                    <textarea name="content_value" rows="4" 
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Masukkan konten..."><?= htmlspecialchars(getStaticContent($key)) ?></textarea>
                                </div>
                                
                                <button type="submit" class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan <?= htmlspecialchars($label) ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Backup & Restore Tab -->
        <div id="content-backup" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Create Backup -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                        Buat Backup
                    </h3>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="create_backup">
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_files" value="1" checked 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Sertakan file yang diupload</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-database mr-2"></i>
                            Buat Backup Sekarang
                        </button>
                    </form>
                    
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Backup akan menyimpan semua konten website termasuk artikel, galeri, dan pengaturan.
                        </p>
                    </div>
                </div>

                <!-- Backup List -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history mr-2 text-orange-600"></i>
                        Daftar Backup
                    </h3>
                    
                    <?php if (empty($backup_list)): ?>
                        <p class="text-gray-500 text-center py-8">
                            <i class="fas fa-archive text-4xl mb-4 block"></i>
                            Belum ada backup yang dibuat
                        </p>
                    <?php else: ?>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($backup_list as $backup): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($backup['filename']) ?></div>
                                        <div class="text-sm text-gray-500">
                                            <?= formatFileSize($backup['size']) ?> • 
                                            <?= date('d/m/Y H:i', $backup['created_at']) ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= htmlspecialchars($backup['path']) ?>" download 
                                           class="text-blue-600 hover:text-blue-800" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin restore backup ini? Data saat ini akan diganti.')">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="action" value="restore_backup">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['path']) ?>">
                                            <button type="submit" class="text-green-600 hover:text-green-800" title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus backup ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="action" value="delete_backup">
                                            <input type="hidden" name="filename" value="<?= htmlspecialchars($backup['filename']) ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>

    <style>
        .tab-button {
            @apply py-2 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 transition duration-200;
        }
        
        .tab-button.active {
            @apply text-blue-600 border-blue-600;
        }
    </style>
</body>
</html>