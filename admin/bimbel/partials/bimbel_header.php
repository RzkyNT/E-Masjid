<?php
// Get current user if not already set
if (!isset($current_user)) {
    $current_user = getCurrentUser();
}

// Set default page title if not set
if (!isset($page_title)) {
    $page_title = 'Bimbel Dashboard';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Bimbel Al-Muhajirin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/images/favicon.svg">
    
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
    <?php if (isset($additional_head)): ?>
        <?php echo $additional_head; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b fixed top-0 left-0 right-0 z-30 transition-all duration-300" id="topHeader">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left side - Mobile menu button and page title -->
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobileSidebarToggle" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Page title and breadcrumb -->
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($page_title); ?></h1>
                        <?php if (isset($page_description)): ?>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($page_description); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Right side - Actions and user menu -->
                <div class="flex items-center space-x-4">
                    <!-- Quick actions -->
                    <div class="hidden md:flex items-center space-x-2">
                        <a href="../../pages/bimbel.php" target="_blank" class="text-gray-500 hover:text-green-600 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Bimbel
                        </a>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700 hidden sm:block"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                <div class="font-medium"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                                <div class="text-xs"><?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?></div>
                            </div>
                            <a href="../dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard Utama
                            </a>
                            <a href="../masjid/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-mosque mr-2"></i>Admin Masjid
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Pengaturan
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Layout Container -->
    <div class="flex pt-16">
        <!-- Include Bimbel Sidebar -->
        <?php include 'bimbel_sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-1 main-content content-with-sidebar">
            <div class="p-6">
                <!-- Quick Actions Bar -->
                <div class="mb-6 flex justify-between items-center">
                    <div class="hidden md:flex items-center space-x-2">
                        <a href="../../pages/bimbel.php" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Publik
                        </a>
                    </div>
                </div>