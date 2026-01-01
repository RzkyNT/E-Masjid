<?php
$page_title = "Al-Quran Digital v2";
$page_description = "Baca Al-Quran digital dengan audio berkualitas tinggi dari 6 qari terbaik menggunakan API EQuran.id v2.0";
$base_url = '..';

// Include header
include '../partials/header.php';
?>

<!-- Additional CSS for Arabic Font -->
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-6">
                        <i class="fas fa-book-open text-4xl"></i>
                    </div>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Al-Quran Digital v2</h1>
                <p class="text-xl md:text-2xl text-green-100 mb-6">
                    Baca dan dengarkan Al-Quran dengan audio berkualitas tinggi
                </p>
                <div class="flex flex-wrap justify-center gap-4 text-sm">
                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                        <i class="fas fa-book mr-2"></i>114 Surat
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                        <i class="fas fa-volume-up mr-2"></i>6 Qari Terbaik
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                        <i class="fas fa-language mr-2"></i>Terjemahan Indonesia
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- API Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-blue-800">Menggunakan API EQuran.id v2.0 dengan Cache Lokal</h3>
                        <p class="text-sm text-blue-600">Audio berkualitas tinggi dari Misyari Rasyid Al-Afasy dengan penyimpanan lokal</p>
                    </div>
                </div>
                <div id="downloadStatus" class="text-sm text-blue-600">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Mengecek status...
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               id="searchSurat" 
                               placeholder="Cari surat (nama atau nomor)..." 
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button id="filterMakki" 
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-green-100 hover:text-green-700 transition duration-200 filter-btn" 
                            data-filter="mekah">
                        <i class="fas fa-kaaba mr-2"></i>Makki
                    </button>
                    <button id="filterMadani" 
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-green-100 hover:text-green-700 transition duration-200 filter-btn" 
                            data-filter="madinah">
                        <i class="fas fa-mosque mr-2"></i>Madani
                    </button>
                    <button id="filterAll" 
                            class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 filter-btn active" 
                            data-filter="all">
                        <i class="fas fa-list mr-2"></i>Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="mt-4 text-gray-600">Memuat daftar surat...</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
            <h3 class="text-lg font-semibold text-red-800 mb-2">Gagal Memuat Data</h3>
            <p class="text-red-600 mb-4">Terjadi kesalahan saat mengambil data dari server.</p>
            <button onclick="loadSuratList()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                <i class="fas fa-redo mr-2"></i>Coba Lagi
            </button>
        </div>

        <!-- Surat List -->
        <div id="suratList" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Surat cards will be populated here -->
        </div>

        <!-- Surat Detail Modal -->
        <div id="suratModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 id="modalSuratName" class="text-2xl font-bold"></h2>
                                <p id="modalSuratInfo" class="text-green-100"></p>
                            </div>
                            <button onclick="closeModal()" class="text-white hover:text-green-200 transition duration-200">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="overflow-y-auto max-h-[70vh]">
                        <!-- Audio Controls -->
                        <div id="audioControls" class="bg-gray-50 p-4 border-b no-print">
                            <div class="flex flex-col sm:flex-row gap-4 items-center">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-microphone mr-2"></i>Qari: Misyari Rasyid Al-Afasy
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="playAllBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                        <i class="fas fa-play mr-2"></i>Putar Semua
                                    </button>
                                    <button id="stopAllBtn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200 hidden">
                                        <i class="fas fa-stop mr-2"></i>Stop
                                    </button>
                                    <button id="showTafsirBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                        <i class="fas fa-book mr-2"></i>Tafsir
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Ayat -->
                        <div id="loadingAyat" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                            <p class="mt-4 text-gray-600">Memuat ayat-ayat...</p>
                        </div>

                        <!-- Ayat List -->
                        <div id="ayatList" class="p-6">
                            <!-- Ayat will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Element -->
<audio id="audioPlayer" preload="none"></audio>

<script>
let suratData = [];
let currentSurat = null;
let currentAudio = null;
let isPlayingAll = false;
let currentAyatIndex = 0;

// Load surat list on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSuratList();
    setupEventListeners();
    checkDownloadStatus();
});

