<?php
require_once '../config/config.php';
require_once '../includes/settings_loader.php';

$page_title = 'Profil Masjid';
$page_description = 'Sejarah, visi misi, dan struktur organisasi Masjid Jami Al-Muhajirin';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Get masjid profile data
$masjid_profile = getMasjidProfile();

// Breadcrumb
$breadcrumb = [
    ['title' => 'Profil Masjid', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-green-600 to-teal-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Profil Masjid</h1>
            <p class="text-xl opacity-90">Mengenal lebih dekat Masjid Jami Al-Muhajirin</p>
        </div>
    </div>
</section>

<!-- Sejarah Masjid -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Sejarah Masjid</h2>
                <div class="prose prose-lg text-gray-600">
                    <?php 
                    $masjid_history = getWebsiteSetting('masjid_history');
                    if (!empty($masjid_history)): 
                    ?>
                        <?php echo nl2br(htmlspecialchars($masjid_history)); ?>
                    <?php else: ?>
                        <p class="mb-4">
                            Masjid Jami Al-Muhajirin didirikan pada tahun 1995 oleh sekelompok jamaah yang berhijrah 
                            ke daerah <?php echo getWebsiteSetting('location_name', 'Bekasi Utara'); ?>. Nama "Al-Muhajirin" dipilih untuk mengenang semangat hijrah 
                            para pendiri yang meninggalkan kampung halaman demi mencari kehidupan yang lebih baik.
                        </p>
                        <p class="mb-4">
                            Awalnya, masjid ini hanya berupa musholla sederhana dengan kapasitas 50 jamaah. 
                            Seiring berjalannya waktu dan bertambahnya jamaah, pada tahun 2005 dilakukan renovasi 
                            besar-besaran untuk memperluas kapasitas menjadi 300 jamaah.
                        </p>
                        <p class="mb-4">
                            Pada tahun 2015, masjid kembali diperluas dengan menambahkan lantai dua dan berbagai 
                            fasilitas penunjang seperti perpustakaan, ruang belajar, dan tempat wudhu yang lebih memadai. 
                            Kini masjid dapat menampung hingga 500 jamaah.
                        </p>
                        <p>
                            Selain sebagai tempat ibadah, masjid juga mengembangkan program pendidikan melalui 
                            Bimbel Al-Muhajirin yang melayani siswa SD, SMP, dan SMA sejak tahun 2010.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="bg-gray-100 rounded-lg p-2">
                    <img src="../assets/images/masjid-sejarah.jpg" 
                         alt="Sejarah Masjid Al-Muhajirin" 
                         class="w-full h-64 object-cover rounded-lg"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjI1NiIgdmlld0JveD0iMCAwIDQwMCAyNTYiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMjU2IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMDAgMTI4TDE2MCA4OEgyNDBMMjAwIDEyOFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHN2Zz4K'">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">1995</div>
                        <div class="text-sm text-gray-600">Tahun Berdiri</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">500</div>
                        <div class="text-sm text-gray-600">Kapasitas Jamaah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Visi Misi -->
<section class="py-16 bg-gray-50" id="visi-misi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Visi & Misi</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Komitmen kami dalam membangun masyarakat yang beriman, bertakwa, dan berakhlak mulia
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Visi -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <div class="flex items-center mb-6">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-eye text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Visi</h3>
                </div>
                <p class="text-gray-600 leading-relaxed text-lg">
                    <?php 
                    $masjid_vision = getWebsiteSetting('masjid_vision');
                    if (!empty($masjid_vision)): 
                        echo '"' . htmlspecialchars($masjid_vision) . '"';
                    else: 
                    ?>
                        "Menjadi masjid yang memakmurkan umat, mengembangkan pendidikan Islam, 
                        dan menjadi pusat dakwah yang rahmatan lil alamiin di wilayah <?php echo getWebsiteSetting('location_name', 'Bekasi Utara'); ?>."
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Misi -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <i class="fas fa-bullseye text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Misi</h3>
                </div>
                <ul class="text-gray-600 space-y-3">
                    <?php 
                    $masjid_mission = getWebsiteSetting('masjid_mission');
                    if (!empty($masjid_mission)): 
                        $mission_items = explode('|', $masjid_mission);
                        foreach ($mission_items as $mission): 
                            if (!empty(trim($mission))):
                    ?>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span><?php echo htmlspecialchars(trim($mission)); ?></span>
                        </li>
                    <?php 
                            endif;
                        endforeach; 
                    else: 
                    ?>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span>Menyelenggarakan ibadah yang khusyuk dan berjamaah</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span>Mengembangkan pendidikan Islam melalui program bimbel dan kajian</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span>Memberdayakan ekonomi umat melalui program sosial</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <span>Menjadi pusat dakwah dan syiar Islam yang moderat</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Fasilitas -->
<section class="py-16 bg-white" id="fasilitas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Fasilitas Masjid</h2>
            <p class="text-gray-600">Berbagai fasilitas yang tersedia untuk kenyamanan jamaah</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Ruang Sholat -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-mosque text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ruang Sholat Utama</h3>
                <p class="text-gray-600 text-sm">Ruang sholat ber-AC dengan kapasitas 500 jamaah, dilengkapi karpet berkualitas dan sound system</p>
            </div>
            
            <!-- Tempat Wudhu -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-blue-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-tint text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tempat Wudhu</h3>
                <p class="text-gray-600 text-sm">Tempat wudhu terpisah untuk pria dan wanita dengan fasilitas air bersih dan toilet</p>
            </div>
            
            <!-- Perpustakaan -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-purple-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-ambulance text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ambulance</h3>
                <p class="text-gray-600 text-sm">Ambulance untuk umum, Melayani dalam dan luar kota</p>
            </div>
            
            <!-- Ruang Belajar -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-yellow-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ruang Belajar</h3>
                <p class="text-gray-600 text-sm">Ruang kelas untuk kegiatan bimbel dan kajian dengan fasilitas lengkap</p>
            </div>
            
            <!-- Parkir -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-red-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-car text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Area Parkir</h3>
                <p class="text-gray-600 text-sm">Parkir luas untuk mobil dan motor dengan keamanan 24 jam</p>
            </div>
            
            <!-- Aula -->
            <div class="text-center p-6 bg-gray-50 rounded-xl">
                <div class="bg-indigo-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-users text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aula Serbaguna</h3>
                <p class="text-gray-600 text-sm">Ruang serbaguna untuk acara besar, pernikahan, dan kegiatan komunitas</p>
            </div>
        </div>
    </div>
</section>

<!-- Struktur Organisasi -->
<section class="py-16 bg-gray-50" id="struktur-dkm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Struktur Organisasi DKM</h2>
            <p class="text-gray-600">Dewan Kemakmuran Masjid Al-Muhajirin Periode 2023-2026</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $dkm_structure = getDKMStructure();
            if (is_array($dkm_structure)):
                foreach ($dkm_structure as $key => $member): 
                    if (is_array($member) && isset($member['name'], $member['position'], $member['description'], $member['color'])):
            ?>
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($member['name']); ?></h3>
                <p class="text-<?php echo $member['color']; ?>-600 font-medium mb-2"><?php echo htmlspecialchars($member['position']); ?></p>
                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($member['description']); ?></p>
            </div>
            <?php 
                    endif;
                endforeach; 
            else:
            ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">Data struktur DKM tidak tersedia.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Lokasi dan Kontak -->
<section class="py-16 bg-white" id="lokasi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Lokasi & Kontak</h2>
            <p class="text-gray-600">Informasi lengkap untuk menghubungi dan mengunjungi masjid</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Informasi Kontak -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Informasi Kontak</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-green-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Alamat</h4>
                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars(getWebsiteSetting('masjid_address'))); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-phone text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Telepon</h4>
                            <p class="text-gray-600"><?php echo htmlspecialchars(getWebsiteSetting('contact_phone')); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-envelope text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Email</h4>
                            <p class="text-gray-600"><?php echo htmlspecialchars(getWebsiteSetting('contact_email')); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-orange-100 rounded-full p-2 mr-4 mt-1">
                            <i class="fas fa-clock text-orange-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Jam Operasional</h4>
                            <p class="text-gray-600">Buka 24 jam untuk ibadah<br>
                            Kantor: Senin-Jumat 08:00-16:00<br>
                            Bimbel: Senin-Sabtu 15:00-21:00</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="mt-8">
                    <h4 class="font-medium text-gray-900 mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition duration-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-pink-600 text-white p-3 rounded-full hover:bg-pink-700 transition duration-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-red-600 text-white p-3 rounded-full hover:bg-red-700 transition duration-200">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="bg-green-600 text-white p-3 rounded-full hover:bg-green-700 transition duration-200">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Peta -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Peta Lokasi</h3>
                <div class="bg-gray-200 rounded-lg h-96 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">Peta akan ditampilkan di sini</p>
                        <p class="text-sm text-gray-500 mt-2">Integrasi dengan Google Maps</p>
                    </div>
                </div>
                
                <div class="mt-4 flex space-x-3">
                    <a href="https://maps.google.com/?q=Masjid+Al-Muhajirin+Bekasi" 
                       target="_blank"
                       class="flex-1 bg-green-600 text-white text-center py-3 rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-directions mr-2"></i>Petunjuk Arah
                    </a>
                    <a href="tel:<?php echo $settings['contact_phone']; ?>" 
                       class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-phone mr-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-green-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Mari Bergabung Bersama Kami</h2>
        <p class="text-xl opacity-90 mb-8">Jadilah bagian dari komunitas masjid yang penuh berkah</p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="../pages/kontak.php" 
               class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-envelope mr-2"></i>Hubungi Kami
            </a>
            <a href="../pages/donasi.php" 
               class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-hand-holding-heart mr-2"></i>Donasi Sekarang
            </a>
        </div>
    </div>
</section>

<?php include '../partials/footer.php'; ?>