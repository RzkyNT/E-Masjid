<?php
require_once 'includes/image_path_helper.php';

$test_path = "assets/uploads/articles/featured_image_1767107511.png";

echo "<h2>Debug File Path Resolution</h2>";
echo "<p><strong>Test Path:</strong> " . htmlspecialchars($test_path) . "</p>";
echo "<p><strong>Current Working Directory:</strong> " . getcwd() . "</p>";

// Test different path combinations
$test_combinations = [
    $test_path,
    './' . $test_path,
    '../' . $test_path,
    '../../' . $test_path,
    dirname(__DIR__) . '/' . $test_path,
    __DIR__ . '/../' . $test_path,
    realpath('.') . '/' . $test_path,
];

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Test Path</th><th>Exists?</th><th>Real Path</th></tr>";

foreach ($test_combinations as $path) {
    $exists = file_exists($path) ? 'YES' : 'NO';
    $real_path = $exists === 'YES' ? realpath($path) : 'N/A';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "<td>$exists</td>";
    echo "<td>" . htmlspecialchars($real_path) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test imageExists function step by step
echo "<h3>imageExists() Function Debug</h3>";

$stored_path = $test_path;
echo "<p>1. Input: " . htmlspecialchars($stored_path) . "</p>";

$stored_path = ltrim($stored_path, './');
echo "<p>2. After ltrim: " . htmlspecialchars($stored_path) . "</p>";

$project_root = dirname(__DIR__) . '/';
echo "<p>3. Project root: " . htmlspecialchars($project_root) . "</p>";

$full_path = $project_root . $stored_path;
echo "<p>4. Full path: " . htmlspecialchars($full_path) . "</p>";

$exists = file_exists($full_path);
echo "<p>5. File exists: " . ($exists ? 'YES' : 'NO') . "</p>";

if ($exists) {
    echo "<p>6. Real path: " . htmlspecialchars(realpath($full_path)) . "</p>";
}

// List actual files in directory
echo "<h3>Actual Files in Directory</h3>";
$dir = 'assets/uploads/articles/';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $dir . $file;
            $size = filesize($file_path);
            $real_path = realpath($file_path);
            echo "<li><strong>$file</strong> (size: $size bytes, real path: " . htmlspecialchars($real_path) . ")</li>";
        }
    }
    echo "</ul>";
}

// Test from different calling contexts
echo "<h3>Test from Different Contexts</h3>";

// Simulate calling from admin/masjid/
$old_cwd = getcwd();
if (is_dir('admin/masjid')) {
    chdir('admin/masjid');
    echo "<p><strong>From admin/masjid/:</strong></p>";
    echo "<p>CWD: " . getcwd() . "</p>";
    
    $admin_test_paths = [
        '../../' . $test_path,
        '../../assets/uploads/articles/featured_image_1767107511.png'
    ];
    
    foreach ($admin_test_paths as $path) {
        $exists = file_exists($path) ? 'YES' : 'NO';
        echo "<p>$path: $exists</p>";
    }
    
    chdir($old_cwd);
}

// Simulate calling from pages/
if (is_dir('pages')) {
    chdir('pages');
    echo "<p><strong>From pages/:</strong></p>";
    echo "<p>CWD: " . getcwd() . "</p>";
    
    $public_test_paths = [
        '../' . $test_path,
        '../assets/uploads/articles/featured_image_1767107511.png'
    ];
    
    foreach ($public_test_paths as $path) {
        $exists = file_exists($path) ? 'YES' : 'NO';
        echo "<p>$path: $exists</p>";
    }
    
    chdir($old_cwd);
}
?>