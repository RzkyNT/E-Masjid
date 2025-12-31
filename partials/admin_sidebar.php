<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['REQUEST_URI'];

// Define menu items
$menu_items = [
    [
        'type' => 'button',
        'id' => 'desktopSidebarToggle',
        'title' => 'Toggle Sidebar',
        'icon' => 'fas fa-bars',
        'class' => 'hidden lg:block fixed top-4 left-4 z-30 bg-gray-800 text-white p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-300'
    ],

    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-home',
        'url' => 'dashboard.php',
        'active' => $current_page === 'dashboard.php'
    ],
    [
        'title' => 'Kelola Berita',
        'icon' => 'fas fa-newspaper',
        'url' => 'berita.php',
        'active' => $current_page === 'berita.php'
    ],
    [
        'title' => 'Kelola Galeri',
        'icon' => 'fas fa-images',
        'url' => 'galeri.php',
        'active' => $current_page === 'galeri.php'
    ],
    [
        'title' => 'Jadwal Jumat',
        'icon' => 'fas fa-calendar-alt',
        'url' => 'jadwal_jumat.php',
        'active' => $current_page === 'jadwal_jumat.php'
    ],
    [
        'title' => 'Kelola Donasi',
        'icon' => 'fas fa-hand-holding-heart',
        'url' => 'donasi.php',
        'active' => $current_page === 'donasi.php'
    ],
    [
        'title' => 'Booking GSG',
        'icon' => 'fas fa-building',
        'url' => 'gsg_bookings.php',
        'active' => $current_page === 'gsg_bookings.php'
    ],
    [
        'title' => 'Pengaturan',
        'icon' => 'fas fa-cog',
        'url' => 'pengaturan.php',
        'active' => $current_page === 'pengaturan.php'
    ]
];
?>

<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:inset-0">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-mosque text-2xl text-green-400"></i>
            </div>
            <div class="ml-3 sidebar-text">
                <h1 class="text-lg font-semibold text-white">CMS Masjid</h1>
                <p class="text-xs text-gray-300">Admin Panel</p>
            </div>
        </div>
        
        <!-- Toggle button for mobile -->
        <button id="sidebarToggle" class="lg:hidden text-gray-300 hover:text-white focus:outline-none focus:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="mt-5 px-2 space-y-1">
        <?php foreach ($menu_items as $item): ?>

            <?php if (($item['type'] ?? '') === 'button'): ?>
                <!-- Sidebar Toggle Button (Desktop) -->
                <button
                    id="<?php echo $item['id']; ?>"
                    class="<?php echo $item['class']; ?>"
                    aria-label="<?php echo $item['title']; ?>">
                    <i class="<?php echo $item['icon']; ?>"></i>
                </button>

            <?php else: ?>
                <!-- Normal Menu Item -->
                <a href="<?php echo $item['url']; ?>"
                class="<?php echo $item['active']
                        ? 'bg-green-600 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>
                        group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                    
                    <i class="<?php echo $item['icon']; ?> mr-3 flex-shrink-0 h-5 w-5"></i>
                    <span class="sidebar-text"><?php echo $item['title']; ?></span>
                </a>
            <?php endif; ?>

        <?php endforeach; ?>
        
        <!-- Separator -->
        <div class="border-t border-gray-700 my-4"></div>
        
        <!-- Back to Main Dashboard -->
        <a href="../dashboard.php"
        class="text-gray-300 hover:bg-gray-700 hover:text-white
                group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200">
            <i class="fas fa-arrow-left mr-3 flex-shrink-0 h-5 w-5"></i>
            <span class="sidebar-text">Dashboard Utama</span>
        </a>
    </nav>


    <!-- Sidebar Footer -->
    <div class="absolute bottom-0 w-full p-4 bg-gray-900">
        <div class="flex items-center">
            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0">
                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
            </div>
            <div class="ml-3 sidebar-text">
                <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($current_user['full_name']); ?></p>
                <p class="text-xs text-gray-300"><?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 hidden lg:hidden"></div>

<!-- Desktop Sidebar Toggle Button -->

<style>
/* Sidebar Animation Styles */
#sidebar:not(.sidebar-collapsed) {
    min-width: 250px !important;
}

