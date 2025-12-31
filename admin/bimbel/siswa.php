<?php
/**
 * Student Management Interface
 * Handles student listing, search, filtering, and CRUD operations
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
// Set page variables
$page_title = 'Manajemen Siswa';
$page_description = 'Kelola data siswa bimbel Al-Muhajirin';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_student':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = addStudent($_POST);
            echo json_encode($result);
            exit;
            
        case 'update_student':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = updateStudent($_POST['id'], $_POST);
            echo json_encode($result);
            exit;
            
        case 'delete_student':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = deleteStudent($_POST['id']);
            echo json_encode($result);
            exit;
            
        case 'get_student':
            $student = getStudentById($_POST['id']);
            if ($student) {
                // Get payment status for current month
                $paymentStatus = getStudentSPPStatus($student['id']);
                $student['payment_status'] = $paymentStatus;
                echo json_encode(['success' => true, 'data' => $student]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
            }
            exit;
            
        case 'record_payment':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = recordSPPPayment(
                $_POST['student_id'],
                $_POST['month'],
                $_POST['year'],
                $_POST['amount'],
                $_POST['payment_method'] ?? 'cash',
                $_POST['notes'] ?? ''
            );
            echo json_encode($result);
            exit;
            
        case 'get_payment_status':
            $paymentStatus = getStudentSPPStatus($_POST['student_id'], $_POST['month'] ?? null, $_POST['year'] ?? null);
            echo json_encode(['success' => true, 'data' => $paymentStatus]);
            exit;
    }
}

// Get filter parameters
$filters = [
    'level' => $_GET['level'] ?? '',
    'status' => $_GET['status'] ?? 'active',
    'class' => $_GET['class'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;

// Get students data
$studentsData = getAllStudents($page, $limit, $filters);
$students = $studentsData['data'];
$pagination = $studentsData['pagination'];

// Get payment status for each student
foreach ($students as &$student) {
    $student['payment_status'] = getStudentSPPStatus($student['id']);
}

// Get statistics
$stats = getStudentStatistics();

// Get unique classes for filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT class FROM students WHERE status = 'active' ORDER BY class");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $classes = [];
}

include 'partials/bimbel_header.php';
?>

<!-- Page Content -->
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Siswa Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?php echo ($stats['by_status']['active'] ?? 0); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-graduation-cap text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">SD</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?php echo ($stats['by_level']['SD'] ?? 0); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-book text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">SMP</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?php echo ($stats['by_level']['SMP'] ?? 0); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-user-graduate text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">SMA</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?php echo ($stats['by_level']['SMA'] ?? 0); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Cari nama siswa, nomor, atau orang tua..." 
                               value="<?php echo htmlspecialchars($filters['search']); ?>"
                               class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    
                    <!-- Level Filter -->
                    <select id="levelFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Jenjang</option>
                        <option value="SD" <?php echo $filters['level'] === 'SD' ? 'selected' : ''; ?>>SD</option>
                        <option value="SMP" <?php echo $filters['level'] === 'SMP' ? 'selected' : ''; ?>>SMP</option>
                        <option value="SMA" <?php echo $filters['level'] === 'SMA' ? 'selected' : ''; ?>>SMA</option>
                    </select>
                    
                    <!-- Status Filter -->
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        <option value="graduated" <?php echo $filters['status'] === 'graduated' ? 'selected' : ''; ?>>Lulus</option>
                        <option value="">Semua Status</option>
                    </select>
                    
                    <!-- Class Filter -->
                    <select id="classFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" 
                                    <?php echo $filters['class'] === $class ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Actions -->
                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                <div class="flex space-x-2">
                    <button onclick="openAddStudentModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Siswa</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Students Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Siswa
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenjang & Kelas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Orang Tua
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            SPP Bulanan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status Bayar
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tgl Daftar
                        </th>
                        <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="<?php echo $current_user['role'] === 'admin_bimbel' ? '8' : '7'; ?>" 
                                class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">Tidak ada siswa ditemukan</p>
                                    <p class="text-sm">Coba ubah filter atau tambah siswa baru</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-green-800">
                                                    <?php echo strtoupper(substr($student['full_name'], 0, 2)); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($student['full_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($student['student_number']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php 
                                            switch($student['level']) {
                                                case 'SD': echo 'bg-green-100 text-green-800'; break;
                                                case 'SMP': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'SMA': echo 'bg-purple-100 text-purple-800'; break;
                                            }
                                            ?>">
                                            <?php echo $student['level']; ?>
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Kelas <?php echo htmlspecialchars($student['class']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($student['parent_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($student['parent_phone']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo formatCurrency($student['monthly_fee']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $paymentStatus = $student['payment_status']['status'] ?? 'unpaid';
                                    $statusClass = $paymentStatus === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    $statusText = $paymentStatus === 'paid' ? 'Lunas' : 'Belum Bayar';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                    <?php if ($paymentStatus === 'paid'): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php echo date('d/m/Y', strtotime($student['payment_status']['payment_date'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php 
                                        switch($student['status']) {
                                            case 'active': echo 'bg-green-100 text-green-800'; break;
                                            case 'inactive': echo 'bg-red-100 text-red-800'; break;
                                            case 'graduated': echo 'bg-blue-100 text-blue-800'; break;
                                        }
                                        ?>">
                                        <?php 
                                        switch($student['status']) {
                                            case 'active': echo 'Aktif'; break;
                                            case 'inactive': echo 'Tidak Aktif'; break;
                                            case 'graduated': echo 'Lulus'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($student['registration_date'])); ?>
                                </td>
                                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="viewStudent(<?php echo $student['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($student['payment_status']['status'] !== 'paid'): ?>
                                        <button onclick="quickPayment(<?php echo $student['id']; ?>)" 
                                                class="text-green-600 hover:text-green-900" title="Bayar SPP">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button onclick="editStudent(<?php echo $student['id']; ?>)" 
                                                class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteStudent(<?php echo $student['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium"><?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?></span>
                        sampai
                        <span class="font-medium"><?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']); ?></span>
                        dari
                        <span class="font-medium"><?php echo $pagination['total_records']; ?></span>
                        siswa
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $pagination['current_page'] - 2);
                        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                   <?php echo $i === $pagination['current_page'] 
                                       ? 'z-10 bg-green-50 border-green-500 text-green-600' 
                                       : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($current_user['role'] === 'admin_bimbel'): ?>
<!-- Add/Edit Student Modal -->
<div id="studentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Siswa Baru</h3>
                <button onclick="closeStudentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="studentForm" class="space-y-4">
                <input type="hidden" id="studentId" name="id">
                <input type="hidden" name="action" id="formAction" value="add_student">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="student_number" class="block text-sm font-medium text-gray-700">Nomor Siswa</label>
                        <input type="text" id="student_number" name="student_number" readonly
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                        <p class="text-xs text-gray-500 mt-1">Akan diisi otomatis jika kosong</p>
                    </div>
                    
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Jenjang *</label>
                        <select id="level" name="level" required onchange="updateMonthlyFee()"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Jenjang</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="class" class="block text-sm font-medium text-gray-700">Kelas *</label>
                        <input type="text" id="class" name="class" required placeholder="Contoh: 1A, 7B, 10 IPA"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="parent_name" class="block text-sm font-medium text-gray-700">Nama Orang Tua *</label>
                        <input type="text" id="parent_name" name="parent_name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="parent_phone" class="block text-sm font-medium text-gray-700">No. HP Orang Tua *</label>
                        <input type="tel" id="parent_phone" name="parent_phone" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat *</label>
                        <textarea id="address" name="address" required rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    
                    <div>
                        <label for="monthly_fee" class="block text-sm font-medium text-gray-700">SPP Bulanan *</label>
                        <input type="number" id="monthly_fee" name="monthly_fee" required min="0" step="1000"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="graduated">Lulus</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeStudentModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div id="viewStudentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Siswa</h3>
                <button onclick="closeViewStudentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="studentDetails" class="space-y-4">
                <!-- Student details will be loaded here -->
            </div>
            
            <div class="flex justify-end pt-4">
                <button onclick="closeViewStudentModal()" 
                        class="px-4 py-2 bg-gray-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Bayar SPP</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="paymentForm" class="space-y-4">
                <input type="hidden" id="paymentStudentId" name="student_id">
                <input type="hidden" name="action" value="record_payment">
                
                <div id="paymentStudentInfo" class="bg-gray-50 p-3 rounded-md">
                    <!-- Student info will be loaded here -->
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="payment_month" class="block text-sm font-medium text-gray-700">Bulan *</label>
                        <select id="payment_month" name="month" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="payment_year" class="block text-sm font-medium text-gray-700">Tahun *</label>
                        <select id="payment_year" name="year" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <?php 
                            $currentYear = date('Y');
                            for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++): 
                            ?>
                                <option value="<?php echo $year; ?>" <?php echo $year == $currentYear ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="payment_amount" class="block text-sm font-medium text-gray-700">Jumlah Bayar *</label>
                    <input type="number" id="payment_amount" name="amount" required min="0" step="1000"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label for="payment_notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                    <textarea id="payment_notes" name="notes" rows="2"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closePaymentModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const levelFilter = document.getElementById('levelFilter');
    const statusFilter = document.getElementById('statusFilter');
    const classFilter = document.getElementById('classFilter');
    
    let searchTimeout;
    
    function applyFilters() {
        const params = new URLSearchParams(window.location.search);
        params.set('search', searchInput.value);
        params.set('level', levelFilter.value);
        params.set('status', statusFilter.value);
        params.set('class', classFilter.value);
        params.delete('page'); // Reset to first page
        
        window.location.search = params.toString();
    }
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
    
    levelFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    classFilter.addEventListener('change', applyFilters);
});

<?php if ($current_user['role'] === 'admin_bimbel'): ?>
// Modal functions
function openAddStudentModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Siswa Baru';
    document.getElementById('formAction').value = 'add_student';
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
    document.getElementById('student_number').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('studentModal').classList.remove('hidden');
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.add('hidden');
}

function closeViewStudentModal() {
    document.getElementById('viewStudentModal').classList.add('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Update monthly fee based on level
function updateMonthlyFee() {
    const level = document.getElementById('level').value;
    const monthlyFeeInput = document.getElementById('monthly_fee');
    
    const defaultFees = {
        'SD': 200000,
        'SMP': 300000,
        'SMA': 400000
    };
    
    if (level && defaultFees[level]) {
        monthlyFeeInput.value = defaultFees[level];
    }
}

// Student form submission
document.getElementById('studentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('siswa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan sistem'
        });
    });
});

// View student details
function viewStudent(id) {
    const formData = new FormData();
    formData.append('action', 'get_student');
    formData.append('id', id);
    
    fetch('siswa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            const detailsHtml = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Siswa</label>
                        <p class="mt-1 text-sm text-gray-900">${student.student_number}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <p class="mt-1 text-sm text-gray-900">${student.full_name}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenjang</label>
                        <p class="mt-1 text-sm text-gray-900">${student.level}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <p class="mt-1 text-sm text-gray-900">${student.class}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Orang Tua</label>
                        <p class="mt-1 text-sm text-gray-900">${student.parent_name}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. HP Orang Tua</label>
                        <p class="mt-1 text-sm text-gray-900">${student.parent_phone}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <p class="mt-1 text-sm text-gray-900">${student.address}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SPP Bulanan</label>
                        <p class="mt-1 text-sm text-gray-900">Rp ${parseInt(student.monthly_fee).toLocaleString('id-ID')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Pembayaran Bulan Ini</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                ${student.payment_status.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${student.payment_status.status === 'paid' ? 'Lunas' : 'Belum Bayar'}
                            </span>
                            ${student.payment_status.status === 'paid' ? 
                                `<br><small class="text-gray-500">Dibayar: ${new Date(student.payment_status.payment_date).toLocaleDateString('id-ID')}</small>` : 
                                ''}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                ${student.status === 'active' ? 'bg-green-100 text-green-800' : 
                                  student.status === 'inactive' ? 'bg-red-100 text-red-800' : 
                                  'bg-blue-100 text-blue-800'}">
                                ${student.status === 'active' ? 'Aktif' : 
                                  student.status === 'inactive' ? 'Tidak Aktif' : 'Lulus'}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Daftar</label>
                        <p class="mt-1 text-sm text-gray-900">${new Date(student.registration_date).toLocaleDateString('id-ID')}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('studentDetails').innerHTML = detailsHtml;
            document.getElementById('viewStudentModal').classList.remove('hidden');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan sistem'
        });
    });
}

// Edit student
function editStudent(id) {
    const formData = new FormData();
    formData.append('action', 'get_student');
    formData.append('id', id);
    
    fetch('siswa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            
            document.getElementById('modalTitle').textContent = 'Edit Siswa';
            document.getElementById('formAction').value = 'update_student';
            document.getElementById('studentId').value = student.id;
            document.getElementById('student_number').value = student.student_number;
            document.getElementById('full_name').value = student.full_name;
            document.getElementById('level').value = student.level;
            document.getElementById('class').value = student.class;
            document.getElementById('parent_name').value = student.parent_name;
            document.getElementById('parent_phone').value = student.parent_phone;
            document.getElementById('address').value = student.address;
            document.getElementById('monthly_fee').value = student.monthly_fee;
            document.getElementById('status').value = student.status;
            
            document.getElementById('studentModal').classList.remove('hidden');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan sistem'
        });
    });
}

// Delete student
function deleteStudent(id) {
    Swal.fire({
        title: 'Hapus Siswa?',
        text: 'Siswa akan dinonaktifkan, bukan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_student');
            formData.append('id', id);
            
            fetch('siswa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem'
                });
            });
        }
    });
}

// Quick payment function
function quickPayment(studentId) {
    const formData = new FormData();
    formData.append('action', 'get_student');
    formData.append('id', studentId);
    
    fetch('siswa.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            
            // Set student info
            document.getElementById('paymentStudentId').value = studentId;
            document.getElementById('paymentStudentInfo').innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-green-800">
                                ${student.full_name.substring(0, 2).toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${student.full_name}</div>
                        <div class="text-sm text-gray-500">${student.student_number} - ${student.level} ${student.class}</div>
                    </div>
                </div>
            `;
            
            // Set default values
            document.getElementById('payment_month').value = new Date().getMonth() + 1;
            document.getElementById('payment_year').value = new Date().getFullYear();
            document.getElementById('payment_amount').value = student.monthly_fee;
            document.getElementById('payment_method').value = 'cash';
            document.getElementById('payment_notes').value = '';
            
            document.getElementById('paymentModal').classList.remove('hidden');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan sistem'
        });
    });
}

// Payment form submission
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('siswa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem'
                });
            });
        });
    }
});
<?php endif; ?>
</script>

<?php include '../../partials/admin_footer.php'; ?>