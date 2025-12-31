<?php
/**
 * Mentor Management Interface
 * Handles mentor listing, search, filtering, and CRUD operations
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
$page_title = 'Manajemen Mentor';
$page_description = 'Kelola data mentor bimbel Al-Muhajirin';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_mentor':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = addMentor($_POST);
            echo json_encode($result);
            exit;
            
        case 'update_mentor':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = updateMentor($_POST['id'], $_POST);
            echo json_encode($result);
            exit;
            
        case 'delete_mentor':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = deleteMentor($_POST['id']);
            echo json_encode($result);
            exit;
            
        case 'get_mentor':
            $mentor = getMentorById($_POST['id']);
            if ($mentor) {
                // Get rate history
                $mentor['rate_history'] = getMentorRateHistory($mentor['id']);
                // Get payment history
                $mentor['payment_history'] = getMentorPaymentHistory($mentor['id']);
                echo json_encode(['success' => true, 'data' => $mentor]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mentor not found']);
            }
            exit;
            
        case 'update_rate':
            if ($current_user['role'] !== 'admin_bimbel') {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            $result = updateMentorRate($_POST['mentor_id'], $_POST['new_rate'], $_POST['reason'] ?? '');
            echo json_encode($result);
            exit;
            
        case 'get_rate_history':
            $rateHistory = getMentorRateHistory($_POST['mentor_id']);
            echo json_encode(['success' => true, 'data' => $rateHistory]);
            exit;
            
        case 'get_payment_history':
            $paymentHistory = getMentorPaymentHistory($_POST['mentor_id'], $_POST['months'] ?? 6);
            echo json_encode(['success' => true, 'data' => $paymentHistory]);
            exit;
    }
}

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? 'active',
    'teaching_level' => $_GET['teaching_level'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;

// Get mentors data
$mentorsData = getAllMentors($page, $limit, $filters);
$mentors = $mentorsData['data'];
$pagination = $mentorsData['pagination'];

// Get statistics
$stats = getMentorStatistics();

include '../../partials/admin_header.php';
?>

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 main-content content-with-sidebar">
    <div class="p-6">

<!-- Page Content -->
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Mentor Aktif</p>
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
                    <p class="text-sm font-medium text-gray-600">Mentor SD</p>
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
                    <p class="text-sm font-medium text-gray-600">Mentor SMP</p>
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
                    <p class="text-sm font-medium text-gray-600">Mentor SMA</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?php echo ($stats['by_level']['SMA'] ?? 0); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Rate Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Rata-rata Honor per Jam</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    <?php echo formatCurrency($stats['average_rate'] ?? 0); ?>
                </p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
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
                               placeholder="Cari nama mentor, kode, atau telepon..." 
                               value="<?php echo htmlspecialchars($filters['search']); ?>"
                               class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    
                    <!-- Teaching Level Filter -->
                    <select id="teachingLevelFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Jenjang</option>
                        <option value="SD" <?php echo $filters['teaching_level'] === 'SD' ? 'selected' : ''; ?>>SD</option>
                        <option value="SMP" <?php echo $filters['teaching_level'] === 'SMP' ? 'selected' : ''; ?>>SMP</option>
                        <option value="SMA" <?php echo $filters['teaching_level'] === 'SMA' ? 'selected' : ''; ?>>SMA</option>
                    </select>
                    
                    <!-- Status Filter -->
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                        <option value="">Semua Status</option>
                    </select>
                </div>
                
                <!-- Actions -->
                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                <div class="flex space-x-2">
                    <button onclick="openAddMentorModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Mentor</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mentors Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mentor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenjang Ajar
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kontak
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Honor/Jam
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tgl Bergabung
                        </th>
                        <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($mentors)): ?>
                        <tr>
                            <td colspan="<?php echo $current_user['role'] === 'admin_bimbel' ? '7' : '6'; ?>" 
                                class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-chalkboard-teacher text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">Tidak ada mentor ditemukan</p>
                                    <p class="text-sm">Coba ubah filter atau tambah mentor baru</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mentors as $mentor): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">
                                                    <?php echo strtoupper(substr($mentor['full_name'], 0, 2)); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($mentor['full_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($mentor['mentor_code']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($mentor['teaching_levels'] as $level): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php 
                                                switch($level) {
                                                    case 'SD': echo 'bg-green-100 text-green-800'; break;
                                                    case 'SMP': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'SMA': echo 'bg-purple-100 text-purple-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($level); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-phone text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($mentor['phone']); ?>
                                    </div>
                                    <?php if (!empty($mentor['email'])): ?>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                        <?php echo htmlspecialchars($mentor['email']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo formatCurrency($mentor['hourly_rate']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo $mentor['status'] === 'active' 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $mentor['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($mentor['join_date'])); ?>
                                </td>
                                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="viewMentorDetails(<?php echo $mentor['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900 p-1" 
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editMentor(<?php echo $mentor['id']; ?>)" 
                                                class="text-green-600 hover:text-green-900 p-1" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="updateMentorRate(<?php echo $mentor['id']; ?>)" 
                                                class="text-yellow-600 hover:text-yellow-900 p-1" 
                                                title="Update Honor">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <button onclick="deleteMentor(<?php echo $mentor['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900 p-1" 
                                                title="Hapus">
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
                        Showing <span class="font-medium"><?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?></span>
                        to <span class="font-medium"><?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']); ?></span>
                        of <span class="font-medium"><?php echo $pagination['total_records']; ?></span> results
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
                        $start_page = max(1, $pagination['current_page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
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
<!-- Add Mentor Modal -->
<div id="addMentorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Mentor Baru</h3>
                <button onclick="closeAddMentorModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="addMentorForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="full_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                        <input type="tel" name="phone" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label>
                    <textarea name="address" required rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenjang yang Diajar *</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SD" class="mr-2">
                            <span>SD</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SMP" class="mr-2">
                            <span>SMP</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SMA" class="mr-2">
                            <span>SMA</span>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Honor per Jam *</label>
                        <input type="number" name="hourly_rate" required min="0" step="1000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                        <input type="date" name="join_date" value="<?php echo date('Y-m-d'); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddMentorModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Mentor Modal -->
<div id="editMentorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Mentor</h3>
                <button onclick="closeEditMentorModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editMentorForm" class="space-y-4">
                <input type="hidden" name="id" id="editMentorId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="full_name" id="editFullName" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                        <input type="tel" name="phone" id="editPhone" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label>
                    <textarea name="address" id="editAddress" required rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenjang yang Diajar *</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SD" id="editSD" class="mr-2">
                            <span>SD</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SMP" id="editSMP" class="mr-2">
                            <span>SMP</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="teaching_levels[]" value="SMA" id="editSMA" class="mr-2">
                            <span>SMA</span>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Honor per Jam *</label>
                        <input type="number" name="hourly_rate" id="editHourlyRate" required min="0" step="1000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="editStatus" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditMentorModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Rate Modal -->
<div id="updateRateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Honor Mentor</h3>
                <button onclick="closeUpdateRateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="updateRateForm" class="space-y-4">
                <input type="hidden" name="mentor_id" id="rateMentorId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mentor</label>
                    <input type="text" id="rateMentorName" readonly 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Honor Saat Ini</label>
                    <input type="text" id="currentRate" readonly 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Honor Baru *</label>
                    <input type="number" name="new_rate" required min="0" step="1000" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Perubahan</label>
                    <textarea name="reason" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeUpdateRateModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Update Honor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mentor Details Modal -->
<div id="mentorDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Mentor</h3>
                <button onclick="closeMentorDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="mentorDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const teachingLevelFilter = document.getElementById('teachingLevelFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput.value) params.set('search', searchInput.value);
        if (teachingLevelFilter.value) params.set('teaching_level', teachingLevelFilter.value);
        if (statusFilter.value) params.set('status', statusFilter.value);
        
        window.location.href = '?' + params.toString();
    }
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
    
    teachingLevelFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});

<?php if ($current_user['role'] === 'admin_bimbel'): ?>
// Modal functions
function openAddMentorModal() {
    document.getElementById('addMentorModal').classList.remove('hidden');
}

function closeAddMentorModal() {
    document.getElementById('addMentorModal').classList.add('hidden');
    document.getElementById('addMentorForm').reset();
}

function closeEditMentorModal() {
    document.getElementById('editMentorModal').classList.add('hidden');
    document.getElementById('editMentorForm').reset();
}

function closeUpdateRateModal() {
    document.getElementById('updateRateModal').classList.add('hidden');
    document.getElementById('updateRateForm').reset();
}

function closeMentorDetailsModal() {
    document.getElementById('mentorDetailsModal').classList.add('hidden');
}

// Add mentor form submission
document.getElementById('addMentorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_mentor');
    
    fetch('mentor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Mentor berhasil ditambahkan!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan mentor');
    });
});

// Edit mentor form submission
document.getElementById('editMentorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_mentor');
    
    fetch('mentor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Mentor berhasil diupdate!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate mentor');
    });
});

// Update rate form submission
document.getElementById('updateRateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_rate');
    
    fetch('mentor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Honor mentor berhasil diupdate!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate honor');
    });
});

// Edit mentor function
function editMentor(id) {
    fetch('mentor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_mentor&id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const mentor = data.data;
            
            document.getElementById('editMentorId').value = mentor.id;
            document.getElementById('editFullName').value = mentor.full_name;
            document.getElementById('editPhone').value = mentor.phone;
            document.getElementById('editEmail').value = mentor.email || '';
            document.getElementById('editAddress').value = mentor.address;
            document.getElementById('editHourlyRate').value = mentor.hourly_rate;
            document.getElementById('editStatus').value = mentor.status;
            
            // Set teaching levels
            document.getElementById('editSD').checked = mentor.teaching_levels.includes('SD');
            document.getElementById('editSMP').checked = mentor.teaching_levels.includes('SMP');
            document.getElementById('editSMA').checked = mentor.teaching_levels.includes('SMA');
            
            document.getElementById('editMentorModal').classList.remove('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data mentor');
    });
}

// Update mentor rate function
function updateMentorRate(id) {
    fetch('mentor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_mentor&id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const mentor = data.data;
            
            document.getElementById('rateMentorId').value = mentor.id;
            document.getElementById('rateMentorName').value = mentor.full_name;
            document.getElementById('currentRate').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(mentor.hourly_rate);
            
            document.getElementById('updateRateModal').classList.remove('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data mentor');
    });
}

// View mentor details function
function viewMentorDetails(id) {
    fetch('mentor.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_mentor&id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const mentor = data.data;
            
            let content = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Dasar</h4>
                        <div class="space-y-2">
                            <div><span class="font-medium">Kode:</span> ${mentor.mentor_code}</div>
                            <div><span class="font-medium">Nama:</span> ${mentor.full_name}</div>
                            <div><span class="font-medium">Telepon:</span> ${mentor.phone}</div>
                            ${mentor.email ? `<div><span class="font-medium">Email:</span> ${mentor.email}</div>` : ''}
                            <div><span class="font-medium">Alamat:</span> ${mentor.address}</div>
                            <div><span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 rounded-full text-xs ${mentor.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${mentor.status === 'active' ? 'Aktif' : 'Tidak Aktif'}
                                </span>
                            </div>
                            <div><span class="font-medium">Bergabung:</span> ${new Date(mentor.join_date).toLocaleDateString('id-ID')}</div>
                        </div>
                    </div>
                    
                    <!-- Teaching Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Mengajar</h4>
                        <div class="space-y-2">
                            <div><span class="font-medium">Jenjang Ajar:</span> 
                                <div class="flex flex-wrap gap-1 mt-1">
                                    ${mentor.teaching_levels.map(level => `
                                        <span class="px-2 py-1 rounded-full text-xs ${
                                            level === 'SD' ? 'bg-green-100 text-green-800' :
                                            level === 'SMP' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-purple-100 text-purple-800'
                                        }">${level}</span>
                                    `).join('')}
                                </div>
                            </div>
                            <div><span class="font-medium">Honor per Jam:</span> Rp ${new Intl.NumberFormat('id-ID').format(mentor.hourly_rate)}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Rate History -->
                <div class="mt-6">
                    <h4 class="font-medium text-gray-900 mb-3">Riwayat Perubahan Honor</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Honor Lama</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Honor Baru</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Diubah Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            if (mentor.rate_history && mentor.rate_history.length > 0) {
                mentor.rate_history.forEach(history => {
                    content += `
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">${new Date(history.changed_at).toLocaleDateString('id-ID')}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${history.old_rate ? 'Rp ' + new Intl.NumberFormat('id-ID').format(history.old_rate) : '-'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(history.new_rate)}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${history.reason || '-'}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${history.changed_by_name || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                content += `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat perubahan honor</td>
                    </tr>
                `;
            }
            
            content += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="mt-6">
                    <h4 class="font-medium text-gray-900 mb-3">Riwayat Pembayaran (6 Bulan Terakhir)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenjang</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hari Hadir</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Jam</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Honor</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            if (mentor.payment_history && mentor.payment_history.length > 0) {
                mentor.payment_history.forEach(payment => {
                    const monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    content += `
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">${monthNames[payment.month]} ${payment.year}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <span class="px-2 py-1 rounded-full text-xs ${
                                    payment.level === 'SD' ? 'bg-green-100 text-green-800' :
                                    payment.level === 'SMP' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-purple-100 text-purple-800'
                                }">${payment.level}</span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900">${payment.present_days}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">${payment.total_hours}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(payment.total_payment)}</td>
                        </tr>
                    `;
                });
            } else {
                content += `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat pembayaran</td>
                    </tr>
                `;
            }
            
            content += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('mentorDetailsContent').innerHTML = content;
            document.getElementById('mentorDetailsModal').classList.remove('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil detail mentor');
    });
}

// Delete mentor function
function deleteMentor(id) {
    if (confirm('Apakah Anda yakin ingin menghapus mentor ini?')) {
        fetch('mentor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_mentor&id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Mentor berhasil dihapus!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus mentor');
        });
    }
}
<?php endif; ?>
</script>

    </div>
</main>

<?php include '../../partials/admin_footer.php'; ?>