<?php
/**
 * Financial Management System
 * Handles income and expense recording, transaction management, and financial reporting
 */

require_once '../../config/config.php';
require_once '../../includes/bimbel_functions.php';
require_once '../../config/auth.php';

// Check authentication and bimbel access
requireLogin();
requirePermission('bimbel_financial', 'read');

// Only Admin Bimbel can access this page
if (!in_array($_SESSION['role'], ['admin_bimbel'])) {
    header('Location: ../../admin/login.php');
    exit;
}

$pageTitle = 'Manajemen Keuangan';
$currentPage = 'keuangan';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_transaction':
                $result = recordFinancialTransaction(
                    $_POST['transaction_type'],
                    $_POST['category'],
                    $_POST['amount'],
                    $_POST['description'],
                    $_POST['transaction_date'],
                    !empty($_POST['reference_id']) ? $_POST['reference_id'] : null,
                    $_POST['notes'] ?? ''
                );
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'update_transaction':
                $transactionId = $_POST['transaction_id'];
                $updateData = [
                    'transaction_type' => $_POST['transaction_type'],
                    'category' => $_POST['category'],
                    'amount' => $_POST['amount'],
                    'description' => $_POST['description'],
                    'transaction_date' => $_POST['transaction_date'],
                    'reference_id' => !empty($_POST['reference_id']) ? $_POST['reference_id'] : null,
                    'notes' => $_POST['notes'] ?? ''
                ];
                $result = updateFinancialTransaction($transactionId, $updateData);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'delete_transaction':
                $result = deleteFinancialTransaction($_POST['transaction_id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Get filter parameters
$filters = [
    'type' => $_GET['type'] ?? '',
    'category' => $_GET['category'] ?? '',
    'month' => $_GET['month'] ?? date('n'),
    'year' => $_GET['year'] ?? date('Y'),
    'search' => $_GET['search'] ?? ''
];

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

// Get transactions with filters
$transactionData = getFinancialTransactions($page, $limit, $filters);
$transactions = $transactionData['data'];
$pagination = $transactionData['pagination'];

// Get financial summary for current month
$financialSummary = getFinancialSummary($filters['month'], $filters['year']);

// Get real-time balance
$balanceInfo = calculateRealTimeBalance();

// Get transaction for editing if edit_id is provided
$editTransaction = null;
if (isset($_GET['edit_id'])) {
    $editTransaction = getFinancialTransactionById($_GET['edit_id']);
}

include '../../partials/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'partials/bimbel_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    <?= $pageTitle ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                        <i class="fas fa-plus me-1"></i>
                        Tambah Transaksi
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Financial Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Pemasukan</h6>
                                    <h4><?= formatCurrency($financialSummary['income']['total'] ?? 0) ?></h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-up fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Pengeluaran</h6>
                                    <h4><?= formatCurrency($financialSummary['expense']['total'] ?? 0) ?></h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-arrow-down fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white <?= ($financialSummary['net_balance'] ?? 0) >= 0 ? 'bg-info' : 'bg-warning' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Saldo Bulanan</h6>
                                    <h4><?= formatCurrency($financialSummary['net_balance'] ?? 0) ?></h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-balance-scale fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white <?= $balanceInfo['current_balance'] >= 0 ? 'bg-primary' : 'bg-secondary' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Saldo Total</h6>
                                    <h4><?= formatCurrency($balanceInfo['current_balance']) ?></h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-wallet fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label for="type" class="form-label">Jenis</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Semua</option>
                                <option value="income" <?= $filters['type'] === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                                <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Semua</option>
                                <option value="spp" <?= $filters['category'] === 'spp' ? 'selected' : '' ?>>SPP</option>
                                <option value="registration" <?= $filters['category'] === 'registration' ? 'selected' : '' ?>>Pendaftaran</option>
                                <option value="operational" <?= $filters['category'] === 'operational' ? 'selected' : '' ?>>Operasional</option>
                                <option value="mentor_payment" <?= $filters['category'] === 'mentor_payment' ? 'selected' : '' ?>>Honor Mentor</option>
                                <option value="utilities" <?= $filters['category'] === 'utilities' ? 'selected' : '' ?>>Utilitas</option>
                                <option value="other" <?= $filters['category'] === 'other' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filters['month'] == $i ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Tahun</label>
                            <select class="form-select" id="year" name="year">
                                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Cari</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($filters['search']) ?>" 
                                   placeholder="Cari deskripsi atau catatan...">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Daftar Transaksi
                        <span class="badge bg-secondary ms-2"><?= $pagination['total_records'] ?> transaksi</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada transaksi ditemukan</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah</th>
                                        <th>Dicatat Oleh</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $transaction['transaction_type'] === 'income' ? 'success' : 'danger' ?>">
                                                    <?= $transaction['transaction_type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $categoryLabels = [
                                                    'spp' => 'SPP',
                                                    'registration' => 'Pendaftaran',
                                                    'operational' => 'Operasional',
                                                    'mentor_payment' => 'Honor Mentor',
                                                    'utilities' => 'Utilitas',
                                                    'other' => 'Lainnya'
                                                ];
                                                echo $categoryLabels[$transaction['category']] ?? $transaction['category'];
                                                ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($transaction['description']) ?>
                                                <?php if (!empty($transaction['notes'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($transaction['notes']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-<?= $transaction['transaction_type'] === 'income' ? 'success' : 'danger' ?>">
                                                    <?= $transaction['transaction_type'] === 'income' ? '+' : '-' ?>
                                                    <?= formatCurrency($transaction['amount']) ?>
                                                </strong>
                                            </td>
                                            <td><?= htmlspecialchars($transaction['recorded_by_name'] ?? 'Unknown') ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?edit_id=<?= $transaction['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDelete(<?= $transaction['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Transaction pagination">
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
        </main>
    </div>
</div>

<!-- Add/Edit Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= $editTransaction ? 'Edit Transaksi' : 'Tambah Transaksi' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?= $editTransaction ? 'update_transaction' : 'add_transaction' ?>">
                    <?php if ($editTransaction): ?>
                        <input type="hidden" name="transaction_id" value="<?= $editTransaction['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Jenis Transaksi *</label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="income" <?= ($editTransaction['transaction_type'] ?? '') === 'income' ? 'selected' : '' ?>>
                                        Pemasukan
                                    </option>
                                    <option value="expense" <?= ($editTransaction['transaction_type'] ?? '') === 'expense' ? 'selected' : '' ?>>
                                        Pengeluaran
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="spp" <?= ($editTransaction['category'] ?? '') === 'spp' ? 'selected' : '' ?>>
                                        SPP
                                    </option>
                                    <option value="registration" <?= ($editTransaction['category'] ?? '') === 'registration' ? 'selected' : '' ?>>
                                        Pendaftaran
                                    </option>
                                    <option value="operational" <?= ($editTransaction['category'] ?? '') === 'operational' ? 'selected' : '' ?>>
                                        Operasional
                                    </option>
                                    <option value="mentor_payment" <?= ($editTransaction['category'] ?? '') === 'mentor_payment' ? 'selected' : '' ?>>
                                        Honor Mentor
                                    </option>
                                    <option value="utilities" <?= ($editTransaction['category'] ?? '') === 'utilities' ? 'selected' : '' ?>>
                                        Utilitas
                                    </option>
                                    <option value="other" <?= ($editTransaction['category'] ?? '') === 'other' ? 'selected' : '' ?>>
                                        Lainnya
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Jumlah *</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       value="<?= $editTransaction['amount'] ?? '' ?>" 
                                       min="1" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                       value="<?= $editTransaction['transaction_date'] ?? date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($editTransaction['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_id" class="form-label">ID Referensi</label>
                                <input type="number" class="form-control" id="reference_id" name="reference_id" 
                                       value="<?= $editTransaction['reference_id'] ?? '' ?>" 
                                       placeholder="ID siswa, mentor, dll (opsional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" 
                                          placeholder="Catatan tambahan (opsional)"><?= htmlspecialchars($editTransaction['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <?= $editTransaction ? 'Update' : 'Simpan' ?>
                    </button>
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
                <p>Apakah Anda yakin ingin menghapus transaksi ini?</p>
                <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_transaction">
                    <input type="hidden" name="transaction_id" id="deleteTransactionId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show add/edit modal if editing
<?php if ($editTransaction): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('addTransactionModal'));
        modal.show();
    });
<?php endif; ?>

// Delete confirmation
function confirmDelete(transactionId) {
    document.getElementById('deleteTransactionId').value = transactionId;
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-format currency input
document.getElementById('amount').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = value;
});
</script>

<?php include '../../partials/admin_footer.php'; ?>