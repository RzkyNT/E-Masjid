<?php
require_once 'config/config.php';
require_once 'includes/image_path_helper.php';

echo "<h2>Test Image Path Functions</h2>";

// Test dengan path sample
$test_path = "assets/uploads/articles/featured_image_1767107511.png";

echo "<h3>Testing Path: " . htmlspecialchars($test_path) . "</h3>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Function</th><th>Result</th><th>File Exists?</th></tr>";

// Test getImagePath untuk admin
$admin_path = getImagePath($test_path, 'admin');
$admin_exists = file_exists($admin_path) ? 'YES' : 'NO';
echo "<tr><td>getImagePath(\$path, 'admin')</td><td>" . htmlspecialchars($admin_path) . "</td><td>$admin_exists</td></tr>";

// Test getImagePath untuk public
$public_path = getImagePath($test_path, 'public');
$public_exists = file_exists($public_path) ? 'YES' : 'NO';
echo "<tr><td>getImagePath(\$path, 'public')</td><td>" . htmlspecialchars($public_path) . "</td><td>$public_exists</td></tr>";

// Test imageExists
$image_exists = imageExists($test_path) ? 'YES' : 'NO';
echo "<tr><td>imageExists(\$path)</td><td>$image_exists</td><td>-</td></tr>";

echo "</table>";

// Test dengan data dari database
echo "<h3>Testing with Real Database Data</h3>";

try {
    $stmt = $pdo->prepare("SELECT id, title, featured_image FROM articles WHERE featured_image IS NOT NULL AND featured_image != '' LIMIT 3");
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
    if (!empty($articles)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Stored Path</th><th>Admin Path</th><th>Admin File Exists?</th><th>Public Path</th><th>Public File Exists?</th></tr>";
        
        foreach ($articles as $article) {
            $stored = $article['featured_image'];
            $admin_path = getImagePath($stored, 'admin');
            $public_path = getImagePath($stored, 'public');
            $admin_exists = file_exists($admin_path) ? 'YES' : 'NO';
            $public_exists = file_exists($public_path) ? 'YES' : 'NO';
            
            echo "<tr>";
            echo "<td>" . $article['id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($article['title'], 0, 20)) . "...</td>";
            echo "<td>" . htmlspecialchars($stored) . "</td>";
            echo "<td>" . htmlspecialchars($admin_path) . "</td>";
            echo "<td>$admin_exists</td>";
            echo "<td>" . htmlspecialchars($public_path) . "</td>";
            echo "<td>$public_exists</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No articles with featured images found in database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Test direktori
echo "<h3>Directory Structure</h3>";
$directories = [
    'assets/uploads/articles/',
    'admin/masjid/assets/uploads/articles/',
    '../../assets/uploads/articles/',
    '../assets/uploads/articles/'
];

foreach ($directories as $dir) {
    $exists = is_dir($dir) ? 'EXISTS' : 'NOT EXISTS';
    $files = [];
    if (is_dir($dir)) {
        $scan = scandir($dir);
        $files = array_filter($scan, function($file) use ($dir) {
            return !in_array($file, ['.', '..', '.htaccess']) && !is_dir($dir . $file);
        });
    }
    
    echo "<p><strong>$dir</strong>: $exists";
    if (!empty($files)) {
        echo " (" . count($files) . " files)";
    }
    echo "</p>";
}

echo "<h3>Current Working Directory</h3>";
echo "<p>" . getcwd() . "</p>";
?>