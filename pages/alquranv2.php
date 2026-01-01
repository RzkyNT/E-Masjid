<?php
$page_title = "Al-Quran Digital v2";
$page_description = "Baca Al-Quran digital dengan audio berkualitas tinggi dari 6 qari terbaik menggunakan API EQuran.id v2.0";
$base_url = '..';

// Include header
include '../partials/header.php';
?>

<!-- Additional CSS for Arabic Font -->
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                        <p class="text-sm text-blue-600">Audio streaming langsung dari CDN berkualitas tinggi</p>
                    </div>
                </div>
                <div class="text-sm text-blue-600">
                    <i class="fas fa-cloud mr-1"></i>Streaming Audio
                </div>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Akses Cepat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button onclick="resumeReading()" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition duration-200 text-sm">
                    <i class="fas fa-play mr-2"></i>Lanjut Baca
                </button>
                <button onclick="showLastRead()" class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition duration-200 text-sm">
                    <i class="fas fa-bookmark mr-2"></i>Terakhir Dibaca
                </button>
                <button onclick="showFavorites()" class="bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition duration-200 text-sm">
                    <i class="fas fa-heart mr-2"></i>Favorit
                </button>
                <button onclick="showBookmarks()" class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 transition duration-200 text-sm">
                    <i class="fas fa-bookmark mr-2"></i>Bookmark
                </button>
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
                    <button id="filterFavorites" 
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 transition duration-200 filter-btn" 
                            data-filter="favorites">
                        <i class="fas fa-heart mr-2"></i>Favorit
                    </button>
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
                            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-microphone mr-2"></i>Qari: Misyari Rasyid Al-Afasy
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button id="playAllBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                        <i class="fas fa-play mr-2"></i>Putar Semua
                                    </button>
                                    <button id="stopAllBtn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200 hidden">
                                        <i class="fas fa-stop mr-2"></i>Stop
                                    </button>
                                    <button id="showTafsirBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                        <i class="fas fa-book mr-2"></i>Tafsir
                                    </button>
                                    <button onclick="toggleFavoriteSurat()" id="favoriteSuratBtn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                        <i class="fas fa-heart mr-2"></i>Favorit
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Reading Progress Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Progress Bacaan</span>
                                    <span id="readingProgress">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="progressBar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
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
let readingStartTime = null;
let currentHighlightedAyat = null;

// LocalStorage keys
const STORAGE_KEYS = {
    LAST_READ: 'alquran_last_read',
    BOOKMARKS: 'alquran_bookmarks',
    FAVORITES: 'alquran_favorites',
    READING_PROGRESS: 'alquran_reading_progress',
    HIGHLIGHTS: 'alquran_highlights',
    READING_STATS: 'alquran_reading_stats',
    READING_SESSION: 'alquran_reading_session'
};

// LocalStorage helper functions
const Storage = {
    get: (key) => {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Error reading from localStorage:', e);
            return null;
        }
    },
    
    set: (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Error writing to localStorage:', e);
            return false;
        }
    },
    
    remove: (key) => {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('Error removing from localStorage:', e);
            return false;
        }
    }
};

// Load surat list on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSuratList();
    setupEventListeners();
    initializeReadingFeatures();
    showLastReadIndicator();
});

// Initialize reading features
function initializeReadingFeatures() {
    // Load reading stats
    updateReadingStats();
    
    // Show resume button if there's a last read
    const lastRead = Storage.get(STORAGE_KEYS.LAST_READ);
    if (lastRead) {
        document.querySelector('button[onclick="resumeReading()"]').classList.remove('hidden');
    }
    
    // Update favorites filter
    updateFavoritesFilter();
}

// Show last read indicator on surat cards
function showLastReadIndicator() {
    const lastRead = Storage.get(STORAGE_KEYS.LAST_READ);
    if (!lastRead) return;
    
    setTimeout(() => {
        const suratCard = document.querySelector(`[onclick="openSuratDetail(${lastRead.surat_id})"]`);
        if (suratCard) {
            const indicator = document.createElement('div');
            indicator.className = 'absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full';
            indicator.innerHTML = '<i class="fas fa-bookmark mr-1"></i>Terakhir';
            suratCard.style.position = 'relative';
            suratCard.appendChild(indicator);
        }
    }, 1000);
}

