<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'list';
$gallery_id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle file upload
function handleFileUpload($file, $type = 'image') {
    $upload_dir = '../../assets/uploads/gallery/';
    $thumbnail_dir = '../../assets/uploads/gallery/thumbnails/';
    
    // Create directories if they don't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($thumbnail_dir)) {
        mkdir($thumbnail_dir, 0755, true);
    }
    
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_file_size = 10 * 1024 * 1024; // 10MB
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    if ($file['size'] > $max_file_size) {
        throw new Exception('File terlalu besar. Maksimal 10MB.');
    }
    
    $file_type = mime_content_type($file['tmp_name']);
    
    if ($type === 'image' && !in_array($file_type, $allowed_image_types)) {
        throw new Exception('Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.');
    }
    
    if ($type === 'video' && !in_array($file_type, $allowed_video_types)) {
        throw new Exception('Tipe video tidak didukung. Gunakan MP4, WebM, atau OGG.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Gagal menyimpan file.');
    }
    
    // Generate thumbnail for images
    if ($type === 'image') {
        generateThumbnail($filepath, $thumbnail_dir . $filename, 300, 300);
    }
    
    return 'assets/uploads/gallery/' . $filename;
}

// Generate thumbnail
function generateThumbnail($source, $destination, $width, $height) {
    $info = getimagesize($source);
    if (!$info) return false;
    
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    $original_width = imagesx($image);
    $original_height = imagesy($image);
    
    // Calculate aspect ratio
    $ratio = min($width / $original_width, $height / $original_height);
    $new_width = $original_width * $ratio;
    $new_height = $original_height * $ratio;
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    
    // Save thumbnail
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumbnail, $destination, 85);
            break;
        case 'image/png':
            imagepng($thumbnail, $destination);
            break;
        case 'image/gif':
            imagegif($thumbnail, $destination);
            break;
        case 'image/webp':
            imagewebp($thumbnail, $destination, 85);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumbnail);
    
    return true;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        $form_action = $_POST['form_action'] ?? '';
        
        if ($form_action === 'upload') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = $_POST['category'] ?? '';
            $file_type = $_POST['file_type'] ?? 'image';
            $status = $_POST['status'] ?? 'active';
            
            // Validate input
            if (empty($title) || empty($category)) {
                $error_message = 'Judul dan kategori harus diisi.';
            } elseif (!in_array($category, ['kegiatan', 'fasilitas', 'kajian'])) {
                $error_message = 'Kategori tidak valid.';
            } elseif (!in_array($file_type, ['image', 'video'])) {
                $error_message = 'Tipe file tidak valid.';
            } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
                $error_message = 'File harus dipilih.';
            } else {
                try {
                    $file_path = handleFileUpload($_FILES['file'], $file_type);
                    
                    // Get sort order
                    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM gallery");
                    $stmt->execute();
                    $sort_order = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO gallery (title, description, file_path, file_type, category, sort_order, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $description, $file_path, $file_type, $category, $sort_order, $status]);
                    
                    $success_message = 'File berhasil diupload.';
                    $action = 'list';
                } catch (Exception $e) {
                    $error_message = 'Gagal upload file: ' . $e->getMessage();
                }
            }
        } elseif ($form_action === 'update' && $gallery_id) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = $_POST['category'] ?? '';
            $status = $_POST['status'] ?? 'active';
            
            if (empty($title) || empty($category)) {
                $error_message = 'Judul dan kategori harus diisi.';
            } elseif (!in_array($category, ['kegiatan', 'fasilitas', 'kajian'])) {
                $error_message = 'Kategori tidak valid.';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE gallery 
                        SET title = ?, description = ?, category = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $description, $category, $status, $gallery_id]);
                    
                    $success_message = 'Item galeri berhasil diperbarui.';
                    $action = 'list';
                } catch (PDOException $e) {
                    $error_message = 'Terjadi kesalahan database: ' . $e->getMessage();
                }
            }
        } elseif ($form_action === 'delete' && $gallery_id) {
            try {
                // Get file path before deleting
                $stmt = $pdo->prepare("SELECT file_path FROM gallery WHERE id = ?");
                $stmt->execute([$gallery_id]);
                $item = $stmt->fetch();
                
                if ($item) {
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
                    $stmt->execute([$gallery_id]);
                    
                    // Delete files
                    $file_path = '../../' . $item['file_path'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    
                    // Delete thumbnail
                    $filename = basename($item['file_path']);
                    $thumbnail_path = '../../assets/uploads/gallery/thumbnails/' . $filename;
                    if (file_exists($thumbnail_path)) {
                        unlink($thumbnail_path);
                    }
                    
                    $success_message = 'Item galeri berhasil dihapus.';
                } else {
                    $error_message = 'Item tidak ditemukan.';
                }
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = 'Gagal menghapus item: ' . $e->getMessage();
            }
        } elseif ($form_action === 'update_order') {
            $orders = $_POST['orders'] ?? [];
            
            try {
                $pdo->beginTransaction();
                
                foreach ($orders as $id => $order) {
                    $stmt = $pdo->prepare("UPDATE gallery SET sort_order = ? WHERE id = ?");
                    $stmt->execute([$order, $id]);
                }
                
                $pdo->commit();
                $success_message = 'Urutan galeri berhasil diperbarui.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = 'Gagal memperbarui urutan: ' . $e->getMessage();
            }
        }
    }
}

