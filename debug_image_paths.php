<?php
require_once 'config/config.php';
require_once 'includes/image_path_helper.php';

echo "<h2>Debug Image Paths</h2>";

try {
    $stmt = $pdo->prepare("SELECT id, title, featured_image FROM articles WHERE featured_image IS NOT NULL AND featured_image != '' ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Stored Path</th><th>Admin Path</th><th>Public Path</th><th>File Exists?</th><th>Actual Location</th></tr>";
    
    foreach ($articles as $article) {
        $stored_path = $article['featured_image'];
        $admin_path = getImagePath($stored_path, 'admin');
        $public_path = getImagePath($stored_path, 'public');
        $file_exists = imageExists($stored_path) ? 'YES' : 'NO';
        
        // Cari lokasi file yang sebenarnya
        $possible_locations = [
            $stored_path,
            'admin/masjid/' . $stored_path,
            'admin/' . $stored_path,
            'pages/' . $stored_path
        ];
        
        $actual_location = 'NOT FOUND';
        foreach ($possible_locations as $location) {
            if (file_exists($location)) {
                $actual_location = $location;
                break;
            }
        }
        
        echo "<tr>";
        echo "<td>" . $article['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($article['title'], 0, 30)) . "...</td>";
        echo "<td>" . htmlspecialchars($stored_path) . "</td>";
        echo "<td>" . htmlspecialchars($admin_path) . "</td>";
        echo "<td>" . htmlspecialchars($public_path) . "</td>";
        echo "<td>" . $file_exists . "</td>";
        echo "<td>" . htmlspecialchars($actual_location) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Current Working Directory: " . getcwd() . "</h3>";
    echo "<h3>Directory Structure Check:</h3>";
    
    $directories_to_check = [
        'assets/uploads/articles/' => 'Correct Location',
        'admin/masjid/assets/uploads/articles/' => 'Wrong Location 1',
        'admin/assets/uploads/articles/' => 'Wrong Location 2',
        'pages/assets/uploads/articles/' => 'Wrong Location 3'
    ];
    
    foreach ($directories_to_check as $dir => $description) {
        echo "<h4>$description: $dir</h4>";
        if (is_dir($dir)) {
            $files = scandir($dir);
            $image_files = array_filter($files, function($file) use ($dir) {
                return !in_array($file, ['.', '..', '.htaccess']) && !is_dir($dir . $file);
            });
            
            if (!empty($image_files)) {
                echo "<ul>";
                foreach ($image_files as $file) {
                    $size = filesize($dir . $file);
                    echo "<li>$file (size: $size bytes)</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No image files found.</p>";
            }
        } else {
            echo "<p>Directory does not exist.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>