async function checkDownloadStatus() {
    try {
        const response = await fetch('../api/download_audio.php?action=stats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            const statusElement = document.getElementById('downloadStatus');
            
            if (stats.percentage >= 100) {
                statusElement.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-1"></i>Audio lengkap tersedia';
                statusElement.className = 'text-sm text-green-600';
            } else if (stats.percentage > 0) {
                statusElement.innerHTML = `<i class="fas fa-download mr-1"></i>${stats.percentage}% audio tersedia (${stats.total_size_mb} MB)`;
            } else {
                statusElement.innerHTML = '<i class="fas fa-cloud-download-alt mr-1"></i>Audio akan diunduh otomatis';
            }
        }
    } catch (error) {
        console.error('Error checking download status:', error);
        document.getElementById('downloadStatus').innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Status tidak tersedia';
    }
}

function setupEventListeners() {
    // Search functionality
    document.getElementById('searchSurat').addEventListener('input', function() {
        filterSurat();
    });

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('active', 'bg-green-600', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.add('active', 'bg-green-600', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');
            filterSurat();
        });
    });

    // Audio controls
    document.getElementById('playAllBtn').addEventListener('click', playAllAyat);
    document.getElementById('stopAllBtn').addEventListener('click', stopAllAyat);
    document.getElementById('showTafsirBtn').addEventListener('click', showTafsir);
    
    // Audio player events
    const audioPlayer = document.getElementById('audioPlayer');
    audioPlayer.addEventListener('ended', function() {
        if (isPlayingAll) {
            playNextAyat();
        }
    });
}

async function loadSuratList() {
    try {
        document.getElementById('loadingState').classList.remove('hidden');
        document.getElementById('errorState').classList.add('hidden');
        document.getElementById('suratList').classList.add('hidden');

        const response = await fetch('../api/equran_v2.php?action=surat_list');
        const result = await response.json();

        if (result.code === 200) {
            suratData = result.data;
            displaySuratList(suratData);
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('suratList').classList.remove('hidden');
        } else {
            throw new Error(result.message || 'Failed to load data');
        }
    } catch (error) {
        console.error('Error loading surat list:', error);
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorState').classList.remove('hidden');
    }
}

