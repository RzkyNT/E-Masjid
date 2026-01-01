<?php
/**
 * Professional Error Handler
 * Masjid Al-Muhajirin Information System
 */

// Get error code from URL parameter or default to 404
$error_code = isset($_GET['code']) ? (int)$_GET['code'] : 404;

// Set appropriate HTTP response code
http_response_code($error_code);

// Error configurations
$errors = [
    400 => [
        'title' => 'Bad Request',
        'message' => 'Permintaan tidak valid. Silakan periksa URL atau data yang dikirim.',
        'icon' => 'fas fa-exclamation-triangle',
        'color' => 'yellow'
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini. Silakan login terlebih dahulu.',
        'icon' => 'fas fa-lock',
        'color' => 'red'
    ],
    403 => [
        'title' => 'Forbidden',
        'message' => 'Akses ke halaman ini dilarang. Anda tidak memiliki hak akses yang diperlukan.',
        'icon' => 'fas fa-ban',
        'color' => 'red'
    ],
    404 => [
        'title' => 'Halaman Tidak Ditemukan',
        'message' => 'Halaman yang Anda cari tidak ditemukan. Mungkin telah dipindahkan atau dihapus.',
        'icon' => 'fas fa-search',
        'color' => 'blue'
    ],
    500 => [
        'title' => 'Server Error',
        'message' => 'Terjadi kesalahan pada server. Tim teknis kami sedang menangani masalah ini.',
        'icon' => 'fas fa-server',
        'color' => 'red'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => 'Layanan sedang dalam pemeliharaan. Silakan coba lagi dalam beberapa saat.',
        'icon' => 'fas fa-tools',
        'color' => 'orange'
    ]
];

// Get error info or default to 404
$error = $errors[$error_code] ?? $errors[404];

// Log error for monitoring (optional)
if ($error_code >= 500) {
    error_log("Error {$error_code}: " . $_SERVER['REQUEST_URI'] . " - " . $_SERVER['HTTP_USER_AGENT']);
}

// Get referrer for back button
$referrer = $_SERVER['HTTP_REFERER'] ?? '/';
$show_back = $referrer !== $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error_code; ?> - <?php echo $error['title']; ?> | Masjid Al-Muhajirin</title>
    <meta name="description" content="<?php echo $error['message']; ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .error-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-mosque text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Masjid Al-Muhajirin</h1>
                        <p class="text-sm text-gray-500">Information System</p>
                    </div>
                </div>
                <div class="text-sm text-gray-500">
                    Error <?php echo $error_code; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <!-- Error Icon -->
                <div class="error-animation mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-<?php echo $error['color']; ?>-100 rounded-full mb-4">
                        <i class="<?php echo $error['icon']; ?> text-4xl text-<?php echo $error['color']; ?>-600"></i>
                    </div>
                </div>

                <!-- Error Code -->
                <h1 class="text-6xl font-bold text-gray-900 mb-4">
                    <?php echo $error_code; ?>
                </h1>

                <!-- Error Title -->
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                    <?php echo $error['title']; ?>
                </h2>

                <!-- Error Message -->
                <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto leading-relaxed">
                    <?php echo $error['message']; ?>
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Kembali ke Beranda
                    </a>
                    
                    <?php if ($show_back): ?>
                    <button onclick="history.back()" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Halaman Sebelumnya
                    </button>
                    <?php endif; ?>
                    
                    <a href="/alquran" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-book-open mr-2"></i>
                        Baca Al-Quran
                    </a>
                </div>

                <!-- Additional Help -->
                <div class="mt-12 p-6 bg-white rounded-xl shadow-lg max-w-2xl mx-auto">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                        Butuh Bantuan?
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="text-left">
                            <h4 class="font-medium text-gray-700 mb-2">Halaman Populer:</h4>
                            <ul class="space-y-1 text-gray-600">
                                <li><a href="/alquran" class="hover:text-green-600 transition duration-200">📖 Al-Quran Digital</a></li>
                                <li><a href="/doa" class="hover:text-green-600 transition duration-200">🤲 Kumpulan Doa</a></li>
                                <li><a href="/hadits" class="hover:text-green-600 transition duration-200">📚 Hadits</a></li>
                                <li><a href="/asmaul-husna" class="hover:text-green-600 transition duration-200">✨ Asmaul Husna</a></li>
                            </ul>
                        </div>
                        <div class="text-left">
                            <h4 class="font-medium text-gray-700 mb-2">Kontak:</h4>
                            <ul class="space-y-1 text-gray-600">
                                <li><i class="fas fa-envelope mr-2"></i>info@masjidalmuhajirin.com</li>
                                <li><i class="fas fa-phone mr-2"></i>+62 xxx-xxxx-xxxx</li>
                                <li><i class="fas fa-map-marker-alt mr-2"></i>Alamat Masjid</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-mosque text-green-400 text-xl mr-2"></i>
                    <span class="font-semibold">Masjid Al-Muhajirin Information System</span>
                </div>
                <p class="text-gray-400 text-sm">
                    © <?php echo date('Y'); ?> Masjid Al-Muhajirin. Semua hak dilindungi.
                </p>
                <div class="mt-4 flex justify-center space-x-4 text-sm text-gray-400">
                    <span>Error Code: <?php echo $error_code; ?></span>
                    <span>•</span>
                    <span>Time: <?php echo date('Y-m-d H:i:s'); ?></span>
                    <span>•</span>
                    <span>IP: <?php echo $_SERVER['REMOTE_ADDR']; ?></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for enhanced UX -->
    <script>
        // Auto-redirect after 30 seconds for 5xx errors
        <?php if ($error_code >= 500): ?>
        setTimeout(function() {
            if (confirm('Halaman akan dialihkan ke beranda. Lanjutkan?')) {
                window.location.href = '/';
            }
        }, 30000);
        <?php endif; ?>

        // Track error for analytics (optional)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'exception', {
                'description': 'Error <?php echo $error_code; ?>: <?php echo $_SERVER['REQUEST_URI']; ?>',
                'fatal': <?php echo $error_code >= 500 ? 'true' : 'false'; ?>
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'h' || e.key === 'H') {
                window.location.href = '/';
            } else if (e.key === 'b' || e.key === 'B') {
                history.back();
            }
        });

        // Show keyboard shortcuts hint
        setTimeout(function() {
            const hint = document.createElement('div');
            hint.className = 'fixed bottom-4 right-4 bg-gray-800 text-white text-xs px-3 py-2 rounded-lg opacity-75';
            hint.innerHTML = 'Tekan H untuk beranda, B untuk kembali';
            document.body.appendChild(hint);
            
            setTimeout(function() {
                hint.style.opacity = '0';
                setTimeout(function() {
                    hint.remove();
                }, 300);
            }, 5000);
        }, 2000);
    </script>
</body>
</html>