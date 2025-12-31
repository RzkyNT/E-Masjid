<?php
/**
 * Mentor Attendance Management
 * Interface for recording and managing mentor attendance by level with payment calculation
 */

require_once '../../config/config.php';
require_once '../../includes/session_check.php';
require_once '../../includes/bimbel_functions.php';

// Get current user
$current_user = getCurrentUser();

// Check if user has access to bimbel module
if (!$current_user || !in_array($current_user['role'], ['admin_bimbel', 'admin_masjid', 'viewer'])) {
    header('Location: ../../admin/login.php');
    exit;
}

$page_title = 'Absensi Mentor';
$page_description = 'Kelola absensi mentor bimbel Al-Muhajirin';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'record_attendance':
                $date = $_POST['attendance_date'] ?? '';
                $mentorId = $_POST['mentor_id'] ?? '';
                $level = $_POST['level'] ?? '';
                $class = $_POST['class'] ?? '';
                $status = $_POST['status'] ?? '';
                $hoursTaught = $_POST['hours_taught'] ?? 0;
                $notes = $_POST['notes'] ?? '';
                
                if (empty($date) || empty($mentorId) || empty($level) || empty($class) || empty($status)) {
                    $message = 'Tanggal, mentor, jenjang, kelas, dan status harus diisi';
                    $messageType = 'error';
                } else {
                    $result = recordMentorAttendance($date, $mentorId, $level, $class, $status, $hoursTaught, $notes);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                break;
        }
    }
}

// Get form data
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedLevel = $_GET['level'] ?? '';
$selectedClass = $_GET['class'] ?? '';

// Get mentor attendance for selected date, level, and class
$mentorAttendance = [];
if ($selectedDate) {
    $mentorAttendance = getMentorAttendanceByDateAndClass($selectedDate, $selectedLevel, $selectedClass);
}

// Get available classes for selected level
$classes = [];
if ($selectedLevel) {
    $classes = getClassesByLevel($selectedLevel);
}

// Get attendance statistics
$stats = getAttendanceStatistics();

