<?php
/**
 * Donation Functions
 * For Masjid Al-Muhajirin Donation System
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Get donation summary for a specific month and year
 */
function getDonationSummary($year = null, $month = null) {
    global $pdo;
    
    if (!$year) $year = date('Y');
    if (!$month) $month = date('n');
    
    try {
        // Get income by category
        $stmt = $pdo->prepare("
            SELECT 
                category,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM donation_transactions 
            WHERE YEAR(transaction_date) = ? 
                AND MONTH(transaction_date) = ?
                AND status = 'verified'
            GROUP BY category
        ");
        
        $stmt->execute([$year, $month]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Initialize categories
        $categories = [
            'operasional' => ['name' => 'Operasional Masjid', 'income' => 0, 'expense' => 0],
            'pembangunan' => ['name' => 'Pembangunan', 'income' => 0, 'expense' => 0],
            'pendidikan' => ['name' => 'Pendidikan', 'income' => 0, 'expense' => 0],
            'sosial' => ['name' => 'Sosial', 'income' => 0, 'expense' => 0],
            'ramadan' => ['name' => 'Program Ramadan', 'income' => 0, 'expense' => 0],
            'umum' => ['name' => 'Infaq Umum', 'income' => 0, 'expense' => 0]
        ];
        
        // Fill with actual data
        foreach ($data as $row) {
            if (isset($categories[$row['category']])) {
                $categories[$row['category']]['income'] = $row['total_income'];
                $categories[$row['category']]['expense'] = $row['total_expense'];
            }
        }
        
        return $categories;
        
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get expense details for transparency report
 */
function getExpenseDetails($year = null, $month = null) {
    global $pdo;
    
    if (!$year) $year = date('Y');
    if (!$month) $month = date('n');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                description,
                amount,
                category
            FROM donation_transactions 
            WHERE YEAR(transaction_date) = ? 
                AND MONTH(transaction_date) = ?
                AND type = 'expense'
                AND status = 'verified'
            ORDER BY amount DESC
        ");
        
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get total income and expense for a month
 */
function getMonthlyTotals($year = null, $month = null) {
    global $pdo;
    
    if (!$year) $year = date('Y');
    if (!$month) $month = date('n');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM donation_transactions 
            WHERE YEAR(transaction_date) = ? 
                AND MONTH(transaction_date) = ?
                AND status = 'verified'
        ");
        
        $stmt->execute([$year, $month]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total_income = $result['total_income'] ?? 0;
        $total_expense = $result['total_expense'] ?? 0;
        $balance = $total_income - $total_expense;
        
        return [
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'balance' => $balance
        ];
        
    } catch (PDOException $e) {
        return [
            'total_income' => 0,
            'total_expense' => 0,
            'balance' => 0
        ];
    }
}

/**
 * Format currency to Indonesian Rupiah
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Get month name in Indonesian
 */
function getIndonesianMonth($month) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    return $months[$month] ?? '';
}

/**
 * Add new donation transaction
 */
function addDonationTransaction($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO donation_transactions 
            (transaction_date, category, type, amount, description, donor_name, donor_phone, payment_method, reference_number, notes, created_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['transaction_date'],
            $data['category'],
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['donor_name'] ?? null,
            $data['donor_phone'] ?? null,
            $data['payment_method'] ?? 'cash',
            $data['reference_number'] ?? null,
            $data['notes'] ?? null,
            $data['created_by'] ?? null,
            $data['status'] ?? 'pending'
        ]);
        
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update monthly summary (call after adding transactions)
 */
function updateMonthlySummary($year = null, $month = null) {
    global $pdo;
    
    if (!$year) $year = date('Y');
    if (!$month) $month = date('n');
    
    try {
        // Delete existing summary
        $stmt = $pdo->prepare("DELETE FROM donation_monthly_summary WHERE year = ? AND month = ?");
        $stmt->execute([$year, $month]);
        
        // Insert updated summary
        $stmt = $pdo->prepare("
            INSERT INTO donation_monthly_summary (year, month, category, total_income, total_expense, net_amount, transaction_count)
            SELECT 
                ? as year,
                ? as month,
                category,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as net_amount,
                COUNT(*) as transaction_count
            FROM donation_transactions 
            WHERE YEAR(transaction_date) = ? 
                AND MONTH(transaction_date) = ?
                AND status = 'verified'
            GROUP BY category
        ");
        
        return $stmt->execute([$year, $month, $year, $month]);
        
    } catch (PDOException $e) {
        return false;
    }
}
?>