// Get gallery item for edit
$gallery_item = null;
if ($action === 'edit' && $gallery_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$gallery_id]);
        $gallery_item = $stmt->fetch();
        
        if (!$gallery_item) {
            $error_message = 'Item galeri tidak ditemukan.';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = 'Gagal mengambil data item galeri.';
        $action = 'list';
    }
}

// Get gallery items for list view
$gallery_items = [];
$stats = ['total' => 0, 'images' => 0, 'videos' => 0];

if ($action === 'list' || $action === 'sort') {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM gallery 
            ORDER BY sort_order ASC, created_at DESC
        ");
        $stmt->execute();
        $gallery_items = $stmt->fetchAll();
        
        // Calculate stats
        $stats['total'] = count($gallery_items);
        foreach ($gallery_items as $item) {
            if ($item['file_type'] === 'image') {
                $stats['images']++;
            } else {
                $stats['videos']++;
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Gagal mengambil daftar galeri.';
    }
}

$page_title = 'Kelola Galeri';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-images text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Kelola Galeri</h1>
                        <p class="text-sm text-gray-500">Manajemen foto dan video</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../pages/galeri.php" class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Galeri
                    </a>
                    
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard CMS
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                
                <a href="berita.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-images mr-3"></i>Kelola Galeri
                </a>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-edit mr-3"></i>Kelola Keuangan
                </a>
                
                <a href="pengaturan.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-cog mr-3"></i>Pengaturan
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <!-- Gallery Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-images text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Item</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-image text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Foto</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['images']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-video text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Video</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $stats['videos']; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Daftar Galeri</h2>
                        <div class="flex space-x-2">
                            <a href="?action=sort" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-sort mr-2"></i>Atur Urutan
                            </a>
                            <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                <i class="fas fa-plus mr-2"></i>Upload File
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!empty($gallery_items)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($gallery_items as $item): ?>
                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                            <!-- Media Preview -->
                            <div class="aspect-square bg-gray-200 relative">
                                <?php if ($item['file_type'] === 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($base_url . '/' . $item['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                        <div class="text-white text-center">
                                            <i class="fas fa-play-circle text-4xl mb-2"></i>
                                            <p class="text-sm">Video</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="absolute top-2 left-2">
                                    <span class="<?php echo $item['status'] === 'active' ? 'bg-green-600' : 'bg-gray-600'; ?> text-white text-xs px-2 py-1 rounded-full">
                                        <?php echo $item['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>
                                
                                <!-- Category Badge -->
                                <div class="absolute top-2 right-2">
                                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                                        <?php echo ucfirst($item['category']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Item Info -->
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 mb-3">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                </p>
                                
                                <!-- Actions -->
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteItem(<?php echo $item['id']; ?>)" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        #<?php echo $item['sort_order']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-images text-gray-300 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada galeri</h3>
                        <p class="text-gray-500 mb-4">Mulai dengan mengupload foto atau video pertama Anda.</p>
                        <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Upload File
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($action === 'sort'): ?>
            <!-- Sort Gallery -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Atur Urutan Galeri</h2>
                        <a href="?action=list" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-blue-800 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Drag dan drop item untuk mengubah urutan tampilan di halaman galeri.
                        </p>
                    </div>
                    
                    <form method="POST" id="sortForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="form_action" value="update_order">
                        
                        <div id="sortable-gallery" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($gallery_items as $index => $item): ?>
                            <div class="sortable-item bg-gray-50 rounded-lg p-4 cursor-move border-2 border-transparent hover:border-blue-300" 
                                 data-id="<?php echo $item['id']; ?>">
                                <input type="hidden" name="orders[<?php echo $item['id']; ?>]" value="<?php echo $index + 1; ?>">
                                
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-grip-vertical text-gray-400"></i>
                                    </div>
                                    
                                    <div class="w-16 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                        <?php if ($item['file_type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($base_url . '/' . $item['file_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                                <i class="fas fa-video text-white text-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo ucfirst($item['category']); ?> • <?php echo ucfirst($item['file_type']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="text-sm text-gray-500">
                                        #<span class="order-number"><?php echo $index + 1; ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="?action=list" 
                               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Simpan Urutan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium text-gray-900">
                            <?php echo $action === 'add' ? 'Upload File Baru' : 'Edit Item Galeri'; ?>
                        </h2>
                        <a href="?action=list" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="form_action" value="<?php echo $action === 'add' ? 'upload' : 'update'; ?>">
                        
                        <?php if ($action === 'add'): ?>
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe File *</label>
                            <div class="flex space-x-4 mb-4">
                                <label class="flex items-center">
                                    <input type="radio" name="file_type" value="image" checked class="mr-2">
                                    <i class="fas fa-image mr-1"></i>Foto
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="file_type" value="video" class="mr-2">
                                    <i class="fas fa-video mr-1"></i>Video
                                </label>
                            </div>
                            
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                            <span>Upload file</span>
                                            <input id="file" name="file" type="file" class="sr-only" required accept="image/*,video/*">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, GIF, WebP hingga 10MB untuk foto<br>
                                        MP4, WebM, OGG hingga 10MB untuk video
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo htmlspecialchars($gallery_item['title'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   required>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Deskripsi opsional"><?php echo htmlspecialchars($gallery_item['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Category and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                                <select id="category" 
                                        name="category" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="kegiatan" <?php echo ($gallery_item['category'] ?? '') === 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                    <option value="fasilitas" <?php echo ($gallery_item['category'] ?? '') === 'fasilitas' ? 'selected' : ''; ?>>Fasilitas</option>
                                    <option value="kajian" <?php echo ($gallery_item['category'] ?? '') === 'kajian' ? 'selected' : ''; ?>>Kajian</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select id="status" 
                                        name="status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        required>
                                    <option value="active" <?php echo ($gallery_item['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo ($gallery_item['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="?action=list" 
                               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                <?php echo $action === 'add' ? 'Upload File' : 'Update Item'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus item ini? File akan dihapus permanen dan tindakan ini tidak dapat dibatalkan.</p>
            
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="form_action" value="delete">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Delete functionality
        function deleteItem(itemId) {
            document.getElementById('deleteForm').action = '?action=list&id=' + itemId;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        
        // File upload preview
        const fileInput = document.getElementById('file');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // You could add preview functionality here
                        console.log('File selected:', file.name);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Sortable functionality
        const sortableGallery = document.getElementById('sortable-gallery');
        if (sortableGallery) {
            new Sortable(sortableGallery, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    // Update order numbers
                    const items = sortableGallery.querySelectorAll('.sortable-item');
                    items.forEach((item, index) => {
                        const input = item.querySelector('input[type="hidden"]');
                        const orderNumber = item.querySelector('.order-number');
                        if (input) input.value = index + 1;
                        if (orderNumber) orderNumber.textContent = index + 1;
                    });
                }
            });
        }
        
        // File type radio button change
        const fileTypeRadios = document.querySelectorAll('input[name="file_type"]');
        fileTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const fileInput = document.getElementById('file');
                if (this.value === 'image') {
                    fileInput.accept = 'image/*';
                } else if (this.value === 'video') {
                    fileInput.accept = 'video/*';
                }
            });
        });
    </script>
    
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .sortable-ghost {
            opacity: 0.4;
        }
        
        .sortable-chosen {
            border-color: #3b82f6 !important;
        }
        
        .sortable-drag {
            transform: rotate(5deg);
        }
    </style>
</body>
</html>