// Get all active mentors for the modal
$allMentors = getAllMentors(1, 100, ['status' => 'active'])['data'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mentor - Bimbel Al-Muhajirin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .mentor-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
            background: white;
        }
        .mentor-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .mentor-header {
            background-color: #f9fafb;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px 8px 0 0;
        }
        .mentor-body {
            padding: 1rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        .status-present {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-absent {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .status-sick {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-permission {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .status-not-recorded {
            background-color: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .quick-filters {
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
        }
        .payment-info {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .level-badge {
            background-color: #3b82f6;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-right: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="main-content content-with-sidebar bg-gray-50 min-h-screen">
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-3 text-green-600"></i>
                    Absensi Mentor
                </h1>
                <p class="text-gray-600 mt-1">Kelola absensi mentor bimbel Al-Muhajirin</p>
            </div>
            <div class="flex space-x-2">
                <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center" 
                        onclick="window.location.href='absensi_siswa.php'">
                    <i class="fas fa-user-check mr-2"></i>Absensi Siswa
                </button>
                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center" 
                        onclick="openModal('addAttendanceModal')">
                    <i class="fas fa-plus mr-2"></i>Tambah Absensi
                </button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?>">
                <div class="flex items-center">
                    <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="stats-card">
                <h5 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-calendar-day mr-2"></i>Absensi Mentor Hari Ini
                </h5>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold"><?= $stats['today']['mentors']['sessions_present'] ?? 0 ?></h3>
                        <small class="opacity-90">Sesi Hadir</small>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold"><?= $stats['today']['mentors']['total_hours'] ?? 0 ?></h3>
                        <small class="opacity-90">Total Jam</small>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h5 class="text-lg font-semibold mb-4 flex items-center text-gray-900">
                    <i class="fas fa-percentage mr-2 text-blue-600"></i>Tingkat Kehadiran
                </h5>
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-blue-600"><?= $stats['today']['mentors']['attendance_rate'] ?? 0 ?>%</h3>
                    <small class="text-gray-600">
                        <?= $stats['today']['mentors']['sessions_present'] ?? 0 ?> dari 
                        <?= $stats['today']['mentors']['total_sessions'] ?? 0 ?> sesi
                    </small>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="quick-filters">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                           id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Filter Jenjang</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                            id="level" name="level" onchange="this.form.submit()">
                        <option value="">Semua Jenjang</option>
                        <option value="SD" <?= $selectedLevel === 'SD' ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= $selectedLevel === 'SMP' ? 'selected' : '' ?>>SMP</option>
                        <option value="SMA" <?= $selectedLevel === 'SMA' ? 'selected' : '' ?>>SMA</option>
                    </select>
                </div>
                <div>
                    <label for="class" class="block text-sm font-medium text-gray-700 mb-1">Filter Kelas</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                            id="class" name="class" <?= empty($classes) ? 'disabled' : '' ?>>
                        <option value="">Semua Kelas</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= htmlspecialchars($class) ?>" <?= $selectedClass === $class ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Mentor Attendance List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($mentorAttendance)): ?>
                <?php 
                $groupedByMentor = [];
                foreach ($mentorAttendance as $attendance) {
                    $mentorId = $attendance['mentor_id'];
                    if (!isset($groupedByMentor[$mentorId])) {
                        $groupedByMentor[$mentorId] = [
                            'mentor' => $attendance,
                            'classes' => []
                        ];
                    }
                    $classKey = $attendance['teaching_level'] . '_' . $attendance['class'];
                    $groupedByMentor[$mentorId]['classes'][$classKey] = $attendance;
                }
                ?>
                
                <?php foreach ($groupedByMentor as $mentorId => $data): ?>
                    <div class="mentor-card">
                        <div class="mentor-header">
                            <h6 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($data['mentor']['full_name']) ?></h6>
                            <small class="text-gray-600"><?= htmlspecialchars($data['mentor']['mentor_code']) ?></small>
                            <div class="mt-2">
                                <?php foreach ($data['mentor']['teaching_levels'] as $level): ?>
                                    <span class="level-badge"><?= $level ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mentor-body">
                            <?php foreach ($data['classes'] as $classKey => $attendance): ?>
                                <div class="mb-4 last:mb-0">
                                    <div class="flex justify-between items-center mb-2">
                                        <strong class="text-gray-900"><?= $attendance['teaching_level'] ?> - <?= $attendance['class'] ?></strong>
                                        <?php if ($attendance['status']): ?>
                                            <span class="status-badge status-<?= $attendance['status'] ?>">
                                                <?php
                                                $statusIcons = [
                                                    'present' => 'fas fa-check',
                                                    'absent' => 'fas fa-times',
                                                    'sick' => 'fas fa-thermometer-half',
                                                    'permission' => 'fas fa-hand-paper'
                                                ];
                                                $statusLabels = [
                                                    'present' => 'Hadir',
                                                    'absent' => 'Tidak Hadir',
                                                    'sick' => 'Sakit',
                                                    'permission' => 'Izin'
                                                ];
                                                ?>
                                                <i class="<?= $statusIcons[$attendance['status']] ?>"></i>
                                                <?= $statusLabels[$attendance['status']] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-not-recorded">
                                                <i class="fas fa-question"></i>
                                                Belum Direkam
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($attendance['status'] === 'present' && $attendance['hours_taught']): ?>
                                        <div class="payment-info">
                                            <small class="text-green-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= $attendance['hours_taught'] ?> jam × 
                                                <?= formatCurrency($attendance['hourly_rate']) ?> = 
                                                <strong><?= formatCurrency($attendance['hours_taught'] * $attendance['hourly_rate']) ?></strong>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($attendance['notes']): ?>
                                        <div class="mt-2">
                                            <small class="text-gray-600">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <?= htmlspecialchars($attendance['notes']) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <button type="button" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm transition-colors duration-200 flex items-center" 
                                                onclick="editAttendance(<?= $mentorId ?>, '<?= $attendance['teaching_level'] ?>', '<?= $attendance['class'] ?>', '<?= $selectedDate ?>')">
                                            <i class="fas fa-edit mr-1"></i>
                                            <?= $attendance['status'] ? 'Edit' : 'Rekam' ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg">
                        <i class="fas fa-info-circle mr-2"></i>
                        Tidak ada data mentor untuk tanggal <?= date('d/m/Y', strtotime($selectedDate)) ?>
                        <?= $selectedLevel ? " jenjang $selectedLevel" : "" ?>
                        <?= $selectedClass ? " kelas $selectedClass" : "" ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Add/Edit Attendance Modal -->
<div id="addAttendanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-900">Rekam Absensi Mentor</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('addAttendanceModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="p-6 space-y-4">
                    <input type="hidden" name="action" value="record_attendance">
                    
                    <div>
                        <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               id="attendance_date" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                    </div>
                    
                    <div>
                        <label for="mentor_id" class="block text-sm font-medium text-gray-700 mb-1">Mentor</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                id="mentor_id" name="mentor_id" required onchange="updateLevelOptions()">
                            <option value="">Pilih Mentor</option>
                            <?php foreach ($allMentors as $mentor): ?>
                                <option value="<?= $mentor['id'] ?>" 
                                        data-levels='<?= json_encode($mentor['teaching_levels']) ?>'
                                        data-rate="<?= $mentor['hourly_rate'] ?>">
                                    <?= htmlspecialchars($mentor['full_name']) ?> (<?= htmlspecialchars($mentor['mentor_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Jenjang</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                id="modal_level" name="level" required disabled onchange="updateClassOptions()">
                            <option value="">Pilih jenjang yang diajar</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="class" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                id="modal_class" name="class" required disabled>
                            <option value="">Pilih kelas</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Kehadiran</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                id="status" name="status" required onchange="toggleHoursField()">
                            <option value="">Pilih Status</option>
                            <option value="present">Hadir</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="sick">Sakit</option>
                            <option value="permission">Izin</option>
                        </select>
                    </div>
                    
                    <div id="hoursField" style="display: none;">
                        <label for="hours_taught" class="block text-sm font-medium text-gray-700 mb-1">Jam Mengajar</label>
                        <div class="flex">
                            <input type="number" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   id="hours_taught" name="hours_taught" min="0" max="8" step="0.5" placeholder="2.0">
                            <span class="px-3 py-2 bg-gray-50 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">jam</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-1" id="paymentCalculation"></div>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  id="notes" name="notes" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                    <button type="button" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200" 
                            onclick="closeModal('addAttendanceModal')">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                        Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function updateLevelOptions() {
        const mentorSelect = document.getElementById('mentor_id');
        const levelSelect = document.getElementById('modal_level');
        const classSelect = document.getElementById('modal_class');
        const selectedOption = mentorSelect.options[mentorSelect.selectedIndex];
        
        // Clear existing options
        levelSelect.innerHTML = '<option value="">Pilih jenjang yang diajar</option>';
        classSelect.innerHTML = '<option value="">Pilih kelas</option>';
        
        if (selectedOption.value) {
            const levels = JSON.parse(selectedOption.dataset.levels || '[]');
            levelSelect.disabled = false;
            
            levels.forEach(level => {
                const option = document.createElement('option');
                option.value = level;
                option.textContent = level;
                levelSelect.appendChild(option);
            });
        } else {
            levelSelect.disabled = true;
            classSelect.disabled = true;
        }
        
        // Reset other fields
        document.getElementById('status').value = '';
        toggleHoursField();
    }

    function updateClassOptions() {
        const levelSelect = document.getElementById('modal_level');
        const classSelect = document.getElementById('modal_class');
        const selectedLevel = levelSelect.value;
        
        // Clear existing options
        classSelect.innerHTML = '<option value="">Pilih kelas</option>';
        
        if (selectedLevel) {
            classSelect.disabled = false;
            
            // Fetch classes for selected level via AJAX
            fetch(`get_classes.php?level=${selectedLevel}`)
                .then(response => response.json())
                .then(classes => {
                    classes.forEach(className => {
                        const option = document.createElement('option');
                        option.value = className;
                        option.textContent = className;
                        classSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching classes:', error);
                    // Fallback: add common class names
                    const commonClasses = ['A', 'B', 'C'];
                    commonClasses.forEach(className => {
                        const option = document.createElement('option');
                        option.value = className;
                        option.textContent = className;
                        classSelect.appendChild(option);
                    });
                });
        } else {
            classSelect.disabled = true;
        }
    }

    function toggleHoursField() {
        const status = document.getElementById('status').value;
        const hoursField = document.getElementById('hoursField');
        const hoursInput = document.getElementById('hours_taught');
        
        if (status === 'present') {
            hoursField.style.display = 'block';
            hoursInput.required = true;
            updatePaymentCalculation();
        } else {
            hoursField.style.display = 'none';
            hoursInput.required = false;
            hoursInput.value = '';
            document.getElementById('paymentCalculation').textContent = '';
        }
    }

    function updatePaymentCalculation() {
        const mentorSelect = document.getElementById('mentor_id');
        const hoursInput = document.getElementById('hours_taught');
        const calculationDiv = document.getElementById('paymentCalculation');
        
        const selectedOption = mentorSelect.options[mentorSelect.selectedIndex];
        const hourlyRate = parseFloat(selectedOption.dataset.rate || 0);
        const hours = parseFloat(hoursInput.value || 0);
        
        if (hourlyRate > 0 && hours > 0) {
            const total = hourlyRate * hours;
            calculationDiv.textContent = `${hours} jam × ${formatCurrency(hourlyRate)} = ${formatCurrency(total)}`;
        } else {
            calculationDiv.textContent = '';
        }
    }

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function editAttendance(mentorId, level, className, date) {
        // Set form values
        document.getElementById('attendance_date').value = date;
        document.getElementById('mentor_id').value = mentorId;
        
        // Update level options and select the level
        updateLevelOptions();
        setTimeout(() => {
            document.getElementById('modal_level').value = level;
            updateClassOptions();
            setTimeout(() => {
                document.getElementById('modal_class').value = className;
            }, 200);
        }, 100);
        
        // Show modal
        openModal('addAttendanceModal');
    }

    // Auto-submit form when date changes
    document.getElementById('date').addEventListener('change', function() {
        this.form.submit();
    });

    // Update payment calculation when hours change
    document.getElementById('hours_taught').addEventListener('input', updatePaymentCalculation);

    // Close modal when clicking outside
    document.getElementById('addAttendanceModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('addAttendanceModal');
        }
    });
</script>
</body>
</html>