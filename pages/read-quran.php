<?php
$surat_id = $_GET['surat'] ?? 1;
$ayat_id = $_GET['ayat'] ?? 1;

$page_title = "Baca Al-Quran - Surat " . $surat_id;
$page_description = "Baca Al-Quran dengan tampilan full screen dan audio berkualitas tinggi";
$base_url = '..';

// Include header
include '../partials/header.php';
?>

<!-- Additional CSS for Arabic Font -->
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <!-- Header Navigation -->
    <div class="bg-white shadow-lg top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Back Button -->
                <!-- <div class="flex items-center space-x-4">
                    <a href="alquranv2.php" class="text-gray-600 hover:text-green-600 transition duration-200">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 id="suratTitle" class="text-xl font-bold text-gray-800">Loading...</h1>
                        <p id="suratInfo" class="text-sm text-gray-600">Loading...</p>
                    </div>
                </div> -->
                 <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-microphone"></i>
                        <span class="text-sm">Qari: Misyari Rasyid Al-Afasy</span>
                    </div>
                    <div id="audioStatus" class="text-sm bg-white/20 px-2 py-1 rounded">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Loading...
                    </div>
                </div>
                <!-- Quick Actions -->
                <div class="flex items-center space-x-2">
                    <button onclick="toggleFavoriteSurat()" id="favoriteSuratBtn" class="bg-gray-600 text-white px-3 py-2 rounded-lg hover:bg-gray-700 transition duration-200 text-sm">
                        <i class="fas fa-heart mr-1"></i>Favorit
                    </button>
                    <button onclick="showTafsir()" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm">
                        <i class="fas fa-book mr-1"></i>Tafsir
                    </button>
                    <button onclick="showSettings()" class="bg-gray-600 text-white px-3 py-2 rounded-lg hover:bg-gray-700 transition duration-200 text-sm">
                        <i class="fas fa-cog mr-1"></i>Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio Controls Bar -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white sticky top-16 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <a href="alquranv2.php" class="text-white hover:text-green-600 transition duration-200">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 id="suratTitle" class="text-xl font-bold text-white">Loading...</h1>
                        <p id="suratInfo" class="text-sm text-white">Loading...</p>
                    </div>
                </div>
                <!-- Audio Info -->
                <!-- <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-microphone"></i>
                        <span class="text-sm">Qari: Misyari Rasyid Al-Afasy</span>
                    </div>
                    <div id="audioStatus" class="text-sm bg-white/20 px-2 py-1 rounded">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Loading...
                    </div>
                </div> -->
                
                <!-- Audio Controls -->
                <div class="flex items-center space-x-2">
                    <button id="prevAyatBtn" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded transition duration-200" disabled>
                        <i class="fas fa-step-backward"></i>
                    </button>
                    <button id="playPauseBtn" class="bg-white text-green-600 hover:bg-gray-100 px-4 py-2 rounded font-semibold transition duration-200" disabled>
                        <i class="fas fa-play mr-2"></i>Putar
                    </button>
                    <button id="nextAyatBtn" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded transition duration-200" disabled>
                        <i class="fas fa-step-forward"></i>
                    </button>
                    <!-- <button id="playAllBtn" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded transition duration-200">
                        <i class="fas fa-list-ul mr-1"></i>Putar Semua
                    </button> -->
                </div>
                
                <!-- Progress -->
                <div class="flex items-center space-x-2 min-w-0 flex-1 sm:flex-initial sm:min-w-48">
                    <span class="text-sm whitespace-nowrap">Progress:</span>
                    <div class="flex-1 bg-white/20 rounded-full h-2">
                        <div id="readingProgressBar" class="bg-white h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <span id="progressText" class="text-sm whitespace-nowrap">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            <p class="mt-4 text-gray-600">Memuat surat...</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
            <h3 class="text-lg font-semibold text-red-800 mb-2">Gagal Memuat Data</h3>
            <p class="text-red-600 mb-4">Terjadi kesalahan saat mengambil data dari server.</p>
            <button onclick="loadSurat()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                <i class="fas fa-redo mr-2"></i>Coba Lagi
            </button>
        </div>

        <!-- Surat Content -->
        <div id="suratContent" class="hidden">
            <!-- Bismillah -->
            <div id="bismillahSection" class="text-center py-8 mb-8 border-b border-gray-200 hidden">
                <div class="text-4xl font-arabic text-green-700 mb-3">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                <div class="text-gray-600">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang</div>
            </div>

            <!-- Ayat List -->
            <div id="ayatList" class="space-y-6">
                <!-- Ayat will be populated here -->
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="bg-white border-t sticky bottom-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <button id="prevSuratBtn" class="flex items-center space-x-2 text-gray-600 hover:text-green-600 transition duration-200" disabled>
                    <i class="fas fa-chevron-left"></i>
                    <span class="hidden sm:inline">Surat Sebelumnya</span>
                </button>
                
                <div class="flex items-center space-x-4">
                    <button onclick="scrollToTop()" class="text-gray-600 hover:text-green-600 transition duration-200">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <span id="currentAyatInfo" class="text-sm text-gray-600">Ayat 1</span>
                    <button onclick="scrollToBottom()" class="text-gray-600 hover:text-green-600 transition duration-200">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>
                
                <button id="nextSuratBtn" class="flex items-center space-x-2 text-gray-600 hover:text-green-600 transition duration-200" disabled>
                    <span class="hidden sm:inline">Surat Selanjutnya</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Audio Elements for Preloading -->
