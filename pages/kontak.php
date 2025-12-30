<?php
require_once '../config/config.php';

$page_title = 'Kontak Kami';
$page_description = 'Hubungi Masjid Jami Al-Muhajirin untuk informasi lebih lanjut';
$base_url = '..';

$success_message = '';
$error_message = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validate input
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error_message = 'Semua field harus diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format email tidak valid.';
        } elseif (strlen($message) < 10) {
            $error_message = 'Pesan minimal 10 karakter.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
                $result = $stmt->execute([$name, $email, $subject, $message]);
                
                if ($result) {
                    $success_message = 'Pesan Anda berhasil dikirim. Kami akan segera merespons.';
                    // Clear form data
                    $name = $email = $subject = $message = '';
                } else {
                    $error_message = 'Gagal mengirim pesan. Silakan coba lagi.';
                }
            } catch (PDOException $e) {
                $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
            }
        }
    }
}

// Breadcrumb
$breadcrumb = [
    ['title' => 'Kontak Kami', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-green-600 to-teal-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Kontak Kami</h1>
            <p class="text-xl opacity-90">Hubungi kami untuk informasi lebih lanjut</p>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
            <!-- Alamat -->
            <div class="text-center p-8 bg-gray-50 rounded-xl">
                <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Alamat</h3>
                <p class="text-gray-600 leading-relaxed">
                    Q2X5+P3M, Jl. Bumi Alinda Kencana<br>
                    Kaliabang Tengah, Bekasi Utara<br>
                    Kota Bekasi, Jawa Barat 17125
                </p>
                <a href="https://maps.google.com/?q=Masjid+Al-Muhajirin+Bekasi" 
                   target="_blank"
                   class="inline-block mt-4 text-green-600 hover:text-green-700 font-medium">
                    <i class="fas fa-directions mr-1"></i>Lihat di Maps
                </a>
            </div>
            
            <!-- Telepon -->
            <div class="text-center p-8 bg-gray-50 rounded-xl">
                <div class="bg-blue-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-phone text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Telepon</h3>
                <p class="text-gray-600 mb-2">
                    <a href="tel:021-12345678" class="hover:text-green-600">021-12345678</a>
                </p>
                <p class="text-gray-600 mb-2">
                    <a href="https://wa.me/6281234567890" class="hover:text-green-600">
                        <i class="fab fa-whatsapp mr-1"></i>0812-3456-7890
                    </a>
                </p>
                <p class="text-sm text-gray-500 mt-4">
                    Senin - Jumat: 08:00 - 16:00<br>
                    Sabtu - Minggu: 08:00 - 12:00
                </p>
            </div>
            
            <!-- Email -->
            <div class="text-center p-8 bg-gray-50 rounded-xl">
                <div class="bg-purple-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-envelope text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Email</h3>
                <p class="text-gray-600 mb-2">
                    <a href="mailto:info@almuhajirin.com" class="hover:text-green-600">info@almuhajirin.com</a>
                </p>
                <p class="text-gray-600 mb-2">
                    <a href="mailto:bimbel@almuhajirin.com" class="hover:text-green-600">bimbel@almuhajirin.com</a>
                </p>
                <p class="text-sm text-gray-500 mt-4">
                    Kami akan merespons dalam 24 jam
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Map -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Kirim Pesan</h2>
                <p class="text-gray-600 mb-8">
                    Silakan isi form di bawah ini untuk menghubungi kami. 
                    Kami akan merespons pesan Anda sesegera mungkin.
                </p>
                
                <!-- Messages -->
                <?php if ($success_message): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($name ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               required>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               required>
                    </div>
                    
                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subjek *</label>
                        <select id="subject" 
                                name="subject" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                required>
                            <option value="">Pilih Subjek</option>
                            <option value="Informasi Umum" <?php echo ($subject ?? '') === 'Informasi Umum' ? 'selected' : ''; ?>>Informasi Umum</option>
                            <option value="Kegiatan Masjid" <?php echo ($subject ?? '') === 'Kegiatan Masjid' ? 'selected' : ''; ?>>Kegiatan Masjid</option>
                            <option value="Bimbel Al-Muhajirin" <?php echo ($subject ?? '') === 'Bimbel Al-Muhajirin' ? 'selected' : ''; ?>>Bimbel Al-Muhajirin</option>
                            <option value="Donasi & Infaq" <?php echo ($subject ?? '') === 'Donasi & Infaq' ? 'selected' : ''; ?>>Donasi & Infaq</option>
                            <option value="Sewa Fasilitas" <?php echo ($subject ?? '') === 'Sewa Fasilitas' ? 'selected' : ''; ?>>Sewa Fasilitas</option>
                            <option value="Lainnya" <?php echo ($subject ?? '') === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Pesan *</label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Tulis pesan Anda di sini..."
                                  required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Minimal 10 karakter</p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition duration-200 font-semibold">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Pesan
                    </button>
                </form>
            </div>
            
            <!-- Map -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Lokasi Kami</h2>
                <div class="bg-gray-200 rounded-lg h-96 mb-6 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">Peta Lokasi Masjid</p>
                        <p class="text-sm text-gray-500 mt-2">Integrasi Google Maps</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <a href="https://maps.google.com/?q=Masjid+Al-Muhajirin+Bekasi" 
                       target="_blank"
                       class="bg-green-600 text-white text-center py-3 rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-directions mr-2"></i>Petunjuk Arah
                    </a>
                    <a href="tel:021-12345678" 
                       class="bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-phone mr-2"></i>Telepon
                    </a>
                </div>
                
                <!-- Additional Info -->
                <div class="mt-8 p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
                    <div class="space-y-3 text-sm text-gray-600">
                        <p class="flex items-center">
                            <i class="fas fa-clock text-green-600 mr-2"></i>
                            Masjid buka 24 jam untuk ibadah
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-car text-blue-600 mr-2"></i>
                            Parkir gratis tersedia
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-wheelchair text-purple-600 mr-2"></i>
                            Akses untuk penyandang disabilitas
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-wifi text-orange-600 mr-2"></i>
                            WiFi gratis untuk jamaah
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Pertanyaan Umum</h2>
            <p class="text-gray-600">Jawaban untuk pertanyaan yang sering diajukan</p>
        </div>
        
        <div class="space-y-6">
            <!-- FAQ Item 1 -->
            <div class="bg-gray-50 rounded-lg p-6">
                <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(1)">
                    <h3 class="text-lg font-semibold text-gray-900">Bagaimana cara mendaftar bimbel?</h3>
                    <i class="fas fa-chevron-down text-gray-500 transform transition-transform" id="faq-icon-1"></i>
                </button>
                <div class="mt-4 text-gray-600 hidden" id="faq-content-1">
                    <p>Pendaftaran bimbel dapat dilakukan dengan datang langsung ke masjid atau menghubungi nomor telepon kami. Bawa fotokopi rapor terakhir dan KTP orang tua. Biaya pendaftaran Rp 100.000 dan SPP bulanan mulai dari Rp 200.000 tergantung jenjang.</p>
                </div>
            </div>
            
            <!-- FAQ Item 2 -->
            <div class="bg-gray-50 rounded-lg p-6">
                <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(2)">
                    <h3 class="text-lg font-semibold text-gray-900">Apakah ada kajian rutin?</h3>
                    <i class="fas fa-chevron-down text-gray-500 transform transition-transform" id="faq-icon-2"></i>
                </button>
                <div class="mt-4 text-gray-600 hidden" id="faq-content-2">
                    <p>Ya, kami mengadakan kajian rutin setiap Minggu pagi pukul 08:00-10:00 WIB dan kajian malam setiap Rabu pukul 20:00-21:30 WIB. Kajian terbuka untuk umum dan gratis.</p>
                </div>
            </div>
            
            <!-- FAQ Item 3 -->
            <div class="bg-gray-50 rounded-lg p-6">
                <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(3)">
                    <h3 class="text-lg font-semibold text-gray-900">Bisakah menyewa fasilitas masjid?</h3>
                    <i class="fas fa-chevron-down text-gray-500 transform transition-transform" id="faq-icon-3"></i>
                </button>
                <div class="mt-4 text-gray-600 hidden" id="faq-content-3">
                    <p>Aula masjid dapat disewa untuk acara pernikahan, aqiqah, atau kegiatan sosial lainnya. Hubungi pengurus untuk informasi tarif dan ketersediaan. Prioritas diberikan untuk jamaah masjid.</p>
                </div>
            </div>
            
            <!-- FAQ Item 4 -->
            <div class="bg-gray-50 rounded-lg p-6">
                <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(4)">
                    <h3 class="text-lg font-semibold text-gray-900">Bagaimana cara berdonasi?</h3>
                    <i class="fas fa-chevron-down text-gray-500 transform transition-transform" id="faq-icon-4"></i>
                </button>
                <div class="mt-4 text-gray-600 hidden" id="faq-content-4">
                    <p>Donasi dapat disalurkan melalui kotak infaq di masjid, transfer bank, atau QRIS. Lihat halaman donasi untuk informasi rekening lengkap. Semua donasi akan digunakan untuk operasional masjid dan kegiatan sosial.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ikuti Media Sosial Kami</h2>
        <p class="text-gray-600 mb-8">Dapatkan update terbaru kegiatan dan kajian masjid</p>
        
        <div class="flex justify-center space-x-6">
            <a href="#" class="bg-blue-600 text-white p-4 rounded-full hover:bg-blue-700 transition duration-200 transform hover:scale-110">
                <i class="fab fa-facebook-f text-xl"></i>
            </a>
            <a href="#" class="bg-pink-600 text-white p-4 rounded-full hover:bg-pink-700 transition duration-200 transform hover:scale-110">
                <i class="fab fa-instagram text-xl"></i>
            </a>
            <a href="#" class="bg-red-600 text-white p-4 rounded-full hover:bg-red-700 transition duration-200 transform hover:scale-110">
                <i class="fab fa-youtube text-xl"></i>
            </a>
            <a href="#" class="bg-green-600 text-white p-4 rounded-full hover:bg-green-700 transition duration-200 transform hover:scale-110">
                <i class="fab fa-whatsapp text-xl"></i>
            </a>
            <a href="#" class="bg-blue-400 text-white p-4 rounded-full hover:bg-blue-500 transition duration-200 transform hover:scale-110">
                <i class="fab fa-telegram text-xl"></i>
            </a>
        </div>
    </div>
</section>

<script>
function toggleFAQ(id) {
    const content = document.getElementById(`faq-content-${id}`);
    const icon = document.getElementById(`faq-icon-${id}`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const messageTextarea = document.getElementById('message');
    
    // Character counter for message
    const counter = document.createElement('div');
    counter.className = 'text-sm text-gray-500 mt-1';
    messageTextarea.parentNode.appendChild(counter);
    
    function updateCounter() {
        const length = messageTextarea.value.length;
        counter.textContent = `${length} karakter`;
        counter.className = length < 10 ? 'text-sm text-red-500 mt-1' : 'text-sm text-gray-500 mt-1';
    }
    
    messageTextarea.addEventListener('input', updateCounter);
    updateCounter();
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
    });
});
</script>

<?php include '../partials/footer.php'; ?>