<?php
require_once '../../includes/image_path_helper.php';

echo "<h2>Test Image Path from Admin/Masjid Context</h2>";
echo "<p><strong>Current Working Directory:</strong> " . getcwd() . "</p>";

$test_path = "assets/uploads/articles/featured_image_1767107511.png";
echo "<p><strong>Test Path:</strong> " . htmlspecialchars($test_path) . "</p>";

// Test imageExists
$exists = imageExists($test_path);
echo "<p><strong>imageExists():</strong> " . ($exists ? 'YES' : 'NO') . "</p>";

// Test getImagePath
$admin_path = getImagePath($test_path, 'admin');
echo "<p><strong>getImagePath(\$path, 'admin'):</strong> " . htmlspecialchars($admin_path) . "</p>";

// Test if the generated path actually works
$path_exists = file_exists($admin_path);
echo "<p><strong>Generated path exists:</strong> " . ($path_exists ? 'YES' : 'NO') . "</p>";

if ($path_exists) {
    echo "<p><strong>Real path:</strong> " . htmlspecialchars(realpath($admin_path)) . "</p>";
    echo "<p><strong>File size:</strong> " . filesize($admin_path) . " bytes</p>";
}

// Test direct paths
$direct_paths = [
    '../../assets/uploads/articles/featured_image_1767107511.png',
    '../assets/uploads/articles/featured_image_1767107511.png',
    'assets/uploads/articles/featured_image_1767107511.png'
];

echo "<h3>Direct Path Tests</h3>";
foreach ($direct_paths as $path) {
    $exists = file_exists($path) ? 'YES' : 'NO';
    echo "<p><strong>$path:</strong> $exists</p>";
}
?>