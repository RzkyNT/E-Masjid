<?php
// Add cache-busting headers for development
require_once '../includes/settings_loader.php';

require_once '../config/config.php';

$page_title = 'Jadwal Sholat Jumat';
$page_description = 'Jadwal sholat Jumat, imam, khotib, dan tema khutbah di Masjid Jami Al-Muhajirin';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Check if user is admin
$is_admin = false;
$current_user = null;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    require_once '../config/auth.php';
    $current_user = getCurrentUser();
    $is_admin = $current_user && hasPermission($current_user['role'], 'masjid_content', 'read');
}

// Breadcrumb
$breadcrumb = [
    ['title' => 'Jadwal Sholat Jumat', 'url' => '']
];

// No need for speakers and themes dropdown - data is entered directly
$speakers = [];
$themes = [];

include '../partials/header.php';
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<!-- Hero Section -->
<div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-calendar-alt text-4xl"></i>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Jadwal Sholat Jumat</h1>
            <p class="text-xl text-green-100 max-w-3xl mx-auto">
                Jadwal lengkap sholat Jumat dengan imam, khotib, dan tema khutbah di <?php echo htmlspecialchars($settings['site_name']); ?>
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Messages -->
    <div id="messageContainer"></div>
    
    <!-- View Toggle & Actions -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-wrap gap-2">
            <button id="cardView" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                <i class="fas fa-th-large mr-1"></i>Tampilan Card
            </button>
            <button id="calendarView" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                <i class="fas fa-calendar mr-1"></i>Tampilan Kalender
            </button>
            <?php if ($is_admin && hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                <button id="addEventBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                <span>Terjadwal</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span>Hari Ini</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                <span>Selesai</span>
            </div>
        </div>
    </div>
    
    <!-- Card View Container -->
    <div id="cardViewContainer" class="block">
        <div id="scheduleCards" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cards will be loaded here -->
        </div>
        
        <!-- Loading State -->
        <div id="cardLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Memuat jadwal...</p>
        </div>
    </div>
    
    <!-- Calendar View Container -->
    <div id="calendarViewContainer" class="hidden">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
    </div>
    
    <!-- Information Section -->
    <div class="mt-12 bg-gray-50 rounded-lg p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Informasi Sholat Jumat</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Waktu Pelaksanaan</h4>
                <p class="text-gray-600 text-sm">
                    Sholat Jumat dilaksanakan setiap hari Jumat pukul 12:00 WIB atau sesuai jadwal yang tertera
                </p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Jamaah</h4>
                <p class="text-gray-600 text-sm">
                    Terbuka untuk seluruh umat Muslim. Diharapkan hadir 15 menit sebelum waktu sholat
                </p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-microphone text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Khutbah</h4>
                <p class="text-gray-600 text-sm">
                    Khutbah disampaikan dalam bahasa Indonesia dengan tema yang bervariasi setiap minggunya
                </p>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900 mb-3">Lokasi</h4>
                <div class="flex items-center justify-center text-gray-600">
                    <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                    <span><?php echo htmlspecialchars($settings['site_name']); ?></span>
                </div>
                <?php 
                $contact_info = getContactInfo();
                if (!empty($contact_info['address'])): 
                ?>
                <p class="text-gray-600 text-sm mt-2">
                    <?php echo htmlspecialchars($contact_info['address']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Detail Jadwal Jumat</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Content -->
            <div class="py-4">
                <!-- View Mode -->
                <div id="viewMode">
                    <div id="eventDetails">
                        <!-- Event details will be loaded here -->
                    </div>
                    <?php if ($is_admin): ?>
                    <div class="flex justify-between pt-4 border-t mt-6">
                        <div>
                            <?php if (hasPermission($current_user['role'], 'masjid_content', 'delete')): ?>
                            <button id="deleteEventBtn" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700 transition duration-200">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="flex space-x-3">
                            <button id="closeViewBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                                Tutup
                            </button>
                            <?php if (hasPermission($current_user['role'], 'masjid_content', 'update')): ?>
                            <button id="editEventBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button id="closeViewBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                            Tutup
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Edit Mode (Admin Only) -->
                <?php if ($is_admin): ?>
                <div id="editMode" class="hidden">
                    <form id="eventForm" class="space-y-6">
                        <input type="hidden" id="eventId" name="event_id">
                        <input type="hidden" id="formAction" name="action" value="add">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Friday Date -->
                            <div>
                                <label for="friday_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Jumat <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="friday_date" 
                                       name="friday_date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required>
                            </div>
                            
                            <!-- Prayer Time -->
                            <div>
                                <label for="prayer_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Sholat <span class="text-red-500">*</span>
                                </label>
                                <input type="time" 
                                       id="prayer_time" 
                                       name="prayer_time" 
                                       value="12:00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imam -->
                            <div>
                                <label for="imam_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Imam <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="imam_name" 
                                       name="imam_name" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required>
                            </div>
                            
                            <!-- Khotib -->
                            <div>
                                <label for="khotib_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Khotib <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="khotib_name" 
                                       name="khotib_name" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       required>
                            </div>
                        </div>
                        
                        <!-- Khutbah Theme -->
                        <div>
                            <label for="khutbah_theme" class="block text-sm font-medium text-gray-700 mb-2">
                                Tema Khutbah <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="khutbah_theme" 
                                   name="khutbah_theme" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   required>
                        </div>
                        
                        <!-- Khutbah Description -->
                        <div>
                            <label for="khutbah_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Khutbah
                            </label>
                            <textarea id="khutbah_description" 
                                      name="khutbah_description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Deskripsi singkat tentang isi khutbah..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi
                                </label>
                                <input type="text" 
                                       id="location" 
                                       name="location" 
                                       value="Masjid Jami Al-Muhajirin"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            
                            <!-- Status -->
                            <div id="statusField" class="hidden">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select id="status" 
                                        name="status" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="scheduled">Terjadwal</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Special Notes -->
                        <div>
                            <label for="special_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Khusus
                            </label>
                            <textarea id="special_notes" 
                                      name="special_notes" 
                                      rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Catatan khusus untuk jamaah (opsional)..."></textarea>
                        </div>
                    </form>
                    
                    <div class="flex justify-end space-x-3 pt-4 border-t mt-6">
                        <button id="cancelEditBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                            Batal
                        </button>
                        <button id="saveEventBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                            <i class="fas fa-save mr-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<!-- Custom Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cardViewBtn = document.getElementById('cardView');
    const calendarViewBtn = document.getElementById('calendarView');
    const cardViewContainer = document.getElementById('cardViewContainer');
    const calendarViewContainer = document.getElementById('calendarViewContainer');
    const scheduleCards = document.getElementById('scheduleCards');
    const cardLoading = document.getElementById('cardLoading');
    const eventModal = document.getElementById('eventModal');
    const modalTitle = document.getElementById('modalTitle');
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const eventDetails = document.getElementById('eventDetails');
    const messageContainer = document.getElementById('messageContainer');
    
    const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
    let calendar = null;
    let currentView = 'card';
    
    // Initialize
    loadCardView();
    
    // View toggle
    cardViewBtn.addEventListener('click', function() {
        switchToCardView();
    });
    
    calendarViewBtn.addEventListener('click', function() {
        switchToCalendarView();
    });
    
    function switchToCardView() {
        currentView = 'card';
        cardViewContainer.classList.remove('hidden');
        calendarViewContainer.classList.add('hidden');
        
        cardViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
        cardViewBtn.classList.add('bg-green-600', 'text-white');
        calendarViewBtn.classList.remove('bg-green-600', 'text-white');
        calendarViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        
        loadCardView();
    }
    
    function switchToCalendarView() {
        currentView = 'calendar';
        cardViewContainer.classList.add('hidden');
        calendarViewContainer.classList.remove('hidden');
        
        calendarViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
        calendarViewBtn.classList.add('bg-green-600', 'text-white');
        cardViewBtn.classList.remove('bg-green-600', 'text-white');
        cardViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        
        if (!calendar) {
            initializeCalendar();
        } else {
            calendar.refetchEvents();
        }
    }
    
    // Load card view
    function loadCardView() {
        cardLoading.classList.remove('hidden');
        scheduleCards.innerHTML = '';
        
        fetch('../api/friday_schedule_events.php')
            .then(response => response.json())
            .then(data => {
                cardLoading.classList.add('hidden');
                if (data.success && data.events.length > 0) {
                    renderCards(data.events);
                } else {
                    scheduleCards.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Jadwal</h3>
                            <p class="text-gray-600">Jadwal sholat Jumat akan segera diumumkan.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                cardLoading.classList.add('hidden');
                console.error('Error loading schedules:', error);
                showMessage('Gagal memuat jadwal', 'error');
            });
    }
    
    // Render cards
    function renderCards(events) {
        const cardsHtml = events.map(event => {
            const props = event.extendedProps;
            const date = new Date(event.start);
            const isToday = props.schedule_status === 'today';
            
            return `
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200 ${isToday ? 'ring-2 ring-blue-500' : ''}" data-event-id="${event.id}">
                    <div class="p-6">
                        <!-- Date Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-green-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-calendar text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        ${formatIndonesianDay(date)}
                                    </h3>
                                    <p class="text-gray-600">
                                        ${formatIndonesianDate(date)}
                                    </p>
                                </div>
                            </div>
                            ${isToday ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Hari Ini</span>' : ''}
                        </div>
                        
                        <!-- Prayer Time -->
                        <div class="mb-4">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-clock mr-2 text-green-600"></i>
                                <span class="font-medium">Waktu Sholat:</span>
                                <span class="ml-2 text-lg font-semibold">${props.prayer_time} WIB</span>
                            </div>
                        </div>
                        
                        <!-- Imam and Khotib -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Imam</p>
                                <p class="text-gray-900 font-medium">${props.imam_name}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Khotib</p>
                                <p class="text-gray-900 font-medium">${props.khotib_name}</p>
                            </div>
                        </div>
                        
                        <!-- Khutbah Theme -->
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-2">Tema Khutbah</p>
                            <h4 class="text-gray-900 font-semibold mb-2">${props.khutbah_theme}</h4>
                            ${props.khutbah_description ? `<p class="text-gray-600 text-sm">${props.khutbah_description}</p>` : ''}
                        </div>
                        
                        <!-- Special Notes -->
                        ${props.special_notes ? `
                        <div class="mb-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">${props.special_notes}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        
                        <!-- Location and Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                                <span class="text-sm">${props.location}</span>
                            </div>
                            <button class="view-details-btn bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition duration-200" data-event-id="${event.id}">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        scheduleCards.innerHTML = cardsHtml;
        
        // Add click handlers
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = this.dataset.eventId;
                const event = events.find(e => e.id == eventId);
                if (event) {
                    showEventDetails(event);
                }
            });
        });
    }
    
    // Initialize calendar
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            locale: 'id',
            firstDay: 1,
            height: 'auto',
            editable: isAdmin,
            selectable: isAdmin,
            selectMirror: true,
            events: '../api/friday_schedule_events.php',
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            select: function(info) {
                if (isAdmin && info.start.getDay() === 5) {
                    addEvent(info.start);
                } else if (isAdmin) {
                    showMessage('Jadwal hanya bisa ditambahkan pada hari Jumat!', 'error');
                }
                calendar.unselect();
            },
            eventDrop: function(info) {
                if (isAdmin) {
                    if (info.event.start.getDay() !== 5) {
                        info.revert();
                        showMessage('Jadwal hanya bisa dipindahkan ke hari Jumat!', 'error');
                        return;
                    }
                    updateEventDate(info.event.id, info.event.start);
                }
            },
            dayCellDidMount: function(info) {
                if (info.date.getDay() === 5) {
                    info.el.style.backgroundColor = '#f0fdf4';
                }
            }
        });
        
        calendar.render();
    }
    
    // Event handlers
    <?php if ($is_admin): ?>
    document.getElementById('addEventBtn').addEventListener('click', function() {
        addEvent();
    });
    
    document.getElementById('editEventBtn').addEventListener('click', function() {
        switchToEditMode();
    });
    
    document.getElementById('cancelEditBtn').addEventListener('click', function() {
        switchToViewMode();
    });
    
    document.getElementById('saveEventBtn').addEventListener('click', saveEvent);
    document.getElementById('deleteEventBtn').addEventListener('click', deleteEvent);
    <?php endif; ?>
    
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('closeViewBtn').addEventListener('click', closeModal);
    
    // Modal functions
    function showEventDetails(event) {
        const props = event.extendedProps;
        const date = new Date(event.start);
        
        modalTitle.textContent = `Sholat Jumat - ${formatIndonesianDate(date)}`;
        
        eventDetails.innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Waktu Sholat</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-clock mr-2 text-green-600"></i>
                            <span class="text-lg font-medium">${props.prayer_time} WIB</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(props.status)}">
                            ${getStatusLabel(props.status)}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Imam</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            <span>${props.imam_name}</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Khotib</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-microphone mr-2 text-purple-600"></i>
                            <span>${props.khotib_name}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Tema Khutbah</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-medium text-gray-900 mb-2">${props.khutbah_theme}</h5>
                        ${props.khutbah_description ? `<p class="text-gray-600 text-sm">${props.khutbah_description}</p>` : ''}
                    </div>
                </div>
                
                ${props.special_notes ? `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Catatan Khusus</h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">${props.special_notes}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Lokasi</h4>
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        <span>${props.location}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Store current event data
        eventModal.dataset.eventId = event.id;
        eventModal.dataset.eventData = JSON.stringify(event);
        
        switchToViewMode();
        eventModal.classList.remove('hidden');
    }
    
    <?php if ($is_admin): ?>
    function addEvent(date = null) {
        resetForm();
        modalTitle.textContent = 'Tambah Jadwal Jumat';
        document.getElementById('formAction').value = 'add';
        document.getElementById('statusField').classList.add('hidden');
        
        if (date) {
            document.getElementById('friday_date').value = formatDateForInput(date);
        } else {
            const nextFriday = getNextFriday();
            document.getElementById('friday_date').value = formatDateForInput(nextFriday);
        }
        
        switchToEditMode();
        eventModal.classList.remove('hidden');
    }
    
    function switchToEditMode() {
        viewMode.classList.add('hidden');
        editMode.classList.remove('hidden');
        
        // If editing existing event, populate form
        if (eventModal.dataset.eventData) {
            const event = JSON.parse(eventModal.dataset.eventData);
            const props = event.extendedProps;
            
            document.getElementById('formAction').value = 'edit';
            document.getElementById('statusField').classList.remove('hidden');
            document.getElementById('eventId').value = event.id;
            document.getElementById('friday_date').value = formatDateForInput(new Date(event.start));
            document.getElementById('prayer_time').value = props.prayer_time;
            document.getElementById('imam_name').value = props.imam_name;
            document.getElementById('khotib_name').value = props.khotib_name;
            document.getElementById('khutbah_theme').value = props.khutbah_theme;
            document.getElementById('khutbah_description').value = props.khutbah_description || '';
            document.getElementById('location').value = props.location;
            document.getElementById('special_notes').value = props.special_notes || '';
            document.getElementById('status').value = props.status;
        }
    }
    
    function switchToViewMode() {
        editMode.classList.add('hidden');
        viewMode.classList.remove('hidden');
    }
    
    function saveEvent() {
        const formData = new FormData(document.getElementById('eventForm'));
        const action = formData.get('action');
        
        if (!validateForm()) {
            return;
        }
        
        fetch('../api/friday_schedule_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closeModal();
                refreshCurrentView();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error saving event:', error);
            showMessage('Terjadi kesalahan saat menyimpan jadwal', 'error');
        });
    }
    
    function deleteEvent() {
        if (!confirm('Yakin ingin menghapus jadwal ini?')) {
            return;
        }
        
        const eventId = eventModal.dataset.eventId;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('event_id', eventId);
        
        fetch('../api/friday_schedule_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closeModal();
                refreshCurrentView();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting event:', error);
            showMessage('Terjadi kesalahan saat menghapus jadwal', 'error');
        });
    }
    
    function updateEventDate(eventId, newDate) {
        const formData = new FormData();
        formData.append('action', 'update_date');
        formData.append('event_id', eventId);
        formData.append('new_date', formatDateForInput(newDate));
        
        fetch('../api/friday_schedule_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Tanggal jadwal berhasil diperbarui', 'success');
            } else {
                showMessage(data.message, 'error');
                if (calendar) calendar.refetchEvents();
            }
        })
        .catch(error => {
            console.error('Error updating event date:', error);
            showMessage('Terjadi kesalahan saat memperbarui tanggal', 'error');
            if (calendar) calendar.refetchEvents();
        });
    }
    
    function validateForm() {
        const requiredFields = ['friday_date', 'prayer_time', 'imam_name', 'khotib_name', 'khutbah_theme'];
        
        for (const field of requiredFields) {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                showMessage(`Field ${element.previousElementSibling.textContent} harus diisi`, 'error');
                element.focus();
                return false;
            }
        }
        
        const selectedDate = new Date(document.getElementById('friday_date').value);
        if (selectedDate.getDay() !== 5) {
            showMessage('Tanggal yang dipilih harus hari Jumat!', 'error');
            return false;
        }
        
        return true;
    }
    
    function resetForm() {
        document.getElementById('eventForm').reset();
        document.getElementById('prayer_time').value = '12:00';
        document.getElementById('location').value = 'Masjid Jami Al-Muhajirin';
        eventModal.dataset.eventId = '';
        eventModal.dataset.eventData = '';
    }
    <?php endif; ?>
    
    function closeModal() {
        eventModal.classList.add('hidden');
        <?php if ($is_admin): ?>
        resetForm();
        <?php endif; ?>
    }
    
    function refreshCurrentView() {
        if (currentView === 'card') {
            loadCardView();
        } else if (calendar) {
            calendar.refetchEvents();
        }
    }
    
    // Utility functions
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        messageContainer.innerHTML = `
            <div class="${alertClass} border px-4 py-3 rounded mb-4">
                <i class="${iconClass} mr-2"></i>${message}
            </div>
        `;
        
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }
    
    function formatIndonesianDay(date) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return days[date.getDay()];
    }
    
    function formatIndonesianDate(date) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }
    
    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }
    
    function getNextFriday() {
        const today = new Date();
        const dayOfWeek = today.getDay();
        const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
        const nextFriday = new Date(today);
        nextFriday.setDate(today.getDate() + (daysUntilFriday === 0 ? 7 : daysUntilFriday));
        return nextFriday;
    }
    
    function getStatusClass(status) {
        const classes = {
            'scheduled': 'bg-green-100 text-green-800',
            'completed': 'bg-gray-100 text-gray-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }
    
    function getStatusLabel(status) {
        const labels = {
            'scheduled': 'Terjadwal',
            'completed': 'Selesai',
            'cancelled': 'Dibatalkan'
        };
        return labels[status] || 'Tidak Diketahui';
    }
    
    // Close modal when clicking outside
    eventModal.addEventListener('click', function(e) {
        if (e.target === eventModal) {
            closeModal();
        }
    });
    
    // Validate Friday date input
    <?php if ($is_admin): ?>
    document.getElementById('friday_date').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        if (selectedDate.getDay() !== 5) {
            showMessage('Tanggal yang dipilih harus hari Jumat!', 'error');
            this.focus();
        }
    });
    <?php endif; ?>
});
</script>

<?php include '../partials/footer.php'; ?>