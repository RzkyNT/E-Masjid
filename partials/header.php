<?php
// Include site defaults if not already included
if (!function_exists('getAllSiteSettings')) {
    require_once __DIR__ . '/../config/site_defaults.php';
}

// Get site settings
if (!isset($settings)) {
    $settings = getAllSiteSettings();
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
    <link rel="icon" type="image/x-icon" href="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/images/favicon.svg">
    
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
                    <span class="hidden sm:flex items-center">
                        <i class="fas fa-phone mr-1"></i>
                        <?php echo htmlspecialchars($settings['contact_phone']); ?>
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        <span class="hidden sm:inline"><?php echo htmlspecialchars($settings['contact_email']); ?></span>
                        <span class="sm:hidden">Email</span>
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="#" class="hover:text-green-200 transition duration-200" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="hover:text-green-200 transition duration-200" aria-label="WhatsApp">
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
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" class="flex items-center group">
                            <div class="bg-green-600 text-white rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center mr-3 group-hover:bg-green-700 transition duration-200">
                                <i class="fas fa-mosque text-lg sm:text-xl"></i>
                            </div>
                            <div class="hidden sm:block">
                                <h1 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight group-hover:text-green-600 transition duration-200">
                                    <?php echo htmlspecialchars($settings['site_name']); ?>
                                </h1>
                                <p class="text-xs sm:text-sm text-gray-500">Masjid & Bimbel</p>
                            </div>
                            <div class="sm:hidden">
                                <h1 class="text-base font-bold text-gray-900 leading-tight group-hover:text-green-600 transition duration-200">
                                    Al-Muhajirin
                                </h1>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:block">
                    <div class="ml-10 flex items-baseline space-x-1">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                           class="<?php echo isActivePage('index.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-home mr-1"></i>Beranda
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                           class="<?php echo isActivePage('profil.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-info-circle mr-1"></i>Profil
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                           class="<?php echo isActivePage('jadwal_sholat.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-clock mr-1"></i>Jadwal Sholat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                           class="<?php echo isActivePage('berita.php') || isActivePage('berita_detail.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-newspaper mr-1"></i>Berita
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                           class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-images mr-1"></i>Galeri
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                           class="<?php echo isActivePage('donasi.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-hand-holding-heart mr-1"></i>Donasi
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                           class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-envelope mr-1"></i>Kontak
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 px-4 py-2 rounded-md text-sm font-medium transition duration-200 ml-2 focus:outline-none">
                            <i class="fas fa-sign-in-alt mr-1"></i>Admin
                        </a>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button type="button" 
                            class="text-gray-700 hover:text-green-600 focus:outline-none focus:text-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 p-2 rounded-md transition duration-200" 
                            id="mobile-menu-button"
                            aria-label="Toggle mobile menu"
                            aria-expanded="false"
                            aria-controls="mobile-menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="lg:hidden hidden transition-all duration-300 ease-in-out" id="mobile-menu" role="navigation" aria-label="Mobile navigation">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-gray-50 rounded-lg mt-2 mb-2 shadow-lg border border-gray-200">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                       class="<?php echo isActivePage('index.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-home mr-3 w-5"></i>Beranda
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                       class="<?php echo isActivePage('profil.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-info-circle mr-3 w-5"></i>Profil Masjid
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                       class="<?php echo isActivePage('jadwal_sholat.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-clock mr-3 w-5"></i>Jadwal Sholat
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                       class="<?php echo isActivePage('berita.php') || isActivePage('berita_detail.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-newspaper mr-3 w-5"></i>Berita & Kegiatan
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                       class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-images mr-3 w-5"></i>Galeri Foto
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                       class="<?php echo isActivePage('donasi.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-hand-holding-heart mr-3 w-5"></i>Donasi & Infaq
                    </a>
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                       class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-envelope mr-3 w-5"></i>Kontak Kami
                    </a>
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                            <i class="fas fa-sign-in-alt mr-3 w-5"></i>Login Admin
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

    <!-- Mobile Menu JavaScript -->
    <script>
       document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (!mobileMenuButton || !mobileMenu) return;

    const menuIcon = mobileMenuButton.querySelector('i');

    mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');

        if (mobileMenu.classList.contains('hidden')) {
            menuIcon.className = 'fas fa-bars text-xl';
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        } else {
            menuIcon.className = 'fas fa-times text-xl';
            mobileMenuButton.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', function(event) {
        if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
            mobileMenu.classList.add('hidden');
            menuIcon.className = 'fas fa-bars text-xl';
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            mobileMenu.classList.add('hidden');
            menuIcon.className = 'fas fa-bars text-xl';
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        }
    });
});
    </script>