<?php
// Setup database for Masjid Al-Muhajirin
echo "<h2>Database Setup</h2>";

try {
    // Connect to MySQL without selecting database
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL server<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'masjid_bimbel' created/verified<br>";
    
    // Select the database
    $pdo->exec("USE masjid_bimbel");
    echo "✓ Using database 'masjid_bimbel'<br>";
    
    // Read and execute SQL file
    $sql = file_get_contents('database/masjid_bimbel.sql');
    
    // Remove the CREATE DATABASE and USE statements since we already did that
    $sql = preg_replace('/CREATE DATABASE.*?;/', '', $sql);
    $sql = preg_replace('/USE.*?;/', '', $sql);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $success_count++;
            } catch (PDOException $e) {
                // Ignore errors for existing tables/data
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✓ Executed $success_count SQL statements<br>";
    
    // Test the connection with our config
    require_once 'config/config.php';
    echo "✓ Config file loaded successfully<br>";
    
    // Test a simple query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✓ Found {$result['count']} users in database<br>";
    
    echo "<br><strong style='color: green;'>✓ Database setup completed successfully!</strong><br>";
    echo "<a href='index.php'>Go to main website</a> | <a href='admin/login.php'>Go to admin login</a>";
    
} catch (PDOException $e) {
    echo "<strong style='color: red;'>✗ Database setup failed:</strong><br>";
    echo $e->getMessage();
}
?>