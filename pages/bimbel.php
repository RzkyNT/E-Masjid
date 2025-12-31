<?php
require_once '../config/config.php';
require_once '../includes/settings_loader.php';

$page_title = 'Bimbel Al-Muhajirin';
$page_description = 'Bimbingan Belajar Al-Muhajirin - Pendidikan berkualitas dengan nilai-nilai Islam untuk SD, SMP, dan SMA';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Breadcrumb
$breadcrumb = [
    ['title' => 'Bimbel Al-Muhajirin', 'url' => '']
];

include '../partials/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-green-600 to-teal-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Bimbel Al-Muhajirin</h1>
                <p class="text-xl text-green-100 mb-8 leading-relaxed">
                    Bimbingan belajar berkualitas dengan pendekatan Islami untuk mengembangkan potensi akademik dan karakter siswa
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#daftar" class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition duration-200 text-center">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                    </a>
                    <a href="#program" class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-semibold transition duration-200 text-center">
                        <i class="fas fa-info-circle mr-2"></i>Info Program
                    </a>
                </div>
            </div>
            <div class="text-center">
                <div class="bg-white bg-opacity-10 rounded-2xl p-8">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold">10+</div>
                            <div class="text-sm text-green-100">Tahun Pengalaman</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">500+</div>
                            <div class="text-sm text-green-100">Siswa Alumni</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">15+</div>
                            <div class="text-sm text-green-100">Tentor Berpengalaman</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">95%</div>
                            <div class="text-sm text-green-100">Tingkat Kelulusan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Keunggulan Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Mengapa Memilih Bimbel Al-Muhajirin?</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Kami menggabungkan pendidikan akademik berkualitas dengan pembentukan karakter Islami
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-green-50 rounded-xl">
                <div class="bg-green-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Tentor Berpengalaman</h3>
                <p class="text-gray-600 text-sm">
                    Tentor lulusan universitas terkemuka dengan pengalaman mengajar minimal 3 tahun
                </p>
            </div>
            
            <div class="text-center p-6 bg-blue-50 rounded-xl">
                <div class="bg-blue-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Kelas Kecil</h3>
                <p class="text-gray-600 text-sm">
                    Maksimal 10 siswa per kelas untuk pembelajaran yang lebih fokus dan personal
                </p>
            </div>
            
            <div class="text-center p-6 bg-purple-50 rounded-xl">
                <div class="bg-purple-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-book-open text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Kurikulum Terintegrasi</h3>
                <p class="text-gray-600 text-sm">
                    Mengikuti kurikulum nasional dengan tambahan nilai-nilai Islam dalam pembelajaran
                </p>
            </div>
            
            <div class="text-center p-6 bg-yellow-50 rounded-xl">
                <div class="bg-yellow-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Jadwal Fleksibel</h3>
                <p class="text-gray-600 text-sm">
                    Pilihan waktu belajar yang fleksibel, tersedia kelas pagi, siang, dan sore
                </p>
            </div>
            
            <div class="text-center p-6 bg-red-50 rounded-xl">
                <div class="bg-red-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-chart-line text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Monitoring Progress</h3>
                <p class="text-gray-600 text-sm">
                    Laporan perkembangan belajar siswa secara berkala kepada orang tua
                </p>
            </div>
            
            <div class="text-center p-6 bg-indigo-50 rounded-xl">
                <div class="bg-indigo-100 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-mosque text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Lingkungan Islami</h3>
                <p class="text-gray-600 text-sm">
                    Suasana belajar yang kondusif dengan nilai-nilai akhlak dan adab Islami
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Program Section -->
<section class="py-16 bg-gray-50" id="program">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Program Bimbingan Belajar</h2>
            <p class="text-gray-600">Pilihan program sesuai jenjang pendidikan dan kebutuhan siswa</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Program SD -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                    <div class="text-center">
                        <i class="fas fa-child text-3xl mb-3"></i>
                        <h3 class="text-xl font-bold">Program SD</h3>
                        <p class="text-green-100 text-sm">Kelas 1 - 6</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Mata Pelajaran:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Matematika</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Bahasa Indonesia</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>IPA</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Bahasa Inggris</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Pendidikan Agama Islam</li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Jadwal:</h4>
                        <p class="text-sm text-gray-600">Senin - Jumat: 15:00 - 17:00</p>
                        <p class="text-sm text-gray-600">Sabtu: 08:00 - 10:00</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 mb-2">Rp 200.000</div>
                        <div class="text-sm text-gray-500">per bulan</div>
                    </div>
                </div>
            </div>
            
            <!-- Program SMP -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                    <div class="text-center">
                        <i class="fas fa-user-graduate text-3xl mb-3"></i>
                        <h3 class="text-xl font-bold">Program SMP</h3>
                        <p class="text-blue-100 text-sm">Kelas 7 - 9</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Mata Pelajaran:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><i class="fas fa-check text-blue-500 mr-2"></i>Matematika</li>
                            <li><i class="fas fa-check text-blue-500 mr-2"></i>Bahasa Indonesia</li>
                            <li><i class="fas fa-check text-blue-500 mr-2"></i>IPA (Fisika & Biologi)</li>
                            <li><i class="fas fa-check text-blue-500 mr-2"></i>Bahasa Inggris</li>
                            <li><i class="fas fa-check text-blue-500 mr-2"></i>IPS</li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Jadwal:</h4>
                        <p class="text-sm text-gray-600">Senin - Jumat: 16:00 - 18:00</p>
                        <p class="text-sm text-gray-600">Sabtu: 10:00 - 12:00</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-2">Rp 300.000</div>
                        <div class="text-sm text-gray-500">per bulan</div>
                    </div>
                </div>
            </div>
            
            <!-- Program SMA -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6">
                    <div class="text-center">
                        <i class="fas fa-graduation-cap text-3xl mb-3"></i>
                        <h3 class="text-xl font-bold">Program SMA</h3>
                        <p class="text-purple-100 text-sm">Kelas 10 - 12</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Mata Pelajaran:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><i class="fas fa-check text-purple-500 mr-2"></i>Matematika</li>
                            <li><i class="fas fa-check text-purple-500 mr-2"></i>Fisika</li>
                            <li><i class="fas fa-check text-purple-500 mr-2"></i>Kimia</li>
                            <li><i class="fas fa-check text-purple-500 mr-2"></i>Biologi</li>
                            <li><i class="fas fa-check text-purple-500 mr-2"></i>Bahasa Inggris</li>
                        </ul>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-900 mb-2">Jadwal:</h4>
                        <p class="text-sm text-gray-600">Senin - Jumat: 18:30 - 20:30</p>
                        <p class="text-sm text-gray-600">Sabtu: 13:00 - 15:00</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 mb-2">Rp 400.000</div>
                        <div class="text-sm text-gray-500">per bulan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Fasilitas Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Fasilitas Bimbel</h2>
            <p class="text-gray-600">Fasilitas lengkap untuk mendukung proses pembelajaran yang optimal</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-4">
                <div class="bg-green-100 rounded-full w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                    <i class="fas fa-chalkboard text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Ruang Kelas AC</h4>
                <p class="text-gray-600 text-sm">Ruang belajar nyaman dengan AC dan pencahayaan yang baik</p>
            </div>
            
            <div class="text-center p-4">
                <div class="bg-blue-100 rounded-full w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                    <i class="fas fa-wifi text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">WiFi Gratis</h4>
                <p class="text-gray-600 text-sm">Akses internet untuk mendukung pembelajaran digital</p>
            </div>
            
            <div class="text-center p-4">
                <div class="bg-purple-100 rounded-full w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                    <i class="fas fa-book text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Perpustakaan</h4>
                <p class="text-gray-600 text-sm">Koleksi buku pelajaran dan referensi yang lengkap</p>
            </div>
            
            <div class="text-center p-4">
                <div class="bg-yellow-100 rounded-full w-12 h-12 mx-auto mb-3 flex items-center justify-center">
                    <i class="fas fa-car text-yellow-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Parkir Luas</h4>
                <p class="text-gray-600 text-sm">Area parkir yang aman dan luas untuk kendaraan</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimoni Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Testimoni Orang Tua & Siswa</h2>
            <p class="text-gray-600">Apa kata mereka tentang Bimbel Al-Muhajirin</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Ibu Sari</h4>
                        <p class="text-sm text-gray-600">Orang Tua Siswa SD</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm italic">
                    "Alhamdulillah, nilai anak saya meningkat drastis setelah ikut bimbel di sini. 
                    Selain akademik, akhlaknya juga semakin baik."
                </p>
                <div class="flex text-yellow-400 mt-3">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Ahmad Rizki</h4>
                        <p class="text-sm text-gray-600">Siswa SMP Kelas 9</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm italic">
                    "Belajar di sini menyenangkan! Ustadz dan ustadzahnya sabar mengajar, 
                    dan teman-temannya juga baik-baik."
                </p>
                <div class="flex text-yellow-400 mt-3">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-user text-purple-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Bapak Hendra</h4>
                        <p class="text-sm text-gray-600">Orang Tua Siswa SMA</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm italic">
                    "Anak saya berhasil masuk PTN favorit berkat bimbingan dari bimbel ini. 
                    Terima kasih untuk semua tentor yang sudah membimbing."
                </p>
                <div class="flex text-yellow-400 mt-3">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Form Pendaftaran Section -->
