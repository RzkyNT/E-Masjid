<?php
/**
 * Setup Friday Schedule Database
 * Run this file once to create the Friday schedule tables and sample data
 */

require_once 'config/config.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('database/friday_schedule.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $pdo->beginTransaction();
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    $pdo->commit();
    
    echo "✅ Friday Schedule database setup completed successfully!\n\n";
    echo "Created tables:\n";
    echo "- friday_schedules (jadwal sholat Jumat)\n";
    echo "- friday_speakers (daftar imam dan khotib)\n";
    echo "- khutbah_themes (tema khutbah)\n\n";
    echo "Sample data has been inserted.\n\n";
    echo "You can now:\n";
    echo "1. Visit /pages/jadwal_jumat.php to see the public page\n";
    echo "2. Visit /admin/masjid/jadwal_jumat.php to manage schedules\n\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Error setting up Friday Schedule database: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>