<?php
/**
 * Download Popular Surat Audio Script
 * Downloads audio for the most commonly read surat first
 */

require_once __DIR__ . '/../api/download_audio.php';

// Popular surat that should be downloaded first
$popular_surat = [
    1,   // Al-Fatihah
    2,   // Al-Baqarah
    3,   // Ali 'Imran
    4,   // An-Nisa'
    18,  // Al-Kahf
    36,  // Ya-Sin
    55,  // Ar-Rahman
    67,  // Al-Mulk
    78,  // An-Naba'
    112, // Al-Ikhlas
    113, // Al-Falaq
    114  // An-Nas
];

echo "Starting download of popular surat audio files...\n";
echo "Qari: Misyari Rasyid Al-Afasy\n";
echo "========================================\n\n";

$downloader = new AudioDownloader();
$total_downloaded = 0;
$total_failed = 0;
$total_skipped = 0;

foreach ($popular_surat as $surat_id) {
    echo "Processing Surat {$surat_id}...\n";
    
    try {
        // Get surat info
        $surat_data = $downloader->getSuratData($surat_id);
        if (!$surat_data) {
            echo "  ✗ Failed to get surat data\n";
            continue;
        }
        
        $surat_name = $surat_data['namaLatin'] ?? "Surat {$surat_id}";
        echo "  Surat: {$surat_name}\n";
        echo "  Ayat: " . count($surat_data['ayat']) . "\n";
        
        $downloaded = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($surat_data['ayat'] as $ayat) {
            if (!isset($ayat['audio']['05'])) {
                continue;
            }
            
            $ayat_id = $ayat['nomorAyat'];
            $audio_url = $ayat['audio']['05'];
            $local_file = __DIR__ . "/../assets/audio/quran/surat_{$surat_id}_ayat_{$ayat_id}.mp3";
            
            // Skip if file already exists and is valid
            if (file_exists($local_file) && filesize($local_file) > 1000) {
                $skipped++;
                continue;
            }
            
            echo "    Downloading ayat {$ayat_id}... ";
            
            if ($downloader->downloadFile($audio_url, $local_file)) {
                $downloaded++;
                $total_downloaded++;
                echo "✓\n";
            } else {
                $failed++;
                $total_failed++;
                echo "✗\n";
            }
            
            // Small delay between downloads
            usleep(300000); // 0.3 second
        }
        
        $total_skipped += $skipped;
        
        echo "  Result: {$downloaded} downloaded, {$skipped} skipped, {$failed} failed\n";
        echo "  ----------------------------------------\n";
        
        // Longer delay between surat
        sleep(2);
        
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        echo "  ----------------------------------------\n";
        continue;
    }
}

echo "\n========================================\n";
echo "Download Summary:\n";
echo "Total Downloaded: {$total_downloaded}\n";
echo "Total Skipped: {$total_skipped}\n";
echo "Total Failed: {$total_failed}\n";

// Show final statistics
$stats = $downloader->getStats();
echo "\nOverall Statistics:\n";
echo "Expected files: {$stats['total_expected']}\n";
echo "Downloaded: {$stats['downloaded']}\n";
echo "Percentage: {$stats['percentage']}%\n";
echo "Total size: {$stats['total_size_mb']} MB\n";

echo "\nDone!\n";
?>