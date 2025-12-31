<?php
// Include settings loader
require_once __DIR__ . '/../includes/settings_loader.php';

// Initialize settings for this page
if (!isset($settings)) {
    $settings = initializePageSettings();
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
                    <?php 
                    $contact_info = getContactInfo();
                    if (!empty($contact_info['phone'])): 
                    ?>
                    <span class="hidden sm:flex items-center">
                        <i class="fas fa-phone mr-1"></i>
                        <?php echo htmlspecialchars($contact_info['phone']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact_info['email'])): ?>
                    <span class="flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        <span class="hidden sm:inline"><?php echo htmlspecialchars($contact_info['email']); ?></span>
                        <span class="sm:hidden">Email</span>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-3">
                    <?php 
                    $social_links = getSocialMediaLinks();
                    if (!empty($social_links['facebook'])): 
                    ?>
                    <a href="<?php echo htmlspecialchars($social_links['facebook']); ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($social_links['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['instagram']); ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($social_links['youtube'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['youtube']); ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($social_links['twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['twitter']); ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($social_links['telegram'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['telegram']); ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="Telegram">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    $whatsapp_link = getWhatsAppLink();
                    if ($whatsapp_link !== '#'): 
                    ?>
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="hover:text-green-200 transition duration-200" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <?php endif; ?>
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
                        
                        <!-- Tentang Kami -->
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 px-3 py-2 text-sm font-medium flex items-center gap-1 transition duration-200">
                                <i class="fas fa-info-circle"></i> Tentang Kami
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-64 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border border-gray-200">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-mosque mr-2 text-green-600"></i>Profil Masjid
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#visi-misi" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-bullseye mr-2 text-green-600"></i>Visi & Misi
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#fasilitas" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-building mr-2 text-green-600"></i>Fasilitas
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#struktur-dkm" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-users mr-2 text-green-600"></i>Struktur DKM
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#lokasi" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-location mr-2 text-green-600"></i>Lokasi
                                </a>
                            </div>
                        </div>
                        
                        <!-- Layanan -->
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 px-3 py-2 text-sm font-medium flex items-center gap-1 transition duration-200">
                                <i class="fas fa-concierge-bell"></i> Layanan
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border border-gray-200">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/alquran.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-book-open mr-2 text-green-600"></i>Al-Quran Digital
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-clock mr-2 text-green-600"></i>Jadwal Sholat
                                </a>
                                <!-- <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_jumat.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-calendar-alt mr-2 text-green-600"></i>Jadwal Jumat
                                </a> -->
                                <a href="#" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-ambulance mr-2 text-green-600"></i>Ambulance
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/bimbel.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-graduation-cap mr-2 text-green-600"></i>Bimbel Al-Muhajirin
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/gsg.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-building mr-2 text-green-600"></i>Gedung Serba Guna
                                </a>
                            </div>
                        </div>
                        
                        <!-- Kegiatan -->
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 px-3 py-2 text-sm font-medium flex items-center gap-1 transition duration-200">
                                <i class="fas fa-calendar"></i> Kegiatan
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border border-gray-200">
                            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_jumat.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-pray mr-2 text-green-600"></i>Sholat Jumat
                                </a>
                               <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_kajian.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-book-open mr-2 text-green-600"></i>Kajian Rutin
                                </a>       
                            </div>
                        </div>
                        
                        <!-- Berita -->
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 px-3 py-2 text-sm font-medium flex items-center gap-1 transition duration-200">
                                <i class="fas fa-newspaper"></i> Berita
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-64 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border border-gray-200">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-list mr-2 text-green-600"></i>Semua Berita
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=pengumuman" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-bullhorn mr-2 text-green-600"></i>Pengumuman
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kajian" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-book mr-2 text-green-600"></i>Kajian
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kegiatan" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-calendar-alt mr-2 text-green-600"></i>Kegiatan
                                </a>
                            </div>
                        </div>
                        
                        <!-- Donasi -->
                        <div class="relative group">
                            <button class="text-gray-700 hover:text-green-600 px-3 py-2 text-sm font-medium flex items-center gap-1 transition duration-200">
                                <i class="fas fa-hand-holding-heart"></i> Donasi
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 border border-gray-200">
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-hand-holding-usd mr-2 text-green-600"></i>Donasi Umum
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#zakat" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-coins mr-2 text-green-600"></i>Zakat
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#infaq" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 border-b border-gray-100">
                                    <i class="fas fa-heart mr-2 text-green-600"></i>Infaq & Sedekah
                                </a>
                                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#kurban" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                                    <i class="fas fa-horse mr-2 text-green-600"></i>Kurban
                                </a>
                            </div>
                        </div>
                        
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                           class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-images mr-1"></i>Galeri
                        </a>
                        
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                           class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-50 border-b-2 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-b-2 border-transparent hover:border-green-300'; ?> px-3 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-envelope mr-1"></i>Kontak
                        </a>
                        
                        <!-- <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 px-4 py-2 rounded-md text-sm font-medium transition duration-200 ml-2 focus:outline-none">
                            <i class="fas fa-sign-in-alt mr-1"></i>Admin
                        </a> -->
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button type="button" 
                            class="text-gray-700 hover:text-green-600 focus:outline-none focus:text-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 p-2 rounded-md transition duration-200 cursor-pointer" 
                            id="mobile-menu-button"
                            aria-label="Toggle mobile menu"
                            aria-expanded="false"
                            aria-controls="mobile-menu"
                            onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div class="lg:hidden hidden transition-all duration-300 ease-in-out" 
                 id="mobile-menu" 
                 role="navigation" 
                 aria-label="Mobile navigation"
                 style="display: none;">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-gray-50 rounded-lg mt-2 mb-2 shadow-lg border border-gray-200">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" 
                       class="<?php echo isActivePage('index.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-home mr-3 w-5"></i>Beranda
                    </a>
                    
                    <!-- Tentang Kami Mobile -->
                    <div class="border-l-4 border-transparent">
                        <div class="px-3 py-2 text-gray-600 font-medium text-sm uppercase tracking-wide">
                            <i class="fas fa-info-circle mr-2"></i>Tentang Kami
                        </div>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php" 
                           class="<?php echo isActivePage('profil.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-mosque mr-2 w-4"></i>Profil Masjid
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#visi-misi" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-bullseye mr-2 w-4"></i>Visi & Misi
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/profil.php#struktur-dkm" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-users mr-2 w-4"></i>Struktur DKM
                        </a>
                    </div>
                    
                    <!-- Layanan Mobile -->
                    <div class="border-l-4 border-transparent">
                        <div class="px-3 py-2 text-gray-600 font-medium text-sm uppercase tracking-wide">
                            <i class="fas fa-concierge-bell mr-2"></i>Layanan
                        </div>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/alquran.php" 
                           class="<?php echo isActivePage('alquran.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-book-open mr-2 w-4"></i>Al-Quran Digital
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_sholat.php" 
                           class="<?php echo isActivePage('jadwal_sholat.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-clock mr-2 w-4"></i>Jadwal Sholat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/jadwal_jumat.php" 
                           class="<?php echo isActivePage('jadwal_jumat.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-calendar-alt mr-2 w-4"></i>Jadwal Jumat
                        </a>
                        <a href="#" class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-ambulance mr-2 w-4"></i>Ambulance
                        </a>
                        <a href="#" class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-graduation-cap mr-2 w-4"></i>Bimbel Al-Muhajirin
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/gsg.php" class="block px-4 py-3 hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                            <i class="fas fa-building mr-2 text-green-600"></i>Gedung Serba Guna
                        </a>
                    </div>
                    
                    <!-- Kegiatan Mobile -->
                    <div class="border-l-4 border-transparent">
                        <div class="px-3 py-2 text-gray-600 font-medium text-sm uppercase tracking-wide">
                            <i class="fas fa-calendar mr-2"></i>Kegiatan
                        </div>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kajian" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-book-open mr-2 w-4"></i>Kajian Rutin
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kegiatan" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-calendar-check mr-2 w-4"></i>Kegiatan Masjid
                        </a>
                    </div>
                    
                    <!-- Berita Mobile -->
                    <div class="border-l-4 border-transparent">
                        <div class="px-3 py-2 text-gray-600 font-medium text-sm uppercase tracking-wide">
                            <i class="fas fa-newspaper mr-2"></i>Berita
                        </div>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php" 
                           class="<?php echo isActivePage('berita.php') || isActivePage('berita_detail.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-list mr-2 w-4"></i>Semua Berita
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=pengumuman" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-bullhorn mr-2 w-4"></i>Pengumuman
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kajian" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-book mr-2 w-4"></i>Kajian
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/berita.php?category=kegiatan" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-calendar-alt mr-2 w-4"></i>Kegiatan
                        </a>
                    </div>
                    
                    <!-- Donasi Mobile -->
                    <div class="border-l-4 border-transparent">
                        <div class="px-3 py-2 text-gray-600 font-medium text-sm uppercase tracking-wide">
                            <i class="fas fa-hand-holding-heart mr-2"></i>Donasi
                        </div>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php" 
                           class="<?php echo isActivePage('donasi.php') ? 'text-green-600 bg-green-100' : 'text-gray-700 hover:text-green-600 hover:bg-green-50'; ?> block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-hand-holding-usd mr-2 w-4"></i>Donasi Umum
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#zakat" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-coins mr-2 w-4"></i>Zakat
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#infaq" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-heart mr-2 w-4"></i>Infaq & Sedekah
                        </a>
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/donasi.php#kurban" 
                           class="text-gray-700 hover:text-green-600 hover:bg-green-50 block px-6 py-2 text-sm transition duration-200">
                            <i class="fas fa-horse mr-2 w-4"></i>Kurban
                        </a>
                    </div>
                    
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/galeri.php" 
                       class="<?php echo isActivePage('galeri.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-images mr-3 w-5"></i>Galeri Foto
                    </a>
                    
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/pages/kontak.php" 
                       class="<?php echo isActivePage('kontak.php') ? 'text-green-600 bg-green-100 border-l-4 border-green-600' : 'text-gray-700 hover:text-green-600 hover:bg-green-50 border-l-4 border-transparent hover:border-green-300'; ?> block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                        <i class="fas fa-envelope mr-3 w-5"></i>Kontak Kami
                    </a>
                    
                    <!-- <div class="border-t border-gray-200 pt-3 mt-3">
                        <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/admin/login.php" 
                           class="bg-green-600 text-white hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 block px-3 py-3 rounded-md text-base font-medium transition duration-200">
                            <i class="fas fa-sign-in-alt mr-3 w-5"></i>Login Admin
                        </a>
                    </div> -->
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

    <!-- Mobile Menu JavaScript - Simplified Version -->
    <script>
        // Very simple toggle function
        function toggleMobileMenu() {
            console.log('toggleMobileMenu called');
            
            var menu = document.getElementById('mobile-menu');
            var button = document.getElementById('mobile-menu-button');
            
            if (!menu || !button) {
                console.error('Menu or button not found');
                return;
            }
            
            console.log('Menu current classes:', menu.className);
            
            if (menu.style.display === 'none' || menu.classList.contains('hidden')) {
                // Show menu
                menu.style.display = 'block';
                menu.classList.remove('hidden');
                button.querySelector('i').className = 'fas fa-times text-xl';
                console.log('Menu shown');
            } else {
                // Hide menu
                menu.style.display = 'none';
                menu.classList.add('hidden');
                button.querySelector('i').className = 'fas fa-bars text-xl';
                console.log('Menu hidden');
            }
        }
        
        // Test function to verify elements exist
        function testMobileMenu() {
            console.log('Testing mobile menu elements...');
            console.log('Button:', document.getElementById('mobile-menu-button'));
            console.log('Menu:', document.getElementById('mobile-menu'));
            
            var button = document.getElementById('mobile-menu-button');
            if (button) {
                console.log('Button classes:', button.className);
                console.log('Button onclick:', button.onclick);
            }
            
            var menu = document.getElementById('mobile-menu');
            if (menu) {
                console.log('Menu classes:', menu.className);
                console.log('Menu style display:', menu.style.display);
            }
        }
        
        // Run test when page loads
        setTimeout(testMobileMenu, 1000);
    </script>