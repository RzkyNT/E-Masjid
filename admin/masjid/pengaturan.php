<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';
require_once '../../includes/upload_handler.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_general_settings':
                handleGeneralSettings();
                break;
            case 'update_contact_settings':
                handleContactSettings();
                break;
            case 'update_social_media':
                handleSocialMediaSettings();
                break;
            case 'upload_logo':
                handleLogoUpload();
                break;
            case 'update_prayer_settings':
                handlePrayerSettings();
                break;
            case 'update_donation_settings':
                handleDonationSettings();
                break;
            case 'update_dkm_settings':
                handleDKMSettings();
                break;
        }
    }
}

// Handle general settings update
function handleGeneralSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'site_name' => $_POST['site_name'] ?? '',
        'site_description' => $_POST['site_description'] ?? '',
        'site_tagline' => $_POST['site_tagline'] ?? '',
        'masjid_name' => $_POST['masjid_name'] ?? '',
        'masjid_history' => $_POST['masjid_history'] ?? '',
        'masjid_vision' => $_POST['masjid_vision'] ?? '',
        'masjid_mission' => $_POST['masjid_mission'] ?? ''
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, 'text', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $type = in_array($key, ['site_description', 'masjid_history', 'masjid_vision', 'masjid_mission']) ? 'textarea' : 'text';
            $description = ucfirst(str_replace('_', ' ', $key));
            
            $stmt->execute([$key, $value, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Pengaturan umum berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'General settings updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui pengaturan: ' . $e->getMessage();
    }
}

