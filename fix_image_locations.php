<?php
/**
 * Script untuk memindahkan file gambar yang tersimpan di lokasi salah
 * ke lokasi yang benar
 */

require_once 'config/config.php';

echo "<h2>Perbaikan Lokasi File Gambar</h2>";

// Direktori sumber (lokasi salah)
$wrong_locations = [
    'admin/masjid/assets/uploads/articles/',
    'admin/assets/uploads/articles/',
    'pages/assets/uploads/articles/'
];

// Direktori tujuan (lokasi benar)
$correct_location = 'assets/uploads/articles/';

// Pastikan direktori tujuan ada
if (!is_dir($correct_location)) {
    mkdir($correct_location, 0755, true);
    echo "<p>✅ Direktori tujuan dibuat: $correct_location</p>";
}

$moved_files = [];
$errors = [];

// Cari dan pindahkan file dari lokasi yang salah
foreach ($wrong_locations as $wrong_dir) {
    if (is_dir($wrong_dir)) {
        echo "<h3>Memeriksa direktori: $wrong_dir</h3>";
        
        $files = scandir($wrong_dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && !is_dir($wrong_dir . $file)) {
                $source = $wrong_dir . $file;
                $destination = $correct_location . $file;
                
                // Cek apakah file tujuan sudah ada
                if (file_exists($destination)) {
                    echo "<p>⚠️ File sudah ada di tujuan: $file</p>";
                    continue;
                }
                
                // Pindahkan file
                if (rename($source, $destination)) {
                    $moved_files[] = $file;
                    echo "<p>✅ Dipindahkan: $file</p>";
                } else {
                    $errors[] = "Gagal memindahkan: $file";
                    echo "<p>❌ Gagal memindahkan: $file</p>";
                }
            }
        }
    }
}

// Update database jika ada file yang dipindahkan
if (!empty($moved_files)) {
    echo "<h3>Memperbarui Database</h3>";
    
    try {
        $stmt = $pdo->prepare("SELECT id, featured_image FROM articles WHERE featured_image IS NOT NULL AND featured_image != ''");
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $current_path = $article['featured_image'];
            
            // Cek apakah path perlu diperbaiki
            foreach ($wrong_locations as $wrong_dir) {
                if (strpos($current_path, $wrong_dir) === 0) {
                    $filename = basename($current_path);
                    $new_path = $correct_location . $filename;
                    
                    // Update database
                    $update_stmt = $pdo->prepare("UPDATE articles SET featured_image = ? WHERE id = ?");
                    if ($update_stmt->execute([$new_path, $article['id']])) {
                        echo "<p>✅ Database diperbarui untuk artikel ID {$article['id']}: $new_path</p>";
                    } else {
                        echo "<p>❌ Gagal memperbarui database untuk artikel ID {$article['id']}</p>";
                    }
                    break;
                }
            }
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Error database: " . $e->getMessage() . "</p>";
    }
}

// Ringkasan
echo "<h3>Ringkasan</h3>";
echo "<p>File yang dipindahkan: " . count($moved_files) . "</p>";
echo "<p>Error: " . count($errors) . "</p>";

if (!empty($errors)) {
    echo "<h4>Error yang terjadi:</h4>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

// Verifikasi hasil
echo "<h3>Verifikasi Hasil</h3>";
if (is_dir($correct_location)) {
    $files = scandir($correct_location);
    $image_files = array_filter($files, function($file) {
        return !in_array($file, ['.', '..', '.htaccess']) && !is_dir($correct_location . $file);
    });
    
    echo "<p>File gambar di lokasi yang benar: " . count($image_files) . "</p>";
    if (!empty($image_files)) {
        echo "<ul>";
        foreach ($image_files as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
    }
}

echo "<p><strong>Selesai!</strong> Silakan test upload gambar baru dan periksa apakah gambar ditampilkan dengan benar.</p>";
?>