<audio id="currentAudio" preload="metadata"></audio>
<audio id="nextAudio" preload="none"></audio>

<!-- Settings Modal -->
<div id="settingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Pengaturan Bacaan</h3>
                    <button onclick="closeSettings()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Font Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Font Arab</label>
                        <input type="range" id="arabicFontSize" min="16" max="48" value="32" class="w-full">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Kecil</span>
                            <span>Besar</span>
                        </div>
                    </div>
                    
                    <!-- Translation Font Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Font Terjemahan</label>
                        <input type="range" id="translationFontSize" min="12" max="24" value="16" class="w-full">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Kecil</span>
                            <span>Besar</span>
                        </div>
                    </div>
                    
                    <!-- Auto Scroll -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Auto Scroll saat Audio</label>
                        <input type="checkbox" id="autoScroll" checked class="rounded">
                    </div>
                    
                    <!-- Auto Play Next -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Auto Play Ayat Berikutnya</label>
                        <input type="checkbox" id="autoPlayNext" checked class="rounded">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <button onclick="resetSettings()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Reset</button>
                    <button onclick="closeSettings()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSurat = null;
let currentAyatIndex = 0;
let isPlayingAll = false;
let audioPreloadQueue = [];
let settings = {
    arabicFontSize: 32,
    translationFontSize: 16,
    autoScroll: true,
    autoPlayNext: true
};

// LocalStorage keys
const STORAGE_KEYS = {
    LAST_READ: 'alquran_last_read',
    BOOKMARKS: 'alquran_bookmarks',
    FAVORITES: 'alquran_favorites',
    READING_PROGRESS: 'alquran_reading_progress',
    HIGHLIGHTS: 'alquran_highlights',
    READING_STATS: 'alquran_reading_stats',
    SETTINGS: 'alquran_settings'
};

// Storage helper
const Storage = {
    get: (key) => {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            return null;
        }
    },
    set: (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            return false;
        }
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    setupEventListeners();
    loadSurat();
});

// Load settings
function loadSettings() {
    const savedSettings = Storage.get(STORAGE_KEYS.SETTINGS);
    if (savedSettings) {
        settings = { ...settings, ...savedSettings };
    }
    applySettings();
}

// Apply settings
function applySettings() {
    document.documentElement.style.setProperty('--arabic-font-size', settings.arabicFontSize + 'px');
    document.documentElement.style.setProperty('--translation-font-size', settings.translationFontSize + 'px');
    
    // Update sliders
    document.getElementById('arabicFontSize').value = settings.arabicFontSize;
    document.getElementById('translationFontSize').value = settings.translationFontSize;
    document.getElementById('autoScroll').checked = settings.autoScroll;
    document.getElementById('autoPlayNext').checked = settings.autoPlayNext;
}

