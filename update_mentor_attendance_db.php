<?php
/**
 * Update Mentor Attendance Database Schema
 * Add class support to mentor_attendance table
 */

require_once 'config/config.php';

try {
    echo "Starting mentor attendance database update...\n";
    
    // Check if class column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM mentor_attendance LIKE 'class'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "Adding class column to mentor_attendance table...\n";
        
        // Add class column
        $pdo->exec("ALTER TABLE mentor_attendance ADD COLUMN class VARCHAR(10) NOT NULL DEFAULT 'A' AFTER level");
        echo "✓ Class column added successfully\n";
        
        // Update existing records
        $pdo->exec("UPDATE mentor_attendance SET class = 'A' WHERE class = ''");
        echo "✓ Existing records updated with default class\n";
        
        // Add composite index
        $pdo->exec("ALTER TABLE mentor_attendance ADD INDEX idx_mentor_date_level_class (mentor_id, attendance_date, level, class)");
        echo "✓ Composite index added\n";
        
        // Try to drop old index (ignore if doesn't exist)
        try {
            $pdo->exec("ALTER TABLE mentor_attendance DROP INDEX idx_mentor_date_level");
            echo "✓ Old index removed\n";
        } catch (PDOException $e) {
            echo "ℹ Old index not found (this is normal)\n";
        }
        
    } else {
        echo "✓ Class column already exists, skipping update\n";
    }
    
    echo "\nMentor attendance database update completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}
?>