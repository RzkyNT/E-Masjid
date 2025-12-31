<?php
/**
 * SPP Payment Monitoring Dashboard
 * Comprehensive monitoring and analytics for SPP payments
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

// Get monitoring data
$spp_stats = getSPPStatistics();
$payment_trends = getSPPPaymentTrends(6);
$overdue_students = getOverdueSPPPayments(2);
$payment_reminders = getSPPPaymentReminders();

// Get level summaries
$level_summaries = [];
$levels = ['SD', 'SMP', 'SMA'];
foreach ($levels as $level) {
    $level_summaries[$level] = getSPPSummaryByLevel($level);
}

// Get months for display
$months = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
];

$page_title = 'Monitoring Pembayaran SPP';
include '../../partials/admin_header.php';
?>

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 main-content content-with-sidebar">
    <div class="p-6">

<div class="main-content">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Monitoring Pembayaran SPP</h1>
                    <p class="text-gray-600 mt-1">Dashboard analitik dan monitoring pembayaran SPP</p>
                </div>
                
                <div class="flex space-x-3">
                    <a href="spp.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-list mr-2"></i>
                        Kelola Pembayaran
                    </a>
                    <button onclick="refreshData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="px-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Collection Rate -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-percentage text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tingkat Penagihan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($spp_stats['payment_rate'], 1); ?>%</p>
                        <p class="text-xs <?php echo $spp_stats['payment_rate'] >= 80 ? 'text-green-600' : ($spp_stats['payment_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                            <?php 
                            if ($spp_stats['payment_rate'] >= 80) echo 'Sangat Baik';
                            elseif ($spp_stats['payment_rate'] >= 60) echo 'Cukup Baik';
                            else echo 'Perlu Perhatian';
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Outstanding Amount -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Tunggakan</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($spp_stats['outstanding_amount'], 0, ',', '.'); ?></p>
                        <p class="text-xs text-red-600">
                            <?php echo $spp_stats['outstanding_students']; ?> siswa
                        </p>
                    </div>
                </div>
            </div>

            <!-- Monthly Target -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-target text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Target vs Realisasi</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo number_format(($spp_stats['total_income'] / $spp_stats['expected_income']) * 100, 1); ?>%
                        </p>
                        <p class="text-xs text-gray-500">
                            Rp <?php echo number_format($spp_stats['total_income'], 0, ',', '.'); ?> / Rp <?php echo number_format($spp_stats['expected_income'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Overdue Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tunggakan > 2 Bulan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($overdue_students); ?></p>
                        <p class="text-xs text-orange-600">
                            Perlu tindak lanjut
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Reminders -->
    <?php if (!empty($payment_reminders)): ?>
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Pengingat Pembayaran</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <?php foreach (array_slice($payment_reminders, 0, 5) as $reminder): ?>
                    <div class="flex items-center justify-between p-3 rounded-lg <?php echo $reminder['priority'] === 'critical' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200'; ?>">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas <?php echo $reminder['priority'] === 'critical' ? 'fa-exclamation-circle text-red-500' : 'fa-exclamation-triangle text-yellow-500'; ?>"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($reminder['student']['full_name']); ?>
                                    <span class="text-gray-500">(<?php echo $reminder['student']['level']; ?> - <?php echo $reminder['student']['class']; ?>)</span>
                                </p>
                                <p class="text-xs text-gray-600"><?php echo $reminder['message']; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $reminder['priority'] === 'critical' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ucfirst($reminder['priority']); ?>
                            </span>
                            <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                            <a href="spp.php?add_payment=1&student_id=<?php echo $reminder['student']['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Bayar
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($payment_reminders) > 5): ?>
                <div class="mt-4 text-center">
                    <button onclick="showAllReminders()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua (<?php echo count($payment_reminders); ?> pengingat)
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Trends Chart -->
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Tren Pembayaran (6 Bulan Terakhir)</h3>
            </div>
            <div class="p-6">
                <div class="h-64">
                    <canvas id="paymentTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Level Performance -->
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Performa Pembayaran per Jenjang</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($level_summaries as $level => $summary): ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-bold text-gray-900"><?php echo $level; ?></h4>
                            <div class="text-right">
                                <div class="text-2xl font-bold <?php echo $summary['payment_rate'] >= 80 ? 'text-green-600' : ($summary['payment_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                    <?php echo number_format($summary['payment_rate'], 1); ?>%
                                </div>
                                <div class="text-xs text-gray-500">Tingkat Pembayaran</div>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Siswa</span>
                                <span class="font-medium"><?php echo $summary['total_students']; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-green-600">Sudah Bayar</span>
                                <span class="font-medium text-green-600"><?php echo $summary['paid_students']; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-red-600">Belum Bayar</span>
                                <span class="font-medium text-red-600"><?php echo $summary['outstanding_students']; ?></span>
                            </div>
                            
                            <div class="pt-2 border-t">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Pemasukan</span>
                                    <span class="font-medium">Rp <?php echo number_format($summary['total_income'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Target</span>
                                    <span class="text-gray-500">Rp <?php echo number_format($summary['expected_income'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="<?php echo $summary['payment_rate'] >= 80 ? 'bg-green-500' : ($summary['payment_rate'] >= 60 ? 'bg-yellow-500' : 'bg-red-500'); ?> h-3 rounded-full transition-all duration-300" 
                                     style="width: <?php echo min($summary['payment_rate'], 100); ?>%"></div>
                            </div>
                        </div>
                        
                        <?php if ($summary['outstanding_students'] > 0): ?>
                        <div class="mt-4">
                            <a href="spp.php?status=outstanding&level=<?php echo $level; ?>" 
                               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Lihat yang belum bayar →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Students -->
    <?php if (!empty($overdue_students)): ?>
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Siswa dengan Tunggakan > 2 Bulan</h3>
                    <span class="text-sm text-red-600 font-medium"><?php echo count($overdue_students); ?> siswa</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan Tunggakan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tunggakan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                            <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach (array_slice($overdue_students, 0, 10) as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-red-600">
                                                <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['student_number']); ?></div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $student['level']; ?> - <?php echo $student['class']; ?></div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?php echo $student['overdue_count']; ?> bulan
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                Rp <?php echo number_format($student['total_overdue_amount'], 0, ',', '.'); ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($student['parent_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['parent_phone']); ?></div>
                            </td>
                            
                            <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="spp.php?add_payment=1&student_id=<?php echo $student['id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                    <a href="spp_history.php?student_id=<?php echo $student['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="tel:<?php echo $student['parent_phone']; ?>" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($overdue_students) > 10): ?>
            <div class="px-6 py-4 border-t border-gray-200 text-center">
                <a href="spp.php?status=overdue" class="text-blue-600 hover:text-blue-800 font-medium">
                    Lihat semua siswa dengan tunggakan (<?php echo count($overdue_students); ?> siswa)
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Payment Trends Chart
const ctx = document.getElementById('paymentTrendsChart').getContext('2d');
const paymentTrendsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach ($payment_trends as $trend): ?>
            '<?php echo $trend['month_name']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Tingkat Pembayaran (%)',
            data: [
                <?php foreach ($payment_trends as $trend): ?>
                <?php echo number_format($trend['payment_rate'], 2); ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'Pemasukan (Juta Rp)',
            data: [
                <?php foreach ($payment_trends as $trend): ?>
                <?php echo number_format($trend['total_income'] / 1000000, 2); ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Bulan'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Tingkat Pembayaran (%)'
                },
                min: 0,
                max: 100
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Pemasukan (Juta Rp)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Target: 80%';
                        }
                        return '';
                    }
                }
            }
        }
    }
});

// Refresh data function
function refreshData() {
    location.reload();
}

// Show all reminders function
function showAllReminders() {
    // This could open a modal or redirect to a detailed page
    alert('Fitur ini akan menampilkan semua pengingat dalam modal atau halaman terpisah');
}

// Auto refresh every 5 minutes
setInterval(function() {
    const now = new Date();
    const minutes = now.getMinutes();
    
    // Only refresh on the hour or half hour to avoid too frequent updates
    if (minutes === 0 || minutes === 30) {
        refreshData();
    }
}, 60000); // Check every minute
</script>

    </div>
</main>

<?php include '../../partials/admin_footer.php'; ?>