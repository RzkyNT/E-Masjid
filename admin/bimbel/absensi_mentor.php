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

include '../../partials/admin_header.php';
?>

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 main-content content-with-sidebar">
    <div class="p-6">

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
                $status = $_POST['status'] ?? '';
                $hoursTaught = $_POST['hours_taught'] ?? 0;
                $notes = $_POST['notes'] ?? '';
                
                if (empty($date) || empty($mentorId) || empty($level) || empty($status)) {
                    $message = 'Tanggal, mentor, jenjang, dan status harus diisi';
                    $messageType = 'error';
                } else {
                    $result = recordMentorAttendance($date, $mentorId, $level, $status, $hoursTaught, $notes);
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

// Get mentor attendance for selected date and level
$mentorAttendance = [];
if ($selectedDate) {
    $mentorAttendance = getMentorAttendanceByDate($selectedDate, $selectedLevel);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .mentor-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        .mentor-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .mentor-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
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
        }
        .status-present {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-sick {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-permission {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-not-recorded {
            background-color: #e2e3e5;
            color: #6c757d;
            border: 1px solid #d6d8db;
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
        .payment-info {
            background-color: #e8f5e8;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .level-badge {
            background-color: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-right: 0.25rem;
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
                    <h1 class="h2"><i class="fas fa-chalkboard-teacher me-2"></i>Absensi Mentor</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='absensi_siswa.php'">
                                <i class="fas fa-user-check me-1"></i>Absensi Siswa
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                                <i class="fas fa-plus me-1"></i>Tambah Absensi
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
                            <h5><i class="fas fa-calendar-day me-2"></i>Absensi Mentor Hari Ini</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center">
                                        <h3><?= $stats['today']['mentors']['sessions_present'] ?? 0 ?></h3>
                                        <small>Sesi Hadir</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h3><?= $stats['today']['mentors']['total_hours'] ?? 0 ?></h3>
                                        <small>Total Jam</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-percentage me-2"></i>Tingkat Kehadiran</h5>
                                <div class="text-center">
                                    <h3 class="text-primary"><?= $stats['today']['mentors']['attendance_rate'] ?? 0 ?>%</h3>
                                    <small class="text-muted">
                                        <?= $stats['today']['mentors']['sessions_present'] ?? 0 ?> dari 
                                        <?= $stats['today']['mentors']['total_sessions'] ?? 0 ?> sesi
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="quick-filters">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="level" class="form-label">Filter Jenjang</label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Semua Jenjang</option>
                                <option value="SD" <?= $selectedLevel === 'SD' ? 'selected' : '' ?>>SD</option>
                                <option value="SMP" <?= $selectedLevel === 'SMP' ? 'selected' : '' ?>>SMP</option>
                                <option value="SMA" <?= $selectedLevel === 'SMA' ? 'selected' : '' ?>>SMA</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i>Tampilkan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Mentor Attendance List -->
                <div class="row">
                    <?php if (!empty($mentorAttendance)): ?>
                        <?php 
                        $groupedByMentor = [];
                        foreach ($mentorAttendance as $attendance) {
                            $mentorId = $attendance['id'];
                            if (!isset($groupedByMentor[$mentorId])) {
                                $groupedByMentor[$mentorId] = [
                                    'mentor' => $attendance,
                                    'levels' => []
                                ];
                            }
                            $groupedByMentor[$mentorId]['levels'][$attendance['teaching_level']] = $attendance;
                        }
                        ?>
                        
                        <?php foreach ($groupedByMentor as $mentorId => $data): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="mentor-card">
                                    <div class="mentor-header">
                                        <h6 class="mb-1"><?= htmlspecialchars($data['mentor']['full_name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($data['mentor']['mentor_code']) ?></small>
                                        <div class="mt-2">
                                            <?php foreach ($data['mentor']['teaching_levels'] as $level): ?>
                                                <span class="level-badge"><?= $level ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="mentor-body">
                                        <?php foreach ($data['levels'] as $level => $attendance): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong><?= $level ?></strong>
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
                                                        <small>
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= $attendance['hours_taught'] ?> jam × 
                                                            <?= formatCurrency($attendance['hourly_rate']) ?> = 
                                                            <strong><?= formatCurrency($attendance['hours_taught'] * $attendance['hourly_rate']) ?></strong>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($attendance['notes']): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            <?= htmlspecialchars($attendance['notes']) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editAttendance(<?= $mentorId ?>, '<?= $level ?>', '<?= $selectedDate ?>')">
                                                        <i class="fas fa-edit me-1"></i>
                                                        <?= $attendance['status'] ? 'Edit' : 'Rekam' ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Tidak ada data mentor untuk tanggal <?= date('d/m/Y', strtotime($selectedDate)) ?>
                                <?= $selectedLevel ? " jenjang $selectedLevel" : "" ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Attendance Modal -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rekam Absensi Mentor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="record_attendance">
                        
                        <div class="mb-3">
                            <label for="attendance_date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="attendance_date" name="attendance_date" 
                                   value="<?= htmlspecialchars($selectedDate) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mentor_id" class="form-label">Mentor</label>
                            <select class="form-select" id="mentor_id" name="mentor_id" required onchange="updateLevelOptions()">
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
                        
                        <div class="mb-3">
                            <label for="level" class="form-label">Jenjang</label>
                            <select class="form-select" id="modal_level" name="level" required disabled>
                                <option value="">Pilih jenjang yang diajar</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Kehadiran</label>
                            <select class="form-select" id="status" name="status" required onchange="toggleHoursField()">
                                <option value="">Pilih Status</option>
                                <option value="present">Hadir</option>
                                <option value="absent">Tidak Hadir</option>
                                <option value="sick">Sakit</option>
                                <option value="permission">Izin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="hoursField" style="display: none;">
                            <label for="hours_taught" class="form-label">Jam Mengajar</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="hours_taught" name="hours_taught" 
                                       min="0" max="8" step="0.5" placeholder="2.0">
                                <span class="input-group-text">jam</span>
                            </div>
                            <div class="form-text" id="paymentCalculation"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateLevelOptions() {
            const mentorSelect = document.getElementById('mentor_id');
            const levelSelect = document.getElementById('modal_level');
            const selectedOption = mentorSelect.options[mentorSelect.selectedIndex];
            
            // Clear existing options
            levelSelect.innerHTML = '<option value="">Pilih jenjang yang diajar</option>';
            
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
            }
            
            // Reset other fields
            document.getElementById('status').value = '';
            toggleHoursField();
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

        function editAttendance(mentorId, level, date) {
            // Set form values
            document.getElementById('attendance_date').value = date;
            document.getElementById('mentor_id').value = mentorId;
            
            // Update level options and select the level
            updateLevelOptions();
            setTimeout(() => {
                document.getElementById('modal_level').value = level;
            }, 100);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addAttendanceModal'));
            modal.show();
        }

        // Auto-submit form when date changes
        document.getElementById('date').addEventListener('change', function() {
            this.form.submit();
        });

        // Update payment calculation when hours change
        document.getElementById('hours_taught').addEventListener('input', updatePaymentCalculation);
    </script>
</body>
</html>