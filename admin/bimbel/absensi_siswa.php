<?php
/**
 * Student Attendance Management - Mobile Friendly Version
 * Interface for recording and managing student attendance by multiple classes
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

$page_title = 'Absensi Siswa';
$page_description = 'Kelola absensi siswa bimbel Al-Muhajirin';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'record_attendance':
                $date = $_POST['attendance_date'] ?? '';
                $level = $_POST['level'] ?? '';
                $classes = $_POST['classes'] ?? [];
                $attendanceData = $_POST['attendance'] ?? [];
                
                if (empty($date) || empty($level) || empty($classes)) {
                    $message = 'Tanggal, jenjang, dan kelas harus diisi';
                    $messageType = 'error';
                } else {
                    $result = recordMultiClassStudentAttendance($date, $level, $classes, $attendanceData);
                    if ($result['success']) {
                        $message = $result['message'];
                        $messageType = 'success';
                    } else {
                        $message = $result['message'];
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}

// Get current date for default
$currentDate = date('Y-m-d');
$selectedDate = $_GET['date'] ?? $currentDate;
$selectedLevel = $_GET['level'] ?? '';
$selectedClasses = $_GET['classes'] ?? [];

// Ensure selectedClasses is always an array
if (!is_array($selectedClasses)) {
    $selectedClasses = [];
}

// Get students for selected classes if level and classes are selected
$students = [];
if ($selectedLevel && !empty($selectedClasses)) {
    $students = getStudentAttendanceByMultipleClasses($selectedDate, $selectedLevel, $selectedClasses);
}

// Get available classes for selected level
$classes = [];
if ($selectedLevel) {
    $classes = getClassesByLevel($selectedLevel);
}

// Get attendance statistics
$stats = getAttendanceStatistics();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa - Bimbel Al-Muhajirin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mobile-first responsive design */
        .attendance-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .attendance-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem;
            border-radius: 12px 12px 0 0;
        }
        
        .student-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: white;
            transition: all 0.2s ease;
        }
        
        .student-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-color: #10b981;
        }
        
        .student-header {
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .student-controls {
            padding: 0.75rem;
            background: #f9fafb;
        }
        
        .status-btn {
            flex: 1;
            padding: 0.75rem 0.5rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }
        
        .status-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .status-btn.active {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .status-btn.present.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: #10b981;
        }
        
        .status-btn.absent.active {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-color: #ef4444;
        }
        
        .status-btn.sick.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-color: #f59e0b;
            color: #1f2937;
        }
        
        .status-btn.permission.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #3b82f6;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .filter-card {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .class-section {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
            background: white;
        }
        
        .class-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 1rem;
            font-weight: 600;
        }
        
        .floating-action {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 50;
        }
        
        .quick-action-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .student-controls {
                padding: 0.5rem;
            }
            
            .status-btn {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
            }
            
            .status-btn i {
                font-size: 1rem;
            }
        }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Multi-select styling */
        .multi-select {
            max-height: 120px;
            overflow-y: auto;
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
            max-width: 300px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="main-content content-with-sidebar bg-gray-50 min-h-screen">
    <div class="p-4 md:p-6">
        <!-- Mobile Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-check mr-3 text-green-600"></i>
                    Absensi Siswa
                </h1>
                <p class="text-gray-600 mt-1 text-sm md:text-base">Kelola absensi siswa bimbel Al-Muhajirin</p>
            </div>
            <div class="flex space-x-2">
                <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center text-sm" 
                        onclick="window.location.href='absensi_mentor.php'">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    <span class="hidden sm:inline">Absensi Mentor</span>
                    <span class="sm:hidden">Mentor</span>
                </button>
            </div>
        </div>

        <!-- Toast Notification -->
        <?php if ($message): ?>
            <div id="toast" class="toast <?= $messageType === 'success' ? 'bg-green-500' : 'bg-red-500' ?> text-white p-4 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <span class="text-sm"><?= htmlspecialchars($message) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards - Mobile Optimized -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="stats-card">
                <h5 class="text-lg font-semibold mb-3 flex items-center">
                    <i class="fas fa-calendar-day mr-2"></i>Hari Ini
                </h5>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold"><?= $stats['today']['students']['present'] ?? 0 ?></h3>
                        <small class="opacity-90">Hadir</small>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold"><?= $stats['today']['students']['attendance_rate'] ?? 0 ?>%</h3>
                        <small class="opacity-90">Kehadiran</small>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <h5 class="text-lg font-semibold mb-3 flex items-center text-gray-900">
                    <i class="fas fa-chart-line mr-2 text-blue-600"></i>Bulanan
                </h5>
                <div class="space-y-2">
                    <?php if (isset($stats['monthly']['students'])): ?>
                        <?php foreach ($stats['monthly']['students'] as $level => $data): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700 text-sm"><?= $level ?></span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium"><?= $data['attendance_rate'] ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Belum ada data</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filter Form - Mobile Optimized -->
        <div class="filter-card">
            <h6 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600"></i>Filter Absensi
            </h6>
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" 
                               id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                    </div>
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Jenjang</label>
                        <select class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" 
                                id="level" name="level" required onchange="this.form.submit()">
                            <option value="">Pilih Jenjang</option>
                            <option value="SD" <?= $selectedLevel === 'SD' ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= $selectedLevel === 'SMP' ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= $selectedLevel === 'SMA' ? 'selected' : '' ?>>SMA</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="classes" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-xs text-gray-500">(Pilih beberapa)</span>
                        </label>
                        <div class="relative">
                            <select class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm multi-select custom-scrollbar" 
                                    id="classes" name="classes[]" multiple <?= empty($classes) ? 'disabled' : '' ?>>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= htmlspecialchars($class) ?>" 
                                            <?= in_array($class, $selectedClasses) ? 'selected' : '' ?>>
                                        Kelas <?= htmlspecialchars($class) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($classes)): ?>
                                <div class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Tahan Ctrl/Cmd untuk pilih beberapa
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="quick-action-btn flex items-center">
                        <i class="fas fa-search mr-2"></i>Tampilkan Siswa
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Form - Mobile Optimized -->
        <?php if (!empty($students)): ?>
            <div class="attendance-card">
                <div class="attendance-header">
                    <h5 class="text-lg font-semibold mb-1 flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        Absensi <?= htmlspecialchars($selectedLevel) ?>
                    </h5>
                    <div class="text-sm opacity-90">
                        <div>Kelas: <?= implode(', ', array_map('htmlspecialchars', $selectedClasses)) ?></div>
                        <div><?= date('d/m/Y', strtotime($selectedDate)) ?> • <?= count($students) ?> siswa dari <?= count($selectedClasses) ?> kelas</div>
                    </div>
                </div>

                <form method="POST" id="attendanceForm" class="p-4">
                    <input type="hidden" name="action" value="record_attendance">
                    <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>">
                    <input type="hidden" name="level" value="<?= htmlspecialchars($selectedLevel) ?>">
                    <?php foreach ($selectedClasses as $class): ?>
                        <input type="hidden" name="classes[]" value="<?= htmlspecialchars($class) ?>">
                    <?php endforeach; ?>

                    <!-- Quick Actions - Mobile Optimized -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-bolt mr-2 text-yellow-600"></i>Aksi Cepat
                        </h6>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                            <button type="button" class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center text-sm font-medium" 
                                    onclick="setAllAttendance('present')">
                                <i class="fas fa-check-double mr-1"></i>
                                <span class="hidden sm:inline">Semua Hadir</span>
                                <span class="sm:hidden">Hadir</span>
                            </button>
                            <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center text-sm font-medium" 
                                    onclick="clearAllAttendance()">
                                <i class="fas fa-eraser mr-1"></i>
                                <span class="hidden sm:inline">Reset</span>
                                <span class="sm:hidden">Reset</span>
                            </button>
                            <?php foreach ($selectedClasses as $class): ?>
                                <button type="button" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center text-sm font-medium" 
                                        onclick="setClassAttendance('<?= $class ?>', 'present')">
                                    <i class="fas fa-check mr-1"></i>
                                    <span class="hidden sm:inline">Kelas <?= $class ?></span>
                                    <span class="sm:hidden"><?= $class ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php 
                    // Group students by class for better organization
                    $studentsByClass = [];
                    foreach ($students as $student) {
                        $studentsByClass[$student['class']][] = $student;
                    }
                    ?>

                    <!-- Students by Class - Mobile Optimized -->
                    <?php foreach ($studentsByClass as $className => $classStudents): ?>
                        <div class="class-section mb-4">
                            <div class="class-header">
                                <div class="flex justify-between items-center">
                                    <h6 class="font-semibold flex items-center">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        Kelas <?= htmlspecialchars($className) ?>
                                    </h6>
                                    <span class="text-sm opacity-90"><?= count($classStudents) ?> siswa</span>
                                </div>
                            </div>

                            <div class="p-4 space-y-3">
                                <?php foreach ($classStudents as $student): ?>
                                    <div class="student-card">
                                        <div class="student-header">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h6 class="font-semibold text-gray-900"><?= htmlspecialchars($student['full_name']) ?></h6>
                                                    <p class="text-sm text-gray-600 mt-1">
                                                        <?= htmlspecialchars($student['student_number']) ?>
                                                    </p>
                                                    <?php if ($student['status']): ?>
                                                        <p class="text-xs text-blue-600 mt-1">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            <?= date('d/m H:i', strtotime($student['recorded_at'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($student['status']): ?>
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                                        <?php
                                                        switch($student['status']) {
                                                            case 'present': echo 'bg-green-100 text-green-800'; break;
                                                            case 'absent': echo 'bg-red-100 text-red-800'; break;
                                                            case 'sick': echo 'bg-yellow-100 text-yellow-800'; break;
                                                            case 'permission': echo 'bg-blue-100 text-blue-800'; break;
                                                        }
                                                        ?>">
                                                        <?php
                                                        switch($student['status']) {
                                                            case 'present': echo 'Hadir'; break;
                                                            case 'absent': echo 'Tidak Hadir'; break;
                                                            case 'sick': echo 'Sakit'; break;
                                                            case 'permission': echo 'Izin'; break;
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="student-controls">
                                            <div class="grid grid-cols-4 gap-2">
                                                <button type="button" 
                                                        class="status-btn present <?= $student['status'] === 'present' ? 'active' : '' ?>"
                                                        onclick="setAttendance(<?= $student['id'] ?>, 'present', this)">
                                                    <i class="fas fa-check text-lg"></i>
                                                    <span>Hadir</span>
                                                </button>
                                                <button type="button" 
                                                        class="status-btn absent <?= $student['status'] === 'absent' ? 'active' : '' ?>"
                                                        onclick="setAttendance(<?= $student['id'] ?>, 'absent', this)">
                                                    <i class="fas fa-times text-lg"></i>
                                                    <span>Tidak</span>
                                                </button>
                                                <button type="button" 
                                                        class="status-btn sick <?= $student['status'] === 'sick' ? 'active' : '' ?>"
                                                        onclick="setAttendance(<?= $student['id'] ?>, 'sick', this)">
                                                    <i class="fas fa-thermometer-half text-lg"></i>
                                                    <span>Sakit</span>
                                                </button>
                                                <button type="button" 
                                                        class="status-btn permission <?= $student['status'] === 'permission' ? 'active' : '' ?>"
                                                        onclick="setAttendance(<?= $student['id'] ?>, 'permission', this)">
                                                    <i class="fas fa-hand-paper text-lg"></i>
                                                    <span>Izin</span>
                                                </button>
                                            </div>
                                            <input type="hidden" name="attendance[<?= $student['id'] ?>]" 
                                                   value="<?= htmlspecialchars($student['status'] ?? '') ?>" 
                                                   id="attendance_<?= $student['id'] ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>

            <!-- Floating Save Button -->
            <div class="floating-action">
                <button type="submit" form="attendanceForm" class="quick-action-btn flex items-center shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    <span class="hidden sm:inline">Simpan Absensi</span>
                    <span class="sm:hidden">Simpan</span>
                </button>
            </div>

        <?php elseif ($selectedLevel && !empty($selectedClasses)): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span>Tidak ada siswa aktif di kelas <?= htmlspecialchars($selectedLevel) ?> <?= implode(', ', array_map('htmlspecialchars', $selectedClasses)) ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 border border-gray-200 text-gray-700 p-6 rounded-lg text-center">
                <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                <h6 class="font-semibold text-gray-900 mb-2">Pilih Filter untuk Mulai</h6>
                <p class="text-sm text-gray-600">Pilih tanggal, jenjang, dan kelas untuk menampilkan daftar siswa</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    // Mobile-optimized JavaScript
    function setAttendance(studentId, status, button) {
        // Remove active class from all buttons in this row
        const controls = button.closest('.student-controls');
        const buttons = controls.querySelectorAll('.status-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        button.classList.add('active');
        
        // Update hidden input
        document.getElementById('attendance_' + studentId).value = status;
        
        // Add haptic feedback on mobile
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
        
        // Show visual feedback
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);
    }

    function setAllAttendance(status) {
        const students = document.querySelectorAll('[id^="attendance_"]');
        let count = 0;
        
        students.forEach(input => {
            const controls = input.closest('.student-controls');
            const buttons = controls.querySelectorAll('.status-btn');
            
            // Remove active from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Add active to the correct button
            const targetButton = controls.querySelector('.status-btn.' + status);
            if (targetButton) {
                targetButton.classList.add('active');
            }
            
            // Update input value
            input.value = status;
            count++;
        });
        
        showToast(`${count} siswa diset ${getStatusLabel(status)}`, 'success');
    }

    function clearAllAttendance() {
        const students = document.querySelectorAll('[id^="attendance_"]');
        let count = 0;
        
        students.forEach(input => {
            const controls = input.closest('.student-controls');
            const buttons = controls.querySelectorAll('.status-btn');
            
            // Remove active from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Clear input value
            input.value = '';
            count++;
        });
        
        showToast(`${count} siswa direset`, 'info');
    }

    function setClassAttendance(className, status) {
        // Find all students in the specified class by checking their student info
        const students = document.querySelectorAll('[id^="attendance_"]');
        let count = 0;
        
        students.forEach(input => {
            const studentCard = input.closest('.student-card');
            const studentInfo = studentCard.querySelector('.student-header p');
            
            // Check if this student is in the target class by looking at the class section
            const classSection = studentCard.closest('.class-section');
            const classHeader = classSection.querySelector('.class-header h6');
            
            if (classHeader && classHeader.textContent.includes(`Kelas ${className}`)) {
                const controls = input.closest('.student-controls');
                const buttons = controls.querySelectorAll('.status-btn');
                
                // Remove active from all buttons
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Add active to the correct button
                const targetButton = controls.querySelector('.status-btn.' + status);
                if (targetButton) {
                    targetButton.classList.add('active');
                }
                
                // Update input value
                input.value = status;
                count++;
            }
        });
        
        if (count > 0) {
            showToast(`Kelas ${className}: ${count} siswa diset ${getStatusLabel(status)}`, 'success');
        }
    }

    function getStatusLabel(status) {
        const labels = {
            'present': 'hadir',
            'absent': 'tidak hadir',
            'sick': 'sakit',
            'permission': 'izin'
        };
        return labels[status] || status;
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white p-4 rounded-lg shadow-lg`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                <span class="text-sm">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Hide toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }

    // Auto-submit form when date changes
    document.getElementById('date').addEventListener('change', function() {
        this.form.submit();
    });

    // Form validation with better UX
    const attendanceForm = document.getElementById('attendanceForm');
    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('[name^="attendance["]');
            let hasAttendance = false;
            let attendanceCount = 0;
            
            inputs.forEach(input => {
                if (input.value) {
                    hasAttendance = true;
                    attendanceCount++;
                }
            });
            
            if (!hasAttendance) {
                e.preventDefault();
                showToast('Harap isi minimal satu absensi siswa sebelum menyimpan', 'error');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                submitBtn.disabled = true;
            }
            
            showToast(`Menyimpan absensi ${attendanceCount} siswa...`, 'info');
        });
    }

    // Show initial toast if there's a message
    <?php if ($message): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if (toast) {
                setTimeout(() => toast.classList.add('show'), 500);
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 4000);
            }
        });
    <?php endif; ?>

    // Smooth scroll to form when students are loaded
    <?php if (!empty($students)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceCard = document.querySelector('.attendance-card');
            if (attendanceCard && window.innerWidth <= 768) {
                setTimeout(() => {
                    attendanceCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 500);
            }
        });
    <?php endif; ?>

    // Handle multi-select better on mobile
    const classesSelect = document.getElementById('classes');
    if (classesSelect && 'ontouchstart' in window) {
        classesSelect.addEventListener('change', function() {
            const selectedCount = this.selectedOptions.length;
            if (selectedCount > 0) {
                showToast(`${selectedCount} kelas dipilih`, 'info');
            }
        });
    }
</script>
</body>
</html>