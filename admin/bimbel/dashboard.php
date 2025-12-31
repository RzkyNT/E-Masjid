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

// Get current user
$current_user = getCurrentUser();

$pageTitle = 'Dashboard Bimbel';
$page_title = 'Dashboard Bimbel';
$page_description = 'Sistem Manajemen Bimbel Al-Muhajirin';
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

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 main-content content-with-sidebar">
    <div class="p-6">

<!-- Welcome Section -->
<div class="bg-white overflow-hidden shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Selamat datang di Dashboard Bimbel, <?php echo htmlspecialchars($current_user['full_name']); ?>!
                </h2>
                <p class="text-sm text-gray-500">
                    Kelola sistem bimbel Al-Muhajirin dengan mudah dan efisien - <?= $currentMonthName ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Students -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa Aktif</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= ($studentStats['by_status']['active'] ?? 0) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex text-sm text-gray-500">
                    <span class="text-blue-600">SD: <?= ($studentStats['by_level']['SD'] ?? 0) ?></span>
                    <span class="mx-2">•</span>
                    <span class="text-green-600">SMP: <?= ($studentStats['by_level']['SMP'] ?? 0) ?></span>
                    <span class="mx-2">•</span>
                    <span class="text-purple-600">SMA: <?= ($studentStats['by_level']['SMA'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Income -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-arrow-up text-green-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pemasukan Bulan Ini</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= formatCurrency($financialStats['current_month']['income']) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex text-sm text-gray-500">
                    <span class="text-green-600">SPP: <?= formatCurrency($financialSummary['income']['by_category']['spp']['amount'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Balance -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-wallet text-indigo-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Saldo Total</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= formatCurrency($balanceInfo['current_balance']) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex text-sm text-gray-500">
                    <span class="<?= $financialStats['current_month']['net'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        Net: <?= formatCurrency($financialStats['current_month']['net']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Payments -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Tunggakan SPP</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= count($outstandingPayments) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex text-sm text-gray-500">
                    <span class="text-red-600">Total: <?= formatCurrency(array_sum(array_column($outstandingPayments, 'monthly_fee'))) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<?php if ($canModify): ?>
<div class="bg-white overflow-hidden shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
            Aksi Cepat
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="siswa.php" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-plus text-blue-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-blue-900">Tambah Siswa</span>
            </a>
            <a href="mentor.php" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-chalkboard-teacher text-green-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-green-900">Tambah Mentor</span>
            </a>
            <a href="spp.php" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-money-bill text-purple-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-purple-900">Catat Pembayaran</span>
            </a>
            <a href="keuangan.php" class="flex flex-col items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                <i class="fas fa-plus-circle text-indigo-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-indigo-900">Tambah Transaksi</span>
            </a>
            <a href="rekap_bulanan.php" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                <i class="fas fa-chart-line text-yellow-600 text-2xl mb-2"></i>
                <span class="text-sm font-medium text-yellow-900">Generate Rekap</span>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Financial Overview -->
    <div class="lg:col-span-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-chart-area text-blue-600 mr-2"></i>
                    Ringkasan Keuangan Bulanan
                </h3>
                <?php if (!empty($projections['projections'])): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemasukan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="bg-blue-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Saat Ini</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600"><?= formatCurrency($financialStats['current_month']['income']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?= formatCurrency($financialStats['current_month']['expense']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $financialStats['current_month']['net'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= formatCurrency($financialStats['current_month']['net']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $balanceInfo['current_balance'] >= 0 ? 'text-blue-600' : 'text-yellow-600' ?>">
                                        <?= formatCurrency($balanceInfo['current_balance']) ?>
                                    </td>
                                </tr>
                                <?php foreach ($projections['projections'] as $projection): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $projection['month_name'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600"><?= formatCurrency($projection['projected_income']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?= formatCurrency($projection['projected_expense']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm <?= $projection['net_projection'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= formatCurrency($projection['net_projection']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm <?= $projection['projected_balance'] >= 0 ? 'text-blue-600' : 'text-yellow-600' ?>">
                                            <?= formatCurrency($projection['projected_balance']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">Data proyeksi tidak tersedia</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mentor & Attendance Stats -->
    <div class="lg:col-span-1">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-chalkboard-teacher text-green-600 mr-2"></i>
                    Statistik Mentor & Kehadiran
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Mentor Aktif:</span>
                        <span class="text-sm font-medium text-gray-900"><?= ($mentorStats['by_status']['active'] ?? 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Rata-rata Honor:</span>
                        <span class="text-sm font-medium text-gray-900"><?= formatCurrency($mentorStats['average_rate'] ?? 0) ?></span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Mentor per Jenjang:</h4>
                    <div class="space-y-2">
                        <?php foreach (['SD', 'SMP', 'SMA'] as $level): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500"><?= $level ?>:</span>
                                <span class="text-sm text-gray-900"><?= ($mentorStats['by_level'][$level] ?? 0) ?> mentor</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($attendanceStats)): ?>
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Kehadiran Bulan Ini:</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Tingkat Kehadiran Siswa:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($attendanceStats['student_attendance_rate'] ?? 0) >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= number_format($attendanceStats['student_attendance_rate'] ?? 0, 1) ?>%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Tingkat Kehadiran Mentor:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($attendanceStats['mentor_attendance_rate'] ?? 0) >= 85 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= number_format($attendanceStats['mentor_attendance_rate'] ?? 0, 1) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Alerts and Recent Activities -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Outstanding Payments Alert -->
    <?php if (!empty($outstandingPayments)): ?>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    Tunggakan SPP (<?= count($outstandingPayments) ?> siswa)
                </h3>
                <div class="max-h-64 overflow-y-auto">
                    <div class="space-y-3">
                        <?php foreach (array_slice($outstandingPayments, 0, 5) as $payment): ?>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['full_name']) ?></p>
                                    <p class="text-xs text-gray-500"><?= $payment['level'] ?></p>
                                </div>
                                <span class="text-sm font-medium text-red-600"><?= formatCurrency($payment['monthly_fee']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (count($outstandingPayments) > 5): ?>
                    <div class="mt-4 text-center">
                        <a href="spp_monitoring.php" class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat Semua (<?= count($outstandingPayments) - 5 ?> lainnya)
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-list text-blue-600 mr-2"></i>
                Transaksi Terbaru
            </h3>
            <?php if (!empty($recentTransactions)): ?>
                <div class="space-y-3">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars(substr($transaction['description'], 0, 30)) ?><?= strlen($transaction['description']) > 30 ? '...' : '' ?>
                                </p>
                                <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></p>
                            </div>
                            <span class="text-sm font-medium <?= $transaction['transaction_type'] === 'income' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $transaction['transaction_type'] === 'income' ? '+' : '-' ?>
                                <?= formatCurrency($transaction['amount']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="keuangan.php" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 mb-4">Belum ada transaksi</p>
                    <?php if ($canModify): ?>
                        <a href="keuangan.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Transaksi
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- System Status -->
<?php if (empty($outstandingPayments) && empty($lowAttendanceStudents)): ?>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Sistem Berjalan Baik!</h3>
                    <p class="text-sm text-gray-500">
                        Tidak ada tunggakan SPP dan semua siswa memiliki tingkat kehadiran yang baik.
                        Terakhir diperbarui: <?= date('d/m/Y H:i') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($balanceInfo['current_balance'] < 0): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Perhatian: Saldo Negatif</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Saldo saat ini: <strong><?= formatCurrency($balanceInfo['current_balance']) ?></strong></p>
                    <p>Harap segera lakukan penagihan SPP atau kurangi pengeluaran.</p>
                </div>
                <div class="mt-4">
                    <div class="flex space-x-3">
                        <a href="spp_monitoring.php" class="bg-yellow-100 text-yellow-800 px-3 py-2 rounded-md text-sm font-medium hover:bg-yellow-200">
                            <i class="fas fa-money-bill mr-1"></i>
                            Cek Tunggakan SPP
                        </a>
                        <a href="keuangan.php" class="bg-white text-yellow-800 px-3 py-2 rounded-md text-sm font-medium border border-yellow-300 hover:bg-yellow-50">
                            <i class="fas fa-chart-line mr-1"></i>
                            Lihat Keuangan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    </div>
</main>

<?php include '../../partials/admin_footer.php'; ?>