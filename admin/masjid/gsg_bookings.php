<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../includes/session_check.php';

// Require admin masjid permission
requirePermission('masjid_content', 'read');

$current_user = getCurrentUser();
$action = $_GET['action'] ?? 'list';
$booking_id = $_GET['id'] ?? null;

$success_message = '';
$error_message = '';

// Handle add new booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO gsg_bookings (booking_date, nama, no_telp, alamat, keterangan, status) 
                VALUES (?, ?, ?, ?, ?, 'confirmed')
            ");
            $stmt->execute([
                $_POST['booking_date'],
                $_POST['nama'],
                $_POST['no_telp'],
                $_POST['alamat'],
                $_POST['keterangan']
            ]);
            
            $success_message = 'Booking berhasil ditambahkan dan dikonfirmasi.';
            $action = 'list';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error_message = 'Tanggal tersebut sudah ada booking.';
            } else {
                $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Token keamanan tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE gsg_bookings SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $booking_id]);
            
            $success_message = 'Status booking berhasil diperbarui.';
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Handle delete
if ($action === 'delete' && $booking_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM gsg_bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $success_message = 'Booking berhasil dihapus.';
        $action = 'list';
    } catch (PDOException $e) {
        $error_message = 'Terjadi kesalahan saat menghapus booking: ' . $e->getMessage();
    }
}

// Get bookings for list view
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT *, 
               CASE 
                   WHEN booking_date = CURDATE() THEN 'today'
                   WHEN booking_date < CURDATE() THEN 'past'
                   ELSE 'future'
               END as date_status
        FROM gsg_bookings 
        ORDER BY booking_date DESC, created_at DESC
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll();
}

// Get single booking for detail
if ($action === 'detail' && $booking_id) {
    $stmt = $pdo->prepare("SELECT * FROM gsg_bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $error_message = 'Booking tidak ditemukan.';
        $action = 'list';
    }
}

$page_title = 'Kelola Booking GSG';
$page_description = 'Manajemen booking Gedung Serba Guna';

// Include admin header with sidebar
include '../../partials/admin_header.php';
?>

<!-- Messages -->
            <?php if ($success_message): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: '<?php echo addslashes($success_message); ?>',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: '<?php echo addslashes($error_message); ?>',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <!-- List Bookings -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Daftar Booking GSG</h3>
                        <p class="text-sm text-gray-500 mt-1">Kelola semua booking Gedung Serba Guna</p>
                    </div>
                    <a href="?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-1"></i>Tambah Booking
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                $statusClass = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ][$booking['status']] ?? 'bg-gray-100 text-gray-800';
                                
                                $statusLabel = [
                                    'pending' => 'Menunggu Konfirmasi',
                                    'confirmed' => 'Dikonfirmasi',
                                    'cancelled' => 'Dibatalkan'
                                ][$booking['status']] ?? 'Tidak Diketahui';
                                
                                // Highlight untuk booking pending
                                $rowClass = $booking['status'] === 'pending' ? 'bg-yellow-50' : '';
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?>
                                        <?php if ($booking['date_status'] === 'today'): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Hari Ini
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i>Baru
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['nama']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($booking['no_telp']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                        <?php echo htmlspecialchars($booking['keterangan']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=detail&id=<?php echo $booking['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $booking['id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php elseif ($action === 'add'): ?>
            <!-- Add Booking Form -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Tambah Booking Baru</h3>
                    <a href="?action=list" class="text-blue-600 hover:text-blue-900">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
                
                <form method="POST" action="?action=add" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Booking <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="booking_date" 
                                   name="booking_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama" 
                                   name="nama" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="no_telp" 
                                   name="no_telp" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="alamat" 
                                   name="alamat" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan Acara <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keterangan" 
                                  name="keterangan" 
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <a href="?action=list" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i>Simpan & Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
            
            <?php elseif ($action === 'detail' && isset($booking)): ?>
            <!-- Detail Booking -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Detail Booking</h3>
                    <a href="?action=list" class="text-blue-600 hover:text-blue-900">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Booking</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('d F Y', strtotime($booking['booking_date'])); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <form method="POST" action="?action=update_status&id=<?php echo $booking['id']; ?>" class="mt-1">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <select name="status" onchange="this.form.submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                        </form>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($booking['nama']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $booking['no_telp']); ?>" target="_blank" class="text-green-600 hover:text-green-900">
                                <i class="fab fa-whatsapp mr-1"></i><?php echo htmlspecialchars($booking['no_telp']); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($booking['alamat']); ?></p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Keterangan Acara</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($booking['keterangan']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Dibuat</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('d F Y H:i', strtotime($booking['created_at'])); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Terakhir Diupdate</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('d F Y H:i', strtotime($booking['updated_at'])); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(bookingId) {
            Swal.fire({
                title: 'Hapus Booking?',
                text: 'Booking yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + bookingId;
                }
            });
        }
    </script>

<?php include '../../partials/admin_footer.php'; ?>