// Handle contact settings update
function handleContactSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'masjid_address' => $_POST['masjid_address'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'contact_whatsapp' => $_POST['contact_whatsapp'] ?? '',
        'location_coordinates' => $_POST['location_coordinates'] ?? '',
        'google_maps_embed' => $_POST['google_maps_embed'] ?? ''
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $type = in_array($key, ['masjid_address', 'google_maps_embed']) ? 'textarea' : 'text';
            $description = ucfirst(str_replace('_', ' ', $key));
            
            $stmt->execute([$key, $value, $type, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Informasi kontak berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'Contact settings updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui informasi kontak: ' . $e->getMessage();
    }
}

// Handle social media settings update
function handleSocialMediaSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'social_facebook' => $_POST['social_facebook'] ?? '',
        'social_instagram' => $_POST['social_instagram'] ?? '',
        'social_youtube' => $_POST['social_youtube'] ?? '',
        'social_twitter' => $_POST['social_twitter'] ?? '',
        'social_telegram' => $_POST['social_telegram'] ?? ''
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, 'text', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $description = ucfirst(str_replace(['social_', '_'], ['', ' '], $key)) . ' URL';
            $stmt->execute([$key, $value, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Pengaturan media sosial berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'Social media settings updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui pengaturan media sosial: ' . $e->getMessage();
    }
}

// Handle logo upload
function handleLogoUpload() {
    global $pdo, $success_message, $error_message;
    
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Tidak ada file logo yang dipilih atau terjadi kesalahan upload.';
        return;
    }
    
    // Initialize upload handler for settings
    $upload_handler = new SecureUploadHandler('settings');
    
    // Upload the logo
    $result = $upload_handler->uploadFile($_FILES['logo'], 'logo');
    
    if ($result) {
        try {
            // Save logo path to settings
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES ('site_logo', ?, 'image', 'Website logo') 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([$result['relative_path']]);
            
            $success_message = 'Logo berhasil diupload: ' . $result['filename'];
            
            // Log activity
            logActivity('logo_upload', 'Logo uploaded: ' . $result['filename']);
            
        } catch (PDOException $e) {
            $error_message = 'Gagal menyimpan informasi logo: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Gagal upload logo: ' . $upload_handler->getLastError();
    }
}

// Handle prayer settings update
function handlePrayerSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'prayer_api_enabled' => isset($_POST['prayer_api_enabled']) ? '1' : '0',
        'prayer_api_url' => $_POST['prayer_api_url'] ?? '',
        'prayer_location_city' => $_POST['prayer_location_city'] ?? '',
        'prayer_location_country' => $_POST['prayer_location_country'] ?? '',
        'prayer_calculation_method' => $_POST['prayer_calculation_method'] ?? '2'
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, 'text', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $description = ucfirst(str_replace('_', ' ', $key));
            $stmt->execute([$key, $value, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Pengaturan jadwal sholat berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'Prayer settings updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui pengaturan jadwal sholat: ' . $e->getMessage();
    }
}

// Handle donation settings update
function handleDonationSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'donation_account' => $_POST['donation_account'] ?? '',
        'donation_qr_code' => $_POST['donation_qr_code'] ?? '',
        'donation_categories' => $_POST['donation_categories'] ?? '',
        'donation_transparency_text' => $_POST['donation_transparency_text'] ?? ''
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $type = in_array($key, ['donation_account', 'donation_categories', 'donation_transparency_text']) ? 'textarea' : 'text';
            $description = ucfirst(str_replace('_', ' ', $key));
            
            $stmt->execute([$key, $value, $type, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Pengaturan donasi berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'Donation settings updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui pengaturan donasi: ' . $e->getMessage();
    }
}

// Handle DKM settings update
function handleDKMSettings() {
    global $pdo, $success_message, $error_message;
    
    $settings = [
        'dkm_ketua' => $_POST['dkm_ketua'] ?? '',
        'dkm_wakil_ketua' => $_POST['dkm_wakil_ketua'] ?? '',
        'dkm_sekretaris' => $_POST['dkm_sekretaris'] ?? '',
        'dkm_bendahara' => $_POST['dkm_bendahara'] ?? '',
        'dkm_sie_ibadah' => $_POST['dkm_sie_ibadah'] ?? '',
        'dkm_sie_pendidikan' => $_POST['dkm_sie_pendidikan'] ?? '',
        'dkm_sie_sosial' => $_POST['dkm_sie_sosial'] ?? ''
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, 'text', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            $description = ucfirst(str_replace(['dkm_', '_'], ['', ' '], $key));
            $stmt->execute([$key, $value, $description]);
        }
        
        $pdo->commit();
        $success_message = 'Struktur DKM berhasil diperbarui.';
        
        // Log activity
        logActivity('settings_update', 'DKM structure updated');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Gagal memperbarui struktur DKM: ' . $e->getMessage();
    }
}

