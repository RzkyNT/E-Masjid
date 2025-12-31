<?php
/**
 * Monthly Recap Management System
 * Handles monthly financial recap generation, viewing, and management
 */

require_once '../../config/config.php';
require_once '../../includes/bimbel_functions.php';
require_once '../../config/auth.php';

// Check authentication and bimbel access
requireLogin();
requirePermission('bimbel_financial', 'read');

// Only Admin Bimbel and Admin Masjid can access this page
if (!in_array($_SESSION['role'], ['admin_bimbel', 'admin_masjid'])) {
    header('Location: ../../admin/login.php');
    exit;
}

$pageTitle = 'Rekap Bulanan';
$currentPage = 'rekap_bulanan';
$canModify = ($_SESSION['role'] === 'admin_bimbel');

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canModify) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate_recap':
                $result = generateMonthlyRecap(
                    $_POST['month'],
                    $_POST['year'],
                    isset($_POST['force_regenerate'])
                );
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'auto_generate_mentor_payments':
                $result = autoGenerateMentorPayments($_POST['month'], $_POST['year']);
                $message = $result['message'];
                if (!empty($result['errors'])) {
                    $message .= ' Errors: ' . implode(', ', array_slice($result['errors'], 0, 3));
                    if (count($result['errors']) > 3) {
                        $message .= ' and ' . (count($result['errors']) - 3) . ' more...';
                    }
                }
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'delete_recap':
                $result = deleteMonthlyRecap($_POST['month'], $_POST['year']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get filter parameters
$filters = [
    'year' => $_GET['year'] ?? date('Y'),
    'month' => $_GET['month'] ?? ''
];

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;

// Get monthly recaps with filters
$recapData = getAllMonthlyRecaps($page, $limit, $filters);
$recaps = $recapData['data'];
$pagination = $recapData['pagination'];

// Get specific recap for viewing if view_month and view_year are provided
$viewRecap = null;
$viewStats = null;
if (isset($_GET['view_month']) && isset($_GET['view_year'])) {
    $viewRecap = getMonthlyRecap($_GET['view_month'], $_GET['view_year']);
    if ($viewRecap) {
        $viewStats = generateMonthlyStatistics($_GET['view_month'], $_GET['view_year']);
    }
}

include '../../partials/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'partials/bimbel_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-chart-line me-2"></i>
                    <?= $pageTitle ?>
                </h1>
                <?php if ($canModify): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#generateRecapModal">
                            <i class="fas fa-plus me-1"></i>
                            Generate Rekap
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mentorPaymentModal">
                            <i class="fas fa-money-bill me-1"></i>
                            Honor Mentor
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($viewRecap): ?>
                <!-- Detailed Recap View -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Rekap Bulanan - <?= date('F Y', mktime(0, 0, 0, $viewRecap['recap_month'], 1, $viewRecap['recap_year'])) ?>
                        </h5>
                        <a href="?" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Financial Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Saldo Awal</h6>
                                        <h4 class="text-info"><?= formatCurrency($viewRecap['opening_balance']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Total Pemasukan</h6>
                                        <h4 class="text-success"><?= formatCurrency($viewRecap['total_income']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Total Pengeluaran</h6>
                                        <h4 class="text-danger"><?= formatCurrency($viewRecap['total_expense']) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Saldo Akhir</h6>
                                        <h4 class="<?= $viewRecap['closing_balance'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                            <?= formatCurrency($viewRecap['closing_balance']) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Income Breakdown -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Rincian Pemasukan</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>SPP:</span>
                                            <strong class="text-success"><?= formatCurrency($viewRecap['spp_income']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Pendaftaran:</span>
                                            <strong class="text-success"><?= formatCurrency($viewRecap['registration_income']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Lainnya:</span>
                                            <strong class="text-success">
                                                <?= formatCurrency($viewRecap['total_income'] - $viewRecap['spp_income'] - $viewRecap['registration_income']) ?>
                                            </strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong class="text-success"><?= formatCurrency($viewRecap['total_income']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Rincian Pengeluaran</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Honor Mentor:</span>
                                            <strong class="text-danger"><?= formatCurrency($viewRecap['mentor_payment_expense']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Operasional:</span>
                                            <strong class="text-danger"><?= formatCurrency($viewRecap['operational_expense']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Lainnya:</span>
                                            <strong class="text-danger">
                                                <?= formatCurrency($viewRecap['total_expense'] - $viewRecap['mentor_payment_expense'] - $viewRecap['operational_expense']) ?>
                                            </strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong class="text-danger"><?= formatCurrency($viewRecap['total_expense']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <?php if ($viewStats): ?>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Statistik Siswa</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Siswa:</span>
                                                <strong><?= $viewRecap['total_students'] ?></strong>
                                            </div>
                                            <?php foreach ($viewStats['students']['by_level'] as $level => $data): ?>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span><?= $level ?>:</span>
                                                    <span><?= $data['count'] ?> siswa</span>
                                                </div>
                                            <?php endforeach; ?>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <span>Tingkat Pembayaran:</span>
                                                <strong class="text-info"><?= $viewStats['payments']['payment_rate'] ?>%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Statistik Mentor</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Mentor:</span>
                                                <strong><?= $viewRecap['total_mentors'] ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Jam Mengajar:</span>
                                                <strong><?= $viewStats['mentors']['total_hours_taught'] ?> jam</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Honor Terhitung:</span>
                                                <strong class="text-warning"><?= formatCurrency($viewStats['mentors']['total_payment_due']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Tunggakan</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Siswa Belum Bayar:</span>
                                                <strong class="text-warning"><?= $viewStats['outstanding']['total_count'] ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Tunggakan:</span>
                                                <strong class="text-danger"><?= formatCurrency($viewStats['outstanding']['total_amount']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Recap Info -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Dibuat oleh:</strong> <?= htmlspecialchars($viewRecap['generated_by_name'] ?? 'Unknown') ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            <strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($viewRecap['generated_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Recap List View -->
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="year" class="form-label">Tahun</label>
                                <select class="form-select" id="year" name="year">
                                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="month" class="form-label">Bulan</label>
                                <select class="form-select" id="month" name="month">
                                    <option value="">Semua Bulan</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= $filters['month'] == $i ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">
                                    <i class="fas fa-search me-1"></i>
                                    Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recaps Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Daftar Rekap Bulanan
                            <span class="badge bg-secondary ms-2"><?= $pagination['total_records'] ?> rekap</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recaps)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada rekap bulanan ditemukan</p>
                                <?php if ($canModify): ?>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateRecapModal">
                                        <i class="fas fa-plus me-1"></i>
                                        Generate Rekap Pertama
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Periode</th>
                                            <th>Saldo Awal</th>
                                            <th>Pemasukan</th>
                                            <th>Pengeluaran</th>
                                            <th>Saldo Akhir</th>
                                            <th>Siswa</th>
                                            <th>Mentor</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recaps as $recap): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= date('F Y', mktime(0, 0, 0, $recap['recap_month'], 1, $recap['recap_year'])) ?></strong>
                                                </td>
                                                <td class="text-end"><?= formatCurrency($recap['opening_balance']) ?></td>
                                                <td class="text-end text-success"><?= formatCurrency($recap['total_income']) ?></td>
                                                <td class="text-end text-danger"><?= formatCurrency($recap['total_expense']) ?></td>
                                                <td class="text-end">
                                                    <strong class="<?= $recap['closing_balance'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                                                        <?= formatCurrency($recap['closing_balance']) ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center"><?= $recap['total_students'] ?></td>
                                                <td class="text-center"><?= $recap['total_mentors'] ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?view_month=<?= $recap['recap_month'] ?>&view_year=<?= $recap['recap_year'] ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($canModify): ?>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="confirmDelete(<?= $recap['recap_month'] ?>, <?= $recap['recap_year'] ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                                <nav aria-label="Recap pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($pagination['current_page'] > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                            <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php if ($canModify): ?>
    <!-- Generate Recap Modal -->
    <div class="modal fade" id="generateRecapModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Rekap Bulanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="generate_recap">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="month" class="form-label">Bulan *</label>
                                    <select class="form-select" id="month" name="month" required>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Tahun *</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <?php for ($y = date('Y') - 1; $y <= date('Y'); $y++): ?>
                                            <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="force_regenerate" name="force_regenerate">
                                <label class="form-check-label" for="force_regenerate">
                                    Regenerate jika sudah ada
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Rekap akan menghitung semua transaksi keuangan untuk bulan yang dipilih.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Generate Rekap</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mentor Payment Modal -->
    <div class="modal fade" id="mentorPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Auto Generate Honor Mentor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="auto_generate_mentor_payments">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_month" class="form-label">Bulan *</label>
                                    <select class="form-select" id="payment_month" name="month" required>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == (date('n') - 1) ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_year" class="form-label">Tahun *</label>
                                    <select class="form-select" id="payment_year" name="year" required>
                                        <?php for ($y = date('Y') - 1; $y <= date('Y'); $y++): ?>
                                            <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Ini akan membuat transaksi honor untuk semua mentor berdasarkan kehadiran mereka.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Generate Honor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus rekap bulanan ini?</p>
                    <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_recap">
                        <input type="hidden" name="month" id="deleteMonth">
                        <input type="hidden" name="year" id="deleteYear">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Delete confirmation
    function confirmDelete(month, year) {
        document.getElementById('deleteMonth').value = month;
        document.getElementById('deleteYear').value = year;
        var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
    </script>
<?php endif; ?>

<?php include '../../partials/admin_footer.php'; ?>