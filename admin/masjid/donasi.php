<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';
require_once '../../includes/donation_functions.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'list';

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        $post_action = $_POST['action'] ?? '';
        
        switch ($post_action) {
            case 'add_transaction':
                handleAddTransaction();
                break;
            case 'update_summary':
                handleUpdateSummary();
                break;
        }
    }
}

// Handle add transaction
function handleAddTransaction() {
    global $success_message, $error_message;
    
    $data = [
        'transaction_date' => $_POST['transaction_date'] ?? date('Y-m-d'),
        'category' => $_POST['category'] ?? '',
        'type' => $_POST['type'] ?? '',
        'amount' => floatval($_POST['amount'] ?? 0),
        'description' => $_POST['description'] ?? '',
        'donor_name' => $_POST['donor_name'] ?? null,
        'donor_phone' => $_POST['donor_phone'] ?? null,
        'payment_method' => $_POST['payment_method'] ?? 'cash',
        'reference_number' => $_POST['reference_number'] ?? null,
        'notes' => $_POST['notes'] ?? null,
        'status' => 'verified',
        'created_by' => getCurrentUser()['id']
    ];
    
    if (empty($data['category']) || empty($data['type']) || $data['amount'] <= 0 || empty($data['description'])) {
        $error_message = 'Mohon lengkapi semua field yang wajib diisi.';
        return;
    }
    
    if (addDonationTransaction($data)) {
        // Update monthly summary
        $year = date('Y', strtotime($data['transaction_date']));
        $month = date('n', strtotime($data['transaction_date']));
        updateMonthlySummary($year, $month);
        
        $success_message = 'Transaksi berhasil ditambahkan.';
        
        // Log activity
        logActivity('donation_transaction_add', 'Added ' . $data['type'] . ' transaction: ' . $data['description']);
    } else {
        $error_message = 'Gagal menambahkan transaksi.';
    }
}

// Handle update summary
function handleUpdateSummary() {
    global $success_message, $error_message;
    
    $year = $_POST['year'] ?? date('Y');
    $month = $_POST['month'] ?? date('n');
    
    if (updateMonthlySummary($year, $month)) {
        $success_message = 'Ringkasan bulanan berhasil diperbarui.';
    } else {
        $error_message = 'Gagal memperbarui ringkasan bulanan.';
    }
}

// Get recent transactions
function getRecentTransactions($limit = 20) {
    global $pdo;
    
    try {
        // Convert limit to integer to prevent SQL injection
        $limit = (int) $limit;
        
        $stmt = $pdo->query("
            SELECT dt.*, u.full_name as created_by_name
            FROM donation_transactions dt
            LEFT JOIN users u ON dt.created_by = u.id
            ORDER BY dt.created_at DESC
            LIMIT $limit
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

$recent_transactions = getRecentTransactions();
$current_year = date('Y');
$current_month = date('n');
$donation_summary = getDonationSummary($current_year, $current_month);
$monthly_totals = getMonthlyTotals($current_year, $current_month);

$page_title = 'Kelola Donasi';
$page_description = 'Manajemen donasi dan keuangan masjid';

// Include admin header with sidebar
include '../../partials/admin_header.php';
?>

<!-- Messages -->
        <?php if ($success_message): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Pemasukan</p>
                        <p class="text-2xl font-bold text-green-600"><?= formatCurrency($monthly_totals['total_income']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-full p-3 mr-4">
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Pengeluaran</p>
                        <p class="text-2xl font-bold text-red-600"><?= formatCurrency($monthly_totals['total_expense']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3 mr-4">
                        <i class="fas fa-wallet text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Saldo</p>
                        <p class="text-2xl font-bold <?= $monthly_totals['balance'] >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                            <?= formatCurrency($monthly_totals['balance']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Transaction Form -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-plus mr-2 text-green-600"></i>
                Tambah Transaksi
            </h3>
            
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="add_transaction">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kategori</option>
                        <option value="operasional">Operasional Masjid</option>
                        <option value="pembangunan">Pembangunan</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="sosial">Sosial</option>
                        <option value="ramadan">Program Ramadan</option>
                        <option value="umum">Infaq Umum</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                    <select name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Tipe</option>
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah (Rp)
                    </label>

                    <!-- Input tampilan -->
                    <input
                        type="text"
                        id="amount_display"
                        placeholder="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg
                            focus:ring-blue-500 focus:border-blue-500"
                        inputmode="numeric"
                        autocomplete="off"
                        required
                    >

                    <!-- Nilai asli untuk backend -->
                    <input type="hidden" name="amount" id="amount_real">
                </div>
                    <script>
                    const displayInput = document.getElementById('amount_display');
                    const realInput = document.getElementById('amount_real');

                    displayInput.addEventListener('input', function () {
                        // Ambil hanya angka
                        let value = this.value.replace(/\D/g, '');

                        // Set ke hidden input (tanpa titik)
                        realInput.value = value;

                        // Format ke ribuan (Indonesia)
                        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    });
                    </script>   
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" name="description" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Donasi operasional dari Bapak Ahmad">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Donatur (Opsional)</label>
                    <input type="text" name="donor_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="digital">Pembayaran Digital</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history mr-2 text-blue-600"></i>
                    Transaksi Terbaru
                </h3>
                <form method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="update_summary">
                    <input type="hidden" name="year" value="<?= $current_year ?>">
                    <input type="hidden" name="month" value="<?= $current_month ?>">
                    <button type="submit" class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                        <i class="fas fa-sync mr-1"></i> Update Ringkasan
                    </button>
                </form>
            </div>
            
            <?php if (empty($recent_transactions)): ?>
                <p class="text-gray-500 text-center py-8">Belum ada transaksi</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Kategori</th>
                                <th class="px-4 py-2 text-left">Tipe</th>
                                <th class="px-4 py-2 text-left">Deskripsi</th>
                                <th class="px-4 py-2 text-right">Jumlah</th>
                                <th class="px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td class="px-4 py-2"><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                                    <td class="px-4 py-2">
                                        <span class="capitalize"><?= htmlspecialchars($transaction['category']) ?></span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $transaction['type'] === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $transaction['type'] === 'income' ? 'Masuk' : 'Keluar' ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($transaction['description']) ?></td>
                                    <td class="px-4 py-2 text-right font-medium">
                                        <?= formatCurrency($transaction['amount']) ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $transaction['status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= ucfirst($transaction['status']) ?>
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

<?php include '../../partials/admin_footer.php'; ?>