// Get setting value
function getSetting($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

$page_title = 'Pengaturan Website';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mosque text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Pengaturan Website</h1>
                        <p class="text-sm text-gray-500">Konfigurasi website masjid</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../index.php" class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Website
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                <?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?>
                            </div>
                            <a href="../dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard Utama
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
         <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                
                <a href="berita.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-images mr-3"></i>Kelola Galeri
                </a>
                
                <a href="jadwal_jumat.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-calendar-alt mr-3"></i>Jadwal Jumat
                </a>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Kelola Donasi
                </a>

                <a href="pengaturan.php" class="bg-green-600 text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-cog mr-3"></i>Pengaturan
                </a>
                
                <div class="border-t border-gray-700 mt-4 pt-4">
                    <a href="../dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                        <i class="fas fa-arrow-left mr-3"></i>Kembali ke Dashboard
                    </a>
                </div>
            </nav>
        </div>


        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <div class="mb-8">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button onclick="showTab('general')" id="tab-general" class="tab-button active">
                        <i class="fas fa-globe mr-2"></i>
                        Pengaturan Umum
                    </button>
                    <button onclick="showTab('contact')" id="tab-contact" class="tab-button">
                        <i class="fas fa-address-book mr-2"></i>
                        Informasi Kontak
                    </button>
                    <button onclick="showTab('social')" id="tab-social" class="tab-button">
                        <i class="fas fa-share-alt mr-2"></i>
                        Media Sosial
                    </button>
                    <button onclick="showTab('branding')" id="tab-branding" class="tab-button">
                        <i class="fas fa-palette mr-2"></i>
                        Branding
                    </button>
                    <button onclick="showTab('prayer')" id="tab-prayer" class="tab-button">
                        <i class="fas fa-pray mr-2"></i>
                        Jadwal Sholat
                    </button>
                    <button onclick="showTab('donation')" id="tab-donation" class="tab-button">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        Donasi
                    </button>
                    <button onclick="showTab('dkm')" id="tab-dkm" class="tab-button">
                        <i class="fas fa-users mr-2"></i>
                        Struktur DKM
                    </button>
                </nav>
            </div>
            <!-- General Settings Tab -->
            <div id="content-general" class="tab-content">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-globe mr-2 text-blue-600"></i>
                        Pengaturan Umum Website
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_general_settings">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Website
                                </label>
                                <input type="text" name="site_name" 
                                       value="<?php echo htmlspecialchars(getSetting('site_name', 'Masjid Jami Al-Muhajirin')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Nama website">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Masjid
                                </label>
                                <input type="text" name="masjid_name" 
                                       value="<?php echo htmlspecialchars(getSetting('masjid_name', 'Masjid Jami Al-Muhajirin')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Nama resmi masjid">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tagline Website
                            </label>
                            <input type="text" name="site_tagline" 
                                   value="<?php echo htmlspecialchars(getSetting('site_tagline', 'Membangun Umat Menuju Ridho Allah')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Tagline atau slogan website">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Website
                            </label>
                            <textarea name="site_description" rows="3" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Deskripsi singkat tentang website"><?php echo htmlspecialchars(getSetting('site_description', 'Website resmi Masjid Jami Al-Muhajirin')); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sejarah Masjid
                            </label>
                            <textarea name="masjid_history" rows="5" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Sejarah singkat masjid"><?php echo htmlspecialchars(getSetting('masjid_history')); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Visi Masjid
                                </label>
                                <textarea name="masjid_vision" rows="4" 
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Visi masjid"><?php echo htmlspecialchars(getSetting('masjid_vision')); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Misi Masjid
                                </label>
                                <textarea name="masjid_mission" rows="4" 
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Misi masjid"><?php echo htmlspecialchars(getSetting('masjid_mission')); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Pengaturan Umum
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Settings Tab -->
            <div id="content-contact" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-address-book mr-2 text-green-600"></i>
                        Informasi Kontak
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_contact_settings">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Lengkap Masjid
                            </label>
                            <textarea name="masjid_address" rows="3" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Alamat lengkap masjid"><?php echo htmlspecialchars(getSetting('masjid_address')); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon
                                </label>
                                <input type="text" name="contact_phone" 
                                       value="<?php echo htmlspecialchars(getSetting('contact_phone')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="021-12345678">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Kontak
                                </label>
                                <input type="email" name="contact_email" 
                                       value="<?php echo htmlspecialchars(getSetting('contact_email')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="info@masjid.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                WhatsApp (dengan kode negara)
                            </label>
                            <input type="text" name="contact_whatsapp" 
                                   value="<?php echo htmlspecialchars(getSetting('contact_whatsapp')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="628123456789">
                            <p class="mt-1 text-xs text-gray-500">Format: 628123456789 (tanpa tanda + atau spasi)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Koordinat Lokasi (Latitude, Longitude)
                            </label>
                            <input type="text" name="location_coordinates" 
                                   value="<?php echo htmlspecialchars(getSetting('location_coordinates')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="-6.200000, 106.816666">
                            <p class="mt-1 text-xs text-gray-500">Untuk menampilkan lokasi di peta dengan akurat</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Google Maps Embed Code
                            </label>
                            <textarea name="google_maps_embed" rows="4" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="<iframe src=&quot;https://www.google.com/maps/embed?...&quot;></iframe>"><?php echo htmlspecialchars(getSetting('google_maps_embed')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Kode embed dari Google Maps untuk menampilkan peta interaktif</p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-lg hover:bg-green-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Informasi Kontak
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Social Media Settings Tab -->
            <div id="content-social" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-share-alt mr-2 text-purple-600"></i>
                        Pengaturan Media Sosial
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_social_media">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-facebook text-blue-600 mr-2"></i>
                                    Facebook Page URL
                                </label>
                                <input type="url" name="social_facebook" 
                                       value="<?php echo htmlspecialchars(getSetting('social_facebook')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://facebook.com/masjidalmuhajirin">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-instagram text-pink-600 mr-2"></i>
                                    Instagram Profile URL
                                </label>
                                <input type="url" name="social_instagram" 
                                       value="<?php echo htmlspecialchars(getSetting('social_instagram')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://instagram.com/masjidalmuhajirin">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-youtube text-red-600 mr-2"></i>
                                    YouTube Channel URL
                                </label>
                                <input type="url" name="social_youtube" 
                                       value="<?php echo htmlspecialchars(getSetting('social_youtube')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://youtube.com/@masjidalmuhajirin">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-twitter text-blue-400 mr-2"></i>
                                    Twitter/X Profile URL
                                </label>
                                <input type="url" name="social_twitter" 
                                       value="<?php echo htmlspecialchars(getSetting('social_twitter')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://twitter.com/masjidalmuhajirin">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fab fa-telegram text-blue-500 mr-2"></i>
                                Telegram Channel/Group URL
                            </label>
                            <input type="url" name="social_telegram" 
                                   value="<?php echo htmlspecialchars(getSetting('social_telegram')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="https://t.me/masjidalmuhajirin">
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Tips Penggunaan Media Sosial
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>Pastikan URL yang dimasukkan adalah URL lengkap (dimulai dengan https://)</li>
                                            <li>Link media sosial akan ditampilkan di footer website</li>
                                            <li>Kosongkan field jika tidak memiliki akun media sosial tersebut</li>
                                            <li>Periksa kembali URL untuk memastikan link dapat diakses</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white py-2 px-6 rounded-lg hover:bg-purple-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Pengaturan Media Sosial
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Branding Settings Tab -->
            <div id="content-branding" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-palette mr-2 text-orange-600"></i>
                        Branding & Logo
                    </h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Logo Upload -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Upload Logo</h4>
                            
                            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="upload_logo">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Pilih File Logo
                                    </label>
                                    <input type="file" name="logo" accept="image/*" required 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                                    <p class="mt-1 text-xs text-gray-500">
                                        Format: PNG, JPG, JPEG. Maksimal 2MB. Rekomendasi: 200x200px
                                    </p>
                                </div>
                                
                                <button type="submit" class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 transition duration-200">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload Logo
                                </button>
                            </form>
                        </div>

                        <!-- Current Logo Display -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Logo Saat Ini</h4>
                            
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <?php 
                                $current_logo = getSetting('site_logo');
                                if ($current_logo && file_exists($current_logo)): 
                                ?>
                                    <img src="<?php echo htmlspecialchars($current_logo); ?>" 
                                         alt="Logo Masjid" 
                                         class="mx-auto max-w-32 max-h-32 object-contain">
                                    <p class="mt-2 text-sm text-gray-600">
                                        Logo aktif: <?php echo basename($current_logo); ?>
                                    </p>
                                <?php else: ?>
                                    <div class="text-gray-400">
                                        <i class="fas fa-image text-4xl mb-2"></i>
                                        <p class="text-sm">Belum ada logo yang diupload</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Panduan Logo
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Gunakan logo dengan latar belakang transparan (PNG) untuk hasil terbaik</li>
                                        <li>Ukuran yang disarankan: 200x200 piksel atau rasio 1:1</li>
                                        <li>Logo akan ditampilkan di header website dan berbagai tempat lainnya</li>
                                        <li>Pastikan logo terlihat jelas dalam ukuran kecil</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Prayer Settings Tab -->
            <div id="content-prayer" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-pray mr-2 text-teal-600"></i>
                        Pengaturan Jadwal Sholat
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_prayer_settings">
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Informasi Jadwal Sholat
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Website akan menggunakan API eksternal untuk mendapatkan jadwal sholat otomatis. Jika API tidak tersedia, sistem akan menggunakan jadwal manual dari database.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="prayer_api_enabled" value="1" 
                                       <?php echo getSetting('prayer_api_enabled', '1') === '1' ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="ml-2 text-sm text-gray-700">Aktifkan API Jadwal Sholat Otomatis</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Jika dinonaktifkan, akan menggunakan jadwal manual dari database</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL API Jadwal Sholat
                            </label>
                            <input type="url" name="prayer_api_url" 
                                   value="<?php echo htmlspecialchars(getSetting('prayer_api_url', 'https://api.myquran.com/v1/sholat/jadwal')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                                   placeholder="https://api.myquran.com/v1/sholat/jadwal">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Kota/Kabupaten
                                </label>
                                <input type="text" name="prayer_location_city" 
                                       value="<?php echo htmlspecialchars(getSetting('prayer_location_city', 'bekasi')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                                       placeholder="bekasi">
                                <p class="mt-1 text-xs text-gray-500">Nama kota dalam huruf kecil untuk API</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Negara
                                </label>
                                <input type="text" name="prayer_location_country" 
                                       value="<?php echo htmlspecialchars(getSetting('prayer_location_country', 'indonesia')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500"
                                       placeholder="indonesia">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Perhitungan
                            </label>
                            <select name="prayer_calculation_method" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                                <option value="1" <?php echo getSetting('prayer_calculation_method', '2') === '1' ? 'selected' : ''; ?>>University of Islamic Sciences, Karachi</option>
                                <option value="2" <?php echo getSetting('prayer_calculation_method', '2') === '2' ? 'selected' : ''; ?>>Islamic Society of North America (ISNA)</option>
                                <option value="3" <?php echo getSetting('prayer_calculation_method', '2') === '3' ? 'selected' : ''; ?>>Muslim World League</option>
                                <option value="4" <?php echo getSetting('prayer_calculation_method', '2') === '4' ? 'selected' : ''; ?>>Umm Al-Qura University, Makkah</option>
                                <option value="5" <?php echo getSetting('prayer_calculation_method', '2') === '5' ? 'selected' : ''; ?>>Egyptian General Authority of Survey</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Pilih metode perhitungan yang sesuai dengan lokasi masjid</p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-teal-600 text-white py-2 px-6 rounded-lg hover:bg-teal-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Pengaturan Jadwal Sholat
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Donation Settings Tab -->
            <div id="content-donation" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-hand-holding-heart mr-2 text-red-600"></i>
                        Pengaturan Donasi & Infaq
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_donation_settings">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Informasi Rekening Donasi
                            </label>
                            <textarea name="donation_account" rows="4" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                      placeholder="Bank Mandiri: 1234567890 a.n. DKM Al-Muhajirin&#10;Bank BRI: 0987654321 a.n. DKM Al-Muhajirin"><?php echo htmlspecialchars(getSetting('donation_account')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Masukkan informasi rekening donasi, satu rekening per baris</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                QR Code untuk Pembayaran Digital
                            </label>
                            <input type="text" name="donation_qr_code" 
                                   value="<?php echo htmlspecialchars(getSetting('donation_qr_code')); ?>"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                   placeholder="Link atau path ke gambar QR Code">
                            <p class="mt-1 text-xs text-gray-500">URL gambar QR Code untuk QRIS, GoPay, OVO, dll</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori Donasi
                            </label>
                            <textarea name="donation_categories" rows="5" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                      placeholder="1. Donasi Operasional Masjid&#10;2. Donasi Pembangunan&#10;3. Donasi Kegiatan Sosial&#10;4. Donasi Pendidikan"><?php echo htmlspecialchars(getSetting('donation_categories')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Daftar kategori donasi yang tersedia</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teks Transparansi Donasi
                            </label>
                            <textarea name="donation_transparency_text" rows="4" 
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                      placeholder="Laporan penggunaan dana donasi akan dipublikasikan setiap bulan di website dan papan pengumuman masjid. Untuk informasi lebih lanjut, silakan hubungi pengurus DKM."><?php echo htmlspecialchars(getSetting('donation_transparency_text')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Informasi tentang transparansi penggunaan dana donasi</p>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Tips Transparansi Donasi
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>Selalu berikan informasi rekening yang jelas dan resmi</li>
                                            <li>Update secara berkala laporan penggunaan dana</li>
                                            <li>Sediakan kontak yang dapat dihubungi untuk pertanyaan donasi</li>
                                            <li>Pastikan QR Code yang digunakan adalah resmi dan aman</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-red-600 text-white py-2 px-6 rounded-lg hover:bg-red-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Pengaturan Donasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DKM Structure Settings Tab -->
            <div id="content-dkm" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-users mr-2 text-indigo-600"></i>
                        Struktur Organisasi DKM
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_dkm_settings">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-crown text-green-600 mr-2"></i>
                                    Ketua DKM
                                </label>
                                <input type="text" name="dkm_ketua" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_ketua', 'H. Ahmad Suryadi, S.Pd')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama lengkap Ketua DKM">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                                    Wakil Ketua
                                </label>
                                <input type="text" name="dkm_wakil_ketua" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_wakil_ketua', 'Drs. Muhammad Yusuf')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama lengkap Wakil Ketua">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-alt text-purple-600 mr-2"></i>
                                    Sekretaris
                                </label>
                                <input type="text" name="dkm_sekretaris" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_sekretaris', 'Siti Aminah, S.Kom')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama lengkap Sekretaris">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-coins text-orange-600 mr-2"></i>
                                    Bendahara
                                </label>
                                <input type="text" name="dkm_bendahara" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_bendahara', 'Abdul Rahman, S.E')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama lengkap Bendahara">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-pray text-teal-600 mr-2"></i>
                                    Seksi Ibadah
                                </label>
                                <input type="text" name="dkm_sie_ibadah" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_sie_ibadah', 'Ustadz Faisal Hakim')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama Koordinator Ibadah">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-graduation-cap text-pink-600 mr-2"></i>
                                    Seksi Pendidikan
                                </label>
                                <input type="text" name="dkm_sie_pendidikan" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_sie_pendidikan', 'Hj. Fatimah, S.Pd.I')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama Koordinator Pendidikan">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-hands-helping text-red-600 mr-2"></i>
                                    Seksi Sosial
                                </label>
                                <input type="text" name="dkm_sie_sosial" 
                                       value="<?php echo htmlspecialchars(getSetting('dkm_sie_sosial', 'H. Bambang Sutrisno')); ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Nama Koordinator Sosial">
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Informasi Struktur DKM
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>Masukkan nama lengkap beserta gelar jika ada</li>
                                            <li>Struktur ini akan ditampilkan di halaman Profil Masjid</li>
                                            <li>Kosongkan field jika posisi belum terisi</li>
                                            <li>Data akan otomatis tersimpan dan ditampilkan di website</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Struktur DKM
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        // Auto-save draft functionality (optional)
        function autoSave() {
            // Could implement auto-save for forms here
            console.log('Auto-save functionality can be implemented here');
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Add any form validation logic here
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Basic validation can be added here
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('border-red-500');
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Mohon lengkapi semua field yang wajib diisi.');
                    }
                });
            });
        });
    </script>

    <style>
        .tab-button {
            @apply py-2 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 transition duration-200;
        }
        
        .tab-button.active {
            @apply text-blue-600 border-blue-600;
        }
        
        .tab-content {
            @apply transition-all duration-300;
        }
        
        /* Custom scrollbar for better UX */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</body>
</html>