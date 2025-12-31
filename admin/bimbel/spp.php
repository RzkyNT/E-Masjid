<?php
/**
 * SPP Payment Management Interface
 * Handles monthly SPP payment recording, validation, and monitoring
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
// Check if user can modify data (only admin_bimbel can add/edit payments)
$can_modify = ($current_user['role'] === 'admin_bimbel');

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_modify) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_payment':
            $result = recordSPPPaymentWithTransaction(
                $_POST['student_id'],
                $_POST['payment_month'],
                $_POST['payment_year'],
                $_POST['amount'],
                $_POST['payment_method'] ?? 'cash',
                $_POST['notes'] ?? ''
            );
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'update_payment':
            $result = updateSPPPayment($_POST['payment_id'], [
                'amount' => $_POST['amount'],
                'payment_date' => $_POST['payment_date'],
                'payment_method' => $_POST['payment_method'],
                'notes' => $_POST['notes']
            ]);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'delete_payment':
            $result = deleteSPPPayment($_POST['payment_id']);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
            
        case 'bulk_payment':
            $student_ids = $_POST['student_ids'] ?? [];
            $month = $_POST['bulk_month'];
            $year = $_POST['bulk_year'];
            $payment_method = $_POST['bulk_payment_method'] ?? 'cash';
            
            $success_count = 0;
            $error_count = 0;
            
            foreach ($student_ids as $student_id) {
                $student = getStudentById($student_id);
                if ($student) {
                    $result = recordSPPPayment(
                        $student_id,
                        $month,
                        $year,
                        $student['monthly_fee'],
                        $payment_method,
                        'Bulk payment processing'
                    );
                    
                    if ($result['success']) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
            
            $message = "Bulk payment completed: $success_count successful, $error_count failed";
            $message_type = $error_count === 0 ? 'success' : 'warning';
            break;
    }
}

// Get filter parameters
$level_filter = $_GET['level'] ?? '';
$status_filter = $_GET['status'] ?? 'outstanding';
$month_filter = $_GET['month'] ?? date('n');
$year_filter = $_GET['year'] ?? date('Y');
$search = $_GET['search'] ?? '';

// Get SPP statistics
$spp_stats = getSPPStatistics();

// Get students based on status filter
if ($status_filter === 'outstanding') {
    $students = getOutstandingSPPPayments($level_filter);
} else {
    // Get all students with their payment status
    $students = [];
    $all_students = getAllStudents(1, 1000, [
        'level' => $level_filter,
        'search' => $search,
        'status' => 'active'
    ])['data'];
    
    foreach ($all_students as $student) {
        $payment_status = getStudentSPPStatus($student['id'], $month_filter, $year_filter);
        
        if ($status_filter === 'paid' && $payment_status['paid']) {
            $students[] = array_merge($student, $payment_status);
        } elseif ($status_filter === 'all') {
            $students[] = array_merge($student, $payment_status);
        }
    }
}

// Get payment methods
$payment_methods = [
    'cash' => 'Tunai',
    'transfer' => 'Transfer Bank',
    'other' => 'Lainnya'
];

// Get months for dropdown
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Get years for dropdown (current year ± 2)
$current_year = date('Y');
$years = [];
for ($i = $current_year - 2; $i <= $current_year + 1; $i++) {
    $years[$i] = $i;
}

$page_title = 'Pembayaran SPP';
include 'partials/bimbel_header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pembayaran SPP</h1>
                    <p class="text-gray-600 mt-1">Kelola pembayaran SPP siswa bulanan</p>
                </div>
                
                <?php if ($can_modify): ?>
                <div class="flex space-x-3">
                    <button onclick="openAddPaymentModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Pembayaran
                    </button>
                    <button onclick="openBulkPaymentModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-layer-group mr-2"></i>
                        Pembayaran Massal
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
    <div class="mx-6 mb-6">
        <div class="alert alert-<?php echo $message_type; ?> p-4 rounded-lg">
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- SPP Statistics Cards -->
    <div class="px-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Siswa</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($spp_stats['total_students']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Students Paid -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sudah Bayar</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($spp_stats['paid_students']); ?></p>
                        <p class="text-xs text-green-600">
                            <?php echo number_format($spp_stats['payment_rate'], 1); ?>% dari total
                        </p>
                    </div>
                </div>
            </div>

            <!-- Outstanding Students -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Belum Bayar</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($spp_stats['outstanding_students']); ?></p>
                        <p class="text-xs text-red-600">
                            Rp <?php echo number_format($spp_stats['outstanding_amount'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Income -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pemasukan Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($spp_stats['total_income'], 0, ',', '.'); ?></p>
                        <p class="text-xs text-gray-500">
                            Target: Rp <?php echo number_format($spp_stats['expected_income'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Rate -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-pie text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tingkat Pembayaran</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($spp_stats['payment_rate'], 1); ?>%</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo min($spp_stats['payment_rate'], 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Payment Alerts -->
    <?php if ($spp_stats['outstanding_students'] > 0): ?>
    <div class="px-6 mb-6">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <span class="font-medium">Peringatan:</span> 
                        Ada <?php echo $spp_stats['outstanding_students']; ?> siswa yang belum membayar SPP bulan ini 
                        dengan total tunggakan Rp <?php echo number_format($spp_stats['outstanding_amount'], 0, ',', '.'); ?>.
                        <?php if ($can_modify): ?>
                        <a href="?status=outstanding" class="font-medium underline hover:text-yellow-800">
                            Lihat daftar siswa →
                        </a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Summary by Level -->
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Pembayaran per Jenjang</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $levels = ['SD', 'SMP', 'SMA'];
                foreach ($levels as $level):
                    $level_outstanding = getOutstandingSPPPayments($level);
                    $level_students = getAllStudents(1, 1000, ['level' => $level, 'status' => 'active'])['data'];
                    $total_level_students = count($level_students);
                    $outstanding_level_students = count($level_outstanding);
                    $paid_level_students = $total_level_students - $outstanding_level_students;
                    $level_payment_rate = $total_level_students > 0 ? ($paid_level_students / $total_level_students) * 100 : 0;
                ?>
                <div class="border rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-semibold text-gray-900"><?php echo $level; ?></h4>
                        <span class="text-sm font-medium <?php echo $level_payment_rate >= 80 ? 'text-green-600' : ($level_payment_rate >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                            <?php echo number_format($level_payment_rate, 1); ?>%
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Siswa:</span>
                            <span class="font-medium"><?php echo $total_level_students; ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Sudah Bayar:</span>
                            <span class="font-medium"><?php echo $paid_level_students; ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-red-600">Belum Bayar:</span>
                            <span class="font-medium"><?php echo $outstanding_level_students; ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="<?php echo $level_payment_rate >= 80 ? 'bg-green-500' : ($level_payment_rate >= 60 ? 'bg-yellow-500' : 'bg-red-500'); ?> h-2 rounded-full" 
                                 style="width: <?php echo min($level_payment_rate, 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if ($outstanding_level_students > 0 && $can_modify): ?>
                    <div class="mt-3">
                        <a href="?status=outstanding&level=<?php echo $level; ?>" 
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            Lihat yang belum bayar →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="px-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                    <select name="status" class="form-select w-full">
                        <option value="outstanding" <?php echo $status_filter === 'outstanding' ? 'selected' : ''; ?>>Belum Bayar</option>
                        <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Sudah Bayar</option>
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                    </select>
                </div>

                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang</label>
                    <select name="level" class="form-select w-full">
                        <option value="">Semua Jenjang</option>
                        <option value="SD" <?php echo $level_filter === 'SD' ? 'selected' : ''; ?>>SD</option>
                        <option value="SMP" <?php echo $level_filter === 'SMP' ? 'selected' : ''; ?>>SMP</option>
                        <option value="SMA" <?php echo $level_filter === 'SMA' ? 'selected' : ''; ?>>SMA</option>
                    </select>
                </div>

                <!-- Month Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="month" class="form-select w-full">
                        <?php foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $month_filter == $num ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="year" class="form-select w-full">
                        <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year_filter == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Siswa</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Nama atau nomor siswa..." class="form-input w-full">
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="px-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        Daftar Siswa - <?php echo ucfirst($status_filter === 'outstanding' ? 'Belum Bayar' : ($status_filter === 'paid' ? 'Sudah Bayar' : 'Semua')); ?>
                    </h3>
                    <span class="text-sm text-gray-500">
                        <?php echo count($students); ?> siswa ditemukan
                    </span>
                </div>
            </div>

            <?php if (empty($students)): ?>
            <div class="p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Tidak ada data siswa yang ditemukan</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <?php if ($can_modify && $status_filter === 'outstanding'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded">
                            </th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang/Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SPP Bulanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                            <?php if ($can_modify): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <?php if ($can_modify && $status_filter === 'outstanding'): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" 
                                       class="student-checkbox rounded">
                            </td>
                            <?php endif; ?>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['student_number'] ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $student['level']; ?> - Kelas <?php echo $student['class']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['parent_name'] ?? ''); ?></div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo number_format($student['monthly_fee'] ?? 0, 0, ',', '.'); ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (isset($student['paid']) && $student['paid']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Lunas
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Belum Bayar
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if (isset($student['payment_date']) && $student['payment_date']): ?>
                                    <?php echo date('d/m/Y', strtotime($student['payment_date'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            
                            <?php if ($can_modify): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if (!isset($student['paid']) || !$student['paid']): ?>
                                        <button onclick="openPaymentModal(<?php echo $student['id']; ?>)" 
                                                class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="editPayment(<?php echo $student['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deletePayment(<?php echo $student['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="viewPaymentHistory(<?php echo $student['id']; ?>)" 
                                            class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($can_modify): ?>
<!-- Add Payment Modal -->
<div id="addPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Pembayaran SPP</h3>
                <button onclick="closeAddPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="addPaymentForm">
                <input type="hidden" name="action" value="add_payment">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Siswa</label>
                    <select name="student_id" id="studentSelect" required class="form-select w-full">
                        <option value="">Pilih Siswa</option>
                        <?php
                        $all_students = getAllStudents(1, 1000, ['status' => 'active'])['data'];
                        foreach ($all_students as $student):
                        ?>
                        <option value="<?php echo $student['id']; ?>" data-fee="<?php echo $student['monthly_fee']; ?>">
                            <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo $student['level']; ?> - <?php echo $student['class']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <select name="payment_month" required class="form-select w-full">
                            <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select name="payment_year" required class="form-select w-full">
                            <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $year == date('Y') ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Pembayaran</label>
                    <input type="number" name="amount" id="paymentAmount" required min="0" step="1000" 
                           class="form-input w-full" placeholder="0">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select w-full">
                        <?php foreach ($payment_methods as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="form-textarea w-full" 
                              placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddPaymentModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Payment Modal -->
<div id="bulkPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Pembayaran Massal</h3>
                <button onclick="closeBulkPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" id="bulkPaymentForm">
                <input type="hidden" name="action" value="bulk_payment">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Pilih siswa yang akan diproses pembayarannya dengan mencentang checkbox pada tabel.
                    </p>
                    <div id="selectedStudentsCount" class="text-sm font-medium text-blue-600">
                        0 siswa dipilih
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <select name="bulk_month" required class="form-select w-full">
                            <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select name="bulk_year" required class="form-select w-full">
                            <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $year == date('Y') ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="bulk_payment_method" class="form-select w-full">
                        <?php foreach ($payment_methods as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBulkPaymentModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Proses Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Auto-fill payment amount when student is selected
document.getElementById('studentSelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const fee = selectedOption.getAttribute('data-fee');
    if (fee) {
        document.getElementById('paymentAmount').value = fee;
    }
});

// Modal functions
function openAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.remove('hidden');
}

function closeAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.add('hidden');
    document.getElementById('addPaymentForm').reset();
}

function openBulkPaymentModal() {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu siswa untuk pembayaran massal');
        return;
    }
    document.getElementById('bulkPaymentModal').classList.remove('hidden');
}

function closeBulkPaymentModal() {
    document.getElementById('bulkPaymentModal').classList.add('hidden');
}

function openPaymentModal(studentId) {
    // Set the student in the dropdown
    document.getElementById('studentSelect').value = studentId;
    
    // Trigger change event to auto-fill amount
    document.getElementById('studentSelect').dispatchEvent(new Event('change'));
    
    openAddPaymentModal();
}

// Select all functionality
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

// Update selected count
function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    const countElement = document.getElementById('selectedStudentsCount');
    if (countElement) {
        countElement.textContent = `${checkedBoxes.length} siswa dipilih`;
    }
}

// Add event listeners to student checkboxes
document.querySelectorAll('.student-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Bulk payment form submission
document.getElementById('bulkPaymentForm')?.addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    
    // Add selected student IDs to form
    checkedBoxes.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'student_ids[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
});

// Payment history function
function viewPaymentHistory(studentId) {
    // This would open a modal or redirect to payment history page
    window.location.href = `spp_history.php?student_id=${studentId}`;
}

// Edit and delete functions (to be implemented)
function editPayment(studentId) {
    // Implementation for editing payment
    console.log('Edit payment for student:', studentId);
}

function deletePayment(studentId) {
    if (confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')) {
        // Implementation for deleting payment
        console.log('Delete payment for student:', studentId);
    }
}
</script>

<?php include 'partials/bimbel_footer.php'; ?>