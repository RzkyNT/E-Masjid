<?php
// Add cache-busting headers for development
require_once '../includes/settings_loader.php';
if (isDevelopmentMode()) {
    addNoCacheHeaders();
}

require_once '../config/config.php';

$page_title = 'Jadwal Sholat Jumat - Kalender';
$page_description = 'Kalender jadwal sholat Jumat, imam, khotib, dan tema khutbah di Masjid Jami Al-Muhajirin';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Breadcrumb
$breadcrumb = [
    ['title' => 'Jadwal Sholat Jumat', 'url' => '']
];

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
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Kalender Sholat Jumat</h1>
            <p class="text-xl text-green-100 max-w-3xl mx-auto">
                Lihat jadwal sholat Jumat dalam tampilan kalender interaktif di <?php echo htmlspecialchars($settings['site_name']); ?>
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- View Toggle -->
    <div class="mb-6 flex justify-between items-center">
        <div class="flex space-x-2">
            <button id="monthView" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                <i class="fas fa-calendar mr-1"></i>Bulan
            </button>
            <button id="listView" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                <i class="fas fa-list mr-1"></i>Daftar
            </button>
            <a href="jadwal_jumat.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                <i class="fas fa-th-large mr-1"></i>Tampilan Card
            </a>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Export Button -->
            <a href="../api/friday_schedule_ical.php" class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm hover:bg-purple-700 transition duration-200" title="Export ke Kalender">
                <i class="fas fa-download mr-1"></i>Export iCal
            </a>
            
            <!-- Legend -->
            <div class="flex items-center space-x-2 text-sm text-gray-600">
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
    </div>
    
    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="calendar"></div>
    </div>
    
    <!-- Event Details Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Detail Jadwal Jumat</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="py-4" id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end pt-4 border-t">
                    <button id="closeModalBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                        Tutup
                    </button>
                </div>
            </div>
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

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<!-- Custom Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventModal = document.getElementById('eventModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const closeModal = document.getElementById('closeModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const monthViewBtn = document.getElementById('monthView');
    const listViewBtn = document.getElementById('listView');
    
    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        locale: 'id',
        firstDay: 1, // Monday
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            // Fetch events from API
            fetch('../api/friday_schedule_events.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successCallback(data.events);
                    } else {
                        failureCallback(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.setAttribute('title', info.event.title);
        },
        dayCellDidMount: function(info) {
            // Highlight Fridays
            if (info.date.getDay() === 5) {
                info.el.style.backgroundColor = '#f0fdf4';
            }
        }
    });
    
    calendar.render();
    
    // View toggle buttons
    monthViewBtn.addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        updateViewButtons('month');
    });
    
    listViewBtn.addEventListener('click', function() {
        calendar.changeView('listMonth');
        updateViewButtons('list');
    });
    
    function updateViewButtons(activeView) {
        monthViewBtn.classList.remove('bg-green-600', 'text-white');
        monthViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        listViewBtn.classList.remove('bg-green-600', 'text-white');
        listViewBtn.classList.add('bg-gray-300', 'text-gray-700');
        
        if (activeView === 'month') {
            monthViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
            monthViewBtn.classList.add('bg-green-600', 'text-white');
        } else {
            listViewBtn.classList.remove('bg-gray-300', 'text-gray-700');
            listViewBtn.classList.add('bg-green-600', 'text-white');
        }
    }
    
    // Show event details in modal
    function showEventDetails(event) {
        const eventData = event.extendedProps;
        
        modalTitle.textContent = `Sholat Jumat - ${formatDate(event.start)}`;
        
        modalContent.innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Waktu Sholat</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-clock mr-2 text-green-600"></i>
                            <span class="text-lg font-medium">${eventData.prayer_time} WIB</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(eventData.status)}">
                            ${getStatusLabel(eventData.status)}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Imam</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            <span>${eventData.imam_name}</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Khotib</h4>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-microphone mr-2 text-purple-600"></i>
                            <span>${eventData.khotib_name}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Tema Khutbah</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="font-medium text-gray-900 mb-2">${eventData.khutbah_theme}</h5>
                        ${eventData.khutbah_description ? `<p class="text-gray-600 text-sm">${eventData.khutbah_description}</p>` : ''}
                    </div>
                </div>
                
                ${eventData.special_notes ? `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Catatan Khusus</h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">${eventData.special_notes}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Lokasi</h4>
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        <span>${eventData.location}</span>
                    </div>
                </div>
            </div>
        `;
        
        eventModal.classList.remove('hidden');
    }
    
    // Close modal
    closeModal.addEventListener('click', function() {
        eventModal.classList.add('hidden');
    });
    
    closeModalBtn.addEventListener('click', function() {
        eventModal.classList.add('hidden');
    });
    
    // Close modal when clicking outside
    eventModal.addEventListener('click', function(e) {
        if (e.target === eventModal) {
            eventModal.classList.add('hidden');
        }
    });
    
    // Helper functions
    function formatDate(date) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return date.toLocaleDateString('id-ID', options);
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
    
    // Auto-refresh calendar every 30 minutes
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            calendar.refetchEvents();
        }
    }, 1800000); // 30 minutes
});
</script>

<?php include '../partials/footer.php'; ?>