<?php
/**
 * Bimbel Dashboard
 * Main dashboard for bimbel management system with KPIs, statistics, and quick actions
 */

require_once '../../config/config.php';
require_once '../../includes/bimbel_functions.php';
require_once '../../config/auth.php';

// Check authentication and bimbel access
requireLogin();
requirePermission('bimbel_management', 'read');

$pageTitle = 'Dashboard Bimbel';
$currentPage = 'dashboard';
$canModify = ($_SESSION['role'] === 'admin_bimbel');

// Get current date info
$currentMonth = date('n');
$currentYear = date('Y');
$currentMonthName = date('F Y');

// Get dashboard statistics
$studentStats = getStudentStatistics();
$mentorStats = getMentorStatistics();
$financialStats = getFinancialStatistics();
$attendanceStats = getAttendanceStatistics();

// Get financial summary for current month
$financialSummary = getFinancialSummary($currentMonth, $currentYear);

// Get real-time balance
$balanceInfo = calculateRealTimeBalance();

// Get recent activities (recent transactions, payments, registrations)
$recentTransactions = getFinancialTransactions(1, 5)['data'];
$recentSPPPayments = getRecentSPPPayments(5);

// Get outstanding payments
$outstandingPayments = getOutstandingSPPPayments();

// Get students with low attendance
$lowAttendanceStudents = getStudentsWithLowAttendance();

// Get monthly recap for current month (if exists)
$currentMonthRecap = getMonthlyRecap($currentMonth, $currentYear);

// Get projections for next 3 months
$projections = calculateFinancialProjections(3);