function displaySuratList(data) {
    const container = document.getElementById('suratList');
    container.innerHTML = '';

    data.forEach(surat => {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 cursor-pointer transform hover:-translate-y-1';
        card.onclick = () => openSuratDetail(surat.nomor);

        card.innerHTML = `
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold">
                        ${surat.nomor}
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-arabic text-green-700 mb-1">${surat.nama}</div>
                        <div class="text-sm text-gray-500">${surat.jumlahAyat} ayat</div>
                    </div>
                </div>
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">${surat.namaLatin}</h3>
                    <p class="text-gray-600 text-sm mb-3">${surat.arti}</p>
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span class="bg-gray-100 px-2 py-1 rounded">
                            <i class="fas fa-map-marker-alt mr-1"></i>${surat.tempatTurun}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-volume-up mr-1"></i>Audio tersedia
                        </span>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
    });
}

function filterSurat() {
    const searchTerm = document.getElementById('searchSurat').value.toLowerCase();
    const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;

    let filteredData = suratData;

    // Apply search filter
    if (searchTerm) {
        filteredData = filteredData.filter(surat => 
            surat.namaLatin.toLowerCase().includes(searchTerm) ||
            surat.nama.includes(searchTerm) ||
            surat.nomor.toString().includes(searchTerm) ||
            surat.arti.toLowerCase().includes(searchTerm)
        );
    }

    // Apply category filter
    if (activeFilter !== 'all') {
        filteredData = filteredData.filter(surat => 
            surat.tempatTurun.toLowerCase() === activeFilter
        );
    }

    displaySuratList(filteredData);
}

async function openSuratDetail(nomorSurat) {
    try {
        document.getElementById('suratModal').classList.remove('hidden');
        document.getElementById('loadingAyat').classList.remove('hidden');
        document.getElementById('ayatList').innerHTML = '';

        const response = await fetch(`../api/equran_v2.php?action=surat_detail&surat_id=${nomorSurat}`);
        const result = await response.json();

        if (result.code === 200) {
            currentSurat = result.data;
            displaySuratDetail(currentSurat);
            document.getElementById('loadingAyat').classList.add('hidden');
        } else {
            throw new Error(result.message || 'Failed to load surat detail');
        }
    } catch (error) {
        console.error('Error loading surat detail:', error);
        document.getElementById('loadingAyat').classList.add('hidden');
        document.getElementById('ayatList').innerHTML = '<div class="text-center text-red-600 py-8">Gagal memuat detail surat</div>';
    }
}

function displaySuratDetail(surat) {
    // Update modal header
    document.getElementById('modalSuratName').textContent = `${surat.nomor}. ${surat.namaLatin}`;
    document.getElementById('modalSuratInfo').textContent = `${surat.arti} • ${surat.tempatTurun} • ${surat.jumlahAyat} ayat`;

    // Display ayat
    const ayatContainer = document.getElementById('ayatList');
    ayatContainer.innerHTML = '';

    // Add Bismillah for all surat except At-Taubah (9)
    if (surat.nomor !== 9 && surat.nomor !== 1) {
        const bismillah = document.createElement('div');
        bismillah.className = 'text-center py-8 border-b border-gray-200 mb-6';
        bismillah.innerHTML = `
            <div class="text-3xl font-arabic text-green-700 mb-2">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
            <div class="text-sm text-gray-600">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang</div>
        `;
        ayatContainer.appendChild(bismillah);
    }

    surat.ayat.forEach((ayat, index) => {
        const ayatDiv = document.createElement('div');
        ayatDiv.className = 'mb-8 p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200';
        ayatDiv.id = `ayat-${ayat.nomorAyat}`;

        ayatDiv.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                    ${ayat.nomorAyat}
                </div>
                <div class="flex items-center gap-2">
                    ${ayat.audio_local ? '<span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full"><i class="fas fa-hdd mr-1"></i>Lokal</span>' : '<span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full"><i class="fas fa-cloud mr-1"></i>Online</span>'}
                    <button onclick="playAyat(${index})" class="text-green-600 hover:text-green-700 transition duration-200 play-btn no-print" data-index="${index}">
                        <i class="fas fa-play text-lg"></i>
                    </button>
                </div>
            </div>
            <div class="text-right mb-4">
                <div class="text-2xl font-arabic leading-loose text-gray-800 mb-3">${ayat.teksArab}</div>
            </div>
            <div class="text-left">
                <div class="text-gray-700 leading-relaxed">${ayat.teksIndonesia}</div>
            </div>
        `;

        ayatContainer.appendChild(ayatDiv);
    });
}

function playAyat(index) {
    const ayat = currentSurat.ayat[index];
    const audioUrl = ayat.audio['05']; // Misyari Rasyid Al-Afasy only

    if (currentAudio) {
        currentAudio.pause();
    }

    const audioPlayer = document.getElementById('audioPlayer');
    const playBtn = document.querySelector(`[data-index="${index}"]`);
    
    // Show loading state
    playBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i>';
    
    audioPlayer.src = audioUrl;
    
    audioPlayer.onloadstart = function() {
        playBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i>';
    };
    
    audioPlayer.oncanplay = function() {
        playBtn.innerHTML = '<i class="fas fa-pause text-lg"></i>';
    };
    
    audioPlayer.onerror = function() {
        playBtn.innerHTML = '<i class="fas fa-exclamation-triangle text-lg text-red-500"></i>';
        alert('Gagal memuat audio. Silakan coba lagi.');
    };
    
    audioPlayer.play().catch(error => {
        console.error('Error playing audio:', error);
        playBtn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        alert('Gagal memutar audio. Silakan coba lagi.');
    });
    
    currentAudio = audioPlayer;

    // Update other play buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        if (btn !== playBtn) {
            btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        }
    });

    // Highlight current ayat
    document.querySelectorAll('#ayatList > div').forEach(div => {
        div.classList.remove('bg-green-100', 'border-green-300');
        div.classList.add('bg-gray-50');
    });
    document.getElementById(`ayat-${ayat.nomorAyat}`).classList.add('bg-green-100', 'border-green-300');
    document.getElementById(`ayat-${ayat.nomorAyat}`).classList.remove('bg-gray-50');
}