<section class="py-16 bg-white" id="daftar">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Daftar Sekarang</h2>
            <p class="text-gray-600">Isi formulir di bawah ini untuk mendaftar ke Bimbel Al-Muhajirin</p>
        </div>
        
        <div class="bg-gray-50 rounded-xl p-8">
            <form id="registrationForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="namaLengkap" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap Siswa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="namaLengkap" 
                               name="namaLengkap" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               required>
                    </div>
                    <div>
                            <label for="noTelpSiswa" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="noTelpSiswa" 
                                   name="noTelpSiswa" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="jenjang" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenjang Pendidikan <span class="text-red-500">*</span>
                        </label>
                        <select id="jenjang" 
                                name="jenjang" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                required>
                            <option value="">Pilih Jenjang</option>
                            <option value="SD">SD (Sekolah Dasar)</option>
                            <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                            <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="kelas" 
                                name="kelas" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                required>
                            <option value="">Pilih Kelas</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="sekolah" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Sekolah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="sekolah" 
                           name="sekolah" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                           required>
                </div>
                
                <div>
                    <label for="alamatSiswa" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Siswa <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamatSiswa" 
                              name="alamatSiswa" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                              required></textarea>
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Orang Tua/Wali</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="namaOrtu" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Orang Tua/Wali <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="namaOrtu" 
                                   name="namaOrtu" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="noTelpOrtu" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="noTelpOrtu" 
                                   name="noTelpOrtu" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>
                    </div>
                    
                    </div>
                
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Tambahan
                    </label>
                    <textarea id="keterangan" 
                              name="keterangan" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                              placeholder="Informasi tambahan yang perlu diketahui (opsional)"></textarea>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Informasi Pendaftaran</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Formulir akan dikirim via WhatsApp ke admin</li>
                                    <li>Admin akan menghubungi dalam 1x24 jam</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 transition duration-200 font-medium">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Kirim Pendaftaran via WhatsApp
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-green-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Siap Bergabung dengan Bimbel Al-Muhajirin?</h2>
        <p class="text-xl text-green-100 mb-8">
            Wujudkan prestasi akademik terbaik dengan bimbingan yang berkualitas dan Islami
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#daftar" 
               class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
            </a>
            <a href="tel:<?php echo htmlspecialchars($settings['contact_phone']); ?>" 
               class="border-2 border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-semibold transition duration-200">
                <i class="fas fa-phone mr-2"></i>Hubungi Kami
            </a>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenjangSelect = document.getElementById('jenjang');
    const kelasSelect = document.getElementById('kelas');
    const registrationForm = document.getElementById('registrationForm');
    
    // Update kelas options based on jenjang
    jenjangSelect.addEventListener('change', function() {
        const jenjang = this.value;
        kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
        
        if (jenjang === 'SD') {
            for (let i = 1; i <= 6; i++) {
                kelasSelect.innerHTML += `<option value="${i}">Kelas ${i}</option>`;
            }
        } else if (jenjang === 'SMP') {
            for (let i = 7; i <= 9; i++) {
                kelasSelect.innerHTML += `<option value="${i}">Kelas ${i}</option>`;
            }
        } else if (jenjang === 'SMA') {
            for (let i = 10; i <= 12; i++) {
                kelasSelect.innerHTML += `<option value="${i}">Kelas ${i}</option>`;
            }
        }
    });
    
    // Handle form submission
    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(registrationForm);
        
        // Prepare WhatsApp message
        const message = `*PENDAFTARAN BIMBEL AL-MUHAJIRIN*\n\n` +
                       `*DATA SISWA:*\n` +
                       `Nama: ${formData.get('namaLengkap')}\n` +
                       `No. Telp: ${formData.get('noTelpSiswa')}\n` +
                       `Jenjang: ${formData.get('jenjang')} Kelas ${formData.get('kelas')}\n` +
                       `Sekolah: ${formData.get('sekolah')}\n` +
                       `Alamat: ${formData.get('alamatSiswa')}\n\n` +
                       `*DATA ORANG TUA/WALI:*\n` +
                       `Nama: ${formData.get('namaOrtu')}\n` +
                       `No. Telp: ${formData.get('noTelpOrtu')}\n` +
                       `*Catatan Tambahan:*\n${formData.get('keterangan') || '-'}\n\n` +
                       `Mohon informasi lebih lanjut mengenai pendaftaran dan jadwal tes penempatan. Terima kasih.`;
        
        // WhatsApp number (replace with actual number)
        const whatsappNumber = '62895602416781';
        const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
        
        // Show confirmation
        Swal.fire({
            title: 'Konfirmasi Pendaftaran',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Siswa:</strong> ${formData.get('namaLengkap')}</p>
                    <p class="mb-2"><strong>Jenjang:</strong> ${formData.get('jenjang')} Kelas ${formData.get('kelas')}</p>
                    <p class="mb-4"><strong>Orang Tua:</strong> ${formData.get('namaOrtu')}</p>
                    <p class="text-sm text-gray-600">Data pendaftaran akan dikirim via WhatsApp ke admin untuk diproses lebih lanjut.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25d366',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fab fa-whatsapp mr-1"></i> Kirim WhatsApp',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Open WhatsApp
                window.open(whatsappUrl, '_blank');
                
                // Reset form
                registrationForm.reset();
                kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Terkirim!',
                    html: `
                        <div class="text-left">
                            <p class="mb-2"> Data pendaftaran telah dikirim via WhatsApp</p>
                            <p class="mb-2"> Admin akan menghubungi Anda dalam 1x24 jam</p>
                            <p class="text-sm text-gray-600 mt-3">Terima kasih telah memilih Bimbel Al-Muhajirin. Kami akan segera memproses pendaftaran Anda.</p>
                        </div>
                    `,
                    confirmButtonColor: '#059669'
                });
            }
        });
    });
});
</script>

<?php include '../partials/footer.php'; ?>