include '../../partials/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'partials/bimbel_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard Bimbel Al-Muhajirin
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="badge bg-info fs-6">
                            <i class="fas fa-calendar me-1"></i>
                            <?= $currentMonthName ?>
                        </span>
                    </div>
                    <?php if ($canModify): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-1"></i>
                                Quick Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="siswa.php"><i class="fas fa-user-plus me-2"></i>Tambah Siswa</a></li>
                                <li><a class="dropdown-item" href="mentor.php"><i class="fas fa-chalkboard-teacher me-2"></i>Tambah Mentor</a></li>
                                <li><a class="dropdown-item" href="spp.php"><i class="fas fa-money-bill me-2"></i>Catat Pembayaran</a></li>
                                <li><a class="dropdown-item" href="keuangan.php"><i class="fas fa-plus-circle me-2"></i>Tambah Transaksi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="rekap_bulanan.php"><i class="fas fa-chart-line me-2"></i>Generate Rekap</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Siswa Aktif
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= ($studentStats['by_status']['active'] ?? 0) ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        SD: <?= ($studentStats['by_level']['SD'] ?? 0) ?> | 
                                        SMP: <?= ($studentStats['by_level']['SMP'] ?? 0) ?> | 
                                        SMA: <?= ($studentStats['by_level']['SMA'] ?? 0) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Pemasukan Bulan Ini
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= formatCurrency($financialStats['current_month']['income']) ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        SPP: <?= formatCurrency($financialSummary['income']['by_category']['spp']['amount'] ?? 0) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Saldo Total
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= formatCurrency($balanceInfo['current_balance']) ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        Net bulan ini: <?= formatCurrency($financialStats['current_month']['net']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Tunggakan SPP
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= count($outstandingPayments) ?>
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        Total: <?= formatCurrency(array_sum(array_column($outstandingPayments, 'monthly_fee'))) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Statistics Row -->
            <div class="row mb-4">
                <!-- Financial Overview Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-area me-2"></i>
                                Ringkasan Keuangan Bulanan
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($projections['projections'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Proyeksi Pemasukan</th>
                                                <th>Proyeksi Pengeluaran</th>
                                                <th>Net</th>
                                                <th>Saldo Proyeksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-info">
                                                <td><strong>Saat Ini</strong></td>
                                                <td><?= formatCurrency($financialStats['current_month']['income']) ?></td>
                                                <td><?= formatCurrency($financialStats['current_month']['expense']) ?></td>
                                                <td class="<?= $financialStats['current_month']['net'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= formatCurrency($financialStats['current_month']['net']) ?>
                                                </td>
                                                <td class="<?= $balanceInfo['current_balance'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                                    <?= formatCurrency($balanceInfo['current_balance']) ?>
                                                </td>
                                            </tr>
                                            <?php foreach ($projections['projections'] as $projection): ?>
                                                <tr>
                                                    <td><?= $projection['month_name'] ?></td>
                                                    <td class="text-success"><?= formatCurrency($projection['projected_income']) ?></td>
                                                    <td class="text-danger"><?= formatCurrency($projection['projected_expense']) ?></td>
                                                    <td class="<?= $projection['net_projection'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= formatCurrency($projection['net_projection']) ?>
                                                    </td>
                                                    <td class="<?= $projection['projected_balance'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                                        <?= formatCurrency($projection['projected_balance']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Data proyeksi tidak tersedia</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Mentor & Attendance Stats -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                Statistik Mentor & Kehadiran
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Mentor Aktif:</span>
                                    <strong><?= ($mentorStats['by_status']['active'] ?? 0) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Rata-rata Honor:</span>
                                    <strong><?= formatCurrency($mentorStats['average_rate'] ?? 0) ?></strong>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="text-muted">Mentor per Jenjang:</h6>
                                <?php foreach (['SD', 'SMP', 'SMA'] as $level): ?>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?= $level ?>:</span>
                                        <span><?= ($mentorStats['by_level'][$level] ?? 0) ?> mentor</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($attendanceStats)): ?>
                                <hr>
                                <div>
                                    <h6 class="text-muted">Kehadiran Bulan Ini:</h6>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Tingkat Kehadiran Siswa:</span>
                                        <span class="badge bg-<?= ($attendanceStats['student_attendance_rate'] ?? 0) >= 80 ? 'success' : 'warning' ?>">
                                            <?= number_format($attendanceStats['student_attendance_rate'] ?? 0, 1) ?>%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Tingkat Kehadiran Mentor:</span>
                                        <span class="badge bg-<?= ($attendanceStats['mentor_attendance_rate'] ?? 0) >= 85 ? 'success' : 'warning' ?>">
                                            <?= number_format($attendanceStats['mentor_attendance_rate'] ?? 0, 1) ?>%
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts and Notifications Row -->
            <div class="row mb-4">
                <!-- Outstanding Payments Alert -->
                <?php if (!empty($outstandingPayments)): ?>
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Tunggakan SPP (<?= count($outstandingPayments) ?> siswa)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Siswa</th>
                                                <th>Level</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($outstandingPayments, 0, 10) as $payment): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($payment['full_name']) ?></td>
                                                    <td><span class="badge bg-secondary"><?= $payment['level'] ?></span></td>
                                                    <td class="text-danger"><?= formatCurrency($payment['monthly_fee']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($outstandingPayments) > 10): ?>
                                    <div class="text-center mt-2">
                                        <a href="spp_monitoring.php" class="btn btn-sm btn-outline-warning">
                                            Lihat Semua (<?= count($outstandingPayments) - 10 ?> lainnya)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Low Attendance Alert -->
                <?php if (!empty($lowAttendanceStudents)): ?>
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-danger">
                                    <i class="fas fa-user-times me-2"></i>
                                    Kehadiran Rendah (<?= count($lowAttendanceStudents) ?> siswa)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Siswa</th>
                                                <th>Level</th>
                                                <th>Kehadiran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($lowAttendanceStudents, 0, 10) as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                                    <td><span class="badge bg-secondary"><?= $student['level'] ?></span></td>
                                                    <td>
                                                        <span class="badge bg-danger">
                                                            <?= number_format($student['attendance_rate'], 1) ?>%
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($lowAttendanceStudents) > 10): ?>
                                    <div class="text-center mt-2">
                                        <a href="laporan_absensi.php" class="btn btn-sm btn-outline-danger">
                                            Lihat Semua (<?= count($lowAttendanceStudents) - 10 ?> lainnya)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- System Alerts -->
                <?php if (empty($outstandingPayments) && empty($lowAttendanceStudents)): ?>
                    <div class="col-12">
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>
                                Sistem Berjalan Baik!
                            </h4>
                            <p>Tidak ada tunggakan SPP dan semua siswa memiliki tingkat kehadiran yang baik.</p>
                            <hr>
                            <p class="mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Terakhir diperbarui: <?= date('d/m/Y H:i') ?>
                                </small>
                            </p>
                        </div>
                    </div>
                <?php elseif ($balanceInfo['current_balance'] < 0): ?>
                    <div class="col-12">
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Perhatian: Saldo Negatif
                            </h4>
                            <p>Saldo saat ini: <strong><?= formatCurrency($balanceInfo['current_balance']) ?></strong></p>
                            <p>Harap segera lakukan penagihan SPP atau kurangi pengeluaran.</p>
                            <hr>
                            <a href="spp_monitoring.php" class="btn btn-warning btn-sm me-2">
                                <i class="fas fa-money-bill me-1"></i>
                                Cek Tunggakan SPP
                            </a>
                            <a href="keuangan.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-chart-line me-1"></i>
                                Lihat Keuangan
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activities Row -->
            <div class="row mb-4">
                <!-- Recent Transactions -->
                <div class="col-xl-6 col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list me-2"></i>
                                Transaksi Terbaru
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentTransactions)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Deskripsi</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentTransactions as $transaction): ?>
                                                <tr>
                                                    <td><?= date('d/m', strtotime($transaction['transaction_date'])) ?></td>
                                                    <td>
                                                        <small><?= htmlspecialchars(substr($transaction['description'], 0, 30)) ?><?= strlen($transaction['description']) > 30 ? '...' : '' ?></small>
                                                    </td>
                                                    <td class="text-<?= $transaction['transaction_type'] === 'income' ? 'success' : 'danger' ?>">
                                                        <?= $transaction['transaction_type'] === 'income' ? '+' : '-' ?>
                                                        <?= formatCurrency($transaction['amount']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <a href="keuangan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada transaksi</p>
                                    <?php if ($canModify): ?>
                                        <a href="keuangan.php" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Tambah Transaksi
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent SPP Payments -->
                <div class="col-xl-6 col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Pembayaran SPP Terbaru
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentSPPPayments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Siswa</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentSPPPayments as $payment): ?>
                                                <tr>
                                                    <td><?= date('d/m', strtotime($payment['payment_date'])) ?></td>
                                                    <td>
                                                        <small>
                                                            <?= htmlspecialchars($payment['student_name']) ?>
                                                            <span class="badge bg-secondary"><?= $payment['level'] ?></span>
                                                        </small>
                                                    </td>
                                                    <td class="text-success">
                                                        <?= formatCurrency($payment['amount']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <a href="spp.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-money-bill-wave fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada pembayaran SPP</p>
                                    <?php if ($canModify): ?>
                                        <a href="spp.php" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus me-1"></i>
                                            Catat Pembayaran
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-pie me-2"></i>
                                Statistik Cepat
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center mb-3">
                                        <div class="h4 text-primary"><?= ($studentStats['recent_registrations'] ?? 0) ?></div>
                                        <div class="text-xs text-muted">Pendaftaran Baru (30 hari)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center mb-3">
                                        <div class="h4 text-success"><?= count($financialStats['transactions_by_category'] ?? []) ?></div>
                                        <div class="text-xs text-muted">Kategori Transaksi Bulan Ini</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center mb-3">
                                        <div class="h4 text-info"><?= count($recentTransactions) ?></div>
                                        <div class="text-xs text-muted">Transaksi Terbaru</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center mb-3">
                                        <div class="h4 text-warning"><?= count($recentSPPPayments) ?></div>
                                        <div class="text-xs text-muted">Pembayaran SPP Terbaru</div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($financialStats['transactions_by_category'])): ?>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Kategori Transaksi Bulan Ini:</h6>
                                        <?php foreach ($financialStats['transactions_by_category'] as $category => $count): ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-capitalize"><?= str_replace('_', ' ', $category) ?>:</span>
                                                <span class="badge bg-secondary"><?= $count ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <?php if ($currentMonthRecap): ?>
                                                <a href="rekap_bulanan.php?view_month=<?= $currentMonth ?>&view_year=<?= $currentYear ?>" 
                                                   class="btn btn-outline-info mb-2">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    Lihat Rekap Bulan Ini
                                                </a>
                                            <?php elseif ($canModify): ?>
                                                <a href="rekap_bulanan.php" class="btn btn-outline-warning mb-2">
                                                    <i class="fas fa-plus me-1"></i>
                                                    Generate Rekap Bulan Ini
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($canModify): ?>
                                                <br>
                                                <a href="keuangan.php" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Lihat Semua Keuangan
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle me-2"></i>
                                Status Sistem
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="text-success mb-2">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <h6>Database</h6>
                                        <small class="text-muted">Terhubung</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="text-success mb-2">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                        <h6>Siswa Aktif</h6>
                                        <small class="text-muted"><?= ($studentStats['by_status']['active'] ?? 0) ?> siswa</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="text-success mb-2">
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                        </div>
                                        <h6>Mentor Aktif</h6>
                                        <small class="text-muted"><?= ($mentorStats['by_status']['active'] ?? 0) ?> mentor</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="<?= $balanceInfo['current_balance'] >= 0 ? 'text-success' : 'text-warning' ?> mb-2">
                                            <i class="fas fa-wallet fa-2x"></i>
                                        </div>
                                        <h6>Saldo</h6>
                                        <small class="text-muted"><?= $balanceInfo['current_balance'] >= 0 ? 'Positif' : 'Perlu Perhatian' ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-xs {
    font-size: 0.75rem;
}
.font-weight-bold {
    font-weight: 700 !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>

<?php include '../../partials/admin_footer.php'; ?>