function playAllAyat() {
    isPlayingAll = true;
    currentAyatIndex = 0;
    document.getElementById('playAllBtn').classList.add('hidden');
    document.getElementById('stopAllBtn').classList.remove('hidden');
    playAyat(currentAyatIndex);
}

function playNextAyat() {
    currentAyatIndex++;
    if (currentAyatIndex < currentSurat.ayat.length) {
        playAyat(currentAyatIndex);
    } else {
        stopAllAyat();
    }
}

function stopAllAyat() {
    isPlayingAll = false;
    currentAyatIndex = 0;
    if (currentAudio) {
        currentAudio.pause();
    }
    document.getElementById('playAllBtn').classList.remove('hidden');
    document.getElementById('stopAllBtn').classList.add('hidden');
    
    // Reset play buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
    });
    
    // Remove highlights
    document.querySelectorAll('#ayatList > div').forEach(div => {
        div.classList.remove('bg-green-100', 'border-green-300');
        div.classList.add('bg-gray-50');
    });
}

function closeModal() {
    document.getElementById('suratModal').classList.add('hidden');
    stopAllAyat();
}

async function showTafsir() {
    if (!currentSurat) return;
    
    try {
        const response = await fetch(`../api/equran_v2.php?action=tafsir&surat_id=${currentSurat.nomor}`);
        const result = await response.json();
        
        if (result.code === 200) {
            const tafsirData = result.data;
            
            // Create tafsir modal
            const tafsirModal = document.createElement('div');
            tafsirModal.id = 'tafsirModal';
            tafsirModal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto';
            tafsirModal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-2xl font-bold">Tafsir ${tafsirData.namaLatin}</h2>
                                    <p class="text-blue-100">${tafsirData.arti}</p>
                                </div>
                                <button onclick="closeTafsirModal()" class="text-white hover:text-blue-200 transition duration-200">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        <div class="overflow-y-auto max-h-[70vh] p-6">
                            <div class="prose max-w-none">
                                <p class="text-gray-700 leading-relaxed">${tafsirData.tafsir}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(tafsirModal);
            
            // Add close function to window
            window.closeTafsirModal = function() {
                document.body.removeChild(tafsirModal);
            };
            
            // Close on outside click
            tafsirModal.addEventListener('click', function(e) {
                if (e.target === tafsirModal) {
                    window.closeTafsirModal();
                }
            });
            
        } else {
            alert('Gagal memuat tafsir surat');
        }
    } catch (error) {
        console.error('Error loading tafsir:', error);
        alert('Terjadi kesalahan saat memuat tafsir');
    }
}

// Close modal when clicking outside
document.getElementById('suratModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        // Close tafsir modal if exists
        const tafsirModal = document.getElementById('tafsirModal');
        if (tafsirModal) {
            document.body.removeChild(tafsirModal);
        }
    }
    
    // Space bar to play/pause current audio
    if (e.code === 'Space' && currentAudio && document.getElementById('suratModal').classList.contains('hidden') === false) {
        e.preventDefault();
        if (currentAudio.paused) {
            currentAudio.play();
        } else {
            currentAudio.pause();
        }
    }
});
</script>

<style>
.font-arabic {
    font-family: 'Amiri', 'Times New Roman', serif;
    line-height: 2.2;
}

.filter-btn.active {
    background-color: #059669 !important;
    color: white !important;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #059669;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #047857;
}

/* Animation for cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#suratList > div {
    animation: fadeInUp 0.5s ease-out;
}

/* Responsive text sizes */
@media (max-width: 640px) {
    .font-arabic {
        font-size: 1.5rem;
        line-height: 2;
    }
}

@media (min-width: 641px) {
    .font-arabic {
        font-size: 2rem;
        line-height: 2.2;
    }
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

/* Hover effects for ayat cards */
#ayatList > div:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Audio button states */
.play-btn:hover {
    transform: scale(1.1);
}

.play-btn:active {
    transform: scale(0.95);
}

/* Modal backdrop blur */
.modal-backdrop {
    backdrop-filter: blur(4px);
}

/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Focus states for accessibility */
button:focus,
input:focus,
select:focus {
    outline: 2px solid #059669;
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .font-arabic {
        font-size: 18pt;
        line-height: 1.8;
    }
}
</style>

<?php include '../partials/footer.php'; ?>