<?php
require_once 'config/config.php';

try {
    echo "Checking Friday Schedule tables...\n\n";
    
    // Check if tables exist
    $tables = ['friday_schedules', 'friday_speakers', 'khutbah_themes'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Count records
            $count_stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $count_stmt->fetchColumn();
            echo "   Records: $count\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
        }
    }
    
    echo "\n";
    
    // Check sample data in friday_schedules
    $stmt = $pdo->query("SELECT friday_date, imam_name, khotib_name, khutbah_theme FROM friday_schedules ORDER BY friday_date LIMIT 3");
    $schedules = $stmt->fetchAll();
    
    if (!empty($schedules)) {
        echo "Sample Friday schedules:\n";
        foreach ($schedules as $schedule) {
            echo "- " . $schedule['friday_date'] . ": " . $schedule['imam_name'] . " / " . $schedule['khotib_name'] . "\n";
            echo "  Theme: " . $schedule['khutbah_theme'] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>