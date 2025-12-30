<?php
// Get site settings
if (!isset($settings)) {
    try {
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_description', 'contact_phone', 'contact_email')");
        $stmt->execute();
        $settings_data = $stmt->fetchAll();
        
        $settings = [];
        foreach ($settings_data as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
    } catch (PDOException $e) {
        $settings = [
            'site_name' => 'Masjid Jami Al-Muhajirin',
            'site_description' => 'Website resmi Masjid Jami Al-Muhajirin',
            'contact_phone' => '021-12345678',
            'contact_email' => 'info@almuhajirin.com'
        ];
    }
}

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActivePage($page) {
    global $current_page, $current_dir;
    
    if ($page === 'index.php' && ($current_page === 'index.php' || $current_dir === 'masjid')) {
        return true;
    }
    
    return $current_page === $page;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo htmlspecialchars($settings['site_name']); ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : htmlspecialchars($settings['site_description']); ?>">
    <meta name="keywords" content="masjid, islam, bekasi, al-muhajirin, bimbel, dakwah">
    <meta name="author" content="<?php echo htmlspecialchars($settings['site_name']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : htmlspecialchars($settings['site_name']); ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : htmlspecialchars($settings['site_description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/images/favicon.ico">
    
    <!-- Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669',
                        secondary: '#0d9488',
                        accent: '#f59e0b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Top Bar -->
    <div class="bg-green-600 text-white text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-10">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-1"></i>
                        <?php echo htmlspecialchars($settings['contact_phone']); ?>
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        <?php echo htmlspecialchars($settings['contact_email']); ?>
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="#" class="hover:text-green-200 transition duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo and Site Name -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" class="flex items-center">
                            <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center mr-3">
                                <i class="fas fa-mosque text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold text-gray-900 leading-tight">
                                    <?php echo htmlspecialchars($settings['site_name']); ?>
                                </h1>
                                <p class="text-xs text-gray-500">Masjid & Bimbel</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:block">
                    <div class="ml-10 flex items-baseline space-x-1">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                           class="<?php echo isActivePage('index.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-home mr-1"></i>Beranda
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                           class="<?php echo isActivePage('profil.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-info-circle mr-1"></i>Profil
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                           class="<?php echo isActivePage('jadwal_sholat.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-clock mr-1"></i>Jadwal Sholat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                           class="<?php echo isActivePage('berita.php') || isActivePage('berita_detail.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-newspaper mr-1"></i>Berita
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                           class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-images mr-1"></i>Galeri
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                           class="<?php echo isActivePage('donasi.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-hand-holding-heart mr-1"></i>Donasi
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                           class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-50' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-envelope mr-1"></i>Kontak
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded-md text-sm font-medium transition duration-200 ml-2">
                            <i class="fas fa-sign-in-alt mr-1"></i>Admin
                        </a>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button type="button" 
                            class="text-gray-700 hover:text-green-600 focus:outline-none focus:text-green-600 p-2" 
                            id="mobile-menu-button"
                            aria-label="Toggle mobile menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="lg:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-gray-50 rounded-lg mt-2 mb-2">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                       class="<?php echo isActivePage('index.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                       class="<?php echo isActivePage('profil.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-info-circle mr-2"></i>Profil Masjid
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                       class="<?php echo isActivePage('jadwal_sholat.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-clock mr-2"></i>Jadwal Sholat
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                       class="<?php echo isActivePage('berita.php') || isActivePage('berita_detail.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-newspaper mr-2"></i>Berita & Kegiatan
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                       class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-images mr-2"></i>Galeri Foto
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                       class="<?php echo isActivePage('donasi.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-hand-holding-heart mr-2"></i>Donasi & Infaq
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                       class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-envelope mr-2"></i>Kontak Kami
                    </a>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 block px-3 py-2 rounded-md text-base font-medium transition duration-200">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Breadcrumb (if not homepage) -->
    <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
    <nav class="bg-gray-100 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-2 py-3 text-sm">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" class="text-green-600 hover:text-green-700">
                    <i class="fas fa-home"></i>
                </a>
                <?php foreach ($breadcrumb as $index => $item): ?>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    <?php if ($index === count($breadcrumb) - 1): ?>
                        <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($item['title']); ?></span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" class="text-green-600 hover:text-green-700">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="min-h-screen">