// Setup event listeners
function setupEventListeners() {
    // Audio controls
    document.getElementById('playPauseBtn').addEventListener('click', togglePlayPause);
    document.getElementById('prevAyatBtn').addEventListener('click', playPreviousAyat);
    document.getElementById('nextAyatBtn').addEventListener('click', playNextAyat);
    // document.getElementById('playAllBtn').addEventListener('click', playAllAyat);
    
    // Navigation
    document.getElementById('prevSuratBtn').addEventListener('click', goToPreviousSurat);
    document.getElementById('nextSuratBtn').addEventListener('click', goToNextSurat);
    
    // Settings
    document.getElementById('arabicFontSize').addEventListener('input', updateArabicFontSize);
    document.getElementById('translationFontSize').addEventListener('input', updateTranslationFontSize);
    document.getElementById('autoScroll').addEventListener('change', updateAutoScroll);
    document.getElementById('autoPlayNext').addEventListener('change', updateAutoPlayNext);
    
    // Audio events
    const currentAudio = document.getElementById('currentAudio');
    currentAudio.addEventListener('loadstart', onAudioLoadStart);
    currentAudio.addEventListener('canplay', onAudioCanPlay);
    currentAudio.addEventListener('play', onAudioPlay);
    currentAudio.addEventListener('pause', onAudioPause);
    currentAudio.addEventListener('ended', onAudioEnded);
    currentAudio.addEventListener('error', onAudioError);
    currentAudio.addEventListener('timeupdate', onAudioTimeUpdate);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', handleKeyboardShortcuts);
    
    // Scroll tracking
    window.addEventListener('scroll', trackScrollPosition);
}

// Load surat
async function loadSurat() {
    const urlParams = new URLSearchParams(window.location.search);
    const suratId = parseInt(urlParams.get('surat')) || 1;
    const ayatId = parseInt(urlParams.get('ayat')) || 1;
    
    try {
        document.getElementById('loadingState').classList.remove('hidden');
        document.getElementById('errorState').classList.add('hidden');
        document.getElementById('suratContent').classList.add('hidden');
        
        const response = await fetch(`../api/equran_v2.php?action=surat_detail&surat_id=${suratId}`);
        const result = await response.json();
        
        if (result.code === 200) {
            currentSurat = result.data;
            currentAyatIndex = ayatId - 1;
            
            displaySurat(currentSurat);
            updateNavigation();
            updateFavoriteButton();
            preloadAudio();
            
            // Save as last read
            saveLastRead(suratId, ayatId);
            
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('suratContent').classList.remove('hidden');
            
            // Scroll to specific ayat if requested
            if (ayatId > 1) {
                setTimeout(() => scrollToAyat(ayatId), 500);
            }
            
        } else {
            throw new Error(result.message || 'Failed to load surat');
        }
    } catch (error) {
        console.error('Error loading surat:', error);
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorState').classList.remove('hidden');
    }
}

// Display surat
function displaySurat(surat) {
    // Update header
    document.getElementById('suratTitle').textContent = `${surat.nomor}. ${surat.namaLatin}`;
    document.getElementById('suratInfo').textContent = `${surat.arti} • ${surat.tempatTurun} • ${surat.jumlahAyat} ayat`;
    
    // Show bismillah for all surat except At-Taubah (9) and Al-Fatihah (1)
    if (surat.nomor !== 9 && surat.nomor !== 1) {
        document.getElementById('bismillahSection').classList.remove('hidden');
    }
    
    // Display ayat
    const ayatContainer = document.getElementById('ayatList');
    ayatContainer.innerHTML = '';
    
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
        
        ayatDiv.className = `ayat-card p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 ${highlightClass}`;
        ayatDiv.id = `ayat-${ayat.nomorAyat}`;
        ayatDiv.dataset.ayatIndex = index;
        
        ayatDiv.innerHTML = `
            <div class="flex justify-between items-start mb-6">
                <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">
                    ${ayat.nomorAyat}
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="toggleHighlight(${surat.nomor}, ${ayat.nomorAyat})" class="text-gray-400 hover:text-yellow-500 transition duration-200">
                        <i class="fas fa-highlighter"></i>
                    </button>
                    <button onclick="toggleBookmark(${surat.nomor}, ${ayat.nomorAyat})" class="bookmark-btn ${isBookmarked ? 'text-blue-600' : 'text-gray-400'} hover:text-blue-700 transition duration-200">
                        <i class="fas fa-bookmark"></i>
                    </button>
                    <button onclick="playAyat(${index})" class="play-ayat-btn text-green-600 hover:text-green-700 transition duration-200" data-index="${index}">
                        <i class="fas fa-play text-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="text-right mb-6">
                <div class="arabic-text font-arabic leading-loose text-gray-800 mb-4">${ayat.teksArab}</div>
            </div>
            
            <div class="text-left">
                <div class="translation-text text-gray-700 leading-relaxed">${ayat.teksIndonesia}</div>
                ${highlight && highlight.note ? `<div class="mt-3 p-3 bg-yellow-100 rounded-lg text-sm italic">${highlight.note}</div>` : ''}
            </div>
        `;
        
        ayatContainer.appendChild(ayatDiv);
    });
    
    updateReadingProgress();
}

