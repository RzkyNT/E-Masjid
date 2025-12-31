<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';
require_once '../../includes/image_path_helper.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'list';
$article_id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Get article data for edit (needed for form processing)
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
            $featured_image = '';
            
            // Handle image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                require_once '../../includes/upload_handler.php';
                $upload_handler = new SecureUploadHandler('articles');
                $upload_result = $upload_handler->uploadFile($_FILES['featured_image'], 'featured_image');
                
                if ($upload_result) {
                    $featured_image = $upload_result['relative_path'];
                } else {
                    $error_message = 'Gagal upload gambar: ' . $upload_handler->getLastError();
                }
            } elseif ($form_action === 'update' && $article) {
                // Keep existing image if no new image uploaded
                $featured_image = $article['featured_image'] ?? '';
            }
            
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
                            INSERT INTO articles (title, slug, content, excerpt, category, status, featured_image, author_id, published_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
                        $stmt->execute([$title, $slug, $content, $excerpt, $category, $status, $featured_image, $current_user['id'], $published_at]);
                        
                        $success_message = 'Artikel berhasil dibuat.';
                        $action = 'list';
                    } else {
                        // Update existing article
                        $stmt = $pdo->prepare("
                            UPDATE articles 
                            SET title = ?, content = ?, excerpt = ?, category = ?, status = ?, featured_image = ?,
                                published_at = CASE 
                                    WHEN status = 'draft' AND ? = 'published' THEN NOW() 
                                    WHEN status = 'published' AND ? = 'draft' THEN NULL 
                                    ELSE published_at 
                                END,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $content, $excerpt, $category, $status, $featured_image, $status, $status, $article_id]);
                        
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
$page_description = 'Manajemen artikel dan berita masjid';

// Include admin header with sidebar
include '../../partials/admin_header.php';
?>

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
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
                                        <?php if (!empty($article['featured_image']) && imageExists($article['featured_image'])): ?>
                                            <img 
                                                src="<?php echo htmlspecialchars(getImagePath($article['featured_image'], 'admin')); ?>" 
                                                alt="<?php echo htmlspecialchars($article['title']); ?>"
                                                class="w-16 h-16 object-cover rounded-lg"
                                            >
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

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
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
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
                        
                        <!-- Featured Image Upload -->
                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama</label>
                            <div class="space-y-3">
                                <?php if (!empty($article['featured_image']) && imageExists($article['featured_image'])): ?>
                                <div class="current-image">
                                    <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                                    <img src="<?php echo htmlspecialchars(getImagePath($article['featured_image'], 'admin')); ?>" 
                                         alt="Current featured image" 
                                         class="image-preview border border-gray-300">
                                </div>
                                <?php endif; ?>
                                
                                <input type="file" 
                                       id="featured_image" 
                                       name="featured_image" 
                                       accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                       onchange="previewImage(this)">
                                <p class="text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 5MB. Rekomendasi: 800x600px</p>
                                
                                <!-- Image Preview -->
                                <div id="imagePreview" class="hidden">
                                    <p class="text-sm text-gray-600 mb-2">Preview gambar baru:</p>
                                    <img id="previewImg" src="" alt="Preview" class="image-preview border border-gray-300">
                                </div>
                            </div>
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
                            
                            <!-- Quill Editor Container -->
                            <div id="quill-editor" style="height: 300px; display: block;"></div>
                            
                            <!-- Hidden textarea for form submission -->
                            <textarea id="content" 
                                      name="content" 
                                      style="display: none;" 
                                      required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                            
                            <!-- Loading indicator -->
                            <div id="editor-loading" class="text-center py-4 text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Memuat editor...
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
    
    <!-- Quill.js JavaScript with fallback -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js" 
            onerror="loadQuillFallback()"></script>
    
    <script>
        // Fallback function to load alternative Quill CDN
        function loadQuillFallback() {
            console.log('Primary Quill CDN failed, trying fallback...');
            const script = document.createElement('script');
            script.src = 'https://cdn.quilljs.com/1.3.6/quill.min.js';
            script.onload = function() {
                console.log('✅ Fallback Quill.js loaded successfully');
            };
            script.onerror = function() {
                console.error('❌ Both Quill CDNs failed to load');
            };
            document.head.appendChild(script);
        }
        
        // Check if Quill loaded from primary CDN
        setTimeout(function() {
            if (typeof Quill !== 'undefined') {
                console.log('✅ Primary Quill.js loaded successfully');
            } else {
                console.log('⏳ Waiting for fallback Quill.js...');
            }
        }, 100);
    </script>
    
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            let quill;
            const quillEditor = document.getElementById('quill-editor');
            const contentTextarea = document.getElementById('content');
            const loadingIndicator = document.getElementById('editor-loading');
            
            // Check if we're on the add/edit page
            if (quillEditor && contentTextarea) {
                try {
                    // Check if Quill is available immediately
                    if (typeof Quill !== 'undefined') {
                        initializeQuillEditor();
                    } else {
                        // Wait a bit for Quill to load, then check again
                        let attempts = 0;
                        const maxAttempts = 10;
                        const checkQuill = setInterval(function() {
                            attempts++;
                            if (typeof Quill !== 'undefined') {
                                clearInterval(checkQuill);
                                initializeQuillEditor();
                            } else if (attempts >= maxAttempts) {
                                clearInterval(checkQuill);
                                console.error('Quill.js failed to load after multiple attempts');
                                showFallbackEditor();
                            }
                        }, 200); // Check every 200ms
                    }
                } catch (error) {
                    console.error('Error initializing Quill:', error);
                    showFallbackEditor();
                }
            }
            
            // Function to initialize Quill editor
            function initializeQuillEditor() {
                try {
                    // Hide loading indicator
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                    
                    quill = new Quill('#quill-editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                [{ 'align': [] }],
                                ['blockquote', 'code-block'],
                                ['link', 'image'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Tulis konten artikel di sini...'
                    });
                    
                    // Set initial content if editing
                    if (contentTextarea.value.trim()) {
                        quill.root.innerHTML = contentTextarea.value;
                    }
                    
                    // Update hidden textarea when Quill content changes
                    quill.on('text-change', function() {
                        contentTextarea.value = quill.root.innerHTML;
                    });
                    
                    // Update Quill when form is submitted
                    const form = quillEditor.closest('form');
                    if (form) {
                        form.addEventListener('submit', function() {
                            contentTextarea.value = quill.root.innerHTML;
                        });
                    }
                    
                    // Enhanced auto-save with Quill content
                    quill.on('text-change', function() {
                        clearTimeout(autoSaveTimer);
                        autoSaveTimer = setTimeout(autoSave, 5000);
                    });
                    
                    console.log('✅ Quill editor initialized successfully');
                } catch (error) {
                    console.error('Error creating Quill instance:', error);
                    showFallbackEditor();
                }
            }
            
            // Fallback editor function
            function showFallbackEditor() {
                console.log('Showing fallback textarea editor');
                if (loadingIndicator) {
                    loadingIndicator.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Menggunakan editor sederhana';
                    setTimeout(() => {
                        loadingIndicator.style.display = 'none';
                    }, 2000);
                }
                if (quillEditor) {
                    quillEditor.style.display = 'none';
                }
                contentTextarea.style.display = 'block';
                contentTextarea.rows = 15;
                contentTextarea.className = 'fallback-editor';
                contentTextarea.placeholder = 'Tulis konten artikel di sini...';
            }
            
            // Image preview function
            window.previewImage = function(input) {
                const preview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImg');
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    
                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.classList.add('hidden');
                }
            };
        });
    </script>
</body>
</html>