async function checkDownloadStatus() {
    // Removed - no longer downloading audio to server
    // Audio is streamed directly from CDN
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

    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const lastRead = Storage.get(STORAGE_KEYS.LAST_READ);

    data.forEach(surat => {
        const card = document.createElement('div');
        const isFavorite = favorites.includes(surat.nomor);
        const isLastRead = lastRead && lastRead.surat_id === surat.nomor;
        
        card.className = 'bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 cursor-pointer transform hover:-translate-y-1 relative';
        card.onclick = () => openSuratDetail(surat.nomor);

        card.innerHTML = `
            <div class="p-6">
                ${isLastRead ? '<div class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full"><i class="fas fa-bookmark mr-1"></i>Terakhir</div>' : ''}
                ${isFavorite ? '<div class="absolute top-2 left-2 text-red-500 text-lg"><i class="fas fa-heart"></i></div>' : ''}
                
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
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full"><i class="fas fa-cloud mr-1"></i>Streaming</span>
                            ${getReadingProgress(surat.nomor)}
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
    });
}

// Get reading progress for a surat
function getReadingProgress(suratId) {
    const progress = Storage.get(STORAGE_KEYS.READING_PROGRESS) || {};
    const suratProgress = progress[suratId];
    
    if (!suratProgress) return '';
    
    const percentage = Math.round((suratProgress.lastAyat / suratProgress.totalAyat) * 100);
    if (percentage === 100) {
        return '<span class="text-green-600 bg-green-100 px-2 py-1 rounded-full text-xs"><i class="fas fa-check mr-1"></i>Selesai</span>';
    } else if (percentage > 0) {
        return `<span class="text-blue-600 bg-blue-100 px-2 py-1 rounded-full text-xs">${percentage}%</span>`;
    }
    return '';
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
    if (activeFilter === 'favorites') {
        const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
        filteredData = filteredData.filter(surat => favorites.includes(surat.nomor));
    } else if (activeFilter !== 'all') {
        filteredData = filteredData.filter(surat => 
            surat.tempatTurun.toLowerCase() === activeFilter
        );
    }

    displaySuratList(filteredData);
}

// Update favorites filter button
function updateFavoritesFilter() {
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const favoritesBtn = document.getElementById('filterFavorites');
    if (favorites.length > 0) {
        favoritesBtn.innerHTML = `<i class="fas fa-heart mr-2"></i>Favorit (${favorites.length})`;
    }
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
            
            // Save as last read
            saveLastRead(nomorSurat, 1);
            
            // Start reading session
            startReadingSession(nomorSurat, 1);
            
            // Update favorite button
            updateFavoriteButton(nomorSurat);
            
            // Load bookmarks and highlights
            loadBookmarksAndHighlights(nomorSurat);
            
            // Update reading progress
            updateReadingProgressBar();
            
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

    const bookmarks = Storage.get(STORAGE_KEYS.BOOKMARKS) || {};
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    const suratBookmarks = bookmarks[surat.nomor] || [];
    const suratHighlights = highlights[surat.nomor] || {};

    surat.ayat.forEach((ayat, index) => {
        const ayatDiv = document.createElement('div');
        const isBookmarked = suratBookmarks.includes(ayat.nomorAyat);
        const highlight = suratHighlights[ayat.nomorAyat];
        
        let highlightClass = '';
        if (highlight) {
            highlightClass = `highlight-${highlight.color}`;
        }
        
        ayatDiv.className = `mb-8 p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200 ${highlightClass}`;
        ayatDiv.id = `ayat-${ayat.nomorAyat}`;

        ayatDiv.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                    ${ayat.nomorAyat}
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full"><i class="fas fa-cloud mr-1"></i>Streaming</span>
                    
                    <!-- Highlight Colors -->
                    <div class="relative group">
                        <button onclick="showHighlightMenu(${surat.nomor}, ${ayat.nomorAyat})" class="text-gray-600 hover:text-yellow-600 transition duration-200">
                            <i class="fas fa-highlighter text-sm"></i>
                        </button>
                    </div>
                    
                    <!-- Bookmark Button -->
                    <button onclick="toggleBookmark(${surat.nomor}, ${ayat.nomorAyat})" class="bookmark-btn ${isBookmarked ? 'text-blue-600' : 'text-gray-600'} hover:text-blue-700 transition duration-200" data-surat="${surat.nomor}" data-ayat="${ayat.nomorAyat}">
                        <i class="fas fa-bookmark text-sm"></i>
                    </button>
                    
                    <!-- Play Button -->
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
                ${highlight && highlight.note ? `<div class="mt-2 p-2 bg-yellow-100 rounded text-sm italic">${highlight.note}</div>` : ''}
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
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memuat Audio',
            text: 'Tidak dapat memuat audio. Silakan coba lagi.',
            confirmButtonColor: '#059669'
        });
    };
    
    audioPlayer.play().catch(error => {
        console.error('Error playing audio:', error);
        playBtn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memutar Audio',
            text: 'Tidak dapat memutar audio. Silakan coba lagi.',
            confirmButtonColor: '#059669'
        });
    });
    
    currentAudio = audioPlayer;

    // Update other play buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        if (btn !== playBtn) {
            btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        }
    });

    // Highlight current ayat with audio sync
    highlightCurrentAyat(ayat.nomorAyat);
    
    // Save reading progress
    saveReadingProgress(currentSurat.nomor, ayat.nomorAyat);
    
    // Update last read
    saveLastRead(currentSurat.nomor, ayat.nomorAyat);
}

// Highlight current ayat during audio playback
function highlightCurrentAyat(ayatNumber) {
    // Remove previous highlight
    if (currentHighlightedAyat) {
        currentHighlightedAyat.classList.remove('bg-green-100', 'border-green-300', 'border-2');
        currentHighlightedAyat.classList.add('bg-gray-50');
    }
    
    // Add new highlight
    const ayatElement = document.getElementById(`ayat-${ayatNumber}`);
    if (ayatElement) {
        ayatElement.classList.remove('bg-gray-50');
        ayatElement.classList.add('bg-green-100', 'border-green-300', 'border-2');
        currentHighlightedAyat = ayatElement;
        
        // Scroll to current ayat
        ayatElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
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

// ==================== ADVANCED FEATURES ====================

// 1. LAST READ FUNCTIONALITY
function saveLastRead(suratId, ayatId) {
    const lastRead = {
        surat_id: suratId,
        ayat_id: ayatId,
        timestamp: new Date().toISOString(),
        surat_name: currentSurat ? currentSurat.namaLatin : `Surat ${suratId}`
    };
    Storage.set(STORAGE_KEYS.LAST_READ, lastRead);
}

function resumeReading() {
    const lastRead = Storage.get(STORAGE_KEYS.LAST_READ);
    if (lastRead) {
        openSuratDetail(lastRead.surat_id);
        setTimeout(() => {
            const ayatElement = document.getElementById(`ayat-${lastRead.ayat_id}`);
            if (ayatElement) {
                ayatElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                ayatElement.classList.add('bg-blue-100', 'border-blue-300', 'border-2');
                setTimeout(() => {
                    ayatElement.classList.remove('bg-blue-100', 'border-blue-300', 'border-2');
                }, 3000);
            }
        }, 1000);
    } else {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Riwayat',
            text: 'Belum ada riwayat bacaan yang tersimpan.',
            confirmButtonColor: '#059669'
        });
    }
}

function showLastRead() {
    const lastRead = Storage.get(STORAGE_KEYS.LAST_READ);
    if (lastRead) {
        const date = new Date(lastRead.timestamp).toLocaleDateString('id-ID');
        Swal.fire({
            icon: 'info',
            title: 'Terakhir Dibaca',
            html: `
                <div class="text-left">
                    <p><strong>Surat:</strong> ${lastRead.surat_name}</p>
                    <p><strong>Ayat:</strong> ${lastRead.ayat_id}</p>
                    <p><strong>Tanggal:</strong> ${date}</p>
                </div>
            `,
            confirmButtonColor: '#059669'
        });
    } else {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Riwayat',
            text: 'Belum ada riwayat bacaan yang tersimpan.',
            confirmButtonColor: '#059669'
        });
    }
}

// 2. BOOKMARK FUNCTIONALITY
function toggleBookmark(suratId, ayatId) {
    const bookmarks = Storage.get(STORAGE_KEYS.BOOKMARKS) || {};
    
    if (!bookmarks[suratId]) {
        bookmarks[suratId] = [];
    }
    
    const index = bookmarks[suratId].indexOf(ayatId);
    const bookmarkBtn = document.querySelector(`[data-surat="${suratId}"][data-ayat="${ayatId}"]`);
    
    if (index > -1) {
        // Remove bookmark
        bookmarks[suratId].splice(index, 1);
        if (bookmarks[suratId].length === 0) {
            delete bookmarks[suratId];
        }
        bookmarkBtn.classList.remove('text-blue-600');
        bookmarkBtn.classList.add('text-gray-600');
        showNotification('Bookmark dihapus', 'info');
    } else {
        // Add bookmark
        bookmarks[suratId].push(ayatId);
        bookmarkBtn.classList.remove('text-gray-600');
        bookmarkBtn.classList.add('text-blue-600');
        
        // Ask for note with SweetAlert
        Swal.fire({
            title: 'Tambahkan Catatan',
            input: 'textarea',
            inputPlaceholder: 'Masukkan catatan untuk ayat ini (opsional)...',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Lewati',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const bookmarkNotes = Storage.get(STORAGE_KEYS.BOOKMARKS + '_notes') || {};
                if (!bookmarkNotes[suratId]) bookmarkNotes[suratId] = {};
                bookmarkNotes[suratId][ayatId] = result.value;
                Storage.set(STORAGE_KEYS.BOOKMARKS + '_notes', bookmarkNotes);
            }
        });
        
        showNotification('Ayat di-bookmark', 'success');
    }
    
    Storage.set(STORAGE_KEYS.BOOKMARKS, bookmarks);
}

function showBookmarks() {
    const bookmarks = Storage.get(STORAGE_KEYS.BOOKMARKS) || {};
    const bookmarkNotes = Storage.get(STORAGE_KEYS.BOOKMARKS + '_notes') || {};
    
    if (Object.keys(bookmarks).length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Bookmark',
            text: 'Belum ada ayat yang di-bookmark.',
            confirmButtonColor: '#059669'
        });
        return;
    }
    
    let bookmarkList = '<div class="text-left space-y-3">';
    Object.keys(bookmarks).forEach(suratId => {
        const suratName = suratData.find(s => s.nomor == suratId)?.namaLatin || `Surat ${suratId}`;
        bookmarkList += `<div class="border-b pb-2"><strong>${suratName}:</strong><ul class="ml-4 mt-1">`;
        bookmarks[suratId].forEach(ayatId => {
            const note = bookmarkNotes[suratId] && bookmarkNotes[suratId][ayatId] ? 
                `<br><small class="text-gray-600">${bookmarkNotes[suratId][ayatId]}</small>` : '';
            bookmarkList += `<li>• Ayat ${ayatId}${note}</li>`;
        });
        bookmarkList += '</ul></div>';
    });
    bookmarkList += '</div>';
    
    Swal.fire({
        title: 'Bookmark Ayat',
        html: bookmarkList,
        width: '600px',
        confirmButtonColor: '#059669'
    });
}

// 3. FAVORITE SURAT FUNCTIONALITY
function toggleFavoriteSurat() {
    if (!currentSurat) return;
    
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const suratId = currentSurat.nomor;
    const index = favorites.indexOf(suratId);
    const favoriteBtn = document.getElementById('favoriteSuratBtn');
    
    if (index > -1) {
        // Remove from favorites
        favorites.splice(index, 1);
        favoriteBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Favorit';
        showNotification('Dihapus dari favorit', 'info');
    } else {
        // Add to favorites
        favorites.push(suratId);
        favoriteBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Favorit ❤️';
        showNotification('Ditambahkan ke favorit', 'success');
    }
    
    Storage.set(STORAGE_KEYS.FAVORITES, favorites);
    updateFavoritesFilter();
}

function updateFavoriteButton(suratId) {
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const favoriteBtn = document.getElementById('favoriteSuratBtn');
    
    if (favorites.includes(suratId)) {
        favoriteBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Favorit ❤️';
    } else {
        favoriteBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-2"></i>Favorit';
    }
}

function showFavorites() {
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    if (favorites.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Belum Ada Favorit',
            text: 'Belum ada surat favorit yang tersimpan.',
            confirmButtonColor: '#059669'
        });
        return;
    }
    
    // Filter to show only favorites
    document.getElementById('filterFavorites').click();
}

// 4. READING PROGRESS FUNCTIONALITY
function saveReadingProgress(suratId, ayatId) {
    const progress = Storage.get(STORAGE_KEYS.READING_PROGRESS) || {};
    const surat = suratData.find(s => s.nomor === suratId);
    
    if (surat) {
        progress[suratId] = {
            lastAyat: ayatId,
            totalAyat: surat.jumlahAyat,
            timestamp: new Date().toISOString()
        };
        Storage.set(STORAGE_KEYS.READING_PROGRESS, progress);
        updateReadingProgressBar();
    }
}

function updateReadingProgressBar() {
    if (!currentSurat) return;
    
    const progress = Storage.get(STORAGE_KEYS.READING_PROGRESS) || {};
    const suratProgress = progress[currentSurat.nomor];
    
    if (suratProgress) {
        const percentage = Math.round((suratProgress.lastAyat / suratProgress.totalAyat) * 100);
        document.getElementById('progressBar').style.width = `${percentage}%`;
        document.getElementById('readingProgress').textContent = `${percentage}%`;
    }
}

// 5. READING SESSION FUNCTIONALITY
function startReadingSession(suratId, ayatId) {
    readingStartTime = new Date();
    const session = {
        surat_id: suratId,
        ayat_id: ayatId,
        start_time: readingStartTime.toISOString(),
        scroll_position: 0
    };
    Storage.set(STORAGE_KEYS.READING_SESSION, session);
}

function updateReadingSession(ayatId, scrollPosition = 0) {
    const session = Storage.get(STORAGE_KEYS.READING_SESSION);
    if (session) {
        session.ayat_id = ayatId;
        session.scroll_position = scrollPosition;
        session.last_update = new Date().toISOString();
        Storage.set(STORAGE_KEYS.READING_SESSION, session);
    }
}

// 6. HIGHLIGHT FUNCTIONALITY
function showHighlightMenu(suratId, ayatId) {
    const colors = [
        { name: 'Kuning', value: 'yellow', class: 'bg-yellow-200' },
        { name: 'Hijau', value: 'green', class: 'bg-green-200' },
        { name: 'Biru', value: 'blue', class: 'bg-blue-200' },
        { name: 'Merah', value: 'red', class: 'bg-red-200' },
        { name: 'Ungu', value: 'purple', class: 'bg-purple-200' }
    ];
    
    const menu = document.createElement('div');
    menu.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
    menu.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Pilih Warna Highlight</h3>
            <div class="grid grid-cols-2 gap-2 mb-4">
                ${colors.map(color => `
                    <button onclick="addHighlight(${suratId}, ${ayatId}, '${color.value}')" 
                            class="p-3 rounded-lg border-2 hover:border-gray-400 ${color.class}">
                        ${color.name}
                    </button>
                `).join('')}
            </div>
            <div class="flex gap-2">
                <button onclick="removeHighlight(${suratId}, ${ayatId})" 
                        class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Hapus Highlight
                </button>
                <button onclick="closeHighlightMenu()" 
                        class="flex-1 bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700">
                    Batal
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(menu);
    window.currentHighlightMenu = menu;
}

function addHighlight(suratId, ayatId, color) {
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    
    if (!highlights[suratId]) {
        highlights[suratId] = {};
    }
    
    // Ask for note with SweetAlert
    Swal.fire({
        title: 'Tambahkan Catatan Highlight',
        input: 'textarea',
        inputPlaceholder: 'Masukkan catatan untuk highlight ini (opsional)...',
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Lewati',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        const note = result.isConfirmed ? result.value || '' : '';
        
        highlights[suratId][ayatId] = {
            color: color,
            note: note,
            timestamp: new Date().toISOString()
        };
        
        Storage.set(STORAGE_KEYS.HIGHLIGHTS, highlights);
        
        // Apply highlight to current ayat
        const ayatElement = document.getElementById(`ayat-${ayatId}`);
        if (ayatElement) {
            ayatElement.className = ayatElement.className.replace(/highlight-\w+/g, '');
            ayatElement.classList.add(`highlight-${color}`);
            
            // Add note if provided
            if (note) {
                const existingNote = ayatElement.querySelector('.highlight-note');
                if (existingNote) {
                    existingNote.remove();
                }
                
                const noteDiv = document.createElement('div');
                noteDiv.className = 'mt-2 p-2 bg-yellow-100 rounded text-sm italic highlight-note';
                noteDiv.textContent = note;
                ayatElement.querySelector('.text-left').appendChild(noteDiv);
            }
        }
        
        closeHighlightMenu();
        showNotification('Highlight ditambahkan', 'success');
    });
}

function removeHighlight(suratId, ayatId) {
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    
    if (highlights[suratId] && highlights[suratId][ayatId]) {
        delete highlights[suratId][ayatId];
        
        if (Object.keys(highlights[suratId]).length === 0) {
            delete highlights[suratId];
        }
        
        Storage.set(STORAGE_KEYS.HIGHLIGHTS, highlights);
        
        // Remove highlight from current ayat
        const ayatElement = document.getElementById(`ayat-${ayatId}`);
        if (ayatElement) {
            ayatElement.className = ayatElement.className.replace(/highlight-\w+/g, '');
            const noteElement = ayatElement.querySelector('.highlight-note');
            if (noteElement) {
                noteElement.remove();
            }
        }
        
        showNotification('Highlight dihapus', 'info');
    }
    
    closeHighlightMenu();
}

function closeHighlightMenu() {
    if (window.currentHighlightMenu) {
        document.body.removeChild(window.currentHighlightMenu);
        window.currentHighlightMenu = null;
    }
}

function loadBookmarksAndHighlights(suratId) {
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    const suratHighlights = highlights[suratId] || {};
    
    // Apply highlights
    Object.keys(suratHighlights).forEach(ayatId => {
        const highlight = suratHighlights[ayatId];
        const ayatElement = document.getElementById(`ayat-${ayatId}`);
        if (ayatElement) {
            ayatElement.classList.add(`highlight-${highlight.color}`);
        }
    });
}

// 7. READING STATISTICS
function updateReadingStats() {
    const stats = Storage.get(STORAGE_KEYS.READING_STATS) || {
        totalReadingTime: 0,
        totalAyatRead: 0,
        totalSuratCompleted: 0,
        readingStreakDays: 0,
        lastReadingDate: null
    };
    
    // Update stats based on current session
    if (readingStartTime) {
        const sessionTime = Math.floor((new Date() - readingStartTime) / 1000);
        stats.totalReadingTime += sessionTime;
    }
    
    const today = new Date().toDateString();
    if (stats.lastReadingDate !== today) {
        if (stats.lastReadingDate === new Date(Date.now() - 86400000).toDateString()) {
            stats.readingStreakDays += 1;
        } else {
            stats.readingStreakDays = 1;
        }
        stats.lastReadingDate = today;
    }
    
    Storage.set(STORAGE_KEYS.READING_STATS, stats);
}

// 8. NOTIFICATION SYSTEM
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600',
        warning: 'bg-yellow-600'
    };
    
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Save reading session when page unloads
window.addEventListener('beforeunload', function() {
    updateReadingStats();
    if (currentSurat && currentHighlightedAyat) {
        const ayatId = parseInt(currentHighlightedAyat.id.replace('ayat-', ''));
        updateReadingSession(ayatId, window.scrollY);
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

/* Highlight colors */
.highlight-yellow {
    background-color: #fef3c7 !important;
    border-left: 4px solid #f59e0b;
}

.highlight-green {
    background-color: #d1fae5 !important;
    border-left: 4px solid #10b981;
}

.highlight-blue {
    background-color: #dbeafe !important;
    border-left: 4px solid #3b82f6;
}

.highlight-red {
    background-color: #fee2e2 !important;
    border-left: 4px solid #ef4444;
}

.highlight-purple {
    background-color: #e9d5ff !important;
    border-left: 4px solid #8b5cf6;
}

/* Bookmark and highlight buttons */
.bookmark-btn.text-blue-600 {
    color: #2563eb !important;
}

/* Reading progress animations */
#progressBar {
    transition: width 0.5s ease-in-out;
}

/* Notification animations */
.notification-enter {
    transform: translateX(100%);
}

.notification-enter-active {
    transform: translateX(0);
    transition: transform 0.3s ease-out;
}

.notification-exit {
    transform: translateX(0);
}

.notification-exit-active {
    transform: translateX(100%);
    transition: transform 0.3s ease-in;
}

/* Last read indicator */
.last-read-indicator {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Favorite surat indicator */
.favorite-indicator {
    color: #ef4444;
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0% {
        transform: scale(1);
    }
    14% {
        transform: scale(1.1);
    }
    28% {
        transform: scale(1);
    }
    42% {
        transform: scale(1.1);
    }
    70% {
        transform: scale(1);
    }
}

/* Quick access buttons hover effects */
.quick-access-btn {
    transition: all 0.2s ease-in-out;
}

.quick-access-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Highlight menu */
.highlight-menu {
    backdrop-filter: blur(4px);
}

/* Audio sync highlight */
.audio-sync-highlight {
    background: linear-gradient(90deg, #10b981, #34d399);
    color: white;
    animation: audioSync 0.5s ease-in-out;
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
    
    .highlight-yellow, .highlight-green, .highlight-blue, .highlight-red, .highlight-purple {
        background-color: #f3f4f6 !important;
        border-left: 2px solid #6b7280 !important;
    }
}
</style>

<?php include '../partials/footer.php'; ?>