// Preload audio for better UX
function preloadAudio() {
    if (!currentSurat || !currentSurat.ayat) return;
    
    const currentAudio = document.getElementById('currentAudio');
    const nextAudio = document.getElementById('nextAudio');
    
    // Preload current ayat audio
    if (currentSurat.ayat[currentAyatIndex]) {
        const currentAyat = currentSurat.ayat[currentAyatIndex];
        if (currentAyat.audio && currentAyat.audio['05']) {
            currentAudio.src = currentAyat.audio['05'];
            currentAudio.load();
        }
    }
    
    // Preload next ayat audio
    if (currentSurat.ayat[currentAyatIndex + 1]) {
        const nextAyat = currentSurat.ayat[currentAyatIndex + 1];
        if (nextAyat.audio && nextAyat.audio['05']) {
            nextAudio.src = nextAyat.audio['05'];
            nextAudio.load();
        }
    }
    
    updateAudioControls();
}

// Audio event handlers
function onAudioLoadStart() {
    document.getElementById('audioStatus').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...';
}

function onAudioCanPlay() {
    document.getElementById('audioStatus').innerHTML = '<i class="fas fa-check-circle mr-1"></i>';
    document.getElementById('playPauseBtn').disabled = false;
}

function onAudioPlay() {
    document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-pause mr-2"></i>Pause';
    highlightCurrentAyat();
}

function onAudioPause() {
    document.getElementById('playPauseBtn').innerHTML = '<i class="fas fa-play mr-2"></i>Putar';
}

function onAudioEnded() {
    if (settings.autoPlayNext && (isPlayingAll || currentAyatIndex < currentSurat.ayat.length - 1)) {
        playNextAyat();
    } else {
        onAudioPause();
        isPlayingAll = false;
        
        // Reset all play buttons
        document.querySelectorAll('.play-ayat-btn').forEach(btn => {
            btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        });
    }
}

function onAudioError() {
    document.getElementById('audioStatus').innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Error';
    Swal.fire({
        icon: 'error',
        title: 'Gagal Memuat Audio',
        text: 'Tidak dapat memuat audio. Silakan coba lagi.',
        confirmButtonColor: '#059669'
    });
}

function onAudioTimeUpdate() {
    // Update progress if needed
}

// Audio control functions
function togglePlayPause() {
    const currentAudio = document.getElementById('currentAudio');
    
    if (currentAudio.paused) {
        currentAudio.play().catch(error => {
            console.error('Error playing audio:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memutar Audio',
                text: 'Tidak dapat memutar audio. Silakan coba lagi.',
                confirmButtonColor: '#059669'
            });
        });
    } else {
        currentAudio.pause();
    }
}

