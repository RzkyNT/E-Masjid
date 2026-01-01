<?php
/**
 * Professional Maintenance Page
 * Masjid Al-Muhajirin Information System
 */

// Set appropriate HTTP response code
http_response_code(503);

// Estimated completion time (you can modify this)
$estimated_completion = '2024-12-31 23:59:59';
$completion_timestamp = strtotime($estimated_completion);
$current_timestamp = time();
$time_remaining = $completion_timestamp - $current_timestamp;

// Check if maintenance should be over
if ($time_remaining <= 0) {
    // Redirect to homepage if maintenance time has passed
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode | Masjid Al-Muhajirin</title>
    <meta name="description" content="Website sedang dalam pemeliharaan. Silakan kembali lagi nanti.">
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
        .maintenance-animation {
            animation: pulse 2s ease-in-out infinite;
        }
        .gear-animation {
            animation: rotate 4s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .countdown-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white opacity-5 rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white opacity-5 rounded-full"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Logo and Title -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white bg-opacity-20 rounded-full mb-6 maintenance-animation">
                    <i class="fas fa-mosque text-4xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Masjid Al-Muhajirin</h1>
                <p class="text-xl text-white text-opacity-80">Information System</p>
            </div>

            <!-- Maintenance Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-white bg-opacity-10 rounded-full mb-6">
                    <i class="fas fa-tools text-5xl gear-animation"></i>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Sedang Pemeliharaan</h2>
                <p class="text-xl md:text-2xl text-white text-opacity-90 mb-6">
                    Kami sedang meningkatkan layanan untuk pengalaman yang lebih baik
                </p>
            </div>

            <!-- Countdown Timer -->
            <div class="mb-12">
                <h3 class="text-lg font-semibold mb-4">Estimasi Selesai:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-md mx-auto">
                    <div class="countdown-box rounded-lg p-4">
                        <div id="days" class="text-2xl font-bold">00</div>
                        <div class="text-sm opacity-80">Hari</div>
                    </div>
                    <div class="countdown-box rounded-lg p-4">
                        <div id="hours" class="text-2xl font-bold">00</div>
                        <div class="text-sm opacity-80">Jam</div>
                    </div>
                    <div class="countdown-box rounded-lg p-4">
                        <div id="minutes" class="text-2xl font-bold">00</div>
                        <div class="text-sm opacity-80">Menit</div>
                    </div>
                    <div class="countdown-box rounded-lg p-4">
                        <div id="seconds" class="text-2xl font-bold">00</div>
                        <div class="text-sm opacity-80">Detik</div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-12">
                <div class="max-w-md mx-auto">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Progress Pemeliharaan</span>
                        <span id="progress-percent">75%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-20 rounded-full h-3">
                        <div id="progress-bar" class="bg-white h-3 rounded-full transition-all duration-1000" style="width: 75%"></div>
                    </div>
                </div>
            </div>

            <!-- What We're Doing -->
            <div class="mb-12">
                <h3 class="text-xl font-semibold mb-6">Yang Sedang Kami Kerjakan:</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                    <div class="countdown-box rounded-lg p-6">
                        <i class="fas fa-database text-3xl mb-4 text-blue-300"></i>
                        <h4 class="font-semibold mb-2">Database Optimization</h4>
                        <p class="text-sm opacity-80">Meningkatkan performa database untuk akses yang lebih cepat</p>
                    </div>
                    <div class="countdown-box rounded-lg p-6">
                        <i class="fas fa-shield-alt text-3xl mb-4 text-green-300"></i>
                        <h4 class="font-semibold mb-2">Security Updates</h4>
                        <p class="text-sm opacity-80">Memperbarui sistem keamanan untuk perlindungan yang lebih baik</p>
                    </div>
                    <div class="countdown-box rounded-lg p-6">
                        <i class="fas fa-rocket text-3xl mb-4 text-purple-300"></i>
                        <h4 class="font-semibold mb-2">New Features</h4>
                        <p class="text-sm opacity-80">Menambahkan fitur-fitur baru yang lebih canggih</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="countdown-box rounded-lg p-6 max-w-2xl mx-auto mb-8">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-envelope mr-2"></i>
                    Butuh Bantuan Mendesak?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="mb-2"><i class="fas fa-phone mr-2"></i>+62 xxx-xxxx-xxxx</p>
                        <p class="mb-2"><i class="fas fa-envelope mr-2"></i>info@masjidalmuhajirin.com</p>
                    </div>
                    <div>
                        <p class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i>Alamat Masjid</p>
                        <p class="mb-2"><i class="fas fa-clock mr-2"></i>Senin - Jumat, 08:00 - 17:00</p>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="text-center">
                <p class="text-sm opacity-80 mb-4">Ikuti update terbaru di media sosial kami:</p>
                <div class="flex justify-center space-x-4">
                    <a href="#" class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition duration-200">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition duration-200">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition duration-200">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition duration-200">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-10 text-center py-6 text-white text-opacity-60 text-sm">
        <p>© <?php echo date('Y'); ?> Masjid Al-Muhajirin. Semua hak dilindungi.</p>
        <p class="mt-1">Maintenance started: <?php echo date('d M Y H:i', time() - 3600); ?> WIB</p>
    </footer>

    <!-- JavaScript -->
    <script>
        // Countdown Timer
        const targetDate = new Date('<?php echo $estimated_completion; ?>').getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetDate - now;
            
            if (distance < 0) {
                // Maintenance is over, redirect to homepage
                window.location.href = '/';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }
        
        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Simulate progress (you can replace this with real progress data)
        let progress = 75;
        setInterval(function() {
            if (progress < 95) {
                progress += Math.random() * 0.5;
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('progress-percent').textContent = Math.round(progress) + '%';
            }
        }, 30000); // Update every 30 seconds
        
        // Auto-refresh page every 5 minutes to check if maintenance is over
        setTimeout(function() {
            window.location.reload();
        }, 300000);
        
        // Prevent right-click and F12 (optional security measure)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>