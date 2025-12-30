<?php
require_once '../config/config.php';

$page_title = 'Donasi & Infaq';
$page_description = 'Salurkan donasi dan infaq Anda untuk kemakmuran masjid dan kegiatan sosial';
$base_url = '..';

// Get donation settings
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'donation_%'");
    $stmt->execute();
    $donation_settings_data = $stmt->fetchAll();
    
    $donation_settings = [];
    foreach ($donation_settings_data as $setting) {
        $donation_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    $donation_settings = [];
}

// Default donation accounts if not in database
$default_accounts = [
    'donation_account_mandiri' => 'Bank Mandiri: 1234567890 a.n. DKM Al-Muhajirin',
    'donation_account_bca' => 'Bank BCA: 0987654321 a.n. DKM Al-Muhajirin',
    'donation_account_bni' => 'Bank BNI: 1122334455 a.n. DKM Al-Muhajirin'
];

// Merge with defaults
$donation_settings = array_merge($default_accounts, $donation_settings);

// Breadcrumb
$breadcrumb = [
    ['title' => 'Donasi & Infaq', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-green-600 to-teal-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Donasi & Infaq</h1>
            <p class="text-xl opacity-90 mb-2">Salurkan kebaikan Anda untuk kemakmuran masjid</p>
            <p class="text-lg opacity-75">"Dan belanjakanlah sebagian dari apa yang telah Kami berikan kepadamu" - QS. Al-Munafiqun: 10</p>
        </div>
    </div>
</section>

<!-- Donation Categories -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Kategori Donasi</h2>
            <p class="text-gray-600">Pilih kategori donasi sesuai dengan niat dan kemampuan Anda</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Operasional Masjid -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 border border-green-200">
                <div class="text-center">
                    <div class="bg-green-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-mosque text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Operasional Masjid</h3>
                    <p class="text-gray-600 mb-6">
                        Untuk kebutuhan sehari-hari masjid seperti listrik, air, kebersihan, 
                        dan pemeliharaan fasilitas.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Listrik dan air</p>
                        <p>• Kebersihan masjid</p>
                        <p>• Pemeliharaan fasilitas</p>
                        <p>• Honorarium petugas</p>
                    </div>
                    <button onclick="selectCategory('operasional')" 
                            class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Pembangunan -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8 border border-blue-200">
                <div class="text-center">
                    <div class="bg-blue-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-hammer text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pembangunan & Renovasi</h3>
                    <p class="text-gray-600 mb-6">
                        Untuk pengembangan dan perbaikan infrastruktur masjid 
                        agar lebih nyaman untuk jamaah.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Renovasi ruang sholat</p>
                        <p>• Perbaikan atap dan lantai</p>
                        <p>• Penambahan fasilitas</p>
                        <p>• Pengembangan area parkir</p>
                    </div>
                    <button onclick="selectCategory('pembangunan')" 
                            class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Pendidikan -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-8 border border-purple-200">
                <div class="text-center">
                    <div class="bg-purple-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pendidikan & Bimbel</h3>
                    <p class="text-gray-600 mb-6">
                        Untuk mendukung program pendidikan dan bimbingan belajar 
                        bagi anak-anak kurang mampu.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Beasiswa bimbel</p>
                        <p>• Buku dan alat tulis</p>
                        <p>• Pelatihan guru</p>
                        <p>• Fasilitas belajar</p>
                    </div>
                    <button onclick="selectCategory('pendidikan')" 
                            class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Sosial -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-8 border border-orange-200">
                <div class="text-center">
                    <div class="bg-orange-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-hands-helping text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kegiatan Sosial</h3>
                    <p class="text-gray-600 mb-6">
                        Untuk program bantuan sosial dan kegiatan dakwah 
                        kepada masyarakat sekitar.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Santunan anak yatim</p>
                        <p>• Bantuan fakir miskin</p>
                        <p>• Paket sembako</p>
                        <p>• Kegiatan dakwah</p>
                    </div>
                    <button onclick="selectCategory('sosial')" 
                            class="w-full bg-orange-600 text-white py-3 rounded-lg hover:bg-orange-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Ramadan -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-8 border border-yellow-200">
                <div class="text-center">
                    <div class="bg-yellow-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-moon text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Program Ramadan</h3>
                    <p class="text-gray-600 mb-6">
                        Untuk kegiatan khusus bulan Ramadan seperti 
                        buka puasa bersama dan santunan.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Buka puasa bersama</p>
                        <p>• Santunan Ramadan</p>
                        <p>• Kajian Ramadan</p>
                        <p>• Paket takjil</p>
                    </div>
                    <button onclick="selectCategory('ramadan')" 
                            class="w-full bg-yellow-600 text-white py-3 rounded-lg hover:bg-yellow-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
            
            <!-- Infaq Umum -->
            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-8 border border-teal-200">
                <div class="text-center">
                    <div class="bg-teal-600 text-white rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Infaq Umum</h3>
                    <p class="text-gray-600 mb-6">
                        Infaq umum yang akan digunakan sesuai kebutuhan 
                        mendesak masjid dan jamaah.
                    </p>
                    <div class="space-y-2 text-sm text-gray-600 mb-6">
                        <p>• Kebutuhan mendesak</p>
                        <p>• Bantuan darurat</p>
                        <p>• Program spontan</p>
                        <p>• Sesuai kebijakan DKM</p>
                    </div>
                    <button onclick="selectCategory('umum')" 
                            class="w-full bg-teal-600 text-white py-3 rounded-lg hover:bg-teal-700 transition duration-200">
                        Donasi Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Methods -->
<section class="py-16 bg-gray-50" id="donation-methods">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Cara Berdonasi</h2>
            <p class="text-gray-600">Pilih metode donasi yang paling mudah untuk Anda</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Bank Transfer -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-university text-blue-600 mr-3"></i>
                    Transfer Bank
                </h3>
                
                <div class="space-y-4">
                    <!-- Bank Mandiri -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSI+CjxyZWN0IHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgZmlsbD0iIzAwNTFBNSIgcng9IjQiLz4KPHN2Zz4K" 
                                     alt="Bank Mandiri" class="w-10 h-10 mr-3">
                                <div>
                                    <p class="font-semibold text-gray-900">Bank Mandiri</p>
                                    <p class="text-sm text-gray-600">a.n. DKM Al-Muhajirin</p>
                                </div>
                            </div>
                            <button onclick="copyToClipboard('1234567890')" 
                                    class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-lg font-mono font-bold text-gray-900 mt-2">1234 5678 90</p>
                    </div>
                    
                    <!-- Bank BCA -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSI+CjxyZWN0IHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgZmlsbD0iIzAwNTFBNSIgcng9IjQiLz4KPHN2Zz4K" 
                                     alt="Bank BCA" class="w-10 h-10 mr-3">
                                <div>
                                    <p class="font-semibold text-gray-900">Bank BCA</p>
                                    <p class="text-sm text-gray-600">a.n. DKM Al-Muhajirin</p>
                                </div>
                            </div>
                            <button onclick="copyToClipboard('0987654321')" 
                                    class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-lg font-mono font-bold text-gray-900 mt-2">0987 6543 21</p>
                    </div>
                    
                    <!-- Bank BNI -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSI+CjxyZWN0IHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgZmlsbD0iI0ZGNjYwMCIgcng9IjQiLz4KPHN2Zz4K" 
                                     alt="Bank BNI" class="w-10 h-10 mr-3">
                                <div>
                                    <p class="font-semibold text-gray-900">Bank BNI</p>
                                    <p class="text-sm text-gray-600">a.n. DKM Al-Muhajirin</p>
                                </div>
                            </div>
                            <button onclick="copyToClipboard('1122334455')" 
                                    class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-lg font-mono font-bold text-gray-900 mt-2">1122 3344 55</p>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Setelah transfer, mohon konfirmasi via WhatsApp dengan menyertakan bukti transfer.
                    </p>
                </div>
            </div>
            
            <!-- Digital Payment -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-qrcode text-green-600 mr-3"></i>
                    Pembayaran Digital
                </h3>
                
                <!-- QRIS -->
                <div class="text-center mb-6">
                    <div class="bg-gray-100 rounded-lg p-6 mb-4">
                        <div class="w-48 h-48 bg-white rounded-lg mx-auto flex items-center justify-center border-2 border-dashed border-gray-300">
                            <div class="text-center">
                                <i class="fas fa-qrcode text-gray-400 text-4xl mb-2"></i>
                                <p class="text-gray-500 text-sm">QR Code QRIS</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Scan QR Code di atas dengan aplikasi mobile banking atau e-wallet Anda
                    </p>
                </div>
                
                <!-- E-Wallet Options -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="border border-gray-200 rounded-lg p-3 text-center">
                        <i class="fas fa-mobile-alt text-green-600 text-2xl mb-2"></i>
                        <p class="text-sm font-medium">GoPay</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3 text-center">
                        <i class="fas fa-wallet text-blue-600 text-2xl mb-2"></i>
                        <p class="text-sm font-medium">OVO</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3 text-center">
                        <i class="fas fa-credit-card text-red-600 text-2xl mb-2"></i>
                        <p class="text-sm font-medium">DANA</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3 text-center">
                        <i class="fas fa-university text-purple-600 text-2xl mb-2"></i>
                        <p class="text-sm font-medium">LinkAja</p>
                    </div>
                </div>
                
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-800">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Pembayaran digital aman dan langsung tercatat otomatis.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Direct Donation -->
        <div class="mt-12 bg-white rounded-xl shadow-md p-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center justify-center">
                    <i class="fas fa-hand-holding-heart text-red-600 mr-3"></i>
                    Donasi Langsung
                </h3>
                <p class="text-gray-600 mb-6">
                    Anda juga dapat menyerahkan donasi langsung ke masjid melalui kotak infaq 
                    atau kepada pengurus masjid.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-red-100 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-box text-red-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Kotak Infaq</h4>
                        <p class="text-sm text-gray-600">Tersedia di dalam masjid</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Pengurus DKM</h4>
                        <p class="text-sm text-gray-600">Serahkan kepada pengurus</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-calendar text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Setelah Sholat</h4>
                        <p class="text-sm text-gray-600">Saat jamaah berkumpul</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transparency Report -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Laporan Transparansi</h2>
            <p class="text-gray-600">Penggunaan dana donasi bulan ini</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Income -->
            <div class="bg-green-50 rounded-xl p-8">
                <h3 class="text-xl font-bold text-green-800 mb-6 flex items-center">
                    <i class="fas fa-arrow-up mr-2"></i>
                    Pemasukan Donasi
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Operasional Masjid</span>
                        <span class="font-semibold text-green-700">Rp 15.500.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Pembangunan</span>
                        <span class="font-semibold text-green-700">Rp 8.200.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Pendidikan</span>
                        <span class="font-semibold text-green-700">Rp 5.800.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Sosial</span>
                        <span class="font-semibold text-green-700">Rp 4.300.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Infaq Umum</span>
                        <span class="font-semibold text-green-700">Rp 6.700.000</span>
                    </div>
                    <hr class="border-green-200">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span class="text-green-800">Total Pemasukan</span>
                        <span class="text-green-800">Rp 40.500.000</span>
                    </div>
                </div>
            </div>
            
            <!-- Expenses -->
            <div class="bg-blue-50 rounded-xl p-8">
                <h3 class="text-xl font-bold text-blue-800 mb-6 flex items-center">
                    <i class="fas fa-arrow-down mr-2"></i>
                    Pengeluaran Dana
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Listrik & Air</span>
                        <span class="font-semibold text-blue-700">Rp 3.200.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Kebersihan</span>
                        <span class="font-semibold text-blue-700">Rp 1.800.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Renovasi Atap</span>
                        <span class="font-semibold text-blue-700">Rp 12.500.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Beasiswa Bimbel</span>
                        <span class="font-semibold text-blue-700">Rp 4.200.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Santunan Yatim</span>
                        <span class="font-semibold text-blue-700">Rp 3.500.000</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Operasional Lain</span>
                        <span class="font-semibold text-blue-700">Rp 2.800.000</span>
                    </div>
                    <hr class="border-blue-200">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span class="text-blue-800">Total Pengeluaran</span>
                        <span class="text-blue-800">Rp 28.000.000</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary -->
        <div class="mt-8 bg-gray-50 rounded-xl p-8 text-center">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Saldo Akhir Bulan</h3>
            <p class="text-3xl font-bold text-green-600 mb-2">Rp 12.500.000</p>
            <p class="text-gray-600">Akan digunakan untuk kebutuhan bulan depan</p>
            
            <div class="mt-6">
                <a href="#" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 mr-4">
                    <i class="fas fa-file-pdf mr-2"></i>Download Laporan Lengkap
                </a>
                <a href="../pages/kontak.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-question-circle mr-2"></i>Tanya Pengurus
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-green-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Mari Bersama Membangun Masjid</h2>
        <p class="text-xl opacity-90 mb-8">
            "Barangsiapa yang membangun masjid karena Allah, maka Allah akan membangunkan untuknya rumah di surga"
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="selectCategory('operasional')" 
                    class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-heart mr-2"></i>Donasi Sekarang
            </button>
            <a href="../pages/kontak.php" 
               class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-phone mr-2"></i>Hubungi Kami
            </a>
        </div>
    </div>
</section>

<!-- Donation Modal -->
<div id="donationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Donasi <span id="selectedCategory"></span></h3>
            <button onclick="closeDonationModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-4">Pilih nominal donasi atau masukkan jumlah sesuai kemampuan Anda:</p>
            
            <!-- Preset Amounts -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <button onclick="setAmount(50000)" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Rp 50.000
                </button>
                <button onclick="setAmount(100000)" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Rp 100.000
                </button>
                <button onclick="setAmount(200000)" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Rp 200.000
                </button>
                <button onclick="setAmount(500000)" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Rp 500.000
                </button>
                <button onclick="setAmount(1000000)" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Rp 1.000.000
                </button>
                <button onclick="document.getElementById('customAmount').focus()" class="border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">
                    Lainnya
                </button>
            </div>
            
            <!-- Custom Amount -->
            <div class="mb-4">
                <label for="customAmount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Donasi</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" 
                           id="customAmount" 
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="0">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button onclick="closeDonationModal()" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Batal
            </button>
            <button onclick="proceedToDonation()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Lanjutkan Donasi
            </button>
        </div>
    </div>
</div>

<script>
let selectedDonationCategory = '';

function selectCategory(category) {
    selectedDonationCategory = category;
    document.getElementById('selectedCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
    document.getElementById('donationModal').classList.remove('hidden');
    document.getElementById('donationModal').classList.add('flex');
}

function closeDonationModal() {
    document.getElementById('donationModal').classList.add('hidden');
    document.getElementById('donationModal').classList.remove('flex');
    document.getElementById('customAmount').value = '';
}

function setAmount(amount) {
    document.getElementById('customAmount').value = amount;
}

function proceedToDonation() {
    const amount = document.getElementById('customAmount').value;
    if (!amount || amount <= 0) {
        alert('Silakan masukkan jumlah donasi yang valid');
        return;
    }
    
    // Here you would typically redirect to payment gateway or show payment instructions
    alert(`Terima kasih! Anda akan mendonasikan Rp ${parseInt(amount).toLocaleString('id-ID')} untuk kategori ${selectedDonationCategory}.\n\nSilakan lakukan transfer ke salah satu rekening yang tersedia dan konfirmasi via WhatsApp.`);
    
    // Scroll to payment methods
    closeDonationModal();
    document.getElementById('donation-methods').scrollIntoView({ behavior: 'smooth' });
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Nomor rekening berhasil disalin!');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Nomor rekening berhasil disalin!');
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// WhatsApp confirmation
function confirmViaWhatsApp() {
    const message = `Assalamu'alaikum, saya ingin mengkonfirmasi donasi untuk Masjid Al-Muhajirin. Mohon informasi lebih lanjut.`;
    const whatsappUrl = `https://wa.me/62895602416781?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Add WhatsApp button to bank transfer section
document.addEventListener('DOMContentLoaded', function() {
    const bankSection = document.querySelector('.bg-blue-50');
    if (bankSection) {
        const whatsappBtn = document.createElement('button');
        whatsappBtn.onclick = confirmViaWhatsApp;
        whatsappBtn.className = 'w-full mt-3 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition duration-200';
        whatsappBtn.innerHTML = '<i class="fab fa-whatsapp mr-2"></i>Konfirmasi via WhatsApp';
        bankSection.appendChild(whatsappBtn);
    }
});
</script>

<?php include '../partials/footer.php'; ?>