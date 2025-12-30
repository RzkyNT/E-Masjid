<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../includes/access_control.php';

// Check rate limiting
if (RateLimit::isLimited()) {
    $lockout_time = RateLimit::getLockoutTime();
    $error_message = "Terlalu banyak percobaan login. Coba lagi dalam " . ceil($lockout_time / 60) . " menit.";
} else {

// Redirect if already logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    header("Location: " . getDashboardUrl($user['role']));
    exit();
}

$error_message = '';
$success_message = '';

// Handle logout success message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = 'Anda telah berhasil logout.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error_message = 'Username dan password harus diisi.';
        } else {
            // Attempt login
            $login_result = login($username, $password);
            
            if ($login_result['success']) {
                // Clear rate limiting on successful login
                RateLimit::clearAttempts();
                logSecurityEvent('LOGIN_SUCCESS', "Successful login for username: $username");
                
                // Redirect to appropriate dashboard
                $redirect_url = getDashboardUrl($login_result['user']['role']);
                header("Location: $redirect_url");
                exit();
            } else {
                // Record failed attempt for rate limiting
                RateLimit::recordAttempt();
                $error_message = $login_result['message'];
                logSecurityEvent('LOGIN_FAILED', "Failed login attempt for username: $username - " . $login_result['message']);
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-50 to-teal-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center shadow-lg">
                <i class="fas fa-mosque text-3xl text-green-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Sistem Informasi</h1>
            <p class="text-gray-600">Masjid Jami Al-Muhajirin</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Login Administrator</h2>
            
            <?php if ($error_message): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Username Field -->
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200"
                        placeholder="Masukkan username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        autocomplete="username"
                    >
                    <div class="text-red-500 text-sm mt-1 hidden" id="username-error">Username harus diisi</div>
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i>Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 pr-12"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            id="togglePassword" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="text-red-500 text-sm mt-1 hidden" id="password-error">Password harus diisi</div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                    id="submitBtn"
                >
                    <span id="submitText">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </span>
                    <span id="submitSpinner" class="hidden">
                        <div class="spinner mr-2"></div>Loading...
                    </span>
                </button>
            </form>

            <!-- Additional Info -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Lupa password? Hubungi administrator sistem.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> Masjid Jami Al-Muhajirin. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Form validation and UI enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    eyeIcon.className = 'fas fa-eye';
                } else {
                    eyeIcon.className = 'fas fa-eye-slash';
                }
            });

            // Form validation
            function validateField(field, errorElement, message) {
                if (field.value.trim() === '') {
                    field.classList.add('border-red-500');
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                    return false;
                } else {
                    field.classList.remove('border-red-500');
                    errorElement.classList.add('hidden');
                    return true;
                }
            }

            // Real-time validation
            usernameInput.addEventListener('blur', function() {
                validateField(this, document.getElementById('username-error'), 'Username harus diisi');
            });

            passwordInput.addEventListener('blur', function() {
                validateField(this, document.getElementById('password-error'), 'Password harus diisi');
            });

            // Clear errors on input
            usernameInput.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('border-red-500');
                    document.getElementById('username-error').classList.add('hidden');
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('border-red-500');
                    document.getElementById('password-error').classList.add('hidden');
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const usernameValid = validateField(usernameInput, document.getElementById('username-error'), 'Username harus diisi');
                const passwordValid = validateField(passwordInput, document.getElementById('password-error'), 'Password harus diisi');

                if (!usernameValid || !passwordValid) {
                    e.preventDefault();
                    return;
                }

                // Show loading state
                submitText.classList.add('hidden');
                submitSpinner.classList.remove('hidden');
                submitBtn.disabled = true;
            });

            // Focus on username field
            usernameInput.focus();

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.bg-red-50, .bg-green-50');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });

        // Prevent back button after logout
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        window.addEventListener('popstate', function() {
            window.history.replaceState(null, null, window.location.href);
        });
    </script>
</body>
</html>