function playAyat(index) {
    currentAyatIndex = index;
    preloadAudio();
    
    const currentAudio = document.getElementById('currentAudio');
    const playBtn = document.querySelector(`[data-index="${index}"]`);
    
    // Show loading state
    if (playBtn) {
        playBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i>';
    }
    
    currentAudio.src = currentSurat.ayat[index].audio['05'];
    currentAudio.currentTime = 0;
    
    currentAudio.onloadstart = function() {
        if (playBtn) {
            playBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i>';
        }
    };
    
    currentAudio.oncanplay = function() {
        if (playBtn) {
            playBtn.innerHTML = '<i class="fas fa-pause text-lg"></i>';
        }
    };
    
    currentAudio.onerror = function() {
        if (playBtn) {
            playBtn.innerHTML = '<i class="fas fa-exclamation-triangle text-lg text-red-500"></i>';
        }
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memuat Audio',
            text: 'Tidak dapat memuat audio. Silakan coba lagi.',
            confirmButtonColor: '#059669'
        });
    };
    
    currentAudio.play().catch(error => {
        console.error('Error playing audio:', error);
        if (playBtn) {
            playBtn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        }
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memutar Audio',
            text: 'Tidak dapat memutar audio. Silakan coba lagi.',
            confirmButtonColor: '#059669'
        });
    });
    
    // Update other play buttons
    document.querySelectorAll('.play-ayat-btn').forEach(btn => {
        if (btn !== playBtn) {
            btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
        }
    });
    
    // Update last read
    saveLastRead(currentSurat.nomor, currentSurat.ayat[index].nomorAyat);
    updateReadingProgress();
}

function playNextAyat() {
    if (currentAyatIndex < currentSurat.ayat.length - 1) {
        currentAyatIndex++;
        playAyat(currentAyatIndex);
        updateAudioControls();
    }
}

function playPreviousAyat() {
    if (currentAyatIndex > 0) {
        currentAyatIndex--;
        playAyat(currentAyatIndex);
        updateAudioControls();
    }
}

function playAllAyat() {
    isPlayingAll = true;
    currentAyatIndex = 0;
    playAyat(0);
    
    // Update button text
    // document.getElementById('playAllBtn').innerHTML = '<i class="fas fa-stop mr-1"></i>Stop Semua';
    // document.getElementById('playAllBtn').onclick = stopAllAyat;
}

function stopAllAyat() {
    isPlayingAll = false;
    const currentAudio = document.getElementById('currentAudio');
    currentAudio.pause();
    
    // Reset button
    // document.getElementById('playAllBtn').innerHTML = '<i class="fas fa-list-ul mr-1"></i>Putar Semua';
    // document.getElementById('playAllBtn').onclick = playAllAyat;
    
    // Reset all play buttons
    document.querySelectorAll('.play-ayat-btn').forEach(btn => {
        btn.innerHTML = '<i class="fas fa-play text-lg"></i>';
    });
    
    // Remove highlights
    document.querySelectorAll('.ayat-card').forEach(card => {
        card.classList.remove('ring-2', 'ring-green-500', 'bg-green-50');
    });
}

