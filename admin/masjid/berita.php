<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'list';
$article_id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        $form_action = $_POST['form_action'] ?? '';
        
        if ($form_action === 'create' || $form_action === 'update') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $category = $_POST['category'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            // Validate input
            if (empty($title) || empty($content) || empty($category)) {
                $error_message = 'Judul, konten, dan kategori harus diisi.';
            } elseif (!in_array($category, ['kajian', 'pengumuman', 'kegiatan'])) {
                $error_message = 'Kategori tidak valid.';
            } elseif (!in_array($status, ['draft', 'published'])) {
                $error_message = 'Status tidak valid.';
            } else {
                try {
                    if ($form_action === 'create') {
                        // Check slug uniqueness
                        $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
                        $stmt->execute([$slug]);
                        if ($stmt->fetch()) {
                            $slug .= '-' . time();
                        }
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO articles (title, slug, content, excerpt, category, status, author_id, published_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
                        $stmt->execute([$title, $slug, $content, $excerpt, $category, $status, $current_user['id'], $published_at]);
                        
                        $success_message = 'Artikel berhasil dibuat.';
                        $action = 'list';
                    } else {
                        // Update existing article
                        $stmt = $pdo->prepare("
                            UPDATE articles 
                            SET title = ?, content = ?, excerpt = ?, category = ?, status = ?, 
                                published_at = CASE 
                                    WHEN status = 'draft' AND ? = 'published' THEN NOW() 
                                    WHEN status = 'published' AND ? = 'draft' THEN NULL 
                                    ELSE published_at 
                                END,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $content, $excerpt, $category, $status, $status, $status, $article_id]);
                        
                        $success_message = 'Artikel berhasil diperbarui.';
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Terjadi kesalahan database: ' . $e->getMessage();
                }
            }
        } elseif ($form_action === 'delete' && $article_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
                $stmt->execute([$article_id]);
                $success_message = 'Artikel berhasil dihapus.';
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = 'Gagal menghapus artikel: ' . $e->getMessage();
            }
        }
    }
}

// Get article data for edit
$article = null;
if ($action === 'edit' && $article_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $error_message = 'Artikel tidak ditemukan.';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = 'Gagal mengambil data artikel.';
        $action = 'list';
    }
}

// Get articles list for list view
$articles = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.full_name as author_name 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
        $articles = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = 'Gagal mengambil daftar artikel.';
    }
}

$page_title = 'Kelola Berita';
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
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-newspaper text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Kelola Berita</h1>
                        <p class="text-sm text-gray-500">Manajemen artikel dan berita</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../pages/berita.php" class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Berita
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
                
                <a href="berita.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
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
            <!-- Articles List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Daftar Artikel</h2>
                        <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Artikel
                        </a>
                    </div>
                    
                    <?php if (!empty($articles)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penulis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </div>
                                        <?php if ($article['excerpt']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars(substr($article['excerpt'], 0, 60) . '...'); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $category_colors = [
                                            'kajian' => 'bg-blue-100 text-blue-800',
                                            'pengumuman' => 'bg-yellow-100 text-yellow-800',
                                            'kegiatan' => 'bg-green-100 text-green-800'
                                        ];
                                        $color_class = $category_colors[$article['category']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?>">
                                            <?php echo ucfirst($article['category']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $article['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $article['status'] === 'published' ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <?php if ($article['status'] === 'published'): ?>
                                            <a href="../../pages/berita_detail.php?slug=<?php echo urlencode($article['slug']); ?>"
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?action=edit&id=<?php echo $article['id']; ?>" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteArticle(<?php echo $article['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-newspaper text-gray-300 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada artikel</h3>
                        <p class="text-gray-500 mb-4">Mulai dengan membuat artikel pertama Anda.</p>
                        <a href="?action=add" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Artikel
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium text-gray-900">
                            <?php echo $action === 'add' ? 'Tambah Artikel Baru' : 'Edit Artikel'; ?>
                        </h2>
                        <a href="?action=list" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
                        </a>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="form_action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
                        
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Artikel *</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   required>
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
                                    <option value="kajian" <?php echo ($article['category'] ?? '') === 'kajian' ? 'selected' : ''; ?>>Kajian</option>
                                    <option value="pengumuman" <?php echo ($article['category'] ?? '') === 'pengumuman' ? 'selected' : ''; ?>>Pengumuman</option>
                                    <option value="kegiatan" <?php echo ($article['category'] ?? '') === 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select id="status" 
                                        name="status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        required>
                                    <option value="draft" <?php echo ($article['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Excerpt -->
                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan</label>
                            <textarea id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Ringkasan singkat artikel (opsional)"><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Konten Artikel *</label>
                            <textarea id="content" 
                                      name="content" 
                                      rows="15"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
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
                                <?php echo $action === 'add' ? 'Simpan Artikel' : 'Update Artikel'; ?>
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
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.</p>
            
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
        function deleteArticle(articleId) {
            document.getElementById('deleteForm').action = '?action=list&id=' + articleId;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        
        // Auto-save draft functionality
        let autoSaveTimer;
        const titleInput = document.getElementById('title');
        const contentTextarea = document.getElementById('content');
        
        if (titleInput && contentTextarea) {
            function autoSave() {
                // This would implement auto-save functionality
                console.log('Auto-saving draft...');
            }
            
            [titleInput, contentTextarea].forEach(element => {
                element.addEventListener('input', function() {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(autoSave, 5000); // Auto-save after 5 seconds of inactivity
                });
            });
        }
        
        // Character counter for excerpt
        const excerptTextarea = document.getElementById('excerpt');
        if (excerptTextarea) {
            const maxLength = 200;
            const counter = document.createElement('div');
            counter.className = 'text-sm text-gray-500 mt-1';
            excerptTextarea.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - excerptTextarea.value.length;
                counter.textContent = `${excerptTextarea.value.length}/${maxLength} karakter`;
                counter.className = remaining < 0 ? 'text-sm text-red-500 mt-1' : 'text-sm text-gray-500 mt-1';
            }
            
            excerptTextarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    </script>
</body>
</html>