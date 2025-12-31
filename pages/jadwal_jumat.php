<?php
// Add cache-busting headers for development
require_once '../includes/settings_loader.php';
require_once '../config/config.php';
$page_title = 'Jadwal Sholat Jumat';
$page_description = 'Jadwal sholat Jumat, imam, khotib, dan tema khutbah di Masjid Jami Al-Muhajirin';
$base_url = '..';
// Initialize website settings
$settings = initializePageSettings();
// This is a public view-only page
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
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button id="closeViewBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                            Tutup
                        </button>
                    </div>
                </div>
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
    const eventDetails = document.getElementById('eventDetails');
    const messageContainer = document.getElementById('messageContainer');
    const statusFilter = document.getElementById('statusFilter');
    const eventCount = document.getElementById('eventCount');
   
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
                <div class="border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-colors cursor-pointer ${isToday ? 'bg-blue-50' : ''}" 
                     onclick="showEventDetails(${JSON.stringify(event).replace(/"/g, '&quot;')})" 
                     onkeydown="if(event.key==='Enter'||event.key===' ') showEventDetails(${JSON.stringify(event).replace(/"/g, '&quot;')})"
                     role="button" 
                     tabindex="0"
                     aria-label="Lihat detail jadwal ${formatIndonesianDate(date)}">
                    
                    <div class="p-4 flex items-center justify-between">
                        <!-- Left: Date & Time -->
                        <div class="flex items-center space-x-4">
                            <div class="bg-green-600 text-white rounded-lg p-3 text-center min-w-[60px]">
                                <div class="text-lg font-bold">${date.getDate()}</div>
                                <div class="text-xs uppercase">${formatIndonesianMonth(date)}</div>
                            </div>
                            
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-semibold text-gray-900">${formatIndonesianDay(date)}</h3>
                                    ${isToday ? '<span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">Hari Ini</span>' : ''}
                                    <span class="px-2 py-1 rounded-full text-xs ${statusClass}">${statusLabel}</span>
                                </div>
                                <div class="text-sm text-gray-600 flex items-center space-x-3">
                                    <span><i class="fas fa-clock mr-1 text-green-600"></i>${props.prayer_time} WIB</span>
                                    <span><i class="fas fa-user mr-1 text-blue-600"></i>${props.imam_name}</span>
                                    <span><i class="fas fa-microphone mr-1 text-purple-600"></i>${props.khotib_name}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right: Theme & Actions -->
                        <div class="flex items-center space-x-4">
                            <div class="text-right max-w-xs">
                                <div class="font-medium text-gray-900 truncate">${props.khutbah_theme}</div>
                                <div class="text-sm text-gray-500 flex items-center justify-end mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <span class="truncate">${props.location}</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button class="view-details-btn text-green-600 hover:text-green-700 p-2" 
                                        data-event-id="${event.id}" 
                                        onclick="event.stopPropagation(); showEventDetails(${JSON.stringify(event).replace(/"/g, '&quot;')})"
                                        title="Lihat detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
       
        scheduleList.innerHTML = listHtml;
    }
   
    // Filter event handler
    statusFilter.addEventListener('change', function() {
        renderScheduleList(allEvents, this.value);
    });
   
    // Event handlers
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('closeViewBtn').addEventListener('click', closeModal);
   
    // Global function for onclick handlers
    window.showEventDetails = function(event) {
        const props = event.extendedProps;
        const date = new Date(event.start);
       
        modalTitle.textContent = `Sholat Jumat - ${formatIndonesianDate(date)}`;
       
        eventDetails.innerHTML = `
            <div class="space-y-6">
                <!-- Date & Time Header -->
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">${formatIndonesianDay(date)}, ${formatIndonesianDate(date)}</h3>
                            <div class="flex items-center text-gray-600 mt-1">
                                <i class="fas fa-clock mr-2 text-green-600"></i>
                                <span class="text-lg font-medium">${props.prayer_time} WIB</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusClass(props.status)}">
                            ${getStatusLabel(props.status)}
                        </span>
                    </div>
                </div>
                
                <!-- Imam & Khotib -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Imam
                        </h4>
                        <p class="text-gray-700 font-medium">${props.imam_name}</p>
                    </div>
                   
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-microphone mr-2 text-purple-600"></i>Khotib
                        </h4>
                        <p class="text-gray-700 font-medium">${props.khotib_name}</p>
                    </div>
                </div>
               
                <!-- Khutbah Theme -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-bullhorn mr-2 text-green-600"></i>Tema Khutbah
                    </h4>
                    <h5 class="font-medium text-gray-900 mb-2">${props.khutbah_theme}</h5>
                    ${props.khutbah_description ? `<p class="text-gray-600">${props.khutbah_description}</p>` : ''}
                </div>
               
                <!-- Location -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>Lokasi
                    </h4>
                    <p class="text-gray-700">${props.location}</p>
                </div>
                
                ${props.special_notes ? `
                <!-- Special Notes -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-yellow-600"></i>Catatan Khusus
                    </h4>
                    <p class="text-yellow-700">${props.special_notes}</p>
                </div>
                ` : ''}
            </div>
        `;
       
        // Show modal in view-only mode
        viewMode.classList.remove('hidden');
        eventModal.classList.remove('hidden');
    };
   
    function closeModal() {
        eventModal.classList.add('hidden');
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
});
</script>
<?php include '../partials/footer.php'; ?>