<?php
/**
 * Attendance Reports and Statistics
 * Comprehensive attendance reporting with trends and alerts
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

// Get report parameters
$reportType = $_GET['type'] ?? 'monthly';
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$level = $_GET['level'] ?? '';

// Generate report data
$filters = [
    'month' => $month,
    'year' => $year,
    'level' => $level ?: null
];

$reportData = generateAttendanceReport($reportType, $filters);

// Get trends data
$studentTrends = getAttendanceTrends(6, 'student');
$mentorTrends = getAttendanceTrends(6, 'mentor');

// Get low attendance alerts
$lowAttendanceStudents = getStudentsWithLowAttendance(75, $month, $year);
$lowAttendanceMentors = getMentorsWithLowAttendance(80, $month, $year);

// Get overall statistics
$stats = getAttendanceStatistics();

// Month names for display
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Bimbel Al-Muhajirin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .report-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }
        .report-body {
            padding: 1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .attendance-rate {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .trend-chart {
            height: 300px;
            margin: 1rem 0;
        }
        .alert-item {
            padding: 0.75rem;
            border-left: 4px solid #dc3545;
            background-color: #f8d7da;
            margin-bottom: 0.5rem;
            border-radius: 0 4px 4px 0;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .attendance-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .attendance-excellent {
            background-color: #d4edda;
            color: #155724;
        }
        .attendance-good {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .attendance-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .attendance-poor {
            background-color: #f8d7da;
            color: #721c24;
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
                    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Laporan Absensi</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='absensi_siswa.php'">
                                <i class="fas fa-user-check me-1"></i>Absensi Siswa
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='absensi_mentor.php'">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Absensi Mentor
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Cetak Laporan
                        </button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Jenis Laporan</label>
                            <select class="form-select" id="type" name="type">
                                <option value="monthly" <?= $reportType === 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                                <option value="weekly" <?= $reportType === 'weekly' ? 'selected' : '' ?>>Mingguan</option>
                                <option value="daily" <?= $reportType === 'daily' ? 'selected' : '' ?>>Harian</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month">
                                <?php foreach ($monthNames as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= $month == $num ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Tahun</label>
                            <select class="form-select" id="year" name="year">
                                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="level" class="form-label">Jenjang</label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Semua Jenjang</option>
                                <option value="SD" <?= $level === 'SD' ? 'selected' : '' ?>>SD</option>
                                <option value="SMP" <?= $level === 'SMP' ? 'selected' : '' ?>>SMP</option>
                                <option value="SMA" <?= $level === 'SMA' ? 'selected' : '' ?>>SMA</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-search me-1"></i>Tampilkan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistics Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h6>Kehadiran Siswa Hari Ini</h6>
                        <div class="attendance-rate"><?= $stats['today']['students']['attendance_rate'] ?? 0 ?>%</div>
                        <small><?= $stats['today']['students']['present'] ?? 0 ?> dari <?= $stats['today']['students']['recorded'] ?? 0 ?> siswa</small>
                    </div>
                    <div class="stat-card">
                        <h6>Kehadiran Mentor Hari Ini</h6>
                        <div class="attendance-rate"><?= $stats['today']['mentors']['attendance_rate'] ?? 0 ?>%</div>
                        <small><?= $stats['today']['mentors']['sessions_present'] ?? 0 ?> dari <?= $stats['today']['mentors']['total_sessions'] ?? 0 ?> sesi</small>
                    </div>
                    <div class="stat-card warning">
                        <h6>Siswa Kehadiran Rendah</h6>
                        <div class="attendance-rate"><?= count($lowAttendanceStudents) ?></div>
                        <small>< 75% bulan ini</small>
                    </div>
                    <div class="stat-card warning">
                        <h6>Mentor Kehadiran Rendah</h6>
                        <div class="attendance-rate"><?= count($lowAttendanceMentors) ?></div>
                        <small>< 80% bulan ini</small>
                    </div>
                </div>

                <!-- Attendance Trends Chart -->
                <div class="report-card">
                    <div class="report-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Tren Kehadiran 6 Bulan Terakhir</h5>
                    </div>
                    <div class="report-body">
                        <canvas id="trendsChart" class="trend-chart"></canvas>
                    </div>
                </div>

                <!-- Low Attendance Alerts -->
                <?php if (!empty($lowAttendanceStudents) || !empty($lowAttendanceMentors)): ?>
                    <div class="report-card">
                        <div class="report-header">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Peringatan Kehadiran Rendah</h5>
                        </div>
                        <div class="report-body">
                            <?php if (!empty($lowAttendanceStudents)): ?>
                                <h6>Siswa dengan Kehadiran < 75%</h6>
                                <?php foreach ($lowAttendanceStudents as $student): ?>
                                    <div class="alert-item">
                                        <strong><?= htmlspecialchars($student['full_name']) ?></strong> 
                                        (<?= htmlspecialchars($student['student_number']) ?>) - 
                                        <?= $student['level'] ?> Kelas <?= $student['class'] ?>
                                        <br>
                                        <small>
                                            Kehadiran: <?= $student['attendance_rate'] ?>% 
                                            (<?= $student['present_days'] ?>/<?= $student['total_days'] ?> hari) - 
                                            Orang Tua: <?= htmlspecialchars($student['parent_name']) ?> 
                                            (<?= htmlspecialchars($student['parent_phone']) ?>)
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (!empty($lowAttendanceMentors)): ?>
                                <h6 class="mt-3">Mentor dengan Kehadiran < 80%</h6>
                                <?php foreach ($lowAttendanceMentors as $mentor): ?>
                                    <div class="alert-item">
                                        <strong><?= htmlspecialchars($mentor['full_name']) ?></strong> 
                                        (<?= htmlspecialchars($mentor['mentor_code']) ?>)
                                        <br>
                                        <small>
                                            Kehadiran: <?= $mentor['attendance_rate'] ?>% 
                                            (<?= $mentor['present_sessions'] ?>/<?= $mentor['total_sessions'] ?> sesi) - 
                                            Kontak: <?= htmlspecialchars($mentor['phone']) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Monthly Report Tables -->
                <?php if ($reportType === 'monthly' && isset($reportData['data']['students'])): ?>
                    <!-- Student Attendance Report -->
                    <div class="report-card">
                        <div class="report-header">
                            <h5><i class="fas fa-users me-2"></i>Laporan Kehadiran Siswa - <?= $monthNames[$month] ?> <?= $year ?></h5>
                        </div>
                        <div class="report-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Siswa</th>
                                            <th>Nama Lengkap</th>
                                            <th>Jenjang</th>
                                            <th>Kelas</th>
                                            <th>Total Hari</th>
                                            <th>Hadir</th>
                                            <th>Tidak Hadir</th>
                                            <th>Sakit</th>
                                            <th>Izin</th>
                                            <th>Tingkat Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData['data']['students'] as $index => $student): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                                <td><?= htmlspecialchars($student['level']) ?></td>
                                                <td><?= htmlspecialchars($student['class']) ?></td>
                                                <td><?= $student['total_days'] ?></td>
                                                <td class="text-success"><?= $student['present_days'] ?></td>
                                                <td class="text-danger"><?= $student['absent_days'] ?></td>
                                                <td class="text-warning"><?= $student['sick_days'] ?></td>
                                                <td class="text-info"><?= $student['permission_days'] ?></td>
                                                <td>
                                                    <?php
                                                    $rate = $student['attendance_rate'] ?? 0;
                                                    $badgeClass = 'attendance-excellent';
                                                    if ($rate < 60) $badgeClass = 'attendance-poor';
                                                    elseif ($rate < 75) $badgeClass = 'attendance-warning';
                                                    elseif ($rate < 90) $badgeClass = 'attendance-good';
                                                    ?>
                                                    <span class="attendance-badge <?= $badgeClass ?>"><?= $rate ?>%</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Mentor Attendance Report -->
                    <?php if (!empty($reportData['data']['mentors'])): ?>
                        <div class="report-card">
                            <div class="report-header">
                                <h5><i class="fas fa-chalkboard-teacher me-2"></i>Laporan Kehadiran Mentor - <?= $monthNames[$month] ?> <?= $year ?></h5>
                            </div>
                            <div class="report-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>No</th>
                                                <th>Kode Mentor</th>
                                                <th>Nama Lengkap</th>
                                                <th>Jenjang</th>
                                                <th>Total Sesi</th>
                                                <th>Hadir</th>
                                                <th>Total Jam</th>
                                                <th>Total Honor</th>
                                                <th>Tingkat Kehadiran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportData['data']['mentors'] as $index => $mentor): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($mentor['mentor_code']) ?></td>
                                                    <td><?= htmlspecialchars($mentor['full_name']) ?></td>
                                                    <td><?= htmlspecialchars($mentor['level']) ?></td>
                                                    <td><?= $mentor['total_sessions'] ?></td>
                                                    <td class="text-success"><?= $mentor['present_sessions'] ?></td>
                                                    <td><?= $mentor['total_hours'] ?> jam</td>
                                                    <td><?= formatCurrency($mentor['total_payment']) ?></td>
                                                    <td>
                                                        <?php
                                                        $rate = $mentor['attendance_rate'] ?? 0;
                                                        $badgeClass = 'attendance-excellent';
                                                        if ($rate < 60) $badgeClass = 'attendance-poor';
                                                        elseif ($rate < 80) $badgeClass = 'attendance-warning';
                                                        elseif ($rate < 95) $badgeClass = 'attendance-good';
                                                        ?>
                                                        <span class="attendance-badge <?= $badgeClass ?>"><?= $rate ?>%</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Attendance Trends Chart
        const ctx = document.getElementById('trendsChart').getContext('2d');
        
        const studentTrends = <?= json_encode($studentTrends) ?>;
        const mentorTrends = <?= json_encode($mentorTrends) ?>;
        
        const labels = studentTrends.map(item => item.month);
        const studentRates = studentTrends.map(item => item.attendance_rate || 0);
        const mentorRates = mentorTrends.map(item => item.attendance_rate || 0);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kehadiran Siswa (%)',
                    data: studentRates,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Kehadiran Mentor (%)',
                    data: mentorRates,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tren Kehadiran Bulanan'
                    }
                }
            }
        });

        // Print styles
        window.addEventListener('beforeprint', function() {
            document.body.classList.add('printing');
        });

        window.addEventListener('afterprint', function() {
            document.body.classList.remove('printing');
        });
    </script>

    <style media="print">
        .btn, .filter-section, .sidebar, .navbar {
            display: none !important;
        }
        .main {
            margin-left: 0 !important;
        }
        .report-card {
            break-inside: avoid;
            margin-bottom: 1rem;
        }
        .table {
            font-size: 0.8rem;
        }
    </style>
</body>
</html>