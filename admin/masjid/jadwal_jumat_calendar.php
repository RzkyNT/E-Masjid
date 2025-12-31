<?php
// Add cache-busting headers for admin pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();

$success_message = '';
$error_message = '';

// Get speakers for dropdown
$stmt = $pdo->prepare("SELECT name, role FROM friday_speakers WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$speakers = $stmt->fetchAll();

// Get themes for dropdown
$stmt = $pdo->prepare("SELECT theme_title FROM khutbah_themes WHERE is_active = 1 ORDER BY usage_count DESC, theme_title");
$stmt->execute();
$themes = $stmt->fetchAll();

$page_title = 'Kalender Jadwal Jumat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-alt text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Kalender Jadwal Jumat</h1>
                        <p class="text-sm text-gray-500">Manajemen jadwal dengan kalender interaktif</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="../../pages/jadwal_jumat.php" target="_blank" class="text-gray-500 hover:text-green-600">
                        <i class="fas fa-external-link-alt mr-1"></i>Lihat Halaman Publik
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?>
                            </div>
                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            <i class="fas fa-chevron-down ml-1 text-gray-400"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b">
                                <?php echo ucfirst(str_replace('_', ' ', $current_user['role'])); ?>
                            </div>
                            <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-home mr-2"></i>Dashboard
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <nav class="mt-5 px-2">
                <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                
                <a href="berita.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-newspaper mr-3"></i>Kelola Berita
                </a>
                
                <a href="galeri.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-images mr-3"></i>Kelola Galeri
                </a>
                
                <div class="mt-1">
                    <div class="text-gray-400 px-2 py-2 text-sm font-medium">Jadwal Jumat</div>
                    <a href="jadwal_jumat.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-4 py-2 text-sm rounded-md">
                        <i class="fas fa-list mr-3"></i>Tampilan Daftar
                    </a>
                    <a href="jadwal_jumat_calendar.php" class="bg-green-600 text-white group flex items-center px-4 py-2 text-sm rounded-md">
                        <i class="fas fa-calendar-alt mr-3"></i>Tampilan Kalender
                    </a>
                </div>
                
                <a href="donasi.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-hand-holding-heart mr-3"></i>Kelola Donasi
                </a>
                
                <a href="konten.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-file-alt mr-3"></i>Kelola Konten
                </a>
                
                <a href="pengaturan.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md mt-1">
                    <i class="fas fa-cog mr-3"></i>Pengaturan
                </a>
                
                <div class="border-t border-gray-700 mt-4 pt-4">
                    <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white group flex items-center px-2 py-2 text-base font-medium rounded-md">
                        <i class="fas fa-arrow-left mr-3"></i>Kembali ke Dashboard
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Messages -->
            <div id="messageContainer"></div>
            
            <!-- Action Buttons -->
            <div class="mb-6 flex justify-between items-center">
                <div class="flex space-x-2">
                    <?php if (hasPermission($current_user['role'], 'masjid_content', 'create')): ?>
                        <button id="addEventBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                            <i class="fas fa-plus mr-1"></i>Tambah Jadwal
                        </button>
                    <?php endif; ?>
                    
                    <button id="refreshCalendar" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                    </button>
                </div>
                
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
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span>Dibatalkan</span>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Container -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Jadwal Jumat</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="py-4">
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
                                       required
                                       list="imam_list">
                                <datalist id="imam_list">
                                    <?php foreach ($speakers as $speaker): ?>
                                        <?php if ($speaker['role'] === 'imam' || $speaker['role'] === 'both'): ?>
                                            <option value="<?php echo htmlspecialchars($speaker['name']); ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </datalist>
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
                                       required
                                       list="khotib_list">
                                <datalist id="khotib_list">
                                    <?php foreach ($speakers as $speaker): ?>
                                        <?php if ($speaker['role'] === 'khotib' || $speaker['role'] === 'both'): ?>
                                            <option value="<?php echo htmlspecialchars($speaker['name']); ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </datalist>
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
                                   required
                                   list="theme_list">
                            <datalist id="theme_list">
                                <?php foreach ($themes as $theme): ?>
                                    <option value="<?php echo htmlspecialchars($theme['theme_title']); ?>">
                                <?php endforeach; ?>
                            </datalist>
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
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-between pt-4 border-t">
                    <div>
                        <button id="deleteEventBtn" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700 transition duration-200 hidden">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                    <div class="flex space-x-3">
                        <button id="closeModalBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-400 transition duration-200">
                            Batal
                        </button>
                        <button id="saveEventBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition duration-200">
                            <i class="fas fa-save mr-1"></i>Simpan
                        </button>
                    </div>
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
        const eventForm = document.getElementById('eventForm');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const addEventBtn = document.getElementById('addEventBtn');
        const saveEventBtn = document.getElementById('saveEventBtn');
        const deleteEventBtn = document.getElementById('deleteEventBtn');
        const refreshCalendar = document.getElementById('refreshCalendar');
        const messageContainer = document.getElementById('messageContainer');
        
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
            editable: <?php echo hasPermission($current_user['role'], 'masjid_content', 'update') ? 'true' : 'false'; ?>,
            selectable: <?php echo hasPermission($current_user['role'], 'masjid_content', 'create') ? 'true' : 'false'; ?>,
            selectMirror: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                // Fetch events from API
                fetch('../../api/friday_schedule_events.php')
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
                editEvent(info.event);
            },
            select: function(info) {
                // Only allow selection on Fridays
                if (info.start.getDay() === 5) {
                    addEvent(info.start);
                } else {
                    showMessage('Jadwal hanya bisa ditambahkan pada hari Jumat!', 'error');
                }
                calendar.unselect();
            },
            eventDrop: function(info) {
                // Only allow drop on Fridays
                if (info.event.start.getDay() !== 5) {
                    info.revert();
                    showMessage('Jadwal hanya bisa dipindahkan ke hari Jumat!', 'error');
                    return;
                }
                
                // Update event date
                updateEventDate(info.event.id, info.event.start);
            },
            dayCellDidMount: function(info) {
                // Highlight Fridays
                if (info.date.getDay() === 5) {
                    info.el.style.backgroundColor = '#f0fdf4';
                }
            }
        });
        
        calendar.render();
        
        // Event handlers
        addEventBtn.addEventListener('click', function() {
            addEvent();
        });
        
        refreshCalendar.addEventListener('click', function() {
            calendar.refetchEvents();
            showMessage('Kalender berhasil diperbarui', 'success');
        });
        
        closeModal.addEventListener('click', closeEventModal);
        closeModalBtn.addEventListener('click', closeEventModal);
        
        saveEventBtn.addEventListener('click', saveEvent);
        deleteEventBtn.addEventListener('click', deleteEvent);
        
        // Close modal when clicking outside
        eventModal.addEventListener('click', function(e) {
            if (e.target === eventModal) {
                closeEventModal();
            }
        });
        
        // Validate Friday date
        document.getElementById('friday_date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate.getDay() !== 5) {
                showMessage('Tanggal yang dipilih harus hari Jumat!', 'error');
                this.focus();
            }
        });
        
        // Functions
        function addEvent(date = null) {
            resetForm();
            modalTitle.textContent = 'Tambah Jadwal Jumat';
            document.getElementById('formAction').value = 'add';
            document.getElementById('statusField').classList.add('hidden');
            deleteEventBtn.classList.add('hidden');
            
            if (date) {
                document.getElementById('friday_date').value = formatDateForInput(date);
            } else {
                // Set to next Friday
                const nextFriday = getNextFriday();
                document.getElementById('friday_date').value = formatDateForInput(nextFriday);
            }
            
            eventModal.classList.remove('hidden');
        }
        
        function editEvent(event) {
            resetForm();
            modalTitle.textContent = 'Edit Jadwal Jumat';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('statusField').classList.remove('hidden');
            deleteEventBtn.classList.remove('hidden');
            
            // Fill form with event data
            const props = event.extendedProps;
            document.getElementById('eventId').value = event.id;
            document.getElementById('friday_date').value = formatDateForInput(event.start);
            document.getElementById('prayer_time').value = props.prayer_time;
            document.getElementById('imam_name').value = props.imam_name;
            document.getElementById('khotib_name').value = props.khotib_name;
            document.getElementById('khutbah_theme').value = props.khutbah_theme;
            document.getElementById('khutbah_description').value = props.khutbah_description || '';
            document.getElementById('location').value = props.location;
            document.getElementById('special_notes').value = props.special_notes || '';
            document.getElementById('status').value = props.status;
            
            eventModal.classList.remove('hidden');
        }
        
        function saveEvent() {
            const formData = new FormData(eventForm);
            const action = formData.get('action');
            
            // Validate required fields
            if (!validateForm()) {
                return;
            }
            
            const url = action === 'add' ? '../../api/friday_schedule_crud.php' : '../../api/friday_schedule_crud.php';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    closeEventModal();
                    calendar.refetchEvents();
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
            
            const eventId = document.getElementById('eventId').value;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('event_id', eventId);
            
            fetch('../../api/friday_schedule_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    closeEventModal();
                    calendar.refetchEvents();
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
            
            fetch('../../api/friday_schedule_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Tanggal jadwal berhasil diperbarui', 'success');
                } else {
                    showMessage(data.message, 'error');
                    calendar.refetchEvents(); // Revert on error
                }
            })
            .catch(error => {
                console.error('Error updating event date:', error);
                showMessage('Terjadi kesalahan saat memperbarui tanggal', 'error');
                calendar.refetchEvents(); // Revert on error
            });
        }
        
        function closeEventModal() {
            eventModal.classList.add('hidden');
            resetForm();
        }
        
        function resetForm() {
            eventForm.reset();
            document.getElementById('prayer_time').value = '12:00';
            document.getElementById('location').value = 'Masjid Jami Al-Muhajirin';
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
            
            // Validate Friday date
            const selectedDate = new Date(document.getElementById('friday_date').value);
            if (selectedDate.getDay() !== 5) {
                showMessage('Tanggal yang dipilih harus hari Jumat!', 'error');
                return false;
            }
            
            return true;
        }
        
        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            messageContainer.innerHTML = `
                <div class="${alertClass} border px-4 py-3 rounded mb-4">
                    <i class="${iconClass} mr-2"></i>${message}
                </div>
            `;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
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
    });
    </script>
</body>
</html>