<?php
/**
 * SPP Payment History Interface
 * Shows detailed payment history for individual students
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

// Get student ID from URL
$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    header('Location: spp.php');
    exit;
}

// Get student information
$student = getStudentById($student_id);
if (!$student) {
    header('Location: spp.php?error=student_not_found');
    exit;
}

// Get payment history
$payment_history = getStudentSPPHistory($student_id, 24); // Last 24 months

// Get months for display
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Calculate statistics
$total_paid = array_sum(array_column($payment_history, 'amount'));
$payment_count = count($payment_history);
$average_payment = $payment_count > 0 ? $total_paid / $payment_count : 0;

$page_title = 'Riwayat Pembayaran SPP - ' . $student['full_name'];
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
                    <div class="flex items-center space-x-3">
                        <a href="spp.php" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Riwayat Pembayaran SPP</h1>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($student['full_name']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-sm text-gray-500">Nomor Siswa</div>
                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($student['student_number']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Information Card -->
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Student Info -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Informasi Siswa</h3>
                    <div class="space-y-1">
                        <div class="text-sm"><span class="font-medium">Nama:</span> <?php echo htmlspecialchars($student['full_name']); ?></div>
                        <div class="text-sm"><span class="font-medium">Jenjang:</span> <?php echo $student['level']; ?> - Kelas <?php echo $student['class']; ?></div>
                        <div class="text-sm"><span class="font-medium">Orang Tua:</span> <?php echo htmlspecialchars($student['parent_name']); ?></div>
                        <div class="text-sm"><span class="font-medium">SPP Bulanan:</span> Rp <?php echo number_format($student['monthly_fee'], 0, ',', '.'); ?></div>
                    </div>
                </div>

                <!-- Payment Statistics -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Statistik Pembayaran</h3>
                    <div class="space-y-1">
                        <div class="text-sm"><span class="font-medium">Total Pembayaran:</span> <?php echo $payment_count; ?> kali</div>
                        <div class="text-sm"><span class="font-medium">Total Nominal:</span> Rp <?php echo number_format($total_paid, 0, ',', '.'); ?></div>
                        <div class="text-sm"><span class="font-medium">Rata-rata:</span> Rp <?php echo number_format($average_payment, 0, ',', '.'); ?></div>
                        <div class="text-sm"><span class="font-medium">Status:</span> 
                            <span class="<?php echo $student['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo ucfirst($student['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Current Month Status -->
                <?php 
                $current_month_status = getStudentSPPStatus($student_id, date('n'), date('Y'));
                ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Status Bulan Ini</h3>
                    <div class="space-y-1">
                        <div class="text-sm"><span class="font-medium">Bulan:</span> <?php echo $months[date('n')] . ' ' . date('Y'); ?></div>
                        <div class="text-sm"><span class="font-medium">Status:</span> 
                            <?php if ($current_month_status['paid']): ?>
                                <span class="text-green-600 font-medium">Lunas</span>
                            <?php else: ?>
                                <span class="text-red-600 font-medium">Belum Bayar</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($current_month_status['paid']): ?>
                        <div class="text-sm"><span class="font-medium">Tanggal Bayar:</span> <?php echo date('d/m/Y', strtotime($current_month_status['payment_date'])); ?></div>
                        <div class="text-sm"><span class="font-medium">Jumlah:</span> Rp <?php echo number_format($current_month_status['paid_amount'], 0, ',', '.'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Aksi Cepat</h3>
                    <div class="space-y-2">
                        <?php if (!$current_month_status['paid'] && $current_user['role'] === 'admin_bimbel'): ?>
                        <button onclick="addPaymentForStudent()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Tambah Pembayaran
                        </button>
                        <?php endif; ?>
                        
                        <a href="spp.php" 
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm text-center block">
                            <i class="fas fa-list mr-1"></i>
                            Kembali ke Daftar
                        </a>
                        
                        <button onclick="printHistory()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                            <i class="fas fa-print mr-1"></i>
                            Cetak Riwayat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="px-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Riwayat Pembayaran</h3>
            </div>

            <?php if (empty($payment_history)): ?>
            <div class="p-8 text-center">
                <i class="fas fa-receipt text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Belum ada riwayat pembayaran</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($payment_history as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo $months[$payment['payment_month']] . ' ' . $payment['payment_year']; ?>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('H:i', strtotime($payment['payment_date'])); ?>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?>
                                </div>
                                <?php if ($payment['amount'] != $student['monthly_fee']): ?>
                                <div class="text-xs text-orange-600">
                                    <?php if ($payment['amount'] > $student['monthly_fee']): ?>
                                        Lebih bayar: Rp <?php echo number_format($payment['amount'] - $student['monthly_fee'], 0, ',', '.'); ?>
                                    <?php else: ?>
                                        Kurang bayar: Rp <?php echo number_format($student['monthly_fee'] - $payment['amount'], 0, ',', '.'); ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php 
                                    switch($payment['payment_method']) {
                                        case 'cash': echo 'bg-green-100 text-green-800'; break;
                                        case 'transfer': echo 'bg-blue-100 text-blue-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800'; break;
                                    }
                                    ?>">
                                    <?php 
                                    switch($payment['payment_method']) {
                                        case 'cash': echo 'Tunai'; break;
                                        case 'transfer': echo 'Transfer'; break;
                                        case 'other': echo 'Lainnya'; break;
                                        default: echo ucfirst($payment['payment_method']); break;
                                    }
                                    ?>
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    <?php echo htmlspecialchars($payment['notes'] ?: '-'); ?>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Lunas
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Card -->
    <?php if (!empty($payment_history)): ?>
    <div class="px-6 mt-6">
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Pembayaran</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?php echo $payment_count; ?></div>
                    <div class="text-sm text-gray-600">Total Pembayaran</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">Rp <?php echo number_format($total_paid, 0, ',', '.'); ?></div>
                    <div class="text-sm text-gray-600">Total Nominal</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">Rp <?php echo number_format($average_payment, 0, ',', '.'); ?></div>
                    <div class="text-sm text-gray-600">Rata-rata per Bulan</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function addPaymentForStudent() {
    // Redirect to SPP page with student pre-selected
    window.location.href = 'spp.php?add_payment=1&student_id=<?php echo $student_id; ?>';
}

function printHistory() {
    // Create a print-friendly version
    const printWindow = window.open('', '_blank');
    const printContent = `
        <html>
        <head>
            <title>Riwayat Pembayaran SPP - <?php echo htmlspecialchars($student['full_name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .student-info { margin-bottom: 20px; }
                .student-info table { width: 100%; }
                .student-info td { padding: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .summary { margin-top: 30px; text-align: center; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>RIWAYAT PEMBAYARAN SPP</h1>
                <h2>Bimbel Al-Muhajirin</h2>
            </div>
            
            <div class="student-info">
                <table>
                    <tr>
                        <td><strong>Nama Siswa:</strong></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><strong>Nomor Siswa:</strong></td>
                        <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Jenjang/Kelas:</strong></td>
                        <td><?php echo $student['level']; ?> - Kelas <?php echo $student['class']; ?></td>
                        <td><strong>SPP Bulanan:</strong></td>
                        <td>Rp <?php echo number_format($student['monthly_fee'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Orang Tua:</strong></td>
                        <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                        <td><strong>Tanggal Cetak:</strong></td>
                        <td><?php echo date('d/m/Y H:i'); ?></td>
                    </tr>
                </table>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Periode</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_history as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $months[$payment['payment_month']] . ' ' . $payment['payment_year']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                        <td>Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></td>
                        <td><?php 
                            switch($payment['payment_method']) {
                                case 'cash': echo 'Tunai'; break;
                                case 'transfer': echo 'Transfer'; break;
                                case 'other': echo 'Lainnya'; break;
                                default: echo ucfirst($payment['payment_method']); break;
                            }
                        ?></td>
                        <td><?php echo htmlspecialchars($payment['notes'] ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="summary">
                <p><strong>Total Pembayaran: <?php echo $payment_count; ?> kali | Total Nominal: Rp <?php echo number_format($total_paid, 0, ',', '.'); ?></strong></p>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}
</script>

    </div>
</main>

<?php include '../../partials/admin_footer.php'; ?>