<?php
/**
 * Hadits Grid Page - Grid Display like Doa
 * For Masjid Al-Muhajirin Information System
 * 
 * Displays hadits in grid format similar to doa.php
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';
require_once __DIR__ . '/../includes/advanced_search_engine.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();
$searchEngine = new AdvancedSearchEngine($api);

// Handle parameters
$search = $_GET['search'] ?? '';
$collection = $_GET['collection'] ?? 'arbain';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Initialize variables
$content = [];
$searchResults = [];
$haditsData = null;
$error_message = '';
$isSearchMode = !empty($search);
$isDetailMode = !empty($selectedId);

// Define collections
$collections = [
    'arbain' => ['name' => 'Hadits Arbain', 'max' => 42, 'color' => 'green'],
    'bulughul_maram' => ['name' => 'Bulughul Maram', 'max' => 100, 'color' => 'blue'] // Limited for performance
];

try {
    if ($isDetailMode) {
        // Detail mode - show specific hadits
        switch ($collection) {
            case 'arbain':
                if ($selectedId < 1 || $selectedId > 42) $selectedId = 1;
                $haditsData = $api->getHaditsArbain($selectedId);
                break;
            case 'bulughul_maram':
                if ($selectedId < 1 || $selectedId > 1597) $selectedId = 1;
                $haditsData = $api->getHaditsBulughulMaram($selectedId);
                break;
        }
        
        if (!isset($haditsData['data'])) {
            throw new Exception('Hadits tidak ditemukan');
        }
    } elseif ($isSearchMode) {
        // Search mode
        $filters = ['collection' => $collection];
        $searchResults = $searchEngine->search($search, 'hadits', $filters);
        $content = $searchResults['data'] ?? [];
    } else {
        // Grid mode - load hadits from selected collection
        $maxHadits = $collections[$collection]['max'] ?? 42;
        
        for ($i = 1; $i <= $maxHadits; $i++) {
            try {
                switch ($collection) {
                    case 'arbain':
                        $haditsData = $api->getHaditsArbain($i);
                        break;
                    case 'bulughul_maram':
                        $haditsData = $api->getHaditsBulughulMaram($i);
                        break;
                }
                
                if (isset($haditsData['data'])) {
                    $haditsData['data']['id'] = $i;
                    $haditsData['data']['collection'] = $collection;
                    $content[] = $haditsData['data'];
                }
            } catch (Exception $e) {
                // Skip individual errors but log them
                error_log("Failed to load hadits #$i from $collection: " . $e->getMessage());
                continue;
            }
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Hadits grid page error: " . $e->getMessage());
}

// Set page title
$collectionName = $collections[$collection]['name'] ?? 'Hadits';
$page_title = $collectionName . ' - Grid View';
if ($isDetailMode) {
    $page_title = $collectionName . ' #' . $selectedId;
} elseif ($isSearchMode) {
    $page_title = 'Pencarian ' . $collectionName . ': ' . htmlspecialchars($search);
}

$breadcrumb = [
    ['title' => 'Beranda', 'url' => '../index.php'],
    ['title' => 'Hadits', 'url' => 'hadits.php'],
    ['title' => $collectionName, 'url' => '']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta chars