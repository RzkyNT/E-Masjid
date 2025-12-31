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
$students = $studentsData['data'] ?? [];
$totalStudents = $studentsData['total'] ?? 0;
$totalPages = $studentsData['pages'] ?? 1;

// Get statistics
$studentStats = getStudentStatistics();

include '../../partials/admin_header.php';
?>

<!-- Include Bimbel Sidebar -->
<?php include 'partials/bimbel_sidebar.php'; ?>

<!-- Main Content Area -->
<main class="flex-1 main-content content-with-sidebar">
    <div class="p-6">

<!-- Page Header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-medium text-gray-900">Manajemen Siswa</h2>
                    <p class="text-sm text-gray-500">Kelola data siswa bimbel Al-Muhajirin</p>
                </div>
            </div>
            <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                <div class="flex space-x-3">
                    <button onclick="openAddStudentModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Siswa
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $studentStats['total'] ?? 0 ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-check text-green-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Siswa Aktif</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $studentStats['by_status']['active'] ?? 0 ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-graduation-cap text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Lulus</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $studentStats['by_status']['graduated'] ?? 0 ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-plus text-indigo-600 text-2xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pendaftar Baru</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $studentStats['recent_registrations'] ?? 0 ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Siswa</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search']) ?>" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nama, nomor siswa, atau nomor telepon...">
                </div>
            </div>

            <!-- Level Filter -->
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Jenjang</label>
                <select name="level" id="level" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Jenjang</option>
                    <option value="SD" <?= $filters['level'] === 'SD' ? 'selected' : '' ?>>SD</option>
                    <option value="SMP" <?= $filters['level'] === 'SMP' ? 'selected' : '' ?>>SMP</option>
                    <option value="SMA" <?= $filters['level'] === 'SMA' ? 'selected' : '' ?>>SMA</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                    <option value="graduated" <?= $filters['status'] === 'graduated' ? 'selected' : '' ?>>Lulus</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                Daftar Siswa (<?= $totalStudents ?> siswa)
            </h3>
        </div>

        <?php if (empty($students)): ?>
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada siswa ditemukan</h3>
                <p class="text-gray-500 mb-6">Belum ada data siswa atau tidak ada yang sesuai dengan filter.</p>
                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                    <button onclick="openAddStudentModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Siswa Pertama
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Daftar</th>
                            <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">
                                                    <?= strtoupper(substr($student['full_name'], 0, 2)) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($student['full_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($student['student_number']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= $student['level'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($student['class']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-red-100 text-red-800',
                                        'graduated' => 'bg-purple-100 text-purple-800'
                                    ];
                                    $statusLabels = [
                                        'active' => 'Aktif',
                                        'inactive' => 'Tidak Aktif',
                                        'graduated' => 'Lulus'
                                    ];
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$student['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                        <?= $statusLabels[$student['status']] ?? ucfirst($student['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($student['phone']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($student['registration_date'])) ?>
                                </td>
                                <?php if ($current_user['role'] === 'admin_bimbel'): ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button onclick="viewStudent(<?= $student['id'] ?>)" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editStudent(<?= $student['id'] ?>)" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="recordPayment(<?= $student['id'] ?>)" class="text-green-600 hover:text-green-900" title="Catat Pembayaran">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                            <button onclick="deleteStudent(<?= $student['id'] ?>)" class="text-red-600 hover:text-red-900" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?= (($page - 1) * $limit) + 1 ?></span> to <span class="font-medium"><?= min($page * $limit, $totalStudents) ?></span> of <span class="font-medium"><?= $totalStudents ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php if ($page > 1): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i === $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                        <input type="text" id="full_name" name="full_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Jenjang *</label>
                        <select id="level" name="level" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Jenjang</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="class" class="block text-sm font-medium text-gray-700">Kelas *</label>
                        <input type="text" id="class" name="class" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telepon *</label>
                        <input type="tel" id="phone" name="phone" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="parent_name" class="block text-sm font-medium text-gray-700">Nama Orang Tua</label>
                        <input type="text" id="parent_name" name="parent_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="graduated">Lulus</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeStudentModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Modal functions
function openAddStudentModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Siswa Baru';
    document.getElementById('formAction').value = 'add_student';
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
    document.getElementById('studentModal').classList.remove('hidden');
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.add('hidden');
}

function editStudent(id) {
    // Fetch student data and populate form
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_student&id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const student = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Siswa';
            document.getElementById('formAction').value = 'update_student';
            document.getElementById('studentId').value = student.id;
            document.getElementById('full_name').value = student.full_name;
            document.getElementById('level').value = student.level;
            document.getElementById('class').value = student.class;
            document.getElementById('phone').value = student.phone;
            document.getElementById('parent_name').value = student.parent_name || '';
            document.getElementById('status').value = student.status;
            document.getElementById('address').value = student.address || '';
            document.getElementById('studentModal').classList.remove('hidden');
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function deleteStudent(id) {
    if (confirm('Apakah Anda yakin ingin menghapus siswa ini?')) {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_student&id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

// Form submission
document.getElementById('studentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeStudentModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

// Close modal when clicking outside
document.getElementById('studentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStudentModal();
    }
});
</script>

    </div>
</main>

<?php include '../../partials/admin_footer.php'; ?>