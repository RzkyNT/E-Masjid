<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - <?php echo defined('APP_NAME') ? APP_NAME : 'Sistem Masjid'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center">
            <!-- Error Icon -->
            <div class="bg-red-100 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-ban text-3xl text-red-600"></i>
            </div>
            
            <!-- Error Message -->
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Akses Ditolak</h1>
            <p class="text-gray-600 mb-6">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. 
                Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
            </p>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <button 
                    onclick="history.back()" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200"
                >
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </button>
                
                <?php if (function_exists('getCurrentUser') && getCurrentUser()): ?>
                    <?php $user = getCurrentUser(); ?>
                    <a 
                        href="<?php echo getDashboardUrl($user['role']); ?>" 
                        class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200"
                    >
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a 
                        href="/admin/login.php" 
                        class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Additional Info -->
            <div class="mt-8 text-sm text-gray-500">
                <p>Error Code: 403 - Forbidden</p>
                <?php if (function_exists('getCurrentUser') && getCurrentUser()): ?>
                    <?php $user = getCurrentUser(); ?>
                    <p>User: <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
                <?php endif; ?>
                <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>