.sidebar-collapsed {
    width: 4rem !important;
}

.sidebar-collapsed .sidebar-text {
    display: none;
}

.sidebar-collapsed nav a {
    justify-content: center;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}


/* Content margin adjustment */
.content-with-sidebar {
    margin-left: 16rem; /* 64 * 0.25rem = 16rem */
    transition: margin-left 0.3s ease-in-out;
}


    .header-with-sidebar{
         margin-left: 16rem; /* 64 * 0.25rem = 16rem */
    transition: margin-left 0.3s ease-in-out;
    }
    .header-with-collapsed-sidebar {
         margin-left: 4rem;
    }
.content-with-collapsed-sidebar {
    margin-left: 4rem;
}
.sidebar-collapsed nav a i {
    margin-right: 0;
}

.sidebar-collapsed .absolute.bottom-0 {
    display: none;
}

/* Smooth transitions */
#sidebar {
    transition: width 0.3s ease-in-out;
}

.sidebar-text {
    transition: opacity 0.3s ease-in-out;
}

/* Tooltip for collapsed sidebar */
.sidebar-collapsed nav a {
    position: relative;
}

.sidebar-collapsed nav a:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background-color: #1f2937;
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1000;
    margin-left: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.sidebar-collapsed nav a:hover::before {
    content: '';
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    border: 6px solid transparent;
    border-right-color: #1f2937;
    margin-left: -6px;
    z-index: 1000;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    #desktopSidebarToggle {
        display: none !important;
    }
}

@media (max-width: 1024px) {
    .content-with-sidebar,
    .content-with-collapsed-sidebar {
        margin-left: 0;
    }
    
    .header-with-sidebar,
    .header-with-collapsed-sidebar {
        left: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const topHeader = document.getElementById('topHeader');
    
    // Check if sidebar was collapsed in previous session
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && window.innerWidth >= 1024) {
        sidebar.classList.add('sidebar-collapsed');
        if (mainContent) {
            mainContent.classList.remove('content-with-sidebar');
            mainContent.classList.add('content-with-collapsed-sidebar');
        }
        if (topHeader) {
            topHeader.classList.remove('header-with-sidebar');
            topHeader.classList.add('header-with-collapsed-sidebar');
        }
    } else if (window.innerWidth >= 1024) {
        if (topHeader) {
            topHeader.classList.add('header-with-sidebar');
        }
    }
    
    // Mobile sidebar toggle
    function toggleMobileSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }
    
    // Desktop sidebar collapse/expand
    function toggleDesktopSidebar() {
        const isCurrentlyCollapsed = sidebar.classList.contains('sidebar-collapsed');
        
        if (isCurrentlyCollapsed) {
            // Expand
            sidebar.classList.remove('sidebar-collapsed');
            if (mainContent) {
                mainContent.classList.remove('content-with-collapsed-sidebar');
                mainContent.classList.add('content-with-sidebar');
            }
            if (topHeader) {
                topHeader.classList.remove('header-with-collapsed-sidebar');
                topHeader.classList.add('header-with-sidebar');
            }
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            // Collapse
            sidebar.classList.add('sidebar-collapsed');
            if (mainContent) {
                mainContent.classList.remove('content-with-sidebar');
                mainContent.classList.add('content-with-collapsed-sidebar');
            }
            if (topHeader) {
                topHeader.classList.remove('header-with-sidebar');
                topHeader.classList.add('header-with-collapsed-sidebar');
            }
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    if (desktopSidebarToggle) {
        desktopSidebarToggle.addEventListener('click', toggleDesktopSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleMobileSidebar);
    }
    
    // Add tooltips to collapsed sidebar items
    const menuLinks = sidebar.querySelectorAll('nav a');
    menuLinks.forEach(link => {
        const text = link.querySelector('.sidebar-text');
        if (text) {
            link.setAttribute('data-tooltip', text.textContent.trim());
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth < 1024) {
            // Mobile: hide sidebar
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        } else {
            // Desktop: show sidebar
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }
    });
    
    // Close mobile sidebar when clicking on a link
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                toggleMobileSidebar();
            }
        });
    });
});
</script>