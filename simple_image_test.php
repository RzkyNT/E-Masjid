<?php
require_once 'includes/image_path_helper.php';

$test_path = "assets/uploads/articles/featured_image_1767107511.png";

echo "<h2>Simple Image Test</h2>";
echo "<p><strong>Test Path:</strong> " . htmlspecialchars($test_path) . "</p>";

// Test imageExists
$exists = imageExists($test_path);
echo "<p><strong>imageExists():</strong> " . ($exists ? 'YES' : 'NO') . "</p>";

// Test admin path
$admin_path = getImagePath($test_path, 'admin');
echo "<p><strong>Admin Path:</strong> " . htmlspecialchars($admin_path) . "</p>";

// Test public path
$public_path = getImagePath($test_path, 'public');
echo "<p><strong>Public Path:</strong> " . htmlspecialchars($public_path) . "</p>";

// Show actual image if exists
if ($exists) {
    echo "<h3>Image Preview</h3>";
    echo "<p><strong>From Root:</strong></p>";
    echo "<img src='" . htmlspecialchars($test_path) . "' alt='Test Image' style='max-width: 200px; border: 1px solid #ccc;'>";
    
    echo "<p><strong>Admin Path (should work from admin context):</strong></p>";
    echo "<p>Path: " . htmlspecialchars($admin_path) . "</p>";
    
    echo "<p><strong>Public Path (should work from pages context):</strong></p>";
    echo "<p>Path: " . htmlspecialchars($public_path) . "</p>";
}

// Test HTML output like in admin panel
echo "<h3>Admin Panel HTML Test</h3>";
$article = ['featured_image' => $test_path, 'title' => 'Test Article'];

if (!empty($article['featured_image']) && imageExists($article['featured_image'])) {
    echo "<p>✅ Condition passed: Image exists</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; display: inline-block;'>";
    echo "<img src='" . htmlspecialchars(getImagePath($article['featured_image'], 'admin')) . "' ";
    echo "alt='" . htmlspecialchars($article['title']) . "' ";
    echo "style='width: 64px; height: 64px; object-fit: cover; border-radius: 8px;'>";
    echo "</div>";
} else {
    echo "<p>❌ Condition failed: Image does not exist or path is empty</p>";
    echo "<div style='width: 64px; height: 64px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #ccc;'>";
    echo "<span style='color: #9ca3af;'>📷</span>";
    echo "</div>";
}
?>