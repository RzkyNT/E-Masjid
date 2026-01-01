<?php
/**
 * Doa Cache Warming Script
 * Pre-loads all 108 doa to warm up the cache
 */

require_once 'includes/myquran_api.php';

echo "=== DOA CACHE WARMING ===\n";
echo "Starting cache warming process...\n\n";

$api = new MyQuranAPI();
$successCount = 0;
$errorCount = 0;
$errors = [];

for ($i = 1; $i <= 108; $i++) {
    try {
        $result = $api->getDoa($i);
        if (isset($result['data'])) {
            $successCount++;
            echo "✓ Doa #$i: " . ($result['data']['judul'] ?? 'No title') . "\n";
        } else {
            $errorCount++;
            $errors[] = "Doa #$i: No data returned";
            echo "✗ Doa #$i: No data\n";
        }
        
        // Small delay to avoid overwhelming the API
        usleep(50000); // 0.05 second delay
        
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "Doa #$i: " . $e->getMessage();
        echo "✗ Doa #$i: " . $e->getMessage() . "\n";
        
        // If rate limited, wait longer
        if (strpos($e->getMessage(), 'Rate limit') !== false) {
            echo "  → Waiting 60 seconds due to rate limit...\n";
            sleep(60);
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Successfully cached: $successCount/108 doa\n";
echo "Errors: $errorCount\n";

if (!empty($errors)) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\nCache warming completed!\n";
?>