// Highlight current ayat
function highlightCurrentAyat() {
    // Remove previous highlights
    document.querySelectorAll('.ayat-card').forEach(card => {
        card.classList.remove('ring-2', 'ring-green-500', 'bg-green-50');
    });
    
    // Highlight current ayat
    const currentAyatCard = document.getElementById(`ayat-${currentSurat.ayat[currentAyatIndex].nomorAyat}`);
    if (currentAyatCard) {
        currentAyatCard.classList.add('ring-2', 'ring-green-500', 'bg-green-50');
        
        // Auto scroll if enabled
        if (settings.autoScroll) {
            currentAyatCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    // Update current ayat info
    document.getElementById('currentAyatInfo').textContent = `Ayat ${currentSurat.ayat[currentAyatIndex].nomorAyat}`;
}

// Update audio controls
function updateAudioControls() {
    document.getElementById('prevAyatBtn').disabled = currentAyatIndex === 0;
    document.getElementById('nextAyatBtn').disabled = currentAyatIndex === currentSurat.ayat.length - 1;
}

// Navigation functions
function updateNavigation() {
    const prevBtn = document.getElementById('prevSuratBtn');
    const nextBtn = document.getElementById('nextSuratBtn');
    
    prevBtn.disabled = currentSurat.nomor === 1;
    nextBtn.disabled = currentSurat.nomor === 114;
    
    if (currentSurat.suratSebelumnya) {
        prevBtn.innerHTML = `<i class="fas fa-chevron-left"></i><span class="hidden sm:inline">${currentSurat.suratSebelumnya.namaLatin}</span>`;
    }
    
    if (currentSurat.suratSelanjutnya) {
        nextBtn.innerHTML = `<span class="hidden sm:inline">${currentSurat.suratSelanjutnya.namaLatin}</span><i class="fas fa-chevron-right"></i>`;
    }
}

function goToPreviousSurat() {
    if (currentSurat.nomor > 1) {
        window.location.href = `read-quran.php?surat=${currentSurat.nomor - 1}`;
    }
}

function goToNextSurat() {
    if (currentSurat.nomor < 114) {
        window.location.href = `read-quran.php?surat=${currentSurat.nomor + 1}`;
    }
}

// Utility functions
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function scrollToBottom() {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

function scrollToAyat(ayatNumber) {
    const ayatElement = document.getElementById(`ayat-${ayatNumber}`);
    if (ayatElement) {
        ayatElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Settings functions
function showSettings() {
    document.getElementById('settingsModal').classList.remove('hidden');
}

function closeSettings() {
    document.getElementById('settingsModal').classList.add('hidden');
    Storage.set(STORAGE_KEYS.SETTINGS, settings);
}

function updateArabicFontSize(e) {
    settings.arabicFontSize = parseInt(e.target.value);
    document.documentElement.style.setProperty('--arabic-font-size', settings.arabicFontSize + 'px');
}

function updateTranslationFontSize(e) {
    settings.translationFontSize = parseInt(e.target.value);
    document.documentElement.style.setProperty('--translation-font-size', settings.translationFontSize + 'px');
}

function updateAutoScroll(e) {
    settings.autoScroll = e.target.checked;
}

function updateAutoPlayNext(e) {
    settings.autoPlayNext = e.target.checked;
}

function resetSettings() {
    settings = {
        arabicFontSize: 32,
        translationFontSize: 16,
        autoScroll: true,
        autoPlayNext: true
    };
    applySettings();
}

// Feature functions (simplified versions)
function toggleFavoriteSurat() {
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const suratId = currentSurat.nomor;
    const index = favorites.indexOf(suratId);
    const favoriteBtn = document.getElementById('favoriteSuratBtn');
    
    if (index > -1) {
        favorites.splice(index, 1);
        favoriteBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-1"></i>Favorit';
    } else {
        favorites.push(suratId);
        favoriteBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-1"></i>Favorit ❤️';
    }
    
    Storage.set(STORAGE_KEYS.FAVORITES, favorites);
}

function updateFavoriteButton() {
    const favorites = Storage.get(STORAGE_KEYS.FAVORITES) || [];
    const favoriteBtn = document.getElementById('favoriteSuratBtn');
    
    if (favorites.includes(currentSurat.nomor)) {
        favoriteBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        favoriteBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        favoriteBtn.innerHTML = '<i class="fas fa-heart mr-1"></i>Favorit ❤️';
    }
}

function toggleBookmark(suratId, ayatId) {
    const bookmarks = Storage.get(STORAGE_KEYS.BOOKMARKS) || {};
    
    if (!bookmarks[suratId]) {
        bookmarks[suratId] = [];
    }
    
    const index = bookmarks[suratId].indexOf(ayatId);
    const bookmarkBtn = document.querySelector(`#ayat-${ayatId} .bookmark-btn`);
    
    if (index > -1) {
        bookmarks[suratId].splice(index, 1);
        if (bookmarks[suratId].length === 0) {
            delete bookmarks[suratId];
        }
        bookmarkBtn.classList.remove('text-blue-600');
        bookmarkBtn.classList.add('text-gray-400');
    } else {
        bookmarks[suratId].push(ayatId);
        bookmarkBtn.classList.remove('text-gray-400');
        bookmarkBtn.classList.add('text-blue-600');
    }
    
    Storage.set(STORAGE_KEYS.BOOKMARKS, bookmarks);
}

function toggleHighlight(suratId, ayatId) {
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    
    if (!highlights[suratId]) {
        highlights[suratId] = {};
    }
    
    if (highlights[suratId][ayatId]) {
        // Remove existing highlight
        Swal.fire({
            title: 'Hapus Highlight?',
            text: 'Apakah Anda yakin ingin menghapus highlight ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                delete highlights[suratId][ayatId];
                const ayatElement = document.getElementById(`ayat-${ayatId}`);
                ayatElement.className = ayatElement.className.replace(/highlight-\w+/g, '');
                
                // Remove note if exists
                const noteElement = ayatElement.querySelector('.highlight-note');
                if (noteElement) {
                    noteElement.remove();
                }
                
                Storage.set(STORAGE_KEYS.HIGHLIGHTS, highlights);
                
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Highlight dihapus',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    } else {
        // Add new highlight with color selection and note
        showHighlightColorSelection(suratId, ayatId);
    }
}

function showHighlightColorSelection(suratId, ayatId) {
    const colors = [
        { name: 'Kuning', value: 'yellow', class: 'bg-yellow-200' },
        { name: 'Hijau', value: 'green', class: 'bg-green-200' },
        { name: 'Biru', value: 'blue', class: 'bg-blue-200' },
        { name: 'Merah', value: 'red', class: 'bg-red-200' },
        { name: 'Ungu', value: 'purple', class: 'bg-purple-200' }
    ];
    
    Swal.fire({
        title: 'Pilih Warna Highlight',
        html: `
            <div class="grid grid-cols-2 gap-3 mb-4">
                ${colors.map(color => `
                    <button type="button" class="color-btn p-4 rounded-lg border-2 hover:border-gray-400 ${color.class} transition duration-200" data-color="${color.value}">
                        ${color.name}
                    </button>
                `).join('')}
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        cancelButtonColor: '#6b7280',
        didOpen: () => {
            // Add event listeners to color buttons
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const selectedColor = this.dataset.color;
                    Swal.close();
                    // Small delay to ensure the first modal is fully closed
                    setTimeout(() => {
                        showHighlightNoteInput(suratId, ayatId, selectedColor);
                    }, 100);
                });
            });
        }
    });
}

function showHighlightNoteInput(suratId, ayatId, color) {
    Swal.fire({
        title: 'Tambahkan Catatan',
        input: 'textarea',
        inputPlaceholder: 'Masukkan catatan untuk highlight ini (opsional)...',
        inputAttributes: {
            'aria-label': 'Catatan highlight',
            'rows': '4'
        },
        showCancelButton: true,
        confirmButtonText: 'Simpan Highlight',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        allowOutsideClick: false,
        allowEscapeKey: true,
        inputValidator: (value) => {
            // Note is optional, so no validation needed
            return null;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const note = result.value || '';
            saveHighlight(suratId, ayatId, color, note);
        }
    });
}

function saveHighlight(suratId, ayatId, color, note) {
    // Save highlight to localStorage
    const highlights = Storage.get(STORAGE_KEYS.HIGHLIGHTS) || {};
    if (!highlights[suratId]) {
        highlights[suratId] = {};
    }
    
    highlights[suratId][ayatId] = {
        color: color,
        note: note,
        timestamp: new Date().toISOString()
    };
    
    Storage.set(STORAGE_KEYS.HIGHLIGHTS, highlights);
    
    // Apply highlight visually
    const ayatElement = document.getElementById(`ayat-${ayatId}`);
    if (ayatElement) {
        // Remove existing highlight classes
        ayatElement.className = ayatElement.className.replace(/highlight-\w+/g, '');
        // Add new highlight class
        ayatElement.classList.add(`highlight-${color}`);
        
        // Add note if provided
        if (note && note.trim()) {
            // Remove existing note if any
            const existingNote = ayatElement.querySelector('.highlight-note');
            if (existingNote) {
                existingNote.remove();
            }
            
            // Add new note
            const noteDiv = document.createElement('div');
            noteDiv.className = 'mt-3 p-3 bg-yellow-100 rounded-lg text-sm italic highlight-note';
            noteDiv.textContent = note;
            
            const textLeftDiv = ayatElement.querySelector('.text-left');
            if (textLeftDiv) {
                textLeftDiv.appendChild(noteDiv);
            }
        }
    }
    
    // Show success message
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Highlight berhasil ditambahkan',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
}

function saveLastRead(suratId, ayatId) {
    const lastRead = {
        surat_id: suratId,
        ayat_id: ayatId,
        timestamp: new Date().toISOString(),
        surat_name: currentSurat ? currentSurat.namaLatin : `Surat ${suratId}`
    };
    Storage.set(STORAGE_KEYS.LAST_READ, lastRead);
}

function updateReadingProgress() {
    if (!currentSurat) return;
    
    const progress = Storage.get(STORAGE_KEYS.READING_PROGRESS) || {};
    const currentAyatNumber = currentSurat.ayat[currentAyatIndex].nomorAyat;
    
    progress[currentSurat.nomor] = {
        lastAyat: currentAyatNumber,
        totalAyat: currentSurat.jumlahAyat,
        timestamp: new Date().toISOString()
    };
    
    Storage.set(STORAGE_KEYS.READING_PROGRESS, progress);
    
    // Update progress bar
    const percentage = Math.round((currentAyatNumber / currentSurat.jumlahAyat) * 100);
    document.getElementById('readingProgressBar').style.width = `${percentage}%`;
    document.getElementById('progressText').textContent = `${percentage}%`;
}

function showTafsir() {
    // Redirect to tafsir or show in modal
    Swal.fire({
        title: 'Tafsir',
        text: 'Fitur tafsir akan segera tersedia.',
        icon: 'info',
        confirmButtonColor: '#059669'
    });
}

function trackScrollPosition() {
    // Track which ayat is currently visible
    const ayatCards = document.querySelectorAll('.ayat-card');
    const viewportHeight = window.innerHeight;
    const scrollTop = window.scrollY;
    
    ayatCards.forEach((card, index) => {
        const rect = card.getBoundingClientRect();
        if (rect.top >= 0 && rect.top <= viewportHeight / 2) {
            const ayatIndex = parseInt(card.dataset.ayatIndex);
            if (ayatIndex !== currentAyatIndex && !document.getElementById('currentAudio').paused === false) {
                // Update current ayat info without changing audio
                document.getElementById('currentAyatInfo').textContent = `Ayat ${currentSurat.ayat[ayatIndex].nomorAyat}`;
            }
        }
    });
}

function handleKeyboardShortcuts(e) {
    // Space: Play/Pause
    if (e.code === 'Space' && !e.target.matches('input, textarea')) {
        e.preventDefault();
        togglePlayPause();
    }
    
    // Arrow keys: Navigate ayat
    if (e.code === 'ArrowLeft') {
        e.preventDefault();
        playPreviousAyat();
    }
    
    if (e.code === 'ArrowRight') {
        e.preventDefault();
        playNextAyat();
    }
    
    // Escape: Stop all
    if (e.code === 'Escape') {
        document.getElementById('currentAudio').pause();
        isPlayingAll = false;
    }
}
</script>

<style>
:root {
    --arabic-font-size: 32px;
    --translation-font-size: 16px;
}

.font-arabic {
    font-family: 'Amiri', 'Times New Roman', serif;
    line-height: 2.2;
}

.arabic-text {
    font-size: var(--arabic-font-size);
    line-height: 2.2;
}

.translation-text {
    font-size: var(--translation-font-size);
    line-height: 1.6;
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

/* Smooth transitions */
.ayat-card {
    transition: all 0.3s ease;
}

.ayat-card:hover {
    transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .arabic-text {
        font-size: calc(var(--arabic-font-size) * 0.8);
    }
    
    .translation-text {
        font-size: calc(var(--translation-font-size) * 0.9);
    }
}

/* Print styles */
@media print {
    .sticky, .fixed, button {
        display: none !important;
    }
    
    .ayat-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #e5e7eb;
    }
}
.bottom-6 {
    bottom: 4.0rem;
    z-index: 100;
}
</style>

<?php include '../partials/footer.php'; ?>