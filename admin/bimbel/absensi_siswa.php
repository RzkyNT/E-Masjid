<?php
/**
 * Student Attendance Management
 * Interface for recording and managing student attendance by class
 */

require_once '../../config/config.php';
require_once '../../includes/session_check.php';
require_once '../../includes/access_control.php';
require_once '../../includes/bimbel_functions.php';

// Check access - only admin_bimbel can access

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'record_attendance':
                $date = $_POST['attendance_date'] ?? '';
                $level = $_POST['level'] ?? '';
                $class = $_POST['class'] ?? '';
                $attendanceData = $_POST['attendance'] ?? [];
                
                if (empty($date) || empty($level) || empty($class)) {
                    $message = 'Tanggal, jenjang, dan kelas harus diisi';
                    $messageType = 'error';
                } else {
                    $result = recordStudentAttendance($date, $level, $class, $attendanceData);
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

// Get students for selected class if level and class are selected
$students = [];
if ($selectedLevel && $selectedClass) {
    $students = getStudentAttendanceByClass($selectedDate, $selectedLevel, $selectedClass);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .attendance-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .attendance-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }
        .student-row {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .student-row:last-child {
            border-bottom: none;
        }
        .student-info {
            flex: 1;
        }
        .attendance-controls {
            display: flex;
            gap: 0.5rem;
        }
        .status-btn {
            padding: 0.25rem 0.75rem;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .status-btn.active {
            color: white;
        }
        .status-btn.present.active {
            background-color: #28a745;
            border-color: #28a745;
        }
        .status-btn.absent.active {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .status-btn.sick.active {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .status-btn.permission.active {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .quick-filters {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'partials/bimbel_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/bimbel_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-check me-2"></i>Absensi Siswa</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='absensi_mentor.php'">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Absensi Mentor
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stats-card">
                            <h5><i class="fas fa-calendar-day me-2"></i>Absensi Hari Ini</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <h3><?= $stats['today']['students']['present'] ?? 0 ?></h3>
                                        <small>Siswa Hadir</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h3><?= $stats['today']['students']['attendance_rate'] ?? 0 ?>%</h3>
                                        <small>Tingkat Kehadiran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Kehadiran Bulanan</h5>
                                <?php if (isset($stats['monthly']['students'])): ?>
                                    <?php foreach ($stats['monthly']['students'] as $level => $data): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><?= $level ?></span>
                                            <span class="badge bg-primary"><?= $data['attendance_rate'] ?>%</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada data kehadiran bulan ini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="quick-filters">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="level" class="form-label">Jenjang</label>
                            <select class="form-select" id="level" name="level" required onchange="this.form.submit()">
                                <option value="">Pilih Jenjang</option>
                                <option value="SD" <?= $selectedLevel === 'SD' ? 'selected' : '' ?>>SD</option>
                                <option value="SMP" <?= $selectedLevel === 'SMP' ? 'selected' : '' ?>>SMP</option>
                                <option value="SMA" <?= $selectedLevel === 'SMA' ? 'selected' : '' ?>>SMA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="class" class="form-label">Kelas</label>
                            <select class="form-select" id="class" name="class" required <?= empty($classes) ? 'disabled' : '' ?>>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= htmlspecialchars($class) ?>" <?= $selectedClass === $class ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i>Tampilkan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Attendance Form -->
                <?php if (!empty($students)): ?>
                    <div class="attendance-card">
                        <div class="attendance-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                Absensi <?= htmlspecialchars($selectedLevel) ?> Kelas <?= htmlspecialchars($selectedClass) ?>
                                - <?= date('d/m/Y', strtotime($selectedDate)) ?>
                            </h5>
                            <small class="text-muted">Total: <?= count($students) ?> siswa</small>
                        </div>

                        <form method="POST" id="attendanceForm">
                            <input type="hidden" name="action" value="record_attendance">
                            <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>">
                            <input type="hidden" name="level" value="<?= htmlspecialchars($selectedLevel) ?>">
                            <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass) ?>">

                            <?php foreach ($students as $student): ?>
                                <div class="student-row">
                                    <div class="student-info">
                                        <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($student['student_number']) ?> - 
                                            <?= htmlspecialchars($student['level']) ?> Kelas <?= htmlspecialchars($student['class']) ?>
                                        </small>
                                        <?php if ($student['status']): ?>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-clock me-1"></i>
                                                Terakhir direkam: <?= date('d/m/Y H:i', strtotime($student['recorded_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="attendance-controls">
                                        <button type="button" 
                                                class="status-btn present <?= $student['status'] === 'present' ? 'active' : '' ?>"
                                                onclick="setAttendance(<?= $student['id'] ?>, 'present', this)">
                                            <i class="fas fa-check"></i> Hadir
                                        </button>
                                        <button type="button" 
                                                class="status-btn absent <?= $student['status'] === 'absent' ? 'active' : '' ?>"
                                                onclick="setAttendance(<?= $student['id'] ?>, 'absent', this)">
                                            <i class="fas fa-times"></i> Tidak Hadir
                                        </button>
                                        <button type="button" 
                                                class="status-btn sick <?= $student['status'] === 'sick' ? 'active' : '' ?>"
                                                onclick="setAttendance(<?= $student['id'] ?>, 'sick', this)">
                                            <i class="fas fa-thermometer-half"></i> Sakit
                                        </button>
                                        <button type="button" 
                                                class="status-btn permission <?= $student['status'] === 'permission' ? 'active' : '' ?>"
                                                onclick="setAttendance(<?= $student['id'] ?>, 'permission', this)">
                                            <i class="fas fa-hand-paper"></i> Izin
                                        </button>
                                        <input type="hidden" name="attendance[<?= $student['id'] ?>]" 
                                               value="<?= htmlspecialchars($student['status'] ?? '') ?>" 
                                               id="attendance_<?= $student['id'] ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="p-3 bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-outline-success me-2" onclick="setAllAttendance('present')">
                                            <i class="fas fa-check-double me-1"></i>Semua Hadir
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="clearAllAttendance()">
                                            <i class="fas fa-eraser me-1"></i>Reset
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-1"></i>Simpan Absensi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php elseif ($selectedLevel && $selectedClass): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada siswa aktif di kelas <?= htmlspecialchars($selectedLevel) ?> <?= htmlspecialchars($selectedClass) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        <i class="fas fa-arrow-up me-2"></i>
                        Pilih tanggal, jenjang, dan kelas untuk menampilkan daftar siswa
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setAttendance(studentId, status, button) {
            // Remove active class from all buttons in this row
            const row = button.closest('.student-row');
            const buttons = row.querySelectorAll('.status-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Update hidden input
            document.getElementById('attendance_' + studentId).value = status;
        }

        function setAllAttendance(status) {
            const students = document.querySelectorAll('[id^="attendance_"]');
            students.forEach(input => {
                const studentId = input.id.replace('attendance_', '');
                const row = input.closest('.student-row');
                const buttons = row.querySelectorAll('.status-btn');
                
                // Remove active from all buttons
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Add active to the correct button
                const targetButton = row.querySelector('.status-btn.' + status);
                if (targetButton) {
                    targetButton.classList.add('active');
                }
                
                // Update input value
                input.value = status;
            });
        }

        function clearAllAttendance() {
            const students = document.querySelectorAll('[id^="attendance_"]');
            students.forEach(input => {
                const row = input.closest('.student-row');
                const buttons = row.querySelectorAll('.status-btn');
                
                // Remove active from all buttons
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Clear input value
                input.value = '';
            });
        }

        // Auto-submit form when date changes
        document.getElementById('date').addEventListener('change', function() {
            this.form.submit();
        });

        // Validate form before submission
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('[name^="attendance["]');
            let hasAttendance = false;
            
            inputs.forEach(input => {
                if (input.value) {
                    hasAttendance = true;
                }
            });
            
            if (!hasAttendance) {
                e.preventDefault();
                alert('Harap isi minimal satu absensi siswa sebelum menyimpan.');
                return false;
            }
        });
    </script>
</body>
</html>