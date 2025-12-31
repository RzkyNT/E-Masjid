<?php
require_once '../includes/settings_loader.php';
require_once '../config/config.php';

$page_title = 'Booking Gedung Serba Guna';
$page_description = 'Booking dan reservasi Gedung Serba Guna untuk acara dan kegiatan';
$base_url = '..';

// Initialize website settings
$settings = initializePageSettings();

// Breadcrumb
$breadcrumb = [
    ['title' => 'Booking Gedung Serba Guna', 'url' => '']
];

// Add SweetAlert2
$additional_head = '
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
';

include '../partials/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="flex justify-center mb-6">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-building text-4xl"></i>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Booking Gedung Serba Guna</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                Reservasi Gedung Serba Guna <?php echo htmlspecialchars($settings['site_name']); ?> untuk acara dan kegiatan Anda
            </p>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Information Section -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Informasi Gedung Serba Guna</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Kapasitas</h3>
                <p class="text-gray-600 text-sm">Dapat menampung hingga 200 orang</p>
            </div>
            
            <div class="text-center">
                <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wifi text-green-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Fasilitas</h3>
                <p class="text-gray-600 text-sm">WiFi, AC, Sound System, Proyektor</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-purple-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Waktu Operasional</h3>
                <p class="text-gray-600 text-sm">08:00 - 22:00 WIB</p>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Ketentuan Booking</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Booking minimal 3 hari sebelum acara</li>
                            <li>Minimal penyewaan 1 hari (24 jam)</li>
                            <li>Data booking akan disimpan saat mengirim WhatsApp</li>
                            <li>Status awal: "Menunggu Konfirmasi"</li>
                            <li>Konfirmasi akan diberikan dalam 1x24 jam</li>
                            <li>Pembayaran dilakukan setelah konfirmasi</li>
                            <li>Acara harus sesuai dengan nilai-nilai Islam</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Calendar -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pilih Tanggal</h3>
            <div id="calendar"></div>
            
            <!-- Legend -->
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                    <span class="text-gray-600">Sudah Dikonfirmasi</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                    <span class="text-gray-600">Tersedia</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
                    <span class="text-gray-600">Dipilih</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-400 rounded mr-2"></div>
                    <span class="text-gray-600">Tidak Tersedia</span>
                </div>
            </div>
            
            <!-- Selected Dates Info -->
            <div id="selectedDatesInfo" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                <h4 class="font-semibold text-blue-900 mb-2">Tanggal Dipilih:</h4>
                <div id="selectedDatesList" class="text-blue-800 text-sm"></div>
                <div id="totalDays" class="text-blue-600 font-medium mt-2"></div>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Form Booking</h3>
            
            <form id="bookingForm" class="space-y-4">
                <div>
                    <label for="selectedDatesDisplay" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Dipilih
                    </label>
                    <textarea id="selectedDatesDisplay" 
                              name="selectedDatesDisplay" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm font-mono" 
                              readonly 
                              placeholder="Pilih tanggal di kalender dengan drag atau klik"></textarea>
                </div>
                
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>
                
                <div>
                    <label for="noTelp" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           id="noTelp" 
                           name="noTelp" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="08xxxxxxxxxx"
                           required>
                </div>
                
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamat" 
                              name="alamat" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              required></textarea>
                </div>
                
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan Acara <span class="text-red-500">*</span>
                    </label>
                    <textarea id="keterangan" 
                              name="keterangan" 
                              rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Jelaskan jenis acara, jumlah peserta, dan kebutuhan khusus lainnya..."
                              required></textarea>
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Kirim Booking via WhatsApp
                </button>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for FullCalendar to load
    function initializeCalendar() {
        if (typeof FullCalendar === 'undefined') {
            console.log('Waiting for FullCalendar to load...');
            // Try for maximum 5 seconds
            if (!initializeCalendar.attempts) {
                initializeCalendar.attempts = 0;
            }
            initializeCalendar.attempts++;
            
            if (initializeCalendar.attempts > 50) { // 50 * 100ms = 5 seconds
                console.error('FullCalendar failed to load after 5 seconds');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Kalender tidak dapat dimuat. Silakan refresh halaman atau coba lagi nanti.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }
            
            setTimeout(initializeCalendar, 100);
            return;
        }
        
        console.log('FullCalendar loaded successfully');
        setupCalendar();
    }
    
    function setupCalendar() {
        const calendarEl = document.getElementById('calendar');
        const bookingForm = document.getElementById('bookingForm');
        const selectedDatesDisplay = document.getElementById('selectedDatesDisplay');
        const selectedDatesInfo = document.getElementById('selectedDatesInfo');
        const selectedDatesList = document.getElementById('selectedDatesList');
        const totalDays = document.getElementById('totalDays');
    
    // Variable untuk menyimpan tanggal yang sudah dibooking
    let bookedDates = [];
    let selectedDates = [];
    
    // Load booked dates from database
    loadBookedDates();
    
    // Function untuk load data booking dari database
    function loadBookedDates() {
        fetch('../api/gsg_bookings.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookedDates = data.booked_dates;
                    console.log('Loaded booked dates:', bookedDates);
                    
                    // Initialize calendar setelah data loaded
                    initializeCalendar();
                } else {
                    console.error('Failed to load booked dates:', data.message);
                    // Fallback ke sample data jika API gagal
                    bookedDates = [
                        '2024-01-15', '2024-01-16', '2024-01-17',
                        '2024-01-25', '2024-02-10', '2024-02-14', '2024-02-15'
                    ];
                    initializeCalendar();
                }
            })
            .catch(error => {
                console.error('Error loading booked dates:', error);
                // Fallback ke sample data jika request gagal
                bookedDates = [
                    '2024-01-15', '2024-01-16', '2024-01-17',
                    '2024-01-25', '2024-02-10', '2024-02-14', '2024-02-15'
                ];
                initializeCalendar();
            });
    }
    
    // Function untuk initialize FullCalendar
    function initializeCalendar() {
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan'
        },
        selectable: true,
        selectMirror: true,
        unselectAuto: false,
        dayMaxEvents: true,
        weekends: true,
        
        // Handle date selection (multi-day)
        select: function(info) {
            const startDate = new Date(info.startStr);
            const endDate = new Date(info.endStr);
            endDate.setDate(endDate.getDate() - 1); // FullCalendar end is exclusive
            
            const newSelectedDates = [];
            const currentDate = new Date(startDate);
            
            // Generate array of selected dates
            while (currentDate <= endDate) {
                const dateStr = currentDate.toISOString().split('T')[0];
                newSelectedDates.push(dateStr);
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            // Validate selection
            const validationResult = validateDateSelection(newSelectedDates);
            if (!validationResult.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tanggal Tidak Valid',
                    text: validationResult.message,
                    confirmButtonColor: '#2563eb'
                });
                calendar.unselect();
                return;
            }
            
            // Update selected dates
            selectedDates = newSelectedDates;
            updateSelectedDatesDisplay();
        },
        
        // Handle single date click
        dateClick: function(info) {
            const clickedDate = info.dateStr;
            
            // Validate single date
            const validationResult = validateDateSelection([clickedDate]);
            if (!validationResult.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tanggal Tidak Valid',
                    text: validationResult.message,
                    confirmButtonColor: '#2563eb'
                });
                return;
            }
            
            // Toggle single date selection
            const index = selectedDates.indexOf(clickedDate);
            if (index > -1) {
                selectedDates.splice(index, 1);
            } else {
                selectedDates.push(clickedDate);
            }
            
            selectedDates.sort();
            updateSelectedDatesDisplay();
            updateCalendarDisplay();
        },
        
        // Style dates based on status
        dayCellDidMount: function(info) {
            const dateStr = info.date.toISOString().split('T')[0];
            
            // Style confirmed dates
            if (bookedDates.includes(dateStr)) {
                info.el.style.backgroundColor = '#fee2e2';
                info.el.style.color = '#991b1b';
                info.el.title = 'Tanggal sudah dikonfirmasi untuk acara lain';
                info.el.style.cursor = 'not-allowed';
            }
            
            // Style past dates
            else if (isPastDate(new Date(dateStr))) {
                info.el.style.backgroundColor = '#f3f4f6';
                info.el.style.color = '#9ca3af';
                info.el.style.cursor = 'not-allowed';
            }
            
            // Style dates too soon (less than 3 days)
            else if (isTooSoon(new Date(dateStr))) {
                info.el.style.backgroundColor = '#f3f4f6';
                info.el.style.color = '#9ca3af';
                info.el.style.cursor = 'not-allowed';
                info.el.title = 'Booking minimal 3 hari sebelumnya';
            }
            
            // Style selected dates
            else if (selectedDates.includes(dateStr)) {
                info.el.style.backgroundColor = '#3b82f6';
                info.el.style.color = 'white';
            }
            
            // Style available dates
            else {
                info.el.style.backgroundColor = '#f0fdf4';
                info.el.style.cursor = 'pointer';
            }
        }
    });
    
    calendar.render();
    } // End of initializeCalendar function
    
    // Validation function
    function validateDateSelection(dates) {
        for (const dateStr of dates) {
            const date = new Date(dateStr);
            
            // Check if date is in the past
            if (isPastDate(date)) {
                return {
                    valid: false,
                    message: 'Tidak dapat memilih tanggal yang sudah lewat!'
                };
            }
            
            // Check if date is already confirmed (not just pending)
            if (bookedDates.includes(dateStr)) {
                return {
                    valid: false,
                    message: 'Salah satu tanggal sudah dikonfirmasi untuk acara lain. Silakan pilih tanggal lain.'
                };
            }
            
            // Check minimum booking time (3 days ahead)
            if (isTooSoon(date)) {
                return {
                    valid: false,
                    message: 'Booking minimal 3 hari sebelum acara!'
                };
            }
        }
        
        return { valid: true };
    }
    
    // Update selected dates display
    function updateSelectedDatesDisplay() {
        if (selectedDates.length === 0) {
            selectedDatesInfo.classList.add('hidden');
            selectedDatesDisplay.value = '';
            return;
        }
        
        selectedDatesInfo.classList.remove('hidden');
        
        // Format dates for display
        const formattedDates = selectedDates.map(dateStr => {
            return formatIndonesianDate(new Date(dateStr));
        });
        
        selectedDatesList.innerHTML = formattedDates.join('<br>');
        totalDays.textContent = `Total: ${selectedDates.length} hari`;
        
        // Update form display dengan format yang diminta
        let displayText = 'Tanggal Dipilih:\n';
        displayText += formattedDates.join('\n');
        displayText += `\n\nTotal: ${selectedDates.length} hari`;
        
        selectedDatesDisplay.value = displayText;
        
        updateCalendarDisplay();
    }
    
    // Update calendar visual display
    function updateCalendarDisplay() {
        // Force calendar to re-render day cells
        calendar.render();
    }
    
    // Utility functions
    function isPastDate(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date < today;
    }
    
    function isTooSoon(date) {
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 3);
        minDate.setHours(0, 0, 0, 0);
        return date < minDate;
    }
    
    function formatIndonesianDate(date) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[date.getDay()];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${dayName}, ${day} ${month} ${year}`;
    }
    
    // Handle form submission
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedDates.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Pilih Tanggal',
                text: 'Silakan pilih tanggal terlebih dahulu!',
                confirmButtonColor: '#2563eb'
            });
            return;
        }
        
        const formData = new FormData(bookingForm);
        const nama = formData.get('nama');
        const noTelp = formData.get('noTelp');
        const alamat = formData.get('alamat');
        const keterangan = formData.get('keterangan');
        
        // Prepare date range text
        let dateRangeText;
        if (selectedDates.length === 1) {
            dateRangeText = formatIndonesianDate(new Date(selectedDates[0]));
        } else {
            const firstDate = formatIndonesianDate(new Date(selectedDates[0]));
            const lastDate = formatIndonesianDate(new Date(selectedDates[selectedDates.length - 1]));
            dateRangeText = `${firstDate} - ${lastDate}`;
        }
        
        // Prepare WhatsApp message
        const message = `*BOOKING GEDUNG SERBA GUNA*\n\n` +
                       ` ${selectedDatesDisplay.value}\n` +
                       ` *Nama:* ${nama}\n` +
                       ` *No. Telp:* ${noTelp}\n` +
                       ` *Alamat:* ${alamat}\n\n` +
                       ` *Keterangan Acara:*\n${keterangan}\n\n` +
                       `Mohon konfirmasi ketersediaan gedung untuk tanggal dan durasi tersebut. Terima kasih.`;
        
        // WhatsApp number (replace with actual number)
        const whatsappNumber = '62895602416781';
        const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
        
        // Show confirmation before redirecting
        Swal.fire({
            title: 'Konfirmasi Booking',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Tanggal:</strong> ${dateRangeText}</p>
                    <p class="mb-2"><strong>Total Hari:</strong> ${selectedDates.length} hari</p>
                    <p class="mb-2"><strong>Nama:</strong> ${nama}</p>
                    <p class="mb-4"><strong>No. Telp:</strong> ${noTelp}</p>
                    <p class="text-sm text-gray-600">Data akan disimpan sebagai booking menunggu konfirmasi dan Anda akan diarahkan ke WhatsApp.</p>
                    <p class="text-sm text-yellow-600 mt-2"><strong>Catatan:</strong> Booking akan dikonfirmasi oleh admin setelah menerima pesan WhatsApp.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25d366',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fab fa-whatsapp mr-1"></i> Simpan & Kirim WhatsApp',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menyimpan Booking...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Simpan ke database terlebih dahulu
                const saveData = new FormData();
                saveData.append('dates', selectedDates.join(','));
                saveData.append('nama', nama);
                saveData.append('no_telp', noTelp);
                saveData.append('alamat', alamat);
                saveData.append('keterangan', keterangan);
                
                fetch('../api/gsg_booking_save.php', {
                    method: 'POST',
                    body: saveData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close(); // Close loading
                    
                    if (data.success) {
                        // Jika berhasil disimpan, buka WhatsApp
                        window.open(whatsappUrl, '_blank');
                        
                        // Reset form
                        bookingForm.reset();
                        selectedDates = [];
                        updateSelectedDatesDisplay();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Booking Request Terkirim!',
                            html: `
                                <div class="text-left">
                                    <p class="mb-2"> Data booking telah disimpan dengan status <strong>menunggu konfirmasi</strong></p>
                                    <p class="mb-2"> Pesan WhatsApp telah dikirim ke admin</p>
                                    <p class="text-sm text-gray-600 mt-3">Admin akan memproses booking Anda dalam 1x24 jam. Anda akan dihubungi melalui WhatsApp untuk konfirmasi.</p>
                                </div>
                            `,
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: data.message || 'Terjadi kesalahan saat menyimpan booking.',
                            confirmButtonColor: '#2563eb'
                        });
                    }
                })
                .catch(error => {
                    Swal.close(); // Close loading
                    console.error('Error saving booking:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menyimpan booking. Silakan coba lagi.',
                        confirmButtonColor: '#2563eb'
                    });
                });
            }
        });
    });
    
    } // End of setupCalendar function
    
    // Start the initialization process
    initializeCalendar();
});
</script>

<style>
/* FullCalendar Custom Styles */
.fc-theme-standard .fc-scrollgrid {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.fc-theme-standard td, .fc-theme-standard th {
    border-color: #e5e7eb;
}

.fc-day-today {
    background-color: #dbeafe !important;
}

.fc-button-primary {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
}

.fc-button-primary:hover {
    background-color: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
}

.fc-button-primary:disabled {
    background-color: #9ca3af !important;
    border-color: #9ca3af !important;
}

.fc-highlight {
    background-color: #bfdbfe !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .fc-header-toolbar {
        flex-direction: column;
        gap: 10px;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }
    
    .fc-button {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>

<?php include '../partials/footer.php'; ?>