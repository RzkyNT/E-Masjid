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
// Add SweetAlert2 to page
$additional_head = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
include '../partials/header.php';
?>
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
   
    <!-- Actions -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Daftar Jadwal Sholat Jumat</h2>
            <p class="text-gray-600 mt-1">Jadwal lengkap sholat Jumat dengan imam, khotib, dan tema khutbah</p>
        </div>
       
        <?php if ($is_admin && hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
        <button id="addEventBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-1"></i>Tambah Jadwal
        </button>
        <?php endif; ?>
    </div>
   
    <!-- Schedule List -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Filter Section -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-4">
                    <label for="statusFilter" class="text-sm font-medium text-gray-700">Filter Status:</label>
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua</option>
                        <option value="scheduled">Terjadwal</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div class="text-sm text-gray-600">
                    <span id="eventCount">0</span> jadwal ditemukan
                </div>
            </div>
        </div>
        <div id="scheduleList">
            <!-- Schedule list will be loaded here -->
        </div>
       
        <!-- Loading State -->
        <div id="listLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Memuat jadwal...</p>
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
<!-- Custom Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleList = document.getElementById('scheduleList');
    const listLoading = document.getElementById('listLoading');
    const eventModal = document.getElementById('eventModal');
    const modalTitle = document.getElementById('modalTitle');
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const eventDetails = document.getElementById('eventDetails');
    const messageContainer = document.getElementById('messageContainer');
    const statusFilter = document.getElementById('statusFilter');
    const eventCount = document.getElementById('eventCount');
   
    const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
   
    let allEvents = []; // Store all events for filtering
   
    // Initialize
    loadScheduleList();
   
    // Load schedule list
    function loadScheduleList() {
        listLoading.classList.remove('hidden');
        scheduleList.innerHTML = '';
       
        fetch('../api/friday_schedule_events.php')
            .then(response => response.json())
            .then(data => {
                listLoading.classList.add('hidden');
                if (data.success) {
                    allEvents = data.events;
                    renderScheduleList(allEvents, statusFilter.value);
                } else {
                    scheduleList.innerHTML = `
                        <div class="text-center py-12">
                            <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Jadwal</h3>
                            <p class="text-gray-600">Jadwal sholat Jumat akan segera diumumkan.</p>
                        </div>
                    `;
                    eventCount.textContent = '0';
                }
            })
            .catch(error => {
                listLoading.classList.add('hidden');
                console.error('Error loading schedules:', error);
                showMessage('Gagal memuat jadwal', 'error');
            });
    }
   
    // Render schedule list with filter
    function renderScheduleList(events, filter = '') {
        let filteredEvents = events;
        if (filter) {
            filteredEvents = events.filter(event => event.extendedProps.status === filter);
        }
        
        // Sort by date descending (newest first)
        filteredEvents.sort((a, b) => new Date(b.start) - new Date(a.start));
        
        eventCount.textContent = filteredEvents.length;
        
        if (filteredEvents.length === 0) {
            scheduleList.innerHTML = `
                <div class="text-center py-12 px-6">
                    <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Jadwal</h3>
                    <p class="text-gray-600">Tidak ada jadwal yang sesuai dengan filter yang dipilih.</p>
                    <button id="clearFilter" class="mt-4 text-blue-600 hover:text-blue-800 text-sm underline">Hapus Filter</button>
                </div>
            `;
            document.getElementById('clearFilter')?.addEventListener('click', () => {
                statusFilter.value = '';
                renderScheduleList(allEvents);
            });
            return;
        }
        
        const listHtml = filteredEvents.map((event, index) => {
            const props = event.extendedProps;
            const date = new Date(event.start);
            const isToday = props.schedule_status === 'today';
            const statusClass = getStatusClass(props.status);
            const statusLabel = getStatusLabel(props.status);
           
            return `
                <div class="group border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-0.5 ${isToday ? 'bg-blue-50 border-blue-200' : ''}" role="article" aria-labelledby="event-title-${index}">
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        <!-- Date Card -->
                        <div class="lg:col-span-1 text-center">
                            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-4 shadow-md transform group-hover:scale-105 transition-transform duration-200" aria-label="Tanggal: ${formatIndonesianDate(date)}">
                                    <div class="mt-3 text-center">
                                <div class="text-sm font-semibold text-gray-900 text-white" id="event-title-${index}">${formatIndonesianDay(date)}</div>
                                ${isToday ? '<div class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full mt-1 inline-block">Hari Ini</div>' : ''}
                            </div>    
                            <div class="text-3xl font-bold">${date.getDate()}</div>
                                <div class="text-sm uppercase tracking-wide">${formatIndonesianMonth(date)}</div>
                                <div class="text-xs">${date.getFullYear()}</div>
                            </div>
                        </div>
                       
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-4">
                            <!-- Header with Time and Status -->
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div class="flex items-center text-gray-600 text-sm">
                                    <i class="fas fa-clock mr-2 text-green-600"></i>
                                    <span class="font-medium">${props.prayer_time} WIB</span>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-map-marker-alt mr-1 text-green-600"></i>
                                    <span class="truncate">${props.location}</span>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${statusClass} flex-shrink-0" aria-label="Status: ${statusLabel}">
                                    ${statusLabel}
                                </span>
                            </div>
                           
                            <!-- Imam & Khotib -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Imam</div>
                                    <div class="font-semibold text-gray-900 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 mr-2"></i>
                                        ${props.imam_name}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Khotib</div>
                                    <div class="font-semibold text-gray-900 flex items-center justify-center">
                                        <i class="fas fa-microphone text-purple-600 mr-2"></i>
                                        ${props.khotib_name}
                                    </div>
                                </div>
                            </div>
                           
                            <!-- Theme -->
                            <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-green-500">
                                <div class="flex items-start">
                                    <i class="fas fa-bullhorn text-green-600 mt-0.5 mr-3 flex-shrink-0"></i>
                                    <div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tema Khutbah</div>
                                        <h4 class="font-semibold text-gray-900">${props.khutbah_theme}</h4>
                                        ${props.khutbah_description ? `<p class="text-sm text-gray-600 mt-1">${props.khutbah_description}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                           
                            <!-- Special Notes -->
                            ${props.special_notes ? `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-yellow-400 mt-0.5 mr-3 flex-shrink-0"></i>
                                    <div>
                                        <div class="text-sm font-medium text-yellow-800 mb-1">Catatan Khusus</div>
                                        <p class="text-sm text-yellow-700">${props.special_notes}</p>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                            ${isAdmin ? `
                            <button class="edit-event-btn w-full lg:w-auto bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200" data-event-id="${event.id}" aria-label="Edit jadwal">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
       
        scheduleList.innerHTML = listHtml;
       
        // Add click handlers
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = this.dataset.eventId;
                const event = allEvents.find(e => e.id == eventId);
                if (event) {
                    showEventDetails(event);
                }
            });
        });
       
        document.querySelectorAll('.edit-event-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const eventId = this.dataset.eventId;
                const event = allEvents.find(e => e.id == eventId);
                if (event) {
                    editEvent(event);
                }
            });
        });
    }
   
    // Filter event handler
    statusFilter.addEventListener('change', function() {
        renderScheduleList(allEvents, this.value);
    });
   
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
   
    function editEvent(event) {
        eventModal.dataset.eventId = event.id;
        eventModal.dataset.eventData = JSON.stringify(event);
        modalTitle.textContent = 'Edit Jadwal Jumat';
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
       
        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
       
        fetch('../api/friday_schedule_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close(); // Close loading
            if (data.success) {
                showMessage(data.message, 'success');
                closeModal();
                loadScheduleList();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            Swal.close(); // Close loading
            console.error('Error saving event:', error);
            showMessage('Terjadi kesalahan saat menyimpan jadwal', 'error');
        });
    }
   
    function deleteEvent() {
        Swal.fire({
            title: 'Hapus Jadwal?',
            text: 'Jadwal yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
               
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
                    Swal.close(); // Close loading
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeModal();
                        loadScheduleList();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.close(); // Close loading
                    console.error('Error deleting event:', error);
                    showMessage('Terjadi kesalahan saat menghapus jadwal', 'error');
                });
            }
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
   
    // Utility functions
    function showMessage(message, type) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type === 'success' ? 'success' : 'error',
            title: message,
            showConfirmButton: false,
            timer: type === 'success' ? 3000 : 4000,
            timerProgressBar: true
        });
    }
   
    function formatIndonesianDay(date) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return days[date.getDay()];
    }
   
    function formatIndonesianDate(date) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }
   
    function formatIndonesianMonth(